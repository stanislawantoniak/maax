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

    public function setTemplate($string) {
        return parent::setTemplate('zolagoadminhtml/page/footer.phtml');
    }
}