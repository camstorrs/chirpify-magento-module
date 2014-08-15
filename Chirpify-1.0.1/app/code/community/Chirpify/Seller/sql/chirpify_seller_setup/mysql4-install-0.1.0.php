<?php

$installer = $this;

$installer->startSetup();
/*
 * @todo document fields and add comments for columns
 */
try
{
  $installer->run("
    CREATE TABLE `{$this->getTable('chirpify_seller_listings')}` (
      `chirpify_seller_listing_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `chirpify_api_listing_id`    int(11) unsigned DEFAULT NULL,
      `name` text NOT NULL,
      `price` decimal(10,2) NOT NULL,
      `shipping_price` decimal(10,2) DEFAULT NULL,
      `twitter_text` varchar(140) DEFAULT NULL,
      `facebook_profile_text` text DEFAULT NULL,
      `facebook_page_text` text DEFAULT NULL,
      `qty` int(11) DEFAULT NULL,
      `digital_content_id` varchar(255) DEFAULT NULL,
      `digital_file_name` varchar(255) DEFAULT NULL,
      `active` tinyint(1) DEFAULT 0,
      `date_started` datetime DEFAULT NULL,
      `date_modified` datetime DEFAULT NULL,
      `date_ended` datetime DEFAULT NULL,
      `product_id` int(11) unsigned DEFAULT NULL,
      PRIMARY KEY (`chirpify_seller_listing_id`),
      KEY `product_id_idxfk` (`product_id`),
      CONSTRAINT `chirpify_seller_listings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
  ");
}
catch(Exception $e)
{
  Mage::Log('Table Already exists in '.__FILE__);
}

$installer->endSetup();
//add update to rename table and update column names