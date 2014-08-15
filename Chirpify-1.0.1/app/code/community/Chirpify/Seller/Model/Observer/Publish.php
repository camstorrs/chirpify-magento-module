<?php

class Chirpify_Seller_Model_Observer_Publish
{

  protected $_apiError;

  public function publish( $observer )
  {
    $listing = $observer->getListing();
    $response = $this->_apiRequest( $listing );

    if( !$response )
    {
      Mage::throwException( 'Could not Publish Listing: ' . $this->_apiError );
    }

    if( $response->api_endpoint == 'new' )
    {
      $listing->setChirpifyApiListingId( $response->id );
      $listing->setDateStarted( date( 'Y-m-d H:i:s', time() ) );
    }
    else
    {
      $listing->setDateModified( date( 'Y-m-d H:i:s', time() ) );
    }
    $listing->setActive( 1 );
    $listing->save();
    return $this;
  }

  protected function _apiRequest( $listing )
  {
    $meta = array();
    $meta['twitter_text']             = $listing->twitter_text;
    $meta['facebook_profile_text']    = $listing->facebook_profile_text;
    $meta['facebook_page_text']       = $listing->facebook_page_text;
    $meta['magento_listing_id']       = $listing->getListingId();
    $meta['remote_listing']           = 1;
    !empty( $meta['twitter_text'] )          ? $meta['twitter_enabled']          = 1 : null; // : null; #$listing->getTweetText();
    !empty( $meta['facebook_profile_text'] ) ? $meta['facebook_profile_enabled'] = 1 : null;
    !empty( $meta['facebook_profile_text'] ) ? $meta['facebook_profile_enabled'] = 1 : null;
    !empty( $meta['facebook_page_text'] )    ? $meta['facebook_page_enabled']    = 1 : null;

    $payload = array();
    $payload['meta'] = $meta;
    $payload['id']   = $listing->getChirpifyApiListingId();

    !empty( $payload['id'] ) ? $api_endpoint = 'update' : $api_endpoint = 'new';
    $url = Mage::helper( 'chirpify_seller/apiurls' )->getUrlForMethod( $api_endpoint );

    $payload['callback_url']       = Mage::getBaseUrl() . 'chirpify_seller/chirpify_api/v1';
    $payload['user_id']            = $listing->getUserId();
    $payload['title']              = $listing->getName();
    $payload['price']              = $listing->getPrice();
    $payload['shipping_price']     = $listing->getShippingPrice();
    $payload['quantity']           = $listing->getQty();
    $payload['digital_file_name']  = $listing->getDigitalFileName();
    $payload['digital_content_id'] = $listing->getDigitalContentId();
    $payload['active']             = 1;
    $payload['is_physical']        = 1;
    $payload['is_donation']        = 0;

    if( !empty( $payload['digital_file_name'] ) && !empty( $payload['digital_content_id'] ) )
    {
      $payload['is_physical'] = 0;
    }

    $product = $listing->getProduct();
    $image = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );

    $payload['photo']        = $image;
    $result = Mage::getModel('chirpify_seller/apirequest')->post( $url, $payload );
    $result_obj = json_decode( $result );

    if( $result_obj->active == 1 )
    {
      Mage::Log( 'Chirpify Published - Listing ID ' . $result_obj->id );
      $result_obj->api_endpoint = $api_endpoint;
      return $result_obj;
    }
    else
    {
      Mage::Log( 'CHIRPIFY Listing NOT Published ' );
      Mage::log($result);
      $this->_apiError = 'Chirpify did not return the expected response, check system.log';
      return false;
    }
  }

}