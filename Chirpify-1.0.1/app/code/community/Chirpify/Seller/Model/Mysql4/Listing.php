<?php
class Chirpify_Seller_Model_Mysql4_Listing extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('chirpify_seller/listing','chirpify_seller_listing_id');
	}
}
