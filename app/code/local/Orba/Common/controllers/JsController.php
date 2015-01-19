<?php

class Orba_Common_JsController extends Mage_Core_Controller_Front_Action {
    
    const JS_LIB_EXPIRE_TIME = 31536000;
    
    public function libAction() {
        $_helper = Mage::helper('orbacommon');
        $this->loadLayout();
        $this->renderLayout();
        $expires = self::JS_LIB_EXPIRE_TIME;
		
		Mage::register('turpentine_nocache_flag', 0); // do cache
		
        $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type', 'application/javascript', true)
                ->setHeader('Set-Cookie', '', true)
                ->setHeader('Cache-Control', 'public, cache, must-revalidate, post-check='.$expires.', pre-check='.$expires.', max-age='.$expires, true)
                ->setHeader('Pragma', 'cache', true)
                ->setHeader('Expires', $_helper->timestampToGmtDate(time() + $expires), true);
    }
    
}