<?php
# @todo create proper chirpify campaign delete
class Chirpify_Seller_ChirpifyController extends Mage_Adminhtml_Controller_Action
{

  public function indexAction()
  {
    $this->loadLayout();
    $this->_addContent( $this->getLayout()->createBlock( 'chirpify_seller/campaigns' ) );
    $this->renderLayout();
  }

  public function newAction()
  {
    // the same form is used to create and edit
    $campaign = Mage::getModel( 'chirpify_seller/campaign' )
        ->setProductId( $this->getRequest()->getParam( 'product_id' ) )
        ->save();
    //echo '<pre>'; print_r($campaign);die();
    $this->_forward( 'edit', null, null, array('action_id' => $campaign->getId()) );
  }

  const GRID_NAME = 'chirpify_seller.tab.product';

  public function relatedAction()
  {
    $this->loadLayout();
    Mage::register( 'current_campaign', Mage::getModel( 'chirpify_seller/campaign' )->load( $this->getRequest()->getParam( 'action_id' ) ) );

    $layout = Mage::getSingleton( 'core/layout' );
    $root = $layout->createBlock( 'core/text_list', 'root' );

    $grid_name = self::GRID_NAME;
    $grid = $layout->createBlock( 'chirpify_seller/campaign_edit_tab_products', $grid_name );
    $root->append( $grid );

    $serializer = $layout->createBlock( 'adminhtml/widget_grid_serializer', 'related_grid_serializer' );
    $serializer->initSerializerBlock( $grid_name, 'getSelectedProduct', 'links[product]', 'products_related' );
    $root->append( $serializer );
    $this->renderLayout();
  }

  public function relatedGridAction()
  {
    $this->loadLayout();
    Mage::register( 'current_campaign', Mage::getModel( 'chirpify_seller/campaign' )->load( $this->getRequest()->getParam( 'action_id' ) ) );

    $layout = Mage::getSingleton( 'core/layout' );
    $root = $layout->createBlock( 'core/text_list', 'root' );

    $grid_name = self::GRID_NAME;
    $grid = $layout->createBlock( 'chirpify_seller/campaign_edit_tab_products', $grid_name );

    $root->append( $grid );
    $this->renderLayout();
  }

  public function editAction()
  {
    $o = Mage::getModel( 'chirpify_seller/campaign' );
    if( $id = $this->getRequest()->getParam( 'action_id' ) )
    {
      $o->load( $id );
    }

    Mage::register( 'chirpify_campaign', $o );
    $this->loadLayout();
    $this->_addContent(
        $this->getLayout()->createBlock( 'chirpify_seller/campaign_edit', 'campaign_edit' )
    );

    $tabs = $this->getLayout()->createBlock( 'chirpify_seller/campaign_edit_tabs', 'campaign_edit_tabs' );

    $main_tab = $this->getLayout()->createBlock( 'chirpify_seller/campaign_edit_tab_main', 'campaign_edit_tab_main' );
    $product_tab = $this->getLayout()->createBlock( 'chirpify_seller/campaign_edit_tab_product', 'campaign_edit_tab_product' );

    $tabs->insert( $main_tab );
    $tabs->addTab( 'main_section', 'campaign_edit_tab_main' );
    $tabs->addTab( 'related_product', array(
      'label' => 'Product',
      'url' => Mage::getUrl( '*/*/related', array('_current' => true) ),
      'class' => 'ajax'
    ) );

    $this->_addLeft(
        $tabs
    );
    $this->renderLayout();
  }

  protected function _validatePostData( $data )
  {
    // no longer publishing data to twitter or social networks so just return true until __METHOD__ usage is known
    return true;
  }

