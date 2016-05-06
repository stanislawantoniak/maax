<?php

class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Header extends Mage_Core_Block_Template
{

    /**
     * Title & description na stronach vendora
     *
     * dwa pola konfiguracyjne w vendorze - wpisywane wprost title i description do wyświetlenia
     * jeśli puste to konfiguracja ogólna z placeholderem {{vendor}}
     *
     */
    protected function _prepareLayout()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $storeId = Mage::app()->getStore()->getId();

            $vendor = $this->getVendor();
            $vendorName = $vendor->getVendorName();

            $vendorLandingPageTitle = $vendor->getData("vendor_landing_page_title");
            if(!empty($vendorLandingPageTitle)){
                $headBlock->setTitle($vendorLandingPageTitle);
            } else {
                $generalVendorLandingPageTitle = Mage::getStoreConfig('design/head_vendor/vendor_landing_page_title', $storeId);
                $title = str_replace("{{vendor}}", $vendorName, $generalVendorLandingPageTitle);
                if(!empty($title)){
                    $headBlock->setTitle($title);
                }
            }


            $vendorLandingPageDescription = $vendor->getData("vendor_landing_page_description");
            if(!empty($vendorLandingPageDescription)){
                $headBlock->setDescription($vendorLandingPageDescription);
            } else {
                $generalVendorLandingPageDescription = Mage::getStoreConfig('design/head_vendor/vendor_landing_page_description', $storeId);
                $description = str_replace("{{vendor}}", $vendorName, $generalVendorLandingPageDescription);
                if(!empty($description)){
                    $headBlock->setDescription($description);
                }
            }


        }
    }
    public function getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }

    public function getDesktopVendorHeaderPanel()
    {
        $vendor = $this->getVendor();
        return $this
            ->getLayout()
            ->createBlock('cms/block')
            ->setBlockId('top-bottom-header-desktop-v-' . $vendor['vendor_id'])
            ->toHtml();
    }

    public function getMobileVendorHeaderPanel()
    {
        $vendor = $this->getVendor();
        return $this
            ->getLayout()
            ->createBlock('cms/block')
            ->setBlockId('top-bottom-header-mobile-v-' . $vendor['vendor_id'])
            ->toHtml();
    }
} 