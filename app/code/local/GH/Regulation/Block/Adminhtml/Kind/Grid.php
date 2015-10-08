<?php

class Gh_Regulation_Block_Adminhtml_Kind_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ghregulation_kind_grid');
        $this->setDefaultSort('regulation_kind_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        /** @var GH_Regulation_Model_Resource_Regulation_Kind_Collection $collection */
        $collection = Mage::getResourceModel('ghregulation/regulation_kind_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn("name", array(
            "index"     =>"name",
            "header"    => Mage::helper("ghregulation")->__("Document kind name"),
        ));
        $this->addColumn('action', array(
            'header'    => Mage::helper('ghregulation')->__('Remove'),
            'width'     => '100px',
            'type'      => 'action',
            'getter'    => 'getRegulationKindId',
            'actions' 	=> array(
                array(
                    'caption' => Mage::helper('ghregulation')->__('Remove'),
                    'url'	  => array('base' => '*/*/deleteKind'),
                    'field'   => 'regulation_kind_id',
                    'confirm' => Mage::helper('ghregulation')->__('Are you sure?'),
                ),
            ),
            'filter' => false,
            'sortable' => false,
        ));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        if ($row->getId()) {
            return $this->getUrl('*/*/editKind', array('regulation_kind_id' => $row->getId()));
        }
        return $this->getUrl('*/*/newKind', array('back' => 'kind'));
    }

}