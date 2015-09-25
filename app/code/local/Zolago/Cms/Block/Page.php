<?php
class Zolago_Cms_Block_Page extends Mage_Cms_Block_Page
{
   
    /**
     * Prepare global layout
     *
     * @return Mage_Cms_Block_Page
     */
    protected function _prepareLayout()
    {
        $page = $this->getPage();

        // show breadcrumbs
        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
            && ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_home_page'))
            && ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_no_route'))) {
			
			$vendor = Mage::helper('umicrosite')->getCurrentVendor();        
			
			if($vendor && $vendor->getId()){
				$base = Mage::helper("zolagodropshipmicrosite")->getBaseUrl();
			}else{
				$base = Mage::getBaseUrl();
			}
						
			$breadcrumbs->addCrumb('home', array(
				'label'=>Mage::helper('cms')->__('Home'), 
				'title'=>Mage::helper('cms')->__('Go to Home Page'), 
				'link'=>$base)
			);
			
			// Add "Vendor" crumbs in 3 and more part crumbs
			if($vendor && $vendor->getId() && $page->getIdentifier()!=$vendor->getVendorLandingPage()){
				$breadcrumbs->addCrumb('vendor', array(
					'label'=>Mage::helper('cms')->__($vendor->getVendorName()), 
					'title'=>Mage::helper('cms')->__('Vendor'), 
					'link'=>Mage::getBaseUrl())
				);
			}
			
			$breadcrumbs->addCrumb('cms_page', array(
				'label'=>$page->getTitle(), 
				'title'=>$page->getTitle())
			);
              
        }

        $root = $this->getLayout()->getBlock('root');
        if ($root) {
            $root->addBodyClass('cms-'.$page->getIdentifier());
        }

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($page->getTitle());
            $head->setKeywords($page->getMetaKeywords());
            $head->setDescription($page->getMetaDescription());
        }

        return $this;
    }


	/**
	 * Prepare HTML content
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		/* @var $helper Mage_Cms_Helper_Data */
		$helper = Mage::helper('cms');
		$processor = $helper->getPageTemplateProcessor();
		$html = $processor->filter($this->getPage()->getContent());
		//$html = $this->getMessagesBlock()->toHtml() . $html; //removed because of double messages
		return $html;
	}
}
