<?php

/**
 * Class Zolago_Adminhtml_Block_Customer_Activity_Login_Grid
 */
class Zolago_Adminhtml_Block_Customer_Activity_Login_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('Customer_Activity_Login_Grid');
        $this->setDefaultSort('login_at');
    }

    protected function _prepareCollection() {
        /** @var Zolago_Log_Model_Resource_Customer_Collection $collection */
        $collection = Mage::getResourceModel('zolagolog/customer_collection');
        $collection->addCustomerFilter($this->getCustomerId());

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        /** @var Zolago_Log_Helper_Data $helper */
        $helper = Mage::helper('zolagolog');
        $this->addColumn('login_at', array(
            'header' => $helper->__('Login at'),
            'index'  => 'login_at',
            'type'   => 'datetime'
        ));
        $this->addColumn('logout_at', array(
            'header' => $helper->__('Logout at'),
            'index'  => 'logout_at',
            'type'   => 'datetime'
        ));
        return parent::_prepareColumns();
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function getCustomerId() {
        return (int)$this->getRequest()->getParam('id');
    }
}
