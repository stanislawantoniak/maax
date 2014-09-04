<?php

/**
 * User: Victoria Sultanovska
 */
class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner extends Mage_Core_Block_Template
{

    const BANNER_SLIDER_WIDTH = 969;
    const BANNER_SLIDER_HEIGHT = 327;

    const BANNER_BOX_WIDTH = 280;
    const BANNER_BOX_HEIGHT = 323;

    const BANNER_INSPIRATION_WIDTH = 280;
    const BANNER_INSPIRATION_HEIGHT = 323;

    const BANNER_RESIZE_DIRECTORY = 'bannerresized';


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
        $campaignModel = Mage::getResourceModel('zolagocampaign/campaign');
        $placements = $campaignModel->getCategoryPlacements($rootCatId, $vendorId);

        $bannersByType = array();

        $imagesToScale = array();
        if (!empty($placements)) {
            foreach ($placements as $placement) {
                if ($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE) {
                    $images = unserialize($placement['banner_image']);
                    $placement['images'] = $images;
                    $imagesToScale[$placement['type']][] = $images;
                    $placement['caption'] = unserialize($placement['banner_caption']);
                }
                if ($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_HTML) {

                }
                unset($placement['banner_image']);
                unset($placement['banner_caption']);

                $bannersByType[$placement['type']][] = $placement;
            }
        }
        if(!empty($imagesToScale)){
            $this->scaleBannerImages($imagesToScale);
        }

        return $bannersByType;

    }


    public function scaleBannerImages($imagesToScale)
    {
        if (!empty($imagesToScale)) {
            foreach ($imagesToScale as $type => $imagesToScaleData) {
                switch ($type) {
                    case Zolago_Banner_Model_Banner_Type::TYPE_SLIDER:
                        foreach ($imagesToScaleData as $sliderImageData) {
                            foreach ($sliderImageData as $sliderImage) {

                                $imageSliderPath = Mage::getBaseDir('media') . $sliderImage['path'];
                                $imageSliderResizePath = Mage::getBaseDir('media') . DS . $this->getImageResizePath($type) . $sliderImage['path'];

                                $this->scaleBannerImage($imageSliderPath, $imageSliderResizePath, self::BANNER_SLIDER_WIDTH, self::BANNER_SLIDER_HEIGHT);

                            }
                        }
                        unset($sliderImageData);
                        break;
                    case Zolago_Banner_Model_Banner_Type::TYPE_BOX:
                        foreach ($imagesToScaleData as $boxImageData) {
                            foreach ($boxImageData as $boxImage) {
                                $imageBoxPath = Mage::getBaseDir('media') . $boxImage['path'];
                                $imageBoxResizePath = Mage::getBaseDir('media') . DS . $this->getImageResizePath($type) . $boxImage['path'];

                                $this->scaleBannerImage($imageBoxPath, $imageBoxResizePath, self::BANNER_BOX_WIDTH, self::BANNER_BOX_HEIGHT);
                            }
                        }
                        unset($sliderImageData);
                        break;
                }
            }
        }
    }


    public function getImageResizePath($type)
    {
        return self::BANNER_RESIZE_DIRECTORY . DS . $type;
    }

    public function scaleBannerImage($imagePath, $imageResizePath, $width, $height)
    {
        //Zend_Debug::dump($imageSliderPath, $imageSliderResizePath);
        $image = new Varien_Image($imagePath);

        $image->constrainOnly(false);
        $image->keepFrame(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->keepAspectRatio(true);
        $image->resize($width, $height);
        $image->save($imageResizePath);
    }
} 