<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Subscription_Comment_Grid
    extends Magpleasure_Blog_Block_Adminhtml_Filterable_Grid
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function __construct()
    {
        parent::__construct();
        $this->setId('blogSubscriptionCommentGrid');
        $this->setDefaultSort('subscription_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Comment_Subscription_Collection $collection  */
        $collection = Mage::getModel('mpblog/comment_subscription')->getCollection();

        if (!Mage::app()->isSingleStoreMode()){

            if ($this->isStoreFilterApplied()){
                $storeIds = $this->getAppliedStoreId();
            } else {
                $storeIds = $this->_helper()->getCommon()->getStore()->getFrontendStoreIds();
            }
            $collection->addFieldToFilter('store_id', $storeIds);
        }

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('subscription_id', array(
            'header' => $this->_helper()->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'subscription_id',
        ));

        $this->addColumn('post_id', array(
            'header' => $this->_helper()->__('Post'),
            'align' => 'left',
            'index' => 'post_id',
            'width' => '200px',
            'renderer' => 'Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Post',
            'filter_condition_callback' => array($this, '_filterPostCondition'),
        ));

        $this->addColumn('customer_name', array(
            'header' => $this->_helper()->__('Name'),
            'align' => 'left',
            'index' => 'customer_name',
            'renderer' => 'Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Customer',
        ));

        $this->addColumn('email', array(
            'header' => $this->_helper()->__('Email'),
            'align' => 'left',
            'index' => 'email',
        ));

        $this->addColumn('status', array(
            'header' => $this->_helper()->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('mpblog/comment_subscription')->getOptionsArray(),
        ));

        if(!Mage::app()->isSingleStoreMode() && !$this->isStoreFilterApplied()){
            $this->addColumn('store_id', array(
                'header' => $this->__('Store View'),
                'index' => 'store_id',
                'sortable' => true,
                'width' => '120px',
                'type' => 'store',
                'store_view' => true,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
            ));
        }

        $this->addColumn('created_at', array(
            'header' => $this->_helper()->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '140px',
        ));

        $this->addColumn('updated_at', array(
            'header' => $this->_helper()->__('Updated At'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '140px',
        ));

        $this->addColumn('action',
            array(
                'header' => $this->_helper()->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->_helper()->__('Unsubscribe'),
                        'url' => array('base' => '*/*/unsubscribe', 'params' => $this->_getCommonParams()),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->_helper()->__('Subscribe'),
                        'url' => array('base' => '*/*/subscribe', 'params' => $this->_getCommonParams()),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->_helper()->__('Delete'),
                        'url' => array('base' => '*/*/delete', 'params' => $this->_getCommonParams()),
                        'field' => 'id',
                        'confirm'  => $this->_helper()->__('Do you really want to delete this subscription?')
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscription_id');
        $this->getMassactionBlock()->setFormFieldName('subscriptions');

        $statuses = Mage::getModel('mpblog/comment_subscription')->toOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->_helper()->__('Change Status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->_helper()->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->_helper()->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->_helper()->__('Are you sure?')
        ));
        return $this;
    }

    protected function _filterPostCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addPostTextFilter($value);
    }

    public function getRowUrl($item)
    {
        return '#';
    }

}