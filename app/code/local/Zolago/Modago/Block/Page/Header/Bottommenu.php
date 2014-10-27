<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 02.05.14
 */

class Zolago_Modago_Block_Page_Header_Bottommenu extends Zolago_Modago_Block_Catalog_Category
{
	public function getBaseUrl() {
		$baseUnsecure = $base = Mage::app()->getStore()->getConfig("web/unsecure/base_url");
		if(Mage::app()->getRequest()->isSecure()){
			$baseSecure = Mage::app()->getStore()->getConfig("web/secure/base_url");
			$base = str_replace("{{base_url}}", $baseUnsecure, $baseSecure);
		}
		return $base;
	}
} 