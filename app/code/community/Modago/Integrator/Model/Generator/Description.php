<?php
/**
 * generating description file
 */
class Modago_Integrator_Model_Generator_Description
    extends Modago_Integrator_Model_Generator {
    
    
    /**
     * file path
     *
     * @return string
     */
     protected function _getPath() {
	     return Mage::getBaseDir('var'). DS . parent::DIRECTORY . DS . $this->getExternalId()."_DESCRIPTION_".Mage::getModel('core/date')->date('Y-m-d_H_i_s').".xml";
     }
     
    /**
     * prepare content
     *
     * @return array
     */
     protected function _prepareList() {
         return array();
         /// todo                  
     }
     
    /**
     *	prepare xml block 
     *
     * @return string
     */
     protected function _prepareXmlBlock($item) {
         // todo 
          return '';   
     }

    
    /**
     * prepare header
     *
     * @return string
     */
     protected function _getHeader() {
         return '';
         // todo
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
