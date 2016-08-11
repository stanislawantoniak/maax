<?php

/**
 * Grid for data from beacons - offline customer history
 *
 * Class Zolago_Adminhtml_Block_Customer_Beacon_Data_Grid
 */
class Zolago_Adminhtml_Block_Customer_Beacon_Data_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('Customer_Beacon_Data_Grid');
        $this->setDefaultSort('date');
    }

    protected function _prepareCollection() {
        /** @var GH_Beacon_Model_Resource_Data_collection $collection */
        $collection = Mage::getResourceModel('ghbeacon/data_collection');

        /** @var Zolago_Customer_Model_Customer $customer */
        $customer = Mage::getModel('zolagocustomer/customer');
        $customer->load($this->getCustomerId());
        $email = $customer->getEmail();

        $collection->addEmailFilter($email);

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        /** @var GH_Beacon_Helper_Data $helper */
        $helper = Mage::helper('ghbeacon');

        $this->addColumn('email', array(
            'header' => $helper->__('Email'),
            'index'  => 'email',
        ));

        $this->addColumn('beacon_id', array(
            'header' => $helper->__('Beacon ID'),
            'index'  => 'beacon_id',
        ));


        $this->addColumn('distance', array(
            'header' => $helper->__('Distance'),
            'index'  => 'distance',
        ));

        $this->addColumn('date', array(
            'header' => $helper->__('Date'),
            'index'  => 'date',
            'type'   => 'datetime'
        ));

        $this->addColumn('event_type', array(
            'header'  => $helper->__('Event'),
            'index'   => 'event_type',
            'type'    => 'options',
            'options' => Mage::getSingleton('ghbeacon/source_data_eventtype')->toOptionArray()
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
