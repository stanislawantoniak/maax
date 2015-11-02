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
        $status = false;
        $helper = Mage::helper('modagointegrator');
        try {
            $helper->createFile($this->_getPath());
            while ($list = $this->prepareList()) {
                foreach ($list as $item) {
                    $block = $this->_prepareXmlBlock($item);
                    $helper->addToFile($block);
                }
            } 
            $helper->closeFile();
            $status = true;
        } catch (Mage_Core_Exception $ex) {
            Mage::logException($ex);
            $helper->closeFile();
            return false;
        }
        return $status;
    }
    
    
    /**
     * uploading file
     *
     */
     public function uploadFile() {
     }

}