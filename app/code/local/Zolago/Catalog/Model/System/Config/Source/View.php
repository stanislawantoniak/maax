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
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PENDING,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PENDING
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_EXPORTED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_EXPORTED
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_RETURNED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_RETURNED
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ACK,
                  'label' => Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ACK)),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_BACKORDER,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_BACKORDER
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ONHOLD,
                  'label' => Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ONHOLD)),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_READY,
                  'label' => Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_READY)),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PARTIAL,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PARTIAL
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_SHIPPED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_SHIPPED
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_CANCELED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_CANCELED
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_DELIVERED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_DELIVERED
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_READY,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_READY
                      )),
            array('value' => ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED,
                  'label' => Mage::helper("udpo")->getPoStatusName(
                          ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED
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
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PENDING          =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PENDING),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_EXPORTED         =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_EXPORTED),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_RETURNED         =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_RETURNED),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ACK              =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ACK),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_BACKORDER        =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_BACKORDER),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ONHOLD           =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ONHOLD),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_READY            =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_READY),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PARTIAL          =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_PARTIAL),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_SHIPPED          =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_SHIPPED),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_CANCELED         =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_CANCELED),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_DELIVERED        =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_DELIVERED),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_READY    =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_READY),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED),
            ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED =>
                Mage::helper("udpo")->getPoStatusName(ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED)
        );
    }
}
