<?php
class Chirpify_Seller_Block_Listing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('listing_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('chirpify_seller')->__('Listing Information'));
    }
    
}