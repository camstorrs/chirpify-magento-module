Installing the Package
--------------------------------------------------
The Seller module is a Magento Connect Package

    Chirpify-1.0.1.tgz
    
This is also a tar archive.  If unzipped, the file structure will match Magento's directory structure.  Merging the package directory structure and then clearing Magento cache should be sufficient to manually install the package. 

In addition, Magento Connect packages may be installed via the Magento Connect Admin.

1. Browse to <code>System -&gt; Magento Connect -&gt; Magento Connect Manager</code>

2. Re-enter your Magento Username and Password and click Login

3. Select the Choose File (under Direct Package file upload)

4. Click Upload

You may also sign up for an account on http://magentocommerce.com and list you Magento in the Magento Connect market place.  This will allow users to install it via an extension key.

The extension contains **three** Magento code modules.  The <code>Seller</code> module, which implements the bulk of the application, and a <code>Shipping</code> and <code>Payment</code> module, which implement empty shipping and payment methods for orders place via the API.


Changing Shipping Module, Rewriting Existing
--------------------------------------------------
The code that add a shipping method to the order is at 

    app/code/community/Chirpify/Seller/Model/Service/Api/V1/Order.php

in the <code>\_setShippingMethod</code> method

    protected function _setShippingMethod($quote_id, $store_id)
    {
        $cart_shipping_api  = Mage::getModel('checkout/cart_shipping_api');
        $shipping_code      = 'chirpifyseller_chirpify_seller';
        $result             = $cart_shipping_api->setShippingMethod($quote_id,$shipping_code,$store_id);
        if(!$result)
        {
            throw new Exception(sprintf('Could not add %s Shipping Method',$shipping_code));
        }    
        return $cart_shipping_api;
    }

Magento developer's may create a class rewrite for this method if they wish to have a different shipping method used for a particular installation.  Copying the above code into their class rewrite, and changing <code>$shipping\_code</code>

    //$shipping_code      = 'chirpifyseller_chirpify_seller';
    $shipping_code      = 'carrier_method';
    
will be the quickest way to accomplish this.    

Configuration Values
--------------------------------------------------
The Seller module contains several configuration variables.  Many variables are user settable at 

    System -> Configuration -> Chirpify
    
The defaults for these values, as well as some hard coded items, may be found in 

    app/etc/config.xml
    
at the

    default/chirpify_seller/options

node.  

###&lt;orders\_in\_store\_id&gt;

Configures which Magento store id the API orders will be placed in.


###&lt;api\_url&gt;

Configures the base chirpify API URL

Publish and Unpublish Calls
--------------------------------------------------
The publish and unpublish methods are setup as Magento event observers.  If specific implementations need additional actions to happen during an activation and deactivation, they can listen for the Magento events

    chirpify_seller_listing_publish
    chirpify_seller_listing_unpublish
    
The base observers which make the calls to the Chirpify API are at

    Chirpify/Seller/Model/Observer/Publish.php
    Chirpify/Seller/Model/Observer/Unpublish.php

If an exception is thrown during the execution of these observers, the system will interpret that as a failed API attempt, and the listing will not be activated. 

Database Table
--------------------------------------------------
There's a single database table for storing the listing configurations. This will be automatically created with a Magento Setup Resource

    Chirpify/Seller/sql/chirpify_seller_setup/mysql4-install-0.1.0.php

The base name of this table is 

    chirpify_seller_listings

