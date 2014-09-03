<?php

/**
 * User: Victoria Sultanovska
 */
class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner extends Mage_Core_Block_Template
{
    public function getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }


    public function getBannerPositions()
    {
        $vendor = $this->getVendor();
        $vendorId = $vendor->getId();
        $rootCatId = $vendor->getRootCategory();
        $rootCatId = reset($rootCatId);

        if (empty($rootCatId)) {
            $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        }
        //Zend_Debug::dump($rootCatId);
        $campaignModel = Mage::getResourceModel('zolagocampaign/campaign');
        $placements = $campaignModel->getCategoryPlacements($rootCatId, $vendorId);

        $bannersByType = array();

        if (!empty($placements)) {
            foreach ($placements as $placement) {
//                Zend_Debug::dump($placement);
                if ($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE) {
                    $placement['images'] = unserialize($placement['banner_image']);
                    $placement['caption'] = unserialize($placement['banner_caption']);
                }
                if ($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML) {

                }
                unset($placement['banner_image']);
                unset($placement['banner_caption']);

                $bannersByType[$placement['type']][] = $placement;
            }
        }
        //Zend_Debug::dump($bannersByType);

        return $bannersByType;

    }
} 