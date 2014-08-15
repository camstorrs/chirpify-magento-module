<?php

class Chirpify_Seller_Block_Campaign_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

  private $_chirp_user_id;
  private $_chirp_user;

  protected function _prepareForm()
  {
    $model = Mage::registry( 'chirpify_campaign' );

    $form = new Varien_Data_Form();

    $this->_setChirpUser();

    $form->setHtmlIdPrefix( 'campaign_' );

    $fieldset = $form->addFieldset( 'base_fieldset', array('legend' => Mage::helper( 'chirpify_seller' )->__( 'Campaign Information' )) );

    if( $model->getId() )
    {
      $fieldset->addField( 'action_id', 'hidden', array( 'name' => 'action_id' ) );
    }

    $fieldset->addField( 'name', 'text', array(
      'name' => 'name',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Campaign Name' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Campaign Name' ),
      'required' => true,
    ) );

    $dateFormatIso = Mage::app()->getLocale()->getDateFormat( Mage_Core_Model_Locale::FORMAT_TYPE_SHORT );
    $fieldset->addField( 'price', 'text', array(
      'name' => 'price',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Offered At Price' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Offered At Price' ),
      'required' => true,
    ) );

    $fieldset->addField( 'shipping_price', 'text', array(
      'name' => 'shipping_price',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Flat Shipping Rate' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Flat Shipping Rate' ),
      'required' => true,
    ) );

    $fieldset->addField( 'qty', 'text', array(
      'name' => 'qty',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Quantity to Sell' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Quantity to Sell' ),
      'required' => false,
    ) );

    $fieldset->addField( 'campaign_hashtag', 'text', array(
      'name' => 'campaign_hashtag',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Campaign Hashtag' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Campaign Hashtag' ),
      'required' => true
    ) );
      // 'after_element_html' =>  "<strong>@$this->_twitter_handle</strong><br /> Reply \"buy\" via @Chirpify chrp.in/",

    $fieldset->addField( 'description', 'textarea', array(
      'name'     => 'description',
      'label'    => Mage::helper( 'chirpify_seller' )->__( 'Description' ),
      'title'    => Mage::helper( 'chirpify_seller' )->__( 'Description' ),
      'required' => false,
    ) );

    $file_display = '';
    $current_file = $model->getDigitalFileName();

//    if( !empty( $current_file ) )
//    {
//      $file_display = 'Upload new file';
//      $fieldset->addField('note', 'note', array(
//        'label' => Mage::helper('chirpify_seller')->__( 'Current File Saved' ),
//        'text'  => Mage::helper('chirpify_seller')->__( $current_file ),
//      ));
//    }
//
//     $fieldset->addField( 'digital_file', 'checkbox', array(
//      'label' => Mage::helper( 'chirpify_seller' )->__( 'Digital item' ),
//      'name' => 'digital_file',
//      'checked' => false,
//      'onclick' => "", //add js to display upload dialog
//      'onchange' => "",
//      'value' => '1',
//      'disabled' => false,
//    ) );
//
//    $fieldset->addField( 'digital_file_name', 'file', array(
//      'name'      => 'digital_file_name',
//      'label'     => Mage::helper('chirpify_seller')->__('Upload File (digital campaigns only)'),
//      'required'  => false,
//      'after_element_html' => '<br />',
//      'note'      => $file_display,
//     ) );

     $fieldset->addField( 'product_id', 'hidden', array(
      'name'     => 'product_id',
      'label'    => Mage::helper( 'chirpify_seller' )->__( 'Product ID' ),
      'title'    => Mage::helper( 'chirpify_seller' )->__( 'Product ID' ),
      'required' => false,
      'note'     => 'Please select a product'
    ) );

    $fieldset->addField( 'activate',   'hidden', array( 'name' => 'activate' ) );
    $fieldset->addField( 'deactivate', 'hidden', array( 'name' => 'deactivate' ) );
    $fieldset->addField( 'user_id',    'hidden', array( 'name' => 'user_id' ) );

    $values = $model->getData();

    $values['action_id'] = $model->getId();
    $values['user_id']    = $this->_chirp_user_id;

    !$model->getId()    ? $values['product_id'] = Mage::app()->getRequest()->getParam( 'product_id' ) : null;
    !$model->getPrice() ? $values['price']      = Mage::app()->getRequest()->getParam( 'price' )      : null;
    !$model->getQty()   ? $values['qty']        = Mage::app()->getRequest()->getParam( 'qty' )        : null;


    $form->setValues( $values );
    $this->setForm( $form );

    return parent::_prepareForm();
  }

  public function getTabLabel()
  {
    return Mage::helper( 'chirpify_seller' )->__( 'Campaign Information' );
  }

  /**
   * Prepare title for tab
   *
   * @return string
   */
  public function getTabTitle()
  {
    return Mage::helper( 'chirpify_seller' )->__( 'Campaign Information' );
  }

  /**
   * Returns status flag about this tab can be shown or not
   *
   * @return true
   */
  public function canShowTab()
  {
    return true;
  }

  /**
   * Returns status flag about this tab hidden or not
   *
   * @return true
   */
  public function isHidden()
  {
    return false;
  }

  //update chirpify api calls with updated uri data
  private function _setChirpUser()
  {
    $this->_setChirpifyUerId();
    $url = Mage::helper( 'chirpify_seller/apiurls' )->getUrlForMethod( 'userlookup' );
    $payload = new stdClass();
    $payload->user_id = $this->_chirp_user_id;
    $result = Mage::getModel('chirpify_seller/apirequest')->get( $url, $payload );
    $this->_chirp_user = json_decode ( $result );
    return;
  }

  //update chirpify api calls with updated uri data
  public function _setChirpifyUerId()
  {
    $url = Mage::helper( 'chirpify_seller/apiurls' )->getUrlForMethod( 'user_from_key' );
    $payload = new stdClass();
    $payload->chirp_key = Mage::getStoreConfig( 'chirpify_seller/options/api_key' );
    $result = Mage::getModel('chirpify_seller/apirequest')->post( $url, $payload );
    $result_obj = json_decode ( $result );
    $this->_chirp_user_id = !empty( $result_obj->user_id ) ? $result_obj->user_id : false;
    return;
  }

}