<?php

class Chirpify_Seller_Model_Service_Api_V1_Order
{

  const PREFIX_SHIPPING = 'address_';
  const DEFAULT_PHONE = '555.555.5555';

  protected $_listing = false;

  private $_customer_firstname;
  private $_customer_lastname;
  private $_customer_email;
  private $_guest_checkout;


  public function create( $info )
  {
    $listing = $this->_initListing( $info );

    $this->_setBuyerInfo( $info );
    #create a cart
    $store_id = Mage::getStoreConfig( 'chirpify_seller/options/orders_in_store_id' );
    $quote_id = $this->_createQuote( $store_id );

    $info = $this->_registerInfo( $info, $quote_id, $store_id, $listing );
    # add a product
    $cart_product_api = $this->_addProductToCart( $quote_id, $store_id, $listing );
    # add customer
    $cart_customer_api = $this->_addCustomerToCart( $quote_id, $info, $store_id );
    # add addresses
    $cart_customer_api = $this->_addAddressesToCart( $cart_customer_api, $quote_id, $info, $store_id );
    # set a shipping method
    $cart_shipping_api = $this->_setShippingMethod( $quote_id, $store_id );
    # create payment method
    $cart_payment_api = $this->_setPaymentMethod( $quote_id, $store_id );
    # place the order
    $order_id = $this->_placeOrder( $quote_id, $store_id );

    // Work around for Mage::Core bug where guest checkout emails not sent on order
    // Mage/Sales/Model/Order.php sendNewOrderEmail() does not validate and send correctly
    // Guest Checkout Order failes to send email and above methdod flags as true
    if ( $this->_guest_checkout )
    {
      $order = Mage::getModel('sales/order')->loadByIncrementId( $order_id );
      $order->sendNewOrderEmail();
      $order->setEmailSent(true);
      $order->save();
    }
    # mark the order as complete for digital downloads
    # unregister the information we set
    Mage::unregister( 'chirpify_seller_reset_totals', $info );

    return $order_id;
  }

  protected function _addAddressesToCart( $cart_customer_api, $quote_id, $info, $store_id )
  {
    $shipping_address = $this->_getShippingAddressFromInfo( $info );
    $billing_address  = $shipping_address;

    $billing_address['email'] = $this->_customer_email;
    $billing_address['mode']  = 'billing';

    $result = $cart_customer_api->setAddresses( $quote_id, array( $billing_address, $shipping_address ), $store_id );

    if( !$result )
    {
      throw new Exception( 'Could not add billing and shipping address to cart' );
    }

    return $cart_customer_api;
  }

  protected function _addProductToCart( $quote_id, $store_id, $listing )
  {
    $cart_product_api = Mage::getModel( 'checkout/cart_product_api' );
    $product_id = $this->_getProductIdFromListing( $listing );
    $result = $cart_product_api->add( $quote_id, array( array( 'product_id' => $product_id ) ), $store_id );
    if( !$result )
    {
      throw new Exception( sprintf( 'Could not Add Product [%d] to cart', $product_id ) );
    }
    return $cart_product_api;
  }

  protected function _addCustomerToCart( $quote_id, $info, $store_id )
  {
    $cart_customer_api = Mage::getModel( 'checkout/cart_customer_api' );
    $customer = $this->_getCustomerArrayFromInfo( $info );
    $result = $cart_customer_api->set( $quote_id, $customer, $store_id );
    if( !$result )
    {
      throw new Exception( sprintf( 'Could not Add Chirpify Customer to cart' ) );
    }
    return $cart_customer_api;
  }

  protected function _createQuote( $store_id )
  {
    $cart_api = Mage::getModel( 'checkout/cart_api' );
    $quote_id = $cart_api->create( $store_id );
    if( !$quote_id )
    {
      throw new Exception( 'Could Not Create Quote' );
    }
    return $quote_id;
  }

  protected function _getWebsiteId()
  {
    throw new Exception( 'Not Used' );
  }

