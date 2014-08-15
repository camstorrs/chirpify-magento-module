<?php

class Chirpify_Seller_Model_Listing extends Mage_Core_Model_Abstract
{

  public function _construct()
  {
    $this->_init( 'chirpify_seller/listing' );
  }

  public function publish()
  {
    Mage::dispatchEvent( 'chirpify_seller_listing_publish', array( 'listing' => $this ) );
    return $this;
  }

  public function unpublish()
  {     //@todo update unpublish, should be update
    Mage::dispatchEvent( 'chirpify_seller_listing_unpublish', array( 'listing' => $this ) );
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
