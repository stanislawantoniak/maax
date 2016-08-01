<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Block_Adminhtml_Post_Grid extends Magpleasure_Blog_Block_Adminhtml_Filterable_Grid
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
        $this->setId('blogPostGrid');
        $this->setDefaultSort('published_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _parentPrepareCollection()
    {
        return parent::_prepareCollection();
    }

    protected function _prepareCollection()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $collection */
        $collection = Mage::getModel('mpblog/post')->getCollection();

        # Apply Store FIlter
        if (!Mage::app()->isSingleStoreMode()) {
            $collection->addStoreData();
            $storeIds = $this->isStoreFilterApplied() ?
                array($this->getAppliedStoreId()) :
                $this->_helper()->getCommon()->getStore()->getFrontendStoreIds();

            $collection->addStoreFilter($storeIds);
        }

        # Apply Live Posts Filter
        $collection->addFieldToFilter(
            'status',
            array(
                'nin' => array(
                    Magpleasure_Blog_Model_Post::STATUS_DRAFT,
                    Magpleasure_Blog_Model_Post::STATUS_DELETED,
                )
            )
        );

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('post_id', array(
            'header' => $this->_helper()->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'post_id',
        ));

        $this->addColumn('title', array(
            'header' => $this->_helper()->__('Title'),
            'align' => 'left',
            'index' => 'title',
        ));

        $this->addColumn('url_key', array(
            'header' => $this->_helper()->__('Url Key'),
            'align' => 'left',
            'index' => 'url_key',
        ));

        $this->addColumn('status', array(
            'header' => $this->_helper()->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'renderer' => 'Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Status',
            'options' => Mage::getModel('mpblog/post')->getOptionsArray(),
        ));

        if (!Mage::app()->isSingleStoreMode() && !$this->isStoreFilterApplied()) {
            $this->addColumn('stores', array(
                'header' => $this->__('Store View'),
                'index' => 'stores',
                'sortable' => true,
                'width' => '120px',
                'type' => 'store',
                'store_view' => true,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoresCondition')
            ));
        }

        $this->addColumn('published_at', array(
            'header' => $this->_helper()->__('Published At'),
            'index' => 'published_at',
            'type' => 'datetime',
            'width' => '140px',
            'renderer' => 'Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Published',
            'filter_condition_callback' => array($this, '_filterPublishedAtCondition')
        ));

        $this->addColumn('action',
            array(
                'header' => $this->_helper()->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->_helper()->__('Edit'),
                        'url' => array('base' => '*/*/edit', 'params' => $this->_getCommonParams()),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->_helper()->__('Duplicate'),
                        'url' => array('base' => '*/*/duplicate', 'params' => $this->_getCommonParams()),
                        'field' => 'id'
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

//        $this->addExportType('*/*/exportCsv', $this->_helper()->__('CSV'));
//        $this->addExportType('*/*/exportXml', $this->_helper()->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _getMassActionStatuses()
    {
        $statuses = Mage::getModel('mpblog/post')->toOptionArray();
        $availableStatuses = array();
        foreach ($statuses as $status){
            if ($status['value'] !== Magpleasure_Blog_Model_Post::STATUS_SCHEDULED){
                $availableStatuses[] = $status;
            }
        }
        array_unshift($availableStatuses, array('label' => '', 'value' => ''));
        return $availableStatuses;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('post_id');
        $this->getMassactionBlock()->setFormFieldName('posts');

        $massStatusParams = $this->_getCommonParams();
        $massStatusParams['_current'] = true;

        if ($this->isStoreFilterApplied()) {
            $commonParams['store'] = $this->getAppliedStoreId();
            $massStatusParams['store'] = $this->getAppliedStoreId();
        }

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->_helper()->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete', $this->_getCommonParams()),
            'confirm' => $this->_helper()->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->_helper()->__('Change Status'),
            'url' => $this->getUrl('*/*/massStatus', $massStatusParams),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->_helper()->__('Status'),
                    'values' => $this->_getMassActionStatuses(),
                )
            )
        ));

        $this->getMassactionBlock()->addItem('update_attributes', array(
            'label' => $this->_helper()->__('Update Attributes'),
            'url' => $this->getUrl('*/*/massUpdateAttribute', $this->_getCommonParams()),
        ));

        $this->getMassactionBlock()->addItem('duplicate', array(
            'label' => $this->_helper()->__('Duplicate'),
            'url' => $this->getUrl('*/*/massDuplicate', $this->_getCommonParams()),
            'confirm' => $this->_helper()->__('Are you sure?')
        ));

        return $this;
    }

    protected function _filterPublishedAtCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $filters = new Varien_Object($value);

        /** @var $from Zend_Date */
        if ($from = $filters->getFrom()) {
            $this->getCollection()->addFromFilter($from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }

        /** @var $to Zend_Date */
        if ($to = $filters->getTo()) {
            $this->getCollection()->addToFilter($to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }
    }

    protected function _filterStoresCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }
}