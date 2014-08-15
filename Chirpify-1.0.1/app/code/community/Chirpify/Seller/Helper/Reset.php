<?php

class Chirpify_Seller_Helper_Reset extends Mage_Core_Helper_Abstract
{

  public function resetQuoteTotals( $quote, $info )
  {
    $item = $quote->getItemsCollection()->getFirstItem();
    $grand_total = $info['price'] + $info['campaign']->getPriceShipping();

    $item->setData( 'price', $info['price'] );
    $item->setData( 'base_price', $info['price'] );
    $item->setData( 'row_total', $info['price'] );
    $item->setData( 'base_row_total', $info['price'] );
    $item->setData( 'price_incl_tax', $info['price'] );
    $item->setData( 'base_price_incl_tax', $info['price'] );
    $item->setData( 'row_total_incl_tax', $info['price'] );
    $item->setData( 'base_row_total_incl_tax', $info['price'] );
    $item->save();

    $quote->setData( 'grand_total', $grand_total );
    $quote->setData( 'base_grand_total', $grand_total );
    $quote->setData( 'subtotal', $info['price'] );
    $quote->setData( 'base_subtotal', $info['price'] );
    $quote->setData( 'subtotal_with_discount', $info['price'] );
    $quote->setData( 'base_subtotal_with_discount', $info['price'] );

    // Mage::Log($quote->getData());

    foreach( $quote->getAllAddresses() as $address )
    {
      $address->setData( 'subtotal', $info['price'] );
      $address->setData( 'base_subtotal', $info['price'] );
      $address->setData( 'grand_total', $grand_total );
      $address->setData( 'base_grand_total', $grand_total );
      $address->setData( 'subtotal_incl_tax', $info['price'] );
      $address->save();
      // Mage::Log($address->getData());
    }

    return $quote;
  }

  public function resetOrderItemTotals( $order_item, $info )
  {
    $order_item->setData( 'price', $info['price'] );
    $order_item->setData( 'base_price', $info['price'] );
    $order_item->setData( 'original_price', $info['price'] );
    $order_item->setData( 'base_original_price', $info['price'] );
  }

}