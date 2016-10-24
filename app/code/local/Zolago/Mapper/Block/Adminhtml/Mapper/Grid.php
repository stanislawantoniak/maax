<?php

class Zolago_Mapper_Block_Adminhtml_Mapper_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagomapper_mapper_grid');
        $this->setDefaultSort('mapper_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        //$this->setMassactionIdFieldOnlyIndexValue(true);// custom_id value for mass action
    }

    protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagomapper/mapper_collection');
        /* @var $collection Zolago_Mapper_Model_Resource_Mapper_Collection */
		$collection->setFlag('abstract', true);
		$collection->joinAttributeSet();
        //$collection->addCustomId();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn("attribute_set_name", array(
            "index" => "attribute_set_name",
            "header" => Mage::helper("zolagomapper")->__("Attribute set"),
        ));

        $this->addColumn("name", array(
            "index" => "name",
            "header" => Mage::helper("zolagomapper")->__("Name"),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header' => Mage::helper('zolagomapper')->__('Website'),
                'align' => 'center',
                'width' => '150px',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }
        $this->addColumn("is_active", array(
            "index" => "is_active",
            'type' => 'options',
            'align' => 'center',
            "options" => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray(),
            "header" => Mage::helper("zolagomapper")->__("Is active"),
            'width' => '100px',
        ));
        $this->addColumn('action', array(
            'header' => Mage::helper('zolagomapper')->__('Action'),
            'width' => '100px',
            'type' => 'action',
            "renderer" => Mage::getConfig()->getBlockClassName("zolagomapper/adminhtml_mapper_grid_column_renderer_action"),
            'filter' => false,
            'sortable' => false,
        ));
        return parent::_prepareColumns();
    }


    public function getRowUrl($row){
		if($row->getId()){
			return $this->getUrl('*/*/edit', array('mapper_id'=>$row->getId()));
		}
        return $this->getUrl('*/*/new', array('back'=>'list', 'attribute_set_id'=>$row->getAttributeSetId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('mapper_id');
        $this->getMassactionBlock()->setFormFieldName('custom_ids');

        $this->getMassactionBlock()->addItem('queue', array(
            'label'=> Mage::helper("zolagomapper")->__('Add to queue'),
            'url'  => $this->getUrl('*/*/massQueue', array('' => '')),
        ));

        return $this;
    }
}