  protected function _getAllWebsiteIds()
  {
    return Mage::getModel( 'core/website' )->getCollection()->getAllIds();
  }

  protected function _getCustomerArrayFromInfo( $info, $customer_mode = false )
  {
    # attempt to load a customer for each
    # and every website until we get one
    $customer = $this->_loadCustomerByEmail( $this->_customer_email );
    # add customer
    # const Mage_Checkout_Model_Type_Onepage::METHOD_GUEST    = 'guest';
    # const Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER = 'register';
    # const Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER = 'customer';
    if( $customer )
    {
      $customer_array = array(
        'customer_id' => $customer->getId(),
        'mode'        => Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER,
        'email'       => $this->_customer_email,
        'firstname'   => $customer->getFirstname(),
        'lastname'    => $customer->getLastname(),
      );
      $this->_guest_checkout = false;
    }
    else
    {
      $this->_guest_checkout = true;
      $customer_array = array(
        'mode'      => Mage_Checkout_Model_Type_Onepage::METHOD_GUEST,
        'email'     => $this->_customer_email,
        'firstname' => $this->_customer_firstname,
        'lastname'  => $this->_customer_lastname,
      );
    }

    return $customer_array;
  }

  protected function _getProductPriceFromListing( $listing )
  {
    return $listing->getPrice();
  }

  protected function _getProductIdFromListing( $listing )
  {
    return $listing->getProductId();
  }

  protected function _getShippingAddressFromInfo( $info )
  {
    $shipping = array(
      'mode'                => 'shipping',
      'is_default_shipping' => 1,
      'is_default_billing'  => 1,
      'email'               => $this->_customer_email,
      'firstname'           => $this->_customer_firstname,
      'lastname'            => $this->_customer_lastname,
      'street'              => $info['buyer']['profile']['address'],
      'city'                => $info['buyer']['profile']['city'],
      'region'              => $info['buyer']['profile']['state_province'],
      'region_id'           => '',
      'postcode'            => $info['buyer']['profile']['zip'],
      'country_id'          => $info['buyer']['profile']['country'],
      'telephone'           => self::DEFAULT_PHONE,
    );
    //'region' => $someFixedState, 'region_id'  => '', 'country_id' => $someFixedCountry,
    return $shipping;
  }

  protected function _initListing( $info )
  {
    if( !$this->_listing )
    {
      $this->_listing = Mage::getModel( 'chirpify_seller/listing' )->load( $info['listing']['meta']['magento_listing_id'] );
    }

    if( !$this->_listing->isActive() )
    {
      Mage::log( "Listing is not active" );
      Mage::throwException( 'Inactive Listing/Listing' );
    }

    return $this->_listing;
  }

  protected function _loadCustomerByEmail( $email )
  {
    foreach( $this->_getAllWebsiteIds() as $id )
    {
      $customer = Mage::getModel( 'customer/customer' )->setWebsiteId( $id )->loadByEmail( $email );
      if( $customer->getId() )
      {
        return $customer;
      }
    }
    return false;
  }

  protected function _placeOrder( $quote_id, $store_id )
  {
    # jigger prices before adding addresses, since the order will
    # get its total from the address object.  This helper is the
    # same used in the collect totals event
    # $quote = Mage::getModel('chirpify_seller/api')->getQuote($quote_id, $store_id);
    # $quote = Mage::helper('chirpify_seller/reset')->resetQuoteTotals($quote);
    # $quote->save();
    $cart_api = Mage::getModel( 'checkout/cart_api' );
    $result = $cart_api->createOrder( $quote_id, $store_id );
    $this->_setOrderStatus( $result );
    //if( !empty( $result ) && $this->_guest_checkout )
    if( !empty( $result ) )
    {
      #set a email for order detail if customer does not exist.
      $this->_setOrderEmail( $result );
      $this->_setInvoice( $result );
    }
    return $result;
  }

