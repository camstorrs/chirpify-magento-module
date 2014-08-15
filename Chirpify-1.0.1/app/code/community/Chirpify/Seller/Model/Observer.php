<?php

class Chirpify_Seller_Model_Observer extends Mage_Adminhtml_Block_Template
{

  public function addStartCampaignButton( $observer )
  {
    $product = Mage::registry( 'current_product' );
    $product = $product ? $product : Mage::getModel( 'catalog/product' )->load( Mage::app()->getRequest()->getParam( 'id' ) );

    # "varies is_saleable methods all fail from backend
    # 'simple','grouped','configurable','virtual','bundle','downloadable'
    # no configurable, since they never show
    # no bundle, since that's something you need to configure
    # no grouped, since its actuall two products
    # no virtual or downloadable, since "how would that work?"
    if( !in_array( $product->getTypeId(), array('simple') ) )
    {
      return;
    }


    $layout = Mage::getSingleton( 'core/layout' );

    $data = new stdClass();

    $data->url = $this->getUrl( 'adminhtml/chirpify/new', array('product_id' => $product->getId()) );


    $block = $layout->createBlock( 'core/text' )
        ->setText( '<script type="text/javascript">
            document.observe("dom:loaded", function() {
              // initially hide all containers for tab content
              //$$("div.tabcontent").invoke("hide");
              
              //var button_container = $$(".content-buttons-placeholder p.form-buttons")[0];
              var button_container = $$(".content-header p")[0];
              var button = new Element("button");
              button.update("<span>Chirpify</span>");
              button.addClassName("scalable");
              button.addClassName("add");
              button.id = "chirpify_seller";
              button_container.insert(button);
              
              var data = ' . json_encode( $data ) . '
              $("chirpify_seller").observe("click", function(){
                document.location = data.url;
              });
            });
            
            
        </script>' );
    $layout->getBlock( 'content' )->insert( $block );
  }

  public function resetTotals( $observer )
  {
    // Mage::Log('START OF: ' . __METHOD__);
    // Mage::Log(array_keys($observer->getData()));
    $info = Mage::registry( 'chirpify_seller_reset_totals' );
    if( !$info )
    {
      // Mage::Log('CHIRPIFY REGISTER NOT SET');
      return;
    }

//         Mage::Log("Resetting Totals");
//         Mage::Log($info);

    $quote_id = $info['quote_id'];
    $store_id = $info['store_id'];

    // $api_helper = Mage::getModel('chirpify_seller/api');
    // $quote      = $api_helper->getQuote($quote_id, $store_id);
    $quote = $observer->getQuote();
    $quote = Mage::helper( 'chirpify_seller/reset' )->resetQuoteTotals( $quote, $info );
    $quote->save();

    // Mage::Log('END OF: ' . __METHOD__);
  }

  public function resetOrderItemPrice( $observer )
  {
    $info = Mage::registry( 'chirpify_seller_reset_totals' );
    if( !$info )
    {
      // Mage::Log('CHIRPIFY REGISTER NOT SET');
      return;
    }
    Mage::helper( 'chirpify_seller/reset' )->resetOrderItemTotals( $observer->getOrderItem(), $info );
  }

}