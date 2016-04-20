<?php

class ZolagoOs_OmniChannelMicrositePro_Model_Observer
{
    public function cms_page_render($observer)
    {
        if ($observer->getControllerAction()->getFullActionName()=='umicrosite_index_landingPage'
            && ($_vendor = Mage::helper('umicrosite')->getCurrentVendor())
        ) {
            $landingPageTitle = Mage::helper('umicrosite')->getLandingPageTitle();
            $observer->getPage()->setTitle(
                $landingPageTitle
            );
            /*
            $observer->getPage()->setVendorLandingPageTitle(
                $landingPageTitle
            );
            $reviewsSummaryHtml = '';
            if (Mage::helper('udropship')->isModuleActive('ZolagoOs_OmniChannelVendorRatings')) {
                $reviewsSummaryHtml = Mage::helper('udratings')->getReviewsSummaryHtml($_vendor);
                $observer->getPage()->setVendorReviewsSummaryHtml(
                    $reviewsSummaryHtml
                );
            }
            $contentHeadingBlock = $observer->getControllerAction()->getLayout()->getBlock('page_content_heading');
            if ($contentHeadingBlock) {
                $contentHeadingBlock->setVendorLandingPageTitle(
                    $landingPageTitle
                );
                $contentHeadingBlock->setVendorReviewsSummaryHtml(
                    $reviewsSummaryHtml
                );
            }
            */
        }
    }
}