  public function saveAction()
  {

    # @todo do not save if catalog product id not set
    # check if data sent
    if( $data = $this->getRequest()->getPost() )
    {
      Mage::log('POST_DATA');
      Mage::log($data);

      $data = $this->_filterPostData( $data );
      # init model and set data
      $model = Mage::getModel( 'chirpify_seller/campaign' );

      if( $id = $this->getRequest()->getParam( 'action_id' ) )
      {
        $model->load( $id );
      }

      // Mage::log('FILES');
      // Mage::log($_FILES);
      if( isset( $_FILES['digital_file_name']['name'] ) && (file_exists( $_FILES['digital_file_name']['tmp_name'] )) )
      {
        $data['digital_file_name'] = $_FILES['digital_file_name']['name'];
        $file_type = explode('/', $_FILES['digital_file_name']['type']);

        try
        {
          $uploader = new Varien_File_Uploader( 'digital_file_name' );
          $uploader->setAllowRenameFiles( false ); //true will move your file to a folder the Mage way
          $uploader->setFilesDispersion( false );
          $path = Mage::getBaseDir( 'media' ) . DS;
          $uploader->save( $path, $_FILES['digital_file_name']['name'] );
          $data['digital_file_name'] = $_FILES['digital_file_name']['name'];
          $data['digital_content_id'] = $data['user_id'] . '/' . md5( $data['digital_file_name'] ) . '.' . $file_type[1];
        }
        catch( Exception $e )
        {
          Mage::logException($e);
        }

        //do upload stuff
        $payload = array(
          'user_id'            => $data['user_id'],
          'digital_file_name'  => $data['digital_file_name'],
          'digital_content_id' => $data['digital_content_id'],
        );

        $s3policy = Mage::getModel( 'chirpify_seller/s3' )->s3policy( $payload );
        // https://s3.amazonaws.com/chirpifyfeature/5115883dc602a.medium.jpeg';
        $s3policy['file'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $data['digital_file_name'];
        Mage::log('policy_S3');
        Mage::log( $s3policy );
        $this->_sendTos3( $s3policy );
      }

      $model->addData( $data );
      if( array_key_exists( 'links', $data ) )
      {
        $ids = explode( "&", $data['links']['product'] );
        $model->setProductId( $ids[0] );
        if( count( $ids ) > 1 )
        {
          $this->_getSession()->addNotice( sprintf( 'Used first selected product id [%s]', $ids[0] ) );
        }
      }

      if( !$this->_validatePostData( $data ) )
      {
        $this->_redirect( '*/*/edit', array('action_id' => $model->getId(), '_current' => true) );
        return;
      }

      try
      {
        $model->save();
        // display success message
        Mage::getSingleton( 'adminhtml/session' )->addSuccess(
        Mage::helper( 'chirpify_seller' )->__( 'The Campaign has been saved.' ) );
        // clear previously saved data from session
        Mage::getSingleton( 'adminhtml/session' )->setFormData( false );
        // check if 'Save and Continue'
        if( $this->getRequest()->getParam( 'back' ) )
        {
          $this->_redirect( '*/*/edit', array('page_id' => $model->getId(), '_current' => true) );
          return;
        }

        if( $this->getRequest()->getParam( 'activate' ) )
        {
          $model->activate();
          Mage::getSingleton( 'adminhtml/session' )->addSuccess(
          Mage::helper( 'chirpify_seller' )->__( 'The Campaign has been activated.' )
          );
        }

        if( $this->getRequest()->getParam( 'deactivate' ) )
        {
          $model->deactivate();
          Mage::getSingleton( 'adminhtml/session' )->addSuccess(
          Mage::helper( 'chirpify_seller' )->__( 'The Campaign has been deactivated.' )
          );
        }
        // go to grid
        $this->_redirect( '*/*/' );
        return;
      }
      catch( Mage_Core_Exception $e )
      {
        $this->_getSession()->addError( $e->getMessage() );
      }
      catch( Exception $e )
      {
        $this->_getSession()->addException( $e, Mage::helper( 'chirpify_seller' )
            ->__( 'A PHP error occurred while saving the Campaign.' . $e->getMessage() ) );
      }
      $this->_getSession()->setFormData( $data );
      $this->_redirect( '*/*/edit', array('action_id' => $this->getRequest()->getParam( 'action_id' )) );
      return;
    }
    $this->_redirect( '*/*/' );
  }

  private function _sendTos3( $s3data = null )
  {
    $response = false;
    $amazon_url = 'https://s3.amazonaws.com/' . $s3data['bucket'] . '/';

    if( !empty( $s3data ) )
    {
      Mage::log('policy_data');
      Mage::log($s3data);
      try
      {
         //$s3data = http_build_query( $s3data );
         $ch = curl_init();
         curl_setopt( $ch, CURLOPT_VERBOSE, true);
         curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
         curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
         curl_setopt( $ch, CURLOPT_URL, $amazon_url );
         curl_setopt( $ch, CURLOPT_POST, true );
         curl_setopt( $ch, CURLOPT_POSTFIELDS, $s3data );
         curl_setopt( $ch, CURLOPT_HEADER, true );
         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
         $response = curl_exec( $ch );

         // After curl_exec call:
         $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
         $header = substr( $response, 0, $header_size );
         $body = substr( $response, $header_size );
         Mage::log('S3_RESPONSE');
         Mage::log($header_size);
         Mage::log($header);
         Mage::log($body);
         Mage::log($response);

      }
      catch( Exception $e )
      {
        Mage::logException( $e );
      }
    }
    return $response;
  }

  protected function _filterPostData( $data )
  {
    # @todo update old filter stuff
    $data = $this->_filterDates( $data, array('date_started', 'date_ended') );
    return $data;
  }

}
