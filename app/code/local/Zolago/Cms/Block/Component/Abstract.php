<?php

class Zolago_Cms_Block_Component_Abstract extends Mage_Core_Block_Template {
	
	protected $_component = "";
	
	/**
	 * @return Varien_Object
	 */
	protected function _getRequest() {
		$request = new Varien_Object;
		$request->setVendor(Mage::helper('umicrosite')->getCurrentVendor());
		$request->setCategory(Mage::registry('current_category'));
		return $request;
	}
	
	/**
	 * @return string
	 */
	protected function _toHtml() {
		if($this->_component){
			$request = $this->_getRequest();
			$request->setComponent($this->_component);
			$helper = Mage::helper("zolagocms");
			/* @var $helper Zolago_Cms_Helper_Data */
			if($blockCode = $helper->requestCmsBlockCode($request)){
				$block = $this->_getCmsBlock($blockCode);
				if($block->getId() && $block->getIsActive()){
					/* @var $helper Mage_Cms_Helper_Data */
					$helper = Mage::helper('cms');
					$processor = $helper->getBlockTemplateProcessor();
					$html = $processor->filter($block->getContent());
					$this->addModelTags($block);
					return $html;
				}
			}
		}
		return parent::_toHtml();
	}
	
	
	/**
	 * @param string $blockId
	 * @return Mage_Cms_Model_Block
	 */
	protected function _getCmsBlock($blockId) {
		return Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
	}
}