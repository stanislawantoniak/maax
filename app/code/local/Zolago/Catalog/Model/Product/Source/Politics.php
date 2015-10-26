<?php
/** 
 *source for flag options
 */
class Zolago_Catalog_Model_Product_Source_Politics
        extends Zolago_Catalog_Model_Product_Source_Abstract {

	const FLAG_AUTO = 0;
	const FLAG_MANUAL_DISABLED = 1;
	
    public function getAllOptions() {
        if (!$this->_options || $this->_force) {
            $this->_options = array (
                array (
                    'value' => self::FLAG_AUTO,
                    'label' => Mage::helper('zolagocatalog')->__('Automatic management'),
                ),
                array (
                    'value' => self::FLAG_MANUAL_DISABLED,
                    'label' => Mage::helper('zolagocatalog')->__('Manual out-of-stock'),
                )
            );
        } 
        return $this->_options;
    }
}