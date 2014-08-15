<?php

class Chirpify_Seller_Block_Listing_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{

  /**
   * Set grid params
   *
   */
  public function __construct()
  {
    parent::__construct();
    $this->setId( 'product_grid' );
    $this->setDefaultSort( 'linked_entity_id' );
    $this->setUseAjax( true );
  }

  /**
   * Retirve currently edited product model
   *
   * @return Mage_Catalog_Model_Product
   */
  protected function _getProduct()
  {
    return Mage::registry( 'current_product' );
  }

  /**
   * Prepare collection
   *
   * @return Mage_Adminhtml_Block_Widget_Grid
   */
  protected function _prepareCollection()
  {
    //$store = $this->_getStore();
    $store = new Varien_Object();
    $collection = Mage::getModel( 'catalog/product' )->getCollection()
        ->addAttributeToSelect( 'sku' )
        ->addAttributeToSelect( 'name' )
        ->addAttributeToSelect( 'attribute_set_id' )
        ->addAttributeToSelect( 'type_id' );

    if( Mage::helper( 'catalog' )->isModuleEnabled( 'Mage_CatalogInventory' ) )
    {
      $collection->joinField( 'qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left' );
    }
    if( $store->getId() )
    {
      //$collection->setStoreId($store->getId());
      $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
      $collection->addStoreFilter( $store );
      $collection->joinAttribute( 'name',        'catalog_product/name',       'entity_id', null, 'inner', $adminStore );
      $collection->joinAttribute( 'custom_name', 'catalog_product/name',       'entity_id', null, 'inner', $store->getId() );
      $collection->joinAttribute( 'status',      'catalog_product/status',     'entity_id', null, 'inner', $store->getId() );
      $collection->joinAttribute( 'visibility',  'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId() );
      $collection->joinAttribute( 'price',       'catalog_product/price',      'entity_id', null, 'left',  $store->getId() );
    }
    else
    {
      $collection->addAttributeToSelect( 'price' );
      $collection->joinAttribute( 'status',     'catalog_product/status',     'entity_id', null, 'inner' );
      $collection->joinAttribute( 'visibility', 'catalog_product/visibility', 'entity_id', null, 'inner' );
    }

    $collection->addFieldToFilter( 'type_id', 'simple' );
    $this->setCollection( $collection );

    parent::_prepareCollection();
    $this->getCollection()->addWebsiteNamesToResult();
    return $this;
  }

  /**
   * Add columns to grid
   *
   * @return Mage_Adminhtml_Block_Widget_Grid
   */
  protected function _prepareColumns()
  {
    $this->addColumn( 'linked_in_products', array(
      'header_css_class' => 'a-center',
      'type'   => 'checkbox',
      'name'   => 'in_products',
      'values' => $this->getSelectedProduct(),
      'align'  => 'center',
      'index'  => 'entity_id'
    ) );


    $this->addColumn( 'linked_entity_id', array(
      'header'   => Mage::helper( 'catalog' )->__( 'ID' ),
      'sortable' => true,
      'width'    => 60,
      'index'    => 'entity_id'
    ) );

    $this->addColumn( 'linked_name', array(
      'header' => Mage::helper( 'catalog' )->__( 'Name' ),
      'index'  => 'name'
    ) );

//         $this->addColumn('linked_type', array(
//             'header'    => Mage::helper('catalog')->__('Type'),
//             'width'     => 100,
//             'index'     => 'type_id',
//             'type'      => 'options',
//             'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
//         ));

    $sets = Mage::getResourceModel( 'eav/entity_attribute_set_collection' )
        ->setEntityTypeFilter( Mage::getModel( 'catalog/product' )->getResource()->getTypeId() )
        ->load()
        ->toOptionHash();

    $this->addColumn( 'linked_set_name', array(
      'header'  => Mage::helper( 'catalog' )->__( 'Attrib. Set Name' ),
      'width'   => 130,
      'index'   => 'attribute_set_id',
      'type'    => 'options',
      'options' => $sets,
    ) );

    $this->addColumn( 'linked_status', array(
      'header'  => Mage::helper( 'catalog' )->__( 'Status' ),
      'width'   => 90,
      'index'   => 'status',
      'type'    => 'options',
      'options' => Mage::getSingleton( 'catalog/product_status' )->getOptionArray(),
    ) );

    $this->addColumn( 'linked_visibility', array(
      'header'  => Mage::helper( 'catalog' )->__( 'Visibility' ),
      'width'   => 90,
      'index'   => 'visibility',
      'type'    => 'options',
      'options' => Mage::getSingleton( 'catalog/product_visibility' )->getOptionArray(),
    ) );

    $this->addColumn( 'linked_sku', array(
      'header' => Mage::helper( 'catalog' )->__( 'SKU' ),
      'width'  => 80,
      'index'  => 'sku'
    ) );

    $this->addColumn( 'linked_price', array(
      'header'        => Mage::helper( 'catalog' )->__( 'Price' ),
      'type'          => 'currency',
      'currency_code' => (string) Mage::getStoreConfig( Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE ),
      'index'         => 'price'
    ) );

    return parent::_prepareColumns();
  }

  /**
   * Rerieve grid URL
   *
   * @return string
   */
  public function getGridUrl()
  {
    return $this->getData( 'grid_url' ) 
      ? $this->getData( 'grid_url' )
      : $this->getUrl( '*/*/relatedGrid', array('_current' => true) );
  }
  
  protected function _getListing()
  {
    return Mage::registry( 'current_listing' );
  }
  
  /**
   * Retrieve related products
   *
   * @return array
   */
  public function getSelectedProduct()
  {
    $listing = $this->_getListing();
    $listing_product_id = $listing->getProductId();
    if( !empty( $listing_product_id ) )
    {
      //Mage::log("Yes getSelectedProduct: ");
      //Mage::log( $listing_product_id );
      return array( $listing_product_id );
    }

    $product_id = Mage::app()->getRequest()->getParam( 'product_id' );
    if( $product_id )
    {
      //Mage::log("Yes product_id");
      //Mage::log( $product_id );
      return array( $product_id );
    }
    //Mage::log("No getSelectedProduct");
    return array();
  }

}
