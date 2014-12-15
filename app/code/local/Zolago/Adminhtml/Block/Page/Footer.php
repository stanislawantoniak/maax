<?php
/**
 * removing magento trademarks
 */
class Zolago_Adminhtml_Block_Page_Footer extends Mage_Adminhtml_Block_Page_Footer {
    
    /**
     * overriding setTemplate for all footers
     * @param string $string
     * @return 
     */

    public function getTemplate() {
		if(Mage::getDesign()->getTheme('layout')=="admintheme"){
			return 'zolagoadminhtml/page/footer.phtml';
		}
		return parent::getTemplate();
    }

}