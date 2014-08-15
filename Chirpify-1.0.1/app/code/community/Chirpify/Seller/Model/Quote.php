<?php
class Chirpify_Seller_Model_Quote extends Mage_Sales_Model_Quote
{
    public function getCustomerTaxClassId()
    {
        if(Mage::helper('chirpify_seller')->isApiContext())
        {
            return 0;
        }
        return parent::getCustomerTaxClassId();
    }
}