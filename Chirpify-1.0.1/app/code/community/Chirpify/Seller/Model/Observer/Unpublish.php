<?php

class Chirpify_Seller_Model_Observer_Unpublish
{

  protected $_apiError;

  public function unpublish( $observer )
  {
    $listing = $observer->getListing();
    $response = $this->_apiRequest( $listing );
    if( !$response )
    {
      Mage::throwException( 'Could not Unpublish Listing: ' . $this->_apiError );
    }

    $listing->setActive( 0 );
    $listing->setDateModified( date( 'Y-m-d H:i:s', time() ) );
    $listing->setDateEnded( date( 'Y-m-d H:i:s', time() ) );
    $listing->save();
    return $this;
  }

  protected function _apiRequest( $listing )
  {
    $url     = Mage::helper( 'chirpify_seller/apiurls' )->getUrlForMethod( 'update' );
    $payload = array();
    $payload['active']  = 0;
    $payload['id']      = $listing->getChirpifyApiListingId();
    $payload['user_id'] = $listing->getUserId();

    $result = Mage::getModel('chirpify_seller/apirequest')->post( $url, $payload );
    $result_obj = json_decode( $result );
    //Mage::Log($result);
    if( $result_obj->active == false )
    {
      Mage::Log( 'CHIRPIFY - deacticate success' );
      return true;
    }
    else
    {
      Mage::Log( 'CHIRPIFY - Could not deacticate' );

      $this->_apiError = 'Failed to Unpublish';
      return false;
    }
  }

}
