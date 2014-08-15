<?php

class Chirpify_Seller_Chirpify_ApiController extends Mage_Core_Controller_Front_Action
{
  public function v2Action()
  {
    $this->_validateChirpAPIKey();
    $body = @file_get_contents('php://input');
    $receipt_data = json_decode( $body, true );

    Mage::log("Post: "); Mage::log($receipt_data);
    $response = Mage::getModel( 'chirpify_seller/service_api_v2' )->handle( $receipt_data );

    $this->getResponse()->setHeader( 'Content-Type', 'application/json' )
        ->setBody( Mage::helper( 'core' )->jsonEncode( $response ) );
  }

  private function _parseRequestHeaders()
  {
    $headers = array();
    # For security reasons Magento does not return custom headers.
    # We parse them safely here... outside of the Magento APP scope
    foreach( $_SERVER as $key => $value)
    {
      if( substr ( $key, 0, 5 ) <> 'HTTP_' )
      {
        continue;
      }
      $header = str_replace( ' ', '-', ucwords( str_replace ( '_', ' ', substr($key, 5 ) ) ) );
      $headers[$header] = $value;
    }
    return $headers;
  }

  private function _validateChirpAPIKey()
  {
    $key = Mage::getStoreConfig( 'chirpify_seller/options/api_key' );
    $headers = $this->_parseRequestHeaders();
    if ( isset( $headers['CHIRPIFY-MAGENTO-API-KEY'] ) && $headers['CHIRPIFY-MAGENTO-API-KEY'] == $key )
    {
      return true;
    }
    else
    {
      Mage::log("BAD_KEY_REQUEST");
      die();
    }
  }
  
}
