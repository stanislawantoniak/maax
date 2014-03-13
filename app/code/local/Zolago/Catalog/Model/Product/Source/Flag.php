<?php
/** 
 *source for flag options
 */
class Zolago_Catalog_Model_Product_Source_Flag 
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array (
                array (
                    'value' => '1',
                    'label' => 'Promotion',
                ),
                array (
                    'value' => '2',
                    'label' => 'Bestseller',
                ),
                array (
                    'value' => '3',
                    'label' => 'New',
                ),
                array (
                    'value' => '4',
                    'label' => 'Sale',
                ),
            );
        } 
        return $this->_options;
    }
}