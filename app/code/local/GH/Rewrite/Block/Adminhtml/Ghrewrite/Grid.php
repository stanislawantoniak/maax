<?php

class GH_Rewrite_Block_Adminhtml_Ghrewrite_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('gh_rewrite_grid');
        $this->setDefaultSort('url_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /** @var GH_Rewrite_Model_Resource_Url_Collection $collection */
        $collection = Mage::getModel('ghrewrite/url')->getCollection();

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->addColumn('url_id', array(
            'header' => $rewriteHelper->__('Url ID'),
            'index'  => 'url_id'
        ));

        $this->addColumn('url_rewrite_id', array(
            'header' => $rewriteHelper->__('Url rewrite ID'),
            'index'  => 'url_rewrite_id'
        ));

        $this->addColumn('store_id', array(
            'header' => $rewriteHelper->__('Store ID'),
            'index'  => 'store_id'
        ));

        $this->addColumn('hash_id', array(
            'header' => $rewriteHelper->__('Hash ID'),
            'index'  => 'hash_id'
        ));

        $this->addColumn('category_id', array(
            'header' => $rewriteHelper->__('Category ID'),
            'index'  => 'category_id'
        ));

        $this->addColumn('title', array(
            'header' => $rewriteHelper->__('Title'),
            'index'  => 'title'
        ));

        $this->addColumn('meta_description', array(
            'header' => $rewriteHelper->__('Meta description'),
            'index'  => 'meta_description'
        ));

        $this->addColumn('meta_keywords', array(
            'header' => $rewriteHelper->__('Meta keywords'),
            'index'  => 'meta_keywords'
        ));

        $this->addColumn('category_name', array(
            'header' => $rewriteHelper->__('Category name'),
            'index'  => 'category_name'
        ));

        $this->addColumn('text_field_category', array(
            'header' => $rewriteHelper->__('Text field category'),
            'index'  => 'text_field_category'
        ));

        $this->addColumn('text_field_filter', array(
            'header' => $rewriteHelper->__('Text field filter'),
            'index'  => 'text_field_filter'
        ));

        $this->addColumn('listing_title', array(
            'header' => $rewriteHelper->__('Listing title'),
            'index'  => 'listing_title'
        ));

        $this->addColumn('url', array(
            'header' => $rewriteHelper->__('Url'),
            'index'  => 'url'
        ));

        $this->addColumn('filters', array(
            'header' => $rewriteHelper->__('Filters'),
            'index'  => 'filters'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function _prepareMassaction()
    {
        /** @var Mage_Adminhtml_Helper_Data $adminhtmlHelper */
        $adminhtmlHelper = Mage::helper('adminhtml');

        $this->setMassactionIdField('massaction_id_field');
        $this->getMassactionBlock()->setFormFieldName('url_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> $adminhtmlHelper->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => $adminhtmlHelper->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('generate', array(
            'label'=> $adminhtmlHelper->__('Generate'),
            'url'  => $this->getUrl('*/*/massGenerate', array('' => '')),
            'confirm' => $adminhtmlHelper->__('Are you sure?')
        ));

        return $this;
    }
}