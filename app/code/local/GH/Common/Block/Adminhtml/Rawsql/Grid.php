<?php
/**
 * raw sql querys grid
 */

class Gh_Common_Block_Adminhtml_Rawsql_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ghcommon_rawsql_grid');
        $this->setDefaultSort('query_name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('ghcommon/sql_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $this->addColumn("name", array(
            "index"     =>"query_name",
            "header"    => Mage::helper("ghcommon")->__("Query name"),
        ));
        $this->addColumn('action', array(
            'header'    => Mage::helper('ghcommon')->__('Actions'),
            'width'     => '100px',
            'type'      => 'action',
            'renderer'  => Mage::getConfig()->getBlockClassName('ghcommon/adminhtml_rawsql_grid_column_renderer_action'),
            'filter' => false,
            'sortable' => false,
        ));
        return parent::_prepareColumns();
    }
    
    /**
     * prepare link to edit query
     */

    public function getRowUrl($row){
        if ($row->getId()) {
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        }
        return $this->getUrl('*/*/edit', array('back' => 'index'));
    }

}