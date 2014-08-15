<?php

class Chirpify_Seller_Block_Listing_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

  const MAX_TWEET_LENGTH = 140;

  private $_chirp_user_id;
  private $_chirp_user;
  private $_facebook_profile_disabled;
  private $_facebook_page_disabled;
  private $_twitter_disabled;
  private $_fb_profile_handle;
  private $_fb_page_handle;
  private $_twitter_handle;

  protected function _prepareForm()
  {
    $model = Mage::registry( 'chirpify_listing' );

    $form = new Varien_Data_Form();

    $this->_setChirpUser();
    $this->_setUserHasFacebook();
    $this->_setUserHasTwitter();

    $form->setHtmlIdPrefix( 'listing_' );

    $fieldset = $form->addFieldset( 'base_fieldset', array('legend' => Mage::helper( 'chirpify_seller' )->__( 'Listing Information' )) );

    if( $model->getId() )
    {
      $fieldset->addField( 'listing_id', 'hidden', array( 'name' => 'listing_id' ) );
    }

    $fieldset->addField( 'name', 'text', array(
      'name' => 'name',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Listing Name' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Listing Name' ),
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

    $fieldset->addField( 'twitter_text', 'textarea', array(
      'name' => 'twitter_text',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Tweet Text' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Tweet Text' ),
      'required' => true,
      'disabled' => $this->_has_twitter,
      'after_element_html' =>  "<strong>@$this->_twitter_handle</strong><br /> Reply \"buy\" via @Chirpify chrp.in/",
    ) );

    $fieldset->addField( 'facebook_profile_text', 'textarea', array(
      'name' => 'facebook_profile_text',
      'label' => Mage::helper( 'chirpify_seller' )->__( 'Facebook Profile Text' ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Facebook Profile Text' ),
      'required' => false,
      'disabled' => $this->_facebook_profile_disabled,
      'after_element_html' =>  "<strong>@$this->_fb_profile_handle</strong><br />
        To purchase, simply comment with the word \"buy\"
        with no other words. Be sure to comment \"buy\" with no other words.
        You must be a member of Chirpify, which enables in-stream fundraising and payments on Facebook.
        http://chrp.in/ <br /><br />",
    ) );

    $fieldset->addField( 'facebook_page_text', 'textarea', array(
      'name' => 'facebook_page_text',
      'label' => Mage::helper( 'chirpify_seller' )->__( "Facebook Page Text" ),
      'title' => Mage::helper( 'chirpify_seller' )->__( 'Facebook Page Text' ),
      'required' => false,
      'disabled' => $this->_facebook_page_disabled,
      'after_element_html' =>  "<strong>@$this->_fb_page_handle</strong><br />
        To purchase, simply comment with the word \"buy\"
        with no other words. Be sure to comment \"buy\" with no other words.
        You must be a member of Chirpify, which enables in-stream fundraising and payments on Facebook.
        http://chrp.in/ <br /><br />",
    ) );

    $fieldset->addField( 'chirpify_text', 'textarea', array(
      'name'     => 'chirpify_text',
      'label'    => Mage::helper( 'chirpify_seller' )->__( 'Chirpify Text' ),
      'title'    => Mage::helper( 'chirpify_seller' )->__( 'Chirpify Text' ),
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
//      'label'     => Mage::helper('chirpify_seller')->__('Upload File (digital listings only)'),
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

    $fieldset->addField( 'publish',   'hidden', array( 'name'   => 'publish' ) );
    $fieldset->addField( 'unpublish', 'hidden', array( 'name'   => 'unpublish' ) );
    $fieldset->addField( 'user_id',   'hidden', array( 'name'   => 'user_id' ) );

    $values = $model->getData();

    $values['listing_id'] = $model->getId();
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
    return Mage::helper( 'chirpify_seller' )->__( 'Listing Information' );
  }

  /**
   * Prepare title for tab
   *
   * @return string
   */
  public function getTabTitle()
  {
    return Mage::helper( 'chirpify_seller' )->__( 'Listing Information' );
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

  private function _setUserHasFacebook()
  {
    $this->_facebook_profile_disabled = true;
    $this->_facebook_page_disabled    = true;
    $this->_fb_profile_handle = '';
    $this->_fb_page_handle    = '';

    if( isset( $this->_chirp_user->social_identities->facebook ) )
    {
      foreach( $this->_chirp_user->social_identities->facebook as $fb )
      {

        if( $fb->sub_platform == 'profile' )
        {
          $this->_fb_profile_handle = $fb->handle;
          $this->_facebook_profile_disabled = false;
        }

        if( $fb->sub_platform == 'page' )
        {
          $this->_fb_page_handle = $fb->handle;
          $this->_facebook_page_disabled = false;
        }
      }
    }
    return;
  }

  private function _setUserHasTwitter()
  {
    $this->_twitter_disabled = true;
    $this->_twitter_handle = '';
    if( isset( $this->_chirp_user->social_identities->twitter ) )
    {
      $this->_twitter_handle = $this->_chirp_user->social_identities->twitter[0]->handle;
      $this->_twitter_disabled = false;
    }
    return;
  }

}