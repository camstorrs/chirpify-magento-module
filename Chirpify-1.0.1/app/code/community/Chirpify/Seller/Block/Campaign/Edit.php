<?php

class Chirpify_Seller_Block_Campaign_Edit extends Mage_Adminhtml_Block_Widget_Form_Container //Mage_Core_Block_Abstract
{

  public function __construct()
  {
    # do not allow a hard delete for campaigns
    # unpublishing a listing will soft delete a campaign
    $this->_blockGroup = 'chirpify_seller';
    $this->_controller = 'campaign';
    parent::__construct();

    $model = Mage::registry( 'chirpify_campaign' );
    #active - boolean field in chirpify table
    $active = $model->getData( 'active' );

    $disabled = $active == 1 ? true : false;
    $this->_addButton( 'activate', array(
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Save and Activate Campaign' ),
      'onclick' => 'Chirpify_Seller.activateForm()',
      'class' => 'add',
      'disabled' => $disabled
        ), 1 );

    $disabled = $active == 1 ? false : true;

    $this->_addButton( 'deactivate', array(
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Deactivate Campaign' ),
      'onclick' => 'Chirpify_Seller.deactivateForm()',
      'class' => 'add',
      'disabled' => $disabled
        ), 1 );


    $this->_formScripts[] = '
        var Chirpify_Seller = {};
        Chirpify_Seller.activateForm = function()
        {
            $(\'campaign_activate\').setValue(1);
            editForm.submit();
        }
        Chirpify_Seller.deactivateForm = function()
        {
            $(\'campaign_deactivate\').setValue(1);
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
    return 'Edit Chirpify Campaign';
  }

}
