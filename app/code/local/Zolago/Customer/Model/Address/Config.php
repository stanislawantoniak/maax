<?php

/**
 * Class Zolago_Customer_Model_Address_Config
 */
class Zolago_Customer_Model_Address_Config extends Mage_Customer_Model_Address_Config {
    /**
     * Retrieve address formats
     *
     * @return array
     */
    public function getFormats()
    {
        $store = $this->getStore();
        $storeId = $store->getId();
        if (!isset($this->_types[$storeId])) {
            $this->_types[$storeId] = array();
            foreach ($this->getNode('formats')->children() as $typeCode => $typeConfig) {
                Mage::log($typeCode, null, "address3.log");
                $path = sprintf('%s%s', self::XML_PATH_ADDRESS_TEMPLATE, $typeCode);
                $type = new Varien_Object();
                $htmlEscape = strtolower($typeConfig->htmlEscape);
                $htmlEscape = $htmlEscape == 'false' || $htmlEscape == '0' || $htmlEscape == 'no'
                || !strlen($typeConfig->htmlEscape) ? false : true;
                $type->setCode($typeCode)
                    ->setTitle((string)$typeConfig->title)
                    ->setDefaultFormat(Mage::getStoreConfig($path, $store))
                    ->setHtmlEscape($htmlEscape);

                $renderer = (string)$typeConfig->renderer;
                if (!$renderer) {
                    $renderer = self::DEFAULT_ADDRESS_RENDERER;
                }

                $type->setRenderer(
                    Mage::helper('customer/address')->getRenderer($renderer)->setType($type)
                );

                $this->_types[$storeId][] = $type;
            }
        }

        return $this->_types[$storeId];
    }
}