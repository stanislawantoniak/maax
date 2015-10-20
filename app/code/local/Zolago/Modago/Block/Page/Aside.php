<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 14.05.14
 */

class Zolago_Modago_Block_Page_Aside extends Zolago_Modago_Block_Catalog_Category
{    
    /**
     * use cache     
     */
    protected function _construct() {
        $this->setCacheLifetime(Zolago_Common_Block_Page_Html_Head::BLOCK_CACHE_TTL);
        $storeId = Mage::app()->getStore()->getId();
        $this->setCacheKey('block_page_aside_'.$storeId);
    }
                        

} 