<?php
class Chirpify_Seller_Block_Campaigns_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('chirpifyPageGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('chirpify_seller/campaign')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {

        $this->addColumn('chirpify_seller_campaign_id', array(
            'header'    => Mage::helper('chirpify_seller')->__('ID'),
            'align'     => 'left',
            'index'     => 'chirpify_seller_campaign_id',
        ));
        
        $this->addColumn('name', array(
            'header'    => Mage::helper('chirpify_seller')->__('Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('date_started', array(
            'header'    => Mage::helper('chirpify_seller')->__('Campaign Start Date'),
            'align'     => 'left',
            'type'      => 'date',
            'index'     => 'date_started',
        ));

        $this->addColumn('date_ended', array(
            'header'    => Mage::helper('chirpify_seller')->__('Campaign End Date'),
            'align'     => 'left',
            'type'      => 'date',
            'index'     => 'date_ended',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('action_id' => $row->getId()));
    }
    
}