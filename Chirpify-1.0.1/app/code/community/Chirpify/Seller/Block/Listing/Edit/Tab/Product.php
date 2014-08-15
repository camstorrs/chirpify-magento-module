<?php
class Chirpify_Seller_Block_Listing_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {        
        $model = Mage::registry('chirpify_listing');
        //print_r($model); die('model');
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('listing_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('chirpify_seller')
        ->__('Product Information')));

//       if ($model->getId())
//       {
//         $fieldset->addField('listing_id', 'hidden', array( 'name' => 'listing_id' ));
//       }

        $fieldset->addField('blank', 'text', array(
            'name'      => 'blank',
            'label'     => Mage::helper('chirpify_seller')->__('Blank'),
            'title'     => Mage::helper('chirpify_seller')->__('Blank'),
            'required'  => true,
        ));
        
        $values = $model->getData();
        $values['listing_id'] = $model->getId();
        $values['publish'] = 0;
        
        if(!$model->getId())
        {
            $values['product_id'] = Mage::app()->getRequest()->getParam('product_id');
        }
        
        $form->setValues($values);        

        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    public function getTabLabel()
    {
        return Mage::helper('chirpify_seller')->__('Product');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('chirpify_seller')->__('Product');
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
}