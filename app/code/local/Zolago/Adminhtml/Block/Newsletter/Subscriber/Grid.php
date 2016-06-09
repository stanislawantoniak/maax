<?php

class Zolago_Adminhtml_Block_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    protected $_joined = false;

    public function getCollection() {
        if (!Mage::helper("zolagonewsletter")->isModuleActive())
            return parent::getCollection();

        $coll = parent::getCollection();
        /* @var $coll Mage_Newsletter_Model_Resource_Subscriber_Collection */
        if(!$this->_joined){

            /** @var Zolago_Newsletter_Model_Resource_Subscriber_Collection $zolagoCollection */
            $zolagoCollection = Mage::getResourceModel("zolagonewsletter/subscriber_collection");
            $zolagoCollection->addCoupons($coll);

            $this->_joined = true;
        }

        return $coll;
    }

    protected function _prepareColumns()
    {
        if (!Mage::helper("zolagonewsletter")->isModuleActive())
            return parent::_prepareColumns();

        $return = parent::_prepareColumns();

        $this->addColumn('coupon_code',array(
            'header'    => Mage::helper('newsletter')->__('Coupon sent'),
            'index'     => 'coupon_code',
            'default'   => Mage::helper("adminhtml")->__("No"),
            'filter'    => false
        ));

        return $return;
    }
}