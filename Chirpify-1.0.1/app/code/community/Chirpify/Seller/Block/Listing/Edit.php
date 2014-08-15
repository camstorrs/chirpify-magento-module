<?php

class Chirpify_Seller_Block_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container //Mage_Core_Block_Abstract
{

  public function __construct()
  {
    # do not allow a hard delete for listings
    # unpublishing a listing will soft delete a listing
    # $this->_objectId = 'listing_id';
    $this->_blockGroup = 'chirpify_seller';
    $this->_controller = 'listing';
    parent::__construct();

    $model = Mage::registry( 'chirpify_listing' );
    #active - boolean field in chirpify table
    $active = $model->getData( 'active' );

    $disabled = $active == 1 ? true : false;
    $this->_addButton( 'publish', array(
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Save and Publish Listing' ),
      'onclick' => 'Chirpify_Seller.publishForm()',
      'class' => 'add',
      'disabled' => $disabled
        ), 1 );

    $disabled = $active == 1 ? false : true;

    $this->_addButton( 'unpublish', array(
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Unpublish Listing' ),
      'onclick' => 'Chirpify_Seller.unpublishForm()',
      'class' => 'add',
      'disabled' => $disabled
        ), 1 );


    $this->_formScripts[] = '
        var Chirpify_Seller = {};
        Chirpify_Seller.publishForm = function()
        {
            $(\'listing_publish\').setValue(1);
            editForm.submit();
        }
        Chirpify_Seller.unpublishForm = function()
        {
            $(\'listing_unpublish\').setValue(1);
            editForm.submit();
        }        
        ';
  }

  public function getFormActionUrl()
  {
    return $this->getUrl( '*/*/save' );
  }

  public function getHeaderText()
  {
    return 'Edit Chirpify Listing';
  }

}
