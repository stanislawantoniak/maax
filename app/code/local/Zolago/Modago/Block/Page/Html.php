<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 11.07.2014
 */

class Zolago_Modago_Block_Page_Html extends Mage_Page_Block_Html
{

    /**
     * Add CSS class to page body tag
     *
     * @param string $className
     * @return Mage_Page_Block_Html
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9_]+#', '-', strtolower($className));
        $this->setBodyClass($this->getBodyClass() . ' ' . $className);
        return $this;
    }
/*
	public function getUrl($path) {
		$ref = explode("/",Mage::app()->getRequest()->getServer('HTTP_REFERER'));
		return $ref[0]."//".$ref[2]."/".$path;
	}*/
} 