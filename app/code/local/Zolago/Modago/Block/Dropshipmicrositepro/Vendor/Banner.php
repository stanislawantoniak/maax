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

    const BANNER_INSPIRATION_WIDTH = 203;
    const BANNER_INSPIRATION_HEIGHT = 304;

    const BANNER_RESIZE_DIRECTORY = 'bannerresized';


    public function getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }


    public function getBannerPositions()
    {
        $data = array();
        $vendor = $this->getVendor();
        if(!empty($vendor)){
            $vendorId = $vendor->getId();
            $rootCatId = $vendor->getRootCategory();
            $rootCatId = reset($rootCatId);

            if (empty($rootCatId)) {
                $rootCatId = Mage::app()->getStore()->getRootCategoryId();
            }

            //get current category
            $currentCategory = Mage::registry('current_category');
            if (!empty($currentCategory) && $currentCategory->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                $rootCatId = $currentCategory->getId();
            }


            $campaignModel = Mage::getResourceModel('zolagocampaign/campaign');
            $placements = $campaignModel->getCategoryPlacements($rootCatId, $vendorId,
                array(Zolago_Banner_Model_Banner_Type::TYPE_SLIDER, Zolago_Banner_Model_Banner_Type::TYPE_BOX)
            );


            $placementsByTypeBySlot = array();
            $elementsInSlot = array();
            $numberPositions = array();

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

                    $placementsByTypeBySlot[$placement['type']][$placement['position']][$placement['priority']] = $placement;
                }


                foreach ($placementsByTypeBySlot as $type => $placementsByTypeBySlotItem) {
                    foreach ($placementsByTypeBySlotItem as $position => $positionItem) {
                        $elementsInSlot[$type][] = count($positionItem);
                        $numberPositions[$type][] = $position;
                    }
                }
                unset($type);
                unset($position);

                foreach ($elementsInSlot as $type => $elements) {
                    $max = max($elements);
                    for ($n = 1; $n <= $max; $n++) { //priority
                        for ($i = 1; $i <= count($numberPositions[$type]); $i++) { //position
                            if (isset($placementsByTypeBySlot[$type][$i][$n])) {
                                $data['items'][$type][] = $placementsByTypeBySlot[$type][$i][$n];
                            } else {
                                //start from the beginning
                                $data['items'][$type][] = $placementsByTypeBySlot[$type][$i][$n - $i - 1];
                            }
                        }
                    }
                }
                $data['positions'] = $numberPositions;
                unset($elementsInSlot);
                unset($numberPositions);
            }
            if(!empty($imagesToScale)){
                $this->scaleBannerImages($imagesToScale);
            }
        }

        return $data;

    }



    public function getBannerInspirationPositions()
    {
        $data = array();
        $vendor = $this->getVendor();

        if(!empty($vendor)){
            $vendorId = $vendor->getId();
            $rootCatId = $vendor->getRootCategory();
            $rootCatId = reset($rootCatId);

            if (empty($rootCatId)) {
                $rootCatId = Mage::app()->getStore()->getRootCategoryId();
            }

            //get current category
            $currentCategory = Mage::registry('current_category');
            if (!empty($currentCategory) && $currentCategory->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                $rootCatId = $currentCategory->getId();
            }


            $campaignModel = Mage::getResourceModel('zolagocampaign/campaign');
            $placements = $campaignModel->getCategoryPlacements($rootCatId, $vendorId, array(Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION));


            $placementsByTypeBySlot = array();
            $elementsInSlot = array();
            $numberPositions = array();

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

                    $placementsByTypeBySlot[$placement['type']][$placement['position']][$placement['priority']] = $placement;
                }


                foreach ($placementsByTypeBySlot as $type => $placementsByTypeBySlotItem) {
                    foreach ($placementsByTypeBySlotItem as $position => $positionItem) {
                        $elementsInSlot[$type][] = count($positionItem);
                        $numberPositions[$type][] = $position;
                    }
                }
                unset($type);
                unset($position);

                foreach ($elementsInSlot as $type => $elements) {
                    $max = max($elements);
                    for ($n = 1; $n <= $max; $n++) { //priority
                        for ($i = 1; $i <= count($numberPositions[$type]); $i++) { //position
                            if (isset($placementsByTypeBySlot[$type][$i][$n])) {
                                $data['items'][$type][] = $placementsByTypeBySlot[$type][$i][$n];
                            } else {
                                //start from the beginning
                                $data['items'][$type][] = $placementsByTypeBySlot[$type][$i][$n - $i - 1];
                            }
                        }
                    }
                }
                $data['positions'] = $numberPositions;
                unset($elementsInSlot);
                unset($numberPositions);
            }

            if(!empty($imagesToScale)){
                $this->scaleBannerImages($imagesToScale);
            }
        }


        return $data;

    }

    /**
     * @param $imagesToScale
     */
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
                    case Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION:
                        foreach ($imagesToScaleData as $boxImageData) {
                            foreach ($boxImageData as $boxImage) {
                                $imageBoxPath = Mage::getBaseDir('media') . $boxImage['path'];
                                $imageBoxResizePath = Mage::getBaseDir('media') . DS . $this->getImageResizePath($type) . $boxImage['path'];

                                $this->scaleBannerImage($imageBoxPath, $imageBoxResizePath, self::BANNER_INSPIRATION_WIDTH, self::BANNER_INSPIRATION_HEIGHT);
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