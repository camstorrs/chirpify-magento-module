<?php

/*
 * @TODO document the detail of each method. since this is API consumption
 * Code is not totally obvious
 *
 */

class Chirpify_Seller_Model_Service_Api_V1 extends Varien_Object
{

  public function handle( $receipt_data )
  {
    $response = new stdClass();
    $result = false;
    $error_message = '';

    try
    {
      $result = $this->callMethod( $receipt_data );
    }
    catch( Exception $e )
    {
      $error_message = $e->getMessage();
    }

    if( $result )
    {
      Mage::log("Order Increment ID");
      Mage::log( $result );
      $response->status = 'ok';
      $response->message = sprintf( 'Order Created [%s]', $result );
    }
    else
    {
      Mage::log("Chirpify Order Failed");
      $response->status = 'failed';
      $response->error_message = $error_message;
    }

    return $response;
  }

  public function callMethod( $receipt_data )
  {
    switch( $receipt_data['method'] )
    {
      case 'order.create':
        $o = Mage::getModel( 'chirpify_seller/service_api_v1_order' );
        return $o->create( $receipt_data );
      default:
        throw new Exception( "Unknown Method" );
    }
    return false;
  }

}