<?php

class Zolago_Mapper_Block_Adminhtml_Mapper_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagomapper_mapper_grid');
        $this->setDefaultSort('mapper_id');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagomapper/mapper_collection');
        /* @var $collection Zolago_Mapper_Model_Resource_Mapper_Collection */
		$collection->joinAttributeSet();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn("name", array(
            "index"     =>"name",
            "header"    => Mage::helper("zolagopos")->__("Name"),
        ));
        $this->addColumn("attribute_set_id", array(
            "index"     =>"attribute_set_id",
            "header"    => Mage::helper("zolagomapper")->__("Attribute set"),
        ));
        $this->addColumn("website_id", array(
            "index"     =>"website_id",
            "header"    => Mage::helper("zolagomapper")->__("Website"),
        ));
        $this->addColumn("is_active", array(
            "index"     =>"is_active",
            'type'      => 'options',
            "options"   => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray(),
            "header"    => Mage::helper("zolagomapper")->__("Is active"),
            'width'     => '100px',
        ));
        $this->addColumn('action', array(
            'header'    => Mage::helper('zolagomapper')->__('Action'),
            'width'     => '100px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('zolagomapper')->__('View'),
                    'url'       => array(
                            'base'  => '*/*/edit'
                    ),
                    'field'     => 'mapper_id'
                ),
                array(
                    'caption'   => Mage::helper('zolagomapper')->__('Run'),
                    'url'       => array(
                            'base'  => '*/*/run'
                    ),
                    'field'     => 'mapper_id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'action',
        ));
        return parent::_prepareColumns();
    }


    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array('mapper_id'=>$row->getId()));
    }
    

}