<?php
/** 
 *source for rating options
 */
class Zolago_Catalog_Model_Product_Source_Rating
        extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array (
                array (
                    'value' => '0',
                    'label' => 'No rating',
                ),
                array (
                    'value' => '1',
                    'label' => '1',
                ),
                array (
                    'value' => '2',
                    'label' => '2',
                ),
                array (
                    'value' => '3',
                    'label' => '3',
                ),
                array (
                    'value' => '4',
                    'label' => '4',
                ),
                array (
                    'value' => '5',
                    'label' => '5',
                ),
            );
        } 
        return $this->_options;
    }
}