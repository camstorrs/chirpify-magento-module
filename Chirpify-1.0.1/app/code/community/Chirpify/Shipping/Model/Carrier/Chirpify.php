<?php

class Chirpify_Shipping_Model_Carrier_Chirpify extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

  protected $_code = 'chirpifyseller';

  public function collectRates( Mage_Shipping_Model_Rate_Request $request )
  {
    $result = Mage::getModel( 'shipping/rate_result' );
    $method = Mage::getModel( 'shipping/rate_result_method' );

    $method->setCarrier( 'chirpifyseller' );
    $method->setCarrierTitle( $this->getConfigData( 'title' ) );

    $method->setMethod( 'chirpify_seller' );
    $method->setMethodTitle( $this->getConfigData( 'name' ) );

    $method->setPrice( 0 );
    $method->setCost( 0 );

    $info = Mage::registry( 'chirpify_seller_reset_totals' );
    if( $listing = $info['listing'] )
    {
      $method->setPrice( $listing->getData( 'shipping_price' ) );
      Mage::Log( "Setting Shipping Price to: " ); Mage::log($listing->getData( 'shipping_price' ));
    }

    $result->append( $method );

    return $result;
  }

  public function getAllowedMethods()
  {
    return array('chirpify_seller' => $this->getConfigData( 'name' ));
  }

}