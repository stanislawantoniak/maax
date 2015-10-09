<?php

class Gh_Regulation_Block_Adminhtml_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct() {
        parent::__construct();
        $this->setId('ghregulation_list_grid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        /** @var GH_Regulation_Model_Resource_Regulation_Document_Collection $collection */
        $collection = Mage::getResourceModel('ghregulation/regulation_document_collection');
        $collection->joinType();
        $collection->joinKind();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn("document_link", array(
            "index" => "document_link",
            "header" => Mage::helper("ghregulation")->__("Document"),
        ));
        $this->addColumn("date", array(
            "index"  => "date",
            "header" => Mage::helper("ghregulation")->__("Is valid from"),
        ));
        $this->addColumn("kind_name", array(
            "index"  => "kind_name",
            "header" => Mage::helper("ghregulation")->__("Kind"),
        ));
        $this->addColumn("type_name", array(
            "index"  => "type_name",
            "header" => Mage::helper("ghregulation")->__("Type"),
        ));
        $this->addColumn('action', array(
            'header'  => Mage::helper('ghregulation')->__('Remove'),
            'width'   => '100px',
            'type'    => 'action',
            'getter'  => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('ghregulation')->__('Remove'),
                    'url'     => array('base' => '*/*/deleteDocument'),
                    'field'   => 'id',
                    'confirm' => Mage::helper('ghregulation')->__('Are you sure?'),
                ),
            ),
            'filter' => false,
            'sortable' => false,
        ));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        if ($row->getId()) {
            return $this->getUrl('*/*/editDocument', array('id' => $row->getId()));
        }
        return $this->getUrl('*/*/newKind', array('back' => 'kind'));
    }

}