  protected function _registerInfo( $info, $quote_id, $store_id, $listing )
  {
    # set price information for post collect totals listener,
    # which allows us to override the pricing of individual items
    # inside the cart
    $info['quote_id']       = $quote_id;
    $info['store_id']       = $store_id;
    $info['price']          = array_key_exists( 'price', $info['listing'] ) ? $info['listing']['price'] : $this->_getProductPriceFromListing( $listing );
    $info['shipping_price'] = array_key_exists( 'shipping_price', $info['listing'] ) ? $info['listing']['shipping_price'] : $this->_getShippingProductPriceFromListing( $listing );
    $info['listing']        = $listing;
    Mage::register( 'chirpify_seller_reset_totals', $info );
    return $info;
  }

  protected function _setShippingMethod( $quote_id, $store_id )
  {
    $cart_shipping_api = Mage::getModel( 'checkout/cart_shipping_api' );
    $shipping_code = 'chirpifyseller_chirpify_seller';
    $result = $cart_shipping_api->setShippingMethod( $quote_id, $shipping_code, $store_id );
    if( !$result )
    {
      throw new Exception( sprintf( 'Could not add %s Shipping Method', $shipping_code ) );
    }
    return $cart_shipping_api;
  }

  protected function _setPaymentMethod( $quote_id, $store_id )
  {
    $cart_payment_api = Mage::getModel( 'checkout/cart_payment_api' );
    $payment_info = array(
      'method' => 'chirpify_seller',
      0        => null, # work around developer mode bug
    );
    $result = $cart_payment_api->setPaymentMethod( $quote_id, $payment_info, $store_id );
    if( !$result )
    {
      throw new Exception( sprintf( 'Could not add %s Payment Method', $payment_info['method'] ) );
    }
  }

  /*
   * need to manage order status bassed on digital vs physical listing
   *
   */
  protected function _setOrderStatus( $increment_id )
  {
    $order = Mage::getModel( 'sales/order' );
    $order->loadByIncrementId( $increment_id );
    $order->addStatusToHistory( Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper( 'chirpify_seller' )->__( 'Placed by Chirpify' ) );
    $order->save();
  }

  private function _setBuyerInfo( $info )
  {
    list( $this->_customer_firstname, $this->_customer_lastname ) = explode( ' ', $info['buyer']['profile']['name'] );
    empty( $this->_customer_firstname ) ? $this->_customer_firstname = $info['buyer']['username'] : null;
    empty( $this->_customer_lastname )  ? $this->_customer_lastname  = $info['buyer']['username'] : null;

    $this->_customer_email = null;
    if( !empty( $info['buyer']['email'] ) )
    {
      $this->_customer_email = $info['buyer']['email'];
    }
    elseif( !empty( $info['buyer']['paypal_email'] ) )
    {
      $this->_customer_email = $info['buyer']['paypal_email'];
    }

    return true;
  }

  private function _setInvoice( $increment_id )
  {
    $order = Mage::getModel( 'sales/order' );
    $order->loadByIncrementId( $increment_id );
    try
    {
      if( !$order->canInvoice() )
      {
        Mage::throwException( Mage::helper( 'core' )->__( 'Cannot create an invoice.' ) );
      }
      $invoice = Mage::getModel( 'sales/service_order', $order )->prepareInvoice();
      if( !$invoice->getTotalQty() )
      {
        Mage::throwException( Mage::helper( 'core' )->__( 'Cannot create an invoice without products.' ) );
      }
      $invoice->setRequestedCaptureCase( Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE );
      $invoice->register();
      $transactionSave = Mage::getModel( 'core/resource_transaction' )
          ->addObject( $invoice )
          ->addObject( $invoice->getOrder() );
      $transactionSave->save();
      Mage::log('Invoice Created');
    }
    catch( Mage_Core_Exception $e )
    {
      //write to log
    }
  }

  private function _setOrderEmail( $increment_id )
  {
    $order = Mage::getModel('sales/order')->loadByIncrementId( $increment_id );
    $order->setCustomerEmail( $this->_customer_email )->save();
  }

}