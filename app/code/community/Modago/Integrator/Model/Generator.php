<?php
/**
 * abstract object - generation file
 */
abstract class Modago_Integrator_Model_Generator
    extends Varien_Object {

    /**
     * returns local path to generated file
     */
    abstrach protected function _getPath();
    
    /**
     * returns content 
     */
    abstract protected function _prepareList();
    
    /**
     * preparing xml text block
     */
     abstract protected function _prepareXmlBlock();
    /**
     * generation file
     * 
     * @return bool
     */
    public function generate() {
        if (!$list = $this->_prepareList()) {
            return false;
        }

        try {
            $helper = Mage::helper('modagointegrator');
            $helper->createFile($this->_getPath());
            foreach ($list as $item) {
                $block = $this->_prepareXmlBlock($item);
                $helper->addToFile($block);
            }
            $helper->closeFile();
        } catch (Mage_Core_Exception $ex) {
            Mage::logException($ex);
            $helper->closeFile();
            return false;
        }
        return true;
    }
    
    
    /**
     * uploading file
     *
     */
     public function uploadFile() {
     }


}