<?php
class Zolago_Page_Block_Html_Footer extends Mage_Page_Block_Html_Footer
{

    
    /**
     * cache footer
     */
     protected function _construct()
     {
         $this->setCacheLifetime(Zolago_Common_Block_Page_Html_Head::BLOCK_CACHE_TTL);
     }
    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'PAGE_FOOTER',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->isLoggedIn(),
			Mage::helper('persistent/session')->isPersistent()
        );
    }


}
