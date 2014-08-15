<?php

class Chirpify_Seller_Model_Campaign extends Mage_Core_Model_Abstract
{

  public function _construct()
  {
    $this->_init( 'chirpify_seller/campaign' );
  }

  public function activate()
  {
    Mage::dispatchEvent( 'chirpify_seller_campaign_activate', array( 'campaign' => $this ) );
    return $this;
  }

  public function deactivate()
  {     //@todo update unpublish, should be update
    Mage::dispatchEvent( 'chirpify_seller_campaign_deactivate', array( 'campaign' => $this ) );
    return $this;
  }

  public function getProduct()
  {
    return Mage::getModel( 'catalog/product' )->load( $this->getProductId() );
  }

  public function isActive()
  {
    return $this->getActive();
  }

}
