<?php
/**
 * Source for product description status
 */
class Zolago_Catalog_Model_Product_Source_Description
    extends Zolago_Catalog_Model_Product_Source_Abstract {

    const DESCRIPTION_NOT_ACCEPTED = -1;// Nie zatwierdzony
    const DESCRIPTION_WAITING      =  0;// Oczekuje na zatwierdzenie
    const DESCRIPTION_ACCEPTED     =  1;// Zatwierdzony

    public function getAllOptions() {
        if (!$this->_options || $this->_force) {
            $this->_options = array (
                array (
                    'value' => self::DESCRIPTION_NOT_ACCEPTED,
                    'label' => Mage::helper("zolagocatalog")->__("Description not accepted"),
                ),
                array (
                    'value' => self::DESCRIPTION_WAITING,
                    'label' => Mage::helper("zolagocatalog")->__("Description waiting for acceptation by admin"),
                ),
                array (
                    'value' => self::DESCRIPTION_ACCEPTED,
                    'label' => Mage::helper("zolagocatalog")->__("Description accepted"),
                ),
            );
        }
        return $this->_options;
    }

    /**
     * @param bool $selector
     * @return array
     */
    public function toOptionHash($selector = false) {

        $arr = array();
        if ($selector) {
            $arr[''] = Mage::helper("zolagocatalog")->__("* Please select");
        }

        $arr[self::DESCRIPTION_NOT_ACCEPTED] = Mage::helper("zolagocatalog")->__("Description not accepted");
        $arr[self::DESCRIPTION_WAITING     ] = Mage::helper("zolagocatalog")->__("Description waiting for acceptation by admin");
        $arr[self::DESCRIPTION_ACCEPTED    ] = Mage::helper("zolagocatalog")->__("Description accepted");

        return $arr;
    }
}