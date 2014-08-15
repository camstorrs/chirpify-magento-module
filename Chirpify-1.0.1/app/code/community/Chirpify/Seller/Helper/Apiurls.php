<?php
class Chirpify_Seller_Helper_Apiurls extends Mage_Core_Helper_Abstract
{
    public function getUrlForMethod( $method, $format_type = 'json' )
    {
        return Mage::getStoreConfig('chirpify_seller/options/api_url')
        . '/' 
        . Mage::getStoreConfig('chirpify_seller/options/api_method_' . $method)
        . '/format/' . $format_type . '/';
    }
}