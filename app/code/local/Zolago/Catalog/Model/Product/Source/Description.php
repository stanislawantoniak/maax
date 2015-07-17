<?php
/**
 * Source for product description status
 */
class Zolago_Catalog_Model_Product_Source_Description
    extends Zolago_Catalog_Model_Product_Source_Abstract {

    const DESCRIPTION_NOT_ACCEPTED = 1;// Nie zatwierdzony
    const DESCRIPTION_WAITING      = 2;// Oczekuje na zatwierdzenie
    const DESCRIPTION_ACCEPTED     = 3;// Zatwierdzony

    public function getAllOptions($withEmpty = false, $defaultValues = false) {

        if (!$this->_options || $this->_force) {
            $res = array();
            foreach (self::toOptionHash($withEmpty) as $index => $value) {
                $res[] = array(
                    'value' => $index,
                    'label' => $value
                );
            }
            $this->_options = $res;
        }
        return $this->_options;
    }

    /**
     * @param bool $withEmpty
     * @return array
     */
    public function toOptionHash($withEmpty = false) {

        $arr = array();
        if ($withEmpty) {
            $arr[''] = Mage::helper("zolagocatalog")->__("* Please select");
        }

        $arr[self::DESCRIPTION_NOT_ACCEPTED] = Mage::helper("zolagocatalog")->__("Description not accepted");
        $arr[self::DESCRIPTION_WAITING     ] = Mage::helper("zolagocatalog")->__("Description waiting for acceptation by admin");
        $arr[self::DESCRIPTION_ACCEPTED    ] = Mage::helper("zolagocatalog")->__("Description accepted");

        return $arr;
    }
}