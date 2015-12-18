<?php
/**
 * Benefits block
 */

class Zolago_Modago_Block_Page_Benefits extends Mage_Core_Block_Template
{

    /**
     * use memcache
     */
     protected function _construct() {
         $this->setCacheLifetime(Zolago_Common_Block_Page_Html_Head::BLOCK_CACHE_TTL);
     }
     
     
    /**
     * prepare html
     */

     protected function _toHtml() {
         return $this->getLayout()->createBlock("cms/block")->setBlockId("benefits-strip")->toHtml();
     }    
} 