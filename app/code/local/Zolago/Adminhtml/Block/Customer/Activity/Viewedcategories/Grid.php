<?php

/**
 * Class Zolago_Adminhtml_Block_Customer_Activity_Login_Grid
 */
class Zolago_Adminhtml_Block_Customer_Activity_Viewedcategories_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('Customer_Activity_Viewedcategories_Grid');
        $this->setDefaultSort('visit_time');
    }

    protected function _prepareCollection() {
        /** @var Zolago_Log_Model_Resource_Url_Varien_Collection $collection */
        $collection = Mage::getResourceModel("zolagolog/url_varien_collection");
        $collection->addCustomerUrlFilter($this->getCustomerId());

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        /** @var Zolago_Log_Helper_Data $helper */
        $helper = Mage::helper("zolagolog");

        $this->addColumn('referer', array(
            'header' => $helper->__('Url'),
            'index'  => 'referer',
        ));

        $this->addColumn('visit_time', array(
            'header' => $helper->__('Visit'),
            'index'  => 'visit_time',
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
