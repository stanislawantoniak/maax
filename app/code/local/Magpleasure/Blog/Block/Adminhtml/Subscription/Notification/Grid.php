<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

/** @method Magpleasure_Blog_Model_Mysql4_Comment_Notification_Collection getCollection  */
class Magpleasure_Blog_Block_Adminhtml_Subscription_Notification_Grid
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
        $this->setId('blogCommentNotificationGrid');
        $this->setDefaultSort('notification_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

        $this->setDefaultFilter(array('status' => Magpleasure_Blog_Model_Comment_Notification::STATUS_WAIT));
    }

    protected function _prepareCollection()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Comment_Notification_Collection $collection  */
        $collection = Mage::getModel('mpblog/comment_notification')->getCollection();

        $collection->addSubscriptionData();

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
        $this->addColumn('notification_id', array(
            'header' => $this->_helper()->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'notification_id',
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
            'filter_condition_callback' => array($this, '_filterCommenterNameCondition'),
        ));

        $this->addColumn('email', array(
            'header' => $this->_helper()->__('Email'),
            'align' => 'left',
            'index' => 'email',
            'filter_condition_callback' => array($this, '_filterCommenterEmailCondition'),
        ));

        $this->addColumn('status', array(
            'header' => $this->_helper()->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('mpblog/comment_notification')->getOptionsArray(),
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

        $this->addColumn('updated_at', array(
            'header' => $this->_helper()->__('Recently Updated'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'width' => '140px',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('notification_id');
        $this->getMassactionBlock()->setFormFieldName('notifications');

        $this->getMassactionBlock()->addItem('send_now', array(
            'label' => $this->_helper()->__('Send Now'),
            'url' => $this->getUrl('*/*/massSendNow'),
            'confirm' => $this->_helper()->__("Do you want to send?"),
        ));

        $this->getMassactionBlock()->addItem('cancel', array(
            'label' => $this->_helper()->__('Cancel'),
            'url' => $this->getUrl('*/*/massCancel'),
        ));

        $this->getMassactionBlock()->addItem('send_test', array(
            'label' => $this->_helper()->__('Send Test Email'),
            'url' => $this->getUrl('*/*/massSendTest'),
            'additional' => array(
                'email' => array(
                    'name' => 'email',
                    'type' => 'text',
                    'class' => 'required-entry validate-email',
                    'label' => $this->_helper()->__('Test Email'),
                )
            )
        ));

        return $this;
    }

    protected function _filterCommenterEmailCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this
            ->getCollection()
            ->addEmailFilter($value)
        ;
    }

    protected function _filterCommenterNameCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this
            ->getCollection()
            ->addCommenterNameFilter($value)
        ;
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