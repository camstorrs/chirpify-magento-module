<?php
class Chirpify_Seller_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isApiContext()
    {
        $fullaction = Mage::app()->getRequest()->getRouteName() . 
        Mage::app()->getRequest()->getControllerName() .
        Mage::app()->getRequest()->getActionName();
        return $fullaction == 'chirpify_sellerchirpify_apiv1';
    }
    
    const ROUTE_SERVICE = 'chirpify_seller/chirpify_api/v1';
    public function getFullfillmentUrl()
    {
        return Mage::getModel('core/url')->getUrl(self::ROUTE_SERVICE);;
    }
}