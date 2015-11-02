<?php
/**
 * generating price file
 */
class Modago_Integrator_Model_Generator_Price
    extends Modago_Integrator_Model_Generator {

    protected function _construct() {
        $this->setFileNamePrefix('PRICES');
    }

    /**
     * prepare content
     *
     * @return array
     */
     protected function _prepareList() {
         /// todo                  
         return array();
     }
     
    /**
     *	prepare xml block 
     *
     * @return string
     */
     protected function _prepareXmlBlock($item) {
         return ''; // todo
     }
     
    /**
     * prepare header
     *
     * @return string
     */
     protected function _getHeader() {
         return ''; // todo
     }
     
    /**
     * prepare footer
     *
     * @return string
     */
     protected function _getFooter() {
         return ''; // todo
     }

    
}
