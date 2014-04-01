<?php
class Zolago_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    
    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Breadcrumbs
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
			// Add vendor
			$vendor = Mage::helper('umicrosite')->getCurrentVendor();        
			$url = Mage::helper('core/http')->getHttpReferer();
			
			$baseUnsecure = $base = Mage::app()->getStore()->getConfig("web/unsecure/base_url");
			if(Mage::app()->getRequest()->isSecure()){
				$baseSecure = Mage::app()->getStore()->getConfig("web/secure/base_url");
				$base = str_replace("{{base_url}}", $baseUnsecure, $baseSecure);
			}
			
			if($vendor->getId()){
				$breadcrumbsBlock->addCrumb('home', array(
					'label'=>Mage::helper('catalog')->__('Home'),
					'title'=>Mage::helper('catalog')->__('Go to Home Page'),
					'link'=>$base
				));
				$breadcrumbsBlock->addCrumb('vendor', array(
					'label'=>Mage::helper('catalog')->__($vendor->getVendorName()),
					'title'=>Mage::helper('catalog')->__('Vendor'),
					'link'=>Mage::getBaseUrl()
				));
			}else{
				$breadcrumbsBlock->addCrumb('home', array(
					'label'=>Mage::helper('catalog')->__('Home'),
					'title'=>Mage::helper('catalog')->__('Go to Home Page'),
					'link'=>Mage::getBaseUrl()
				));
			}

            $title = array();
            $path  = Mage::helper('catalog')->getBreadcrumbPath();

            foreach ($path as $name => $breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                $title[] = $breadcrumb['label'];
            }

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle(join($this->getTitleSeparator(), array_reverse($title)));
            }
        }
        return $this;
    }
}
