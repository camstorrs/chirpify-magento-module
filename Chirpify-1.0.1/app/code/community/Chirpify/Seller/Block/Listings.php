<?php
class Chirpify_Seller_Block_Campaigns extends Mage_Adminhtml_Block_Widget_Grid_Container
{

  public function __construct()
  {
    $this->_controller = 'campaigns';
    $this->_blockGroup = 'chirpify_seller';
    $this->_headerText = Mage::helper( 'chirpify_seller' )->__( 'Manage Chirpify Campaigns' );
    parent::__construct();
    $this->_updateButton( 'add', 'label', Mage::helper( 'cms' )->__( 'Add New Chirpify Campaign' ) );
  }

}