<?php
class Chirpify_Seller_Model_Mysql4_Campaign extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('chirpify_seller/campaign','chirpify_seller_campaign_id');
	}
}
