<?php
class Chirpify_Payment_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code            = 'chirpify_seller';
    protected $_canUseCheckout  = false;
    protected $_canAuthorize    = true;
    protected $_canCapture      = true;
}