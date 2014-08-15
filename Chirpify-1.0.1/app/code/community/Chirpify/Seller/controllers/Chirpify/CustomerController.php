<?php

include('Mage/Customer/controllers/AccountController.php');

class Chirpify_Seller_Chirpify_CustomerController extends Mage_Customer_AccountController //Mage_Core_Controller_Front_Action
{

  // public function twitterAction()
  // {
  //   $this->loadLayout();
  //   $this->_initLayoutMessages( 'customer/session' );
  //   $this->_initLayoutMessages( 'catalog/session' );
  //   $this->renderLayout();
  // }

  // public function twitterPostAction()
  // {
  //   if( !$this->_validateFormKey() )
  //   {
  //     return $this->_redirect( '*/*/edit' );
  //   }

  //   try
  //   {
  //     $customer = $this->_getSession()->getCustomer();
  //     $post = $this->getRequest()->getPost();
  //     $customer->setData( 'chirpify_twitter_uid', strip_tags( $post['twitter_id'] ) )
  //         ->save();
  //     $this->_getSession()->addSuccess( 'Did it' );
  //   }
  //   catch( Mage_Core_Exception $e )
  //   {
  //     $this->_getSession()->addError( $e->getMessage() );
  //   }
  //   catch( Exception $e )
  //   {
  //     $this->_getSession()->addError( 'PHP Error, please contact site owner id error persists.' );
  //   }

  //   $this->_redirect( '*/*/twitter' );
  // }
  
  // public function faceBookPostAction()
  // {
  //   //
  // }

}