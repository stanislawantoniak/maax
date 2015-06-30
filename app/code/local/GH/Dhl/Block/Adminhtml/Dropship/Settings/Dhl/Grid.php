<?php

/**
 * GH DHL Account Access settings grid
 */
class GH_Dhl_Block_Adminhtml_Dropship_Settings_Dhl_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('connect_dhl');
        $this->setDefaultSort('value');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $this->setDefaultFilter(array('connect_vendor_dhl' => 1));
        $collection = Mage::getResourceModel('ghdhl/dhl_collection');
        /* @var $collection GH_Dhl_Model_Resource_Dhl_Collection */

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     *
     * @return string
     */

    public function getGridUrl()
    {
        return $this->getUrl('ghdhladmin/dhl/vendor', array('_current' => true));
    }

    protected function _prepareColumns()
    {
        $this->addColumn('connect_vendor_dhl', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'connect_vendor_dhl',
            'values' => $this->_getSelectedDHLAccounts(),
            'align' => 'center',
            'width' => '50px',
            'index' => 'id'
        ));
        $this->addColumn("dhl_account_vendor", array(
            "index" => "dhl_account",
            "header" => Mage::helper("ghdhl")->__("Account"),
        ));
        $this->addColumn("dhl_login_vendor", array(
            "index" => "dhl_login",
            "header" => Mage::helper("ghdhl")->__("Login"),
        ));

        return parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        if ($id == 'connect_vendor_dhl') {
            $select = $this->getCollection()->getSelect();
            $columnValue = $column->getFilter()->getValue();

            if ($columnValue) {
                $select->join(
                    array('dhl_v' => Mage::getSingleton('core/resource')->getTableName('ghdhl/dhl_vendor')),
                    'main_table.id = dhl_v.dhl_id AND dhl_v.vendor_id = ' . $this->getVendorId(),
                    array());

            } else {
                $select->joinLeft(
                    array('dhl_v' => Mage::getSingleton('core/resource')->getTableName('ghdhl/dhl_vendor')),
                    'main_table.id = dhl_v.dhl_id AND dhl_v.vendor_id = ' . $this->getVendorId(),
                    array())
                    ->where('dhl_v.id is null');
            }

            return $this;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     *
     * @return array
     */
    protected function _getSelectedDHLAccounts()
    {
        $json = $this->getRequest()->getPost('selected_dhl_accounts');

        $vendorDHLAccounts = array();
        if (is_null($json)) {
            $vendorId = $this->getVendorId();

            $collection = Mage::getModel('ghdhl/dhl_vendor')->getCollection();

            $collection->getSelect()
                ->where('main_table.vendor_id = ?', $vendorId);

            foreach ($collection as $vendorDHLAccount) {
                $vendorDHLAccounts[] = $vendorDHLAccount->getData('dhl_id');
            }
        } else {
            $vendorDHLAccounts = array_keys((array)Zend_Json::decode($json));
        }

        return $vendorDHLAccounts;

    }

}