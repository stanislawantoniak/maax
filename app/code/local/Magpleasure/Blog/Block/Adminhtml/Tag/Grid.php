<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Tag_Grid extends Magpleasure_Blog_Block_Adminhtml_Filterable_Grid
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
        $this->setId('blogTagGrid');
        $this->setDefaultSort('tag_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection $collection  */
        $collection = Mage::getModel('mpblog/tag')->getCollection();

        if (!Mage::app()->isSingleStoreMode()){

            if ($this->isStoreFilterApplied()){
                $storeIds = array($this->getAppliedStoreId());
            } else {
                $storeIds = $this->_helper()->getCommon()->getStore()->getFrontendStoreIds();
            }
            $collection->addWieghtData($storeIds);
        }

        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('tag_id', array(
            'header' => $this->_helper()->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'tag_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->_helper()->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('url_key', array(
            'header' => $this->_helper()->__('Url Key'),
            'align' => 'left',
            'index' => 'url_key',
        ));

        $this->addColumn('post_count', array(
            'header' => $this->_helper()->__('Used in Posts'),
            'align' => 'right',
            'width' => '80px',
            'index' => 'post_count',
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
                    )
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
        $this->setMassactionIdField('tag_id');
        $this->getMassactionBlock()->setFormFieldName('tags');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->_helper()->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->_helper()->__('Are you sure?')
        ));
        return $this;
    }


}