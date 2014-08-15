<?php

class Chirpify_Seller_Model_Observer_Deactivate
{

  protected $_apiError;

  public function deactivate( $observer )
  {
    $campaign = $observer->getCampaign();
    $response = $this->_apiRequest( $campaign );
    if( !$response )
    {
      Mage::throwException( 'Could not deactivate campaign: ' . $this->_apiError );
    }

    $campaign->setActive( 0 );
    $campaign->setDateModified( date( 'Y-m-d H:i:s', time() ) );
    $campaign->setDateEnded( date( 'Y-m-d H:i:s', time() ) );
    $campaign->save();
    return $this;
  }

  protected function _apiRequest( $campaign )
  {
    //update api reference for update URI should be a put
    $url     = Mage::helper( 'chirpify_seller/apiurls' )->getUrlForMethod( 'update' );
    $payload = array();
    $payload['active']  = 0;
    $payload['id']      = $campaign->getChirpifyApiCampaignId();
    $payload['user_id'] = $campaign->getUserId();

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

      $this->_apiError = 'Failed to Activate';
      return false;
    }
  }

}
