<?php

class Chirpify_Seller_Model_Apirequest extends Mage_Core_Model_Abstract
{
  private $_api_key;
  private $_curl_handle;
  private $_curl_response;
  private $_header_data;
  
  public function post( $url, $payload = array() )
  {
    $this->_set_chirpify_api_key( $url );
    $payload = http_build_query( $payload );
    $this->_curl_handle = curl_init();
    curl_setopt( $this->_curl_handle, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $this->_curl_handle, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $this->_curl_handle, CURLOPT_URL, $url );
    curl_setopt( $this->_curl_handle, CURLOPT_POST, true );
    curl_setopt( $this->_curl_handle, CURLOPT_HTTPHEADER, $this->_header_data );
    curl_setopt( $this->_curl_handle, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $this->_curl_handle, CURLOPT_RETURNTRANSFER, true );
    $this->_curl_response = curl_exec( $this->_curl_handle );
    curl_close($this->_curl_handle);
    return $this->_curl_response;
  }

  /**
   * Make a HTTP GET request
   *
   * @param string $url
   * @param array $payload
   */
  public function get( $url, $payload = array() )
  {
    $this->_set_chirpify_api_key( $url );
    $url = $url . '?' . http_build_query( $payload );
    $this->_curl_handle = curl_init();
    curl_setopt($this->_curl_handle, CURLOPT_URL, $url);
    curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, $this->_header_data);
    curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, true );
    $this->_curl_response = curl_exec($this->_curl_handle);
    curl_close($this->_curl_handle);
    return $this->_curl_response;
  }

  private function _set_chirpify_api_key()
  {
    $this->_api_key = Mage::getStoreConfig( 'chirpify_seller/options/api_key' );
    $this->_header_data = array(
      'CHIRPIFY-API-KEY: ' . $this->_api_key,
      'Content-Type: application/x-www-form-urlencoded'
    );
  }
 
}