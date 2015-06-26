<?php

class GH_Dhl_Block_Adminhtml_Dhl_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ghdhl_dhl_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ghdhl/dhl_collection');
        /* @var $collection GH_Dhl_Model_Resource_Dhl_Collection */

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("id", array(
            "index" => "id",
            "header" => Mage::helper("ghdhl")->__("ID"),
            "align" => "right",
            "type" => "number",
            "width" => "100px"
        ));
        $this->addColumn("dhl_account", array(
            "index" => "dhl_account",
            "header" => Mage::helper("ghdhl")->__("Account"),
        ));
        $this->addColumn("dhl_login", array(
            "index" => "dhl_login",
            "header" => Mage::helper("ghdhl")->__("Login"),
        ));

        $this->addColumn("comment", array(
            "index" => "comment",
            "header" => Mage::helper("ghdhl")->__("Comment"),
        ));

        return parent::_prepareColumns();
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


}