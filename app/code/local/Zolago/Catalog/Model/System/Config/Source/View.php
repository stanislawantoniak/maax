<?php

/**
 * PO open for Magento
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 */
class Zolago_Catalog_Model_System_Config_Source_View
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        return array(
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PENDING,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PENDING
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_RETURNED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_RETURNED
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ACK,
                  'label' => Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ACK)),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_BACKORDER,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_BACKORDER
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ONHOLD,
                  'label' => Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ONHOLD)),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_READY,
                  'label' => Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_READY)),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_READY,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_READY
                      )),
            array('value' => Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED
                      ))

        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PENDING          =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PENDING),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED         =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_RETURNED         =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_RETURNED),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ACK              =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ACK),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_BACKORDER        =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_BACKORDER),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ONHOLD           =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_ONHOLD),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_READY            =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_READY),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL          =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED          =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED         =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED        =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_READY    =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_READY),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED),
            Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED =>
                Mage::helper("udpo")->getPoStatusName(Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED)
        );
    }
}
