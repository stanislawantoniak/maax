<?php

class Zolago_Pos_Block_Adminhtml_Pos_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('zolagopos_pos_grid');
        $this->setDefaultSort('pos_id');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection(){
        $collection = Mage::getResourceModel('zolagopos/pos_collection');
        /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
        $collection->addVendorOwnerName();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $data = array (
            "index"     =>"pos_id",
            "header"    => Mage::helper("zolagopos")->__("Pos ID"),
            "align"     => "right",
            "type"      => "number",
            "width"     => "100px"
        );
        $this->addColumn("pos_id", $data);
        $data['index'] = 'client_number';
        $data['header'] = Mage::helper("zolagopos")->__("Client number");
        
        $this->addColumn("client_number", $data);
        $data['index'] = 'external_id';
        $data['type'] = 'text';
        $data['header'] = Mage::helper("zolagopos")->__("External ID");
        
        $this->addColumn("external_id", $data);
        
        
        $this->addColumn("name", array(
            "index"     =>"name",
            "header"    => Mage::helper("zolagopos")->__("Name"),
        ));
        $this->addColumn("city", array(
            "index"     =>"city",
            "header"    => Mage::helper("zolagopos")->__("City"),
        ));
        $this->addColumn("phone", array(
            "index"     =>"phone",
            "header"    => Mage::helper("zolagopos")->__("Phone"),
            'width'     => '100px',
        ));
        $this->addColumn("email", array(
            "index"     =>"email",
            "header"    => Mage::helper("zolagopos")->__("Email"),
            'width'     => '200px',
        ));
        $this->addColumn("vendor_owner_name", array(
            "index"     =>"vendor_owner_name",
            "header"    => Mage::helper("zolagopos")->__("Vendor owner"),
            'width'     => '200px',
        ));
        $this->addColumn("is_active", array(
            "index"     =>"is_active",
            'type'      => 'options',
            "options"   => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray(),
            "header"    => Mage::helper("zolagopos")->__("Is active"),
            'width'     => '50px',
        ));
        $this->addColumn('action', array(
            'header'    => Mage::helper('zolagopos')->__('Action'),
            'width'     => '100px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('zolagopos')->__('View'),
                    'url'       => array(
                            'base'  => '*/*/edit'
                    ),
                    'field'     => 'pos_id'
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'action',
        ));
        return parent::_prepareColumns();
    }


    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array('pos_id'=>$row->getId()));
    }
    

}