<?php

class Chirpify_Seller_Model_Observer_Activate
{

  protected $_apiError;

  public function activates( $observer )
  {
    $campaign = $observer->getCampaign();
    $response = $this->_apiRequest( $campaign );

    if( !$response )
    {
      Mage::throwException( 'Could not activate campaign: ' . $this->_apiError );
    }

    if( $response->api_endpoint == 'new' )
    {
      $campaign->setChirpifyApiCampaignId( $response->id );
      $campaign->setDateStarted( date( 'Y-m-d H:i:s', time() ) );
    }
    else
    {
      $campaign->setDateModified( date( 'Y-m-d H:i:s', time() ) );
    }
    $campaign->setActive( 1 );
    $campaign->save();
    return $this;
  }

  //update request and payload to activate campaign/action
  protected function _apiRequest( $campaign )
  {
    $meta = array();
    $meta['magento_campaign_id'] = $campaign->getCampaignId();
    $meta['remote_action'] = 1; 

    $payload = array();
    $payload['meta'] = $meta;
    $payload['id']   = $campaign->getChirpifyApiCampaignId();
    //update endpoing uris update and new
    !empty( $payload['id'] ) ? $api_endpoint = 'update' : $api_endpoint = 'new';
    $url = Mage::helper( 'chirpify_seller/apiurls' )->getUrlForMethod( $api_endpoint );

    $payload['callback_url']       = Mage::getBaseUrl() . 'chirpify_seller/chirpify_api/v1';
    $payload['user_id']            = $campaign->getUserId();
    $payload['title']              = $campaign->getName();
    $payload['price']              = $campaign->getPrice();
    $payload['shipping_price']     = $campaign->getShippingPrice();
    $payload['quantity']           = $campaign->getQty();
    $payload['digital_file_name']  = $campaign->getDigitalFileName();
    $payload['digital_content_id'] = $campaign->getDigitalContentId();
    $payload['active']             = 1;
    $payload['is_physical']        = 1;
    $payload['is_donation']        = 0;

    if( !empty( $payload['digital_file_name'] ) && !empty( $payload['digital_content_id'] ) )
    {
      $payload['is_physical'] = 0;
    }

    $product = $campaign->getProduct();
    $image = Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );

    $payload['photo']        = $image;
    $result = Mage::getModel('chirpify_seller/apirequest')->post( $url, $payload );
    $result_obj = json_decode( $result );

    if( $result_obj->active == 1 )
    {
      Mage::Log( 'Chirpify campaign activated - Campaign Action ID ' . $result_obj->id );
      $result_obj->api_endpoint = $api_endpoint;
      return $result_obj;
    }
    else
    {
      Mage::Log( 'Chirpify campaign NOT activated ' );
      Mage::log($result);
      $this->_apiError = 'Chirpify did not return the expected response, check system.log';
      return false;
    }
  }

}