<?php
class Chirpify_Seller_Model_Api extends Mage_Checkout_Model_Api_Resource
{
    public function getQuote($quote_id, $store_id)
    {
        return $this->_getQuote($quote_id, $store_id);
    }
}