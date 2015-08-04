<?php

/**
 * User: Victoria Sultanovska
 */
class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner extends Mage_Core_Block_Template
{

    const BANNER_SLIDER_WIDTH = 969;
    const BANNER_SLIDER_HEIGHT = 327;

    const BANNER_SLIDER_M_WIDTH = 320;
    const BANNER_SLIDER_M_HEIGHT = 316;

    const BANNER_BOX_WIDTH = 280;
    const BANNER_BOX_HEIGHT = 323;

    const BANNER_INSPIRATION_WIDTH = 203;
    const BANNER_INSPIRATION_HEIGHT = 304;

    const BANNER_RESIZE_DIRECTORY = 'bannerresized';
    const BANNER_RESIZE_M_DIRECTORY = 'bannerresized/mobile';

    protected $boxTypes = array(
        Zolago_Banner_Model_Banner_Type::TYPE_SLIDER,
        Zolago_Banner_Model_Banner_Type::TYPE_BOX
    );


    public function getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }


    /**
     * 
     * @return Varien_Object
     */
    protected function _prepareRequest() {
            $time = Mage::getSingleton('core/date')->timestamp();
		$request = new Varien_Object();
		$request->setBannerShow("image");
		$request->setStatus(1); // only active
		$request->setDate(date('Y-m-d H:i:s',$time)); // only not expired
		return $request;
    }

    public function getBoxes() {
		
		$finder = $this->getFinder();
		$request = $this->_prepareRequest();
		$request->setType(Zolago_Banner_Model_Banner_Type::TYPE_BOX);
		return $finder->request($request);
    }	
	
	public function getSliders() {
		$request = $this->_prepareRequest();
		$request->setType(Zolago_Banner_Model_Banner_Type::TYPE_SLIDER);		
		$finder = $this->getFinder();
		return $finder->request($request);
	}
	
	/**
	 * @return Zolago_Banner_Model_Finder
	 */
	public function getFinder() {
		if(!$this->hasData("finder")){
			$placements = array();
			$vendor = $this->getVendor();

			if(!empty($vendor)){
				$vendorId = $vendor->getId();
                $rootCatId = $vendor->getRootCategory();
                $websiteId = Mage::app()->getWebsite()->getId();
                $rootCatId = isset($rootCatId[$websiteId]) ? $rootCatId[$websiteId] : 0;

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
				    $this->boxTypes
				);


                $imagesToScale = array();
                if (!empty($placements)) {
                    foreach ($placements as $placement) {
                        if ($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE) {
                            $images = unserialize($placement['banner_image']);
                            $placement['images'] = $images;
                            $imagesToScale[$placement['type']][] = $images;
                        }
                    }
                }

                if(!empty($imagesToScale)){
                    $this->scaleBannerImages($imagesToScale);
                }

			} else {
                $localVendorId = Mage::helper('udropship')->getLocalVendorId();

                $rootCatId = 0;
                if (Mage::getBlockSingleton('page/html_header')->getIsHomePage()) {
                    $rootCatId = Mage::app()->getStore()->getRootCategoryId();
                } else {
                    //get current category
                    $currentCategory = Mage::registry('current_category');
                    if (!empty($currentCategory) && $currentCategory->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                        $rootCatId = $currentCategory->getId();
                    }
                }


                $campaignModel = Mage::getResourceModel('zolagocampaign/campaign');

                $placements = $campaignModel->getCategoryPlacements($rootCatId, $localVendorId,
                    $this->boxTypes
                );


                $imagesToScale = array();
                if (!empty($placements)) {
                    foreach ($placements as $placement) {
                        if ($placement['banner_show'] == Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE) {
                            $images = unserialize($placement['banner_image']);
                            $placement['images'] = $images;
                            $imagesToScale[$placement['type']][] = $images;
                        }
                    }
                }

                if(!empty($imagesToScale)){
                    $this->scaleBannerImages($imagesToScale);
                }
            }
			$this->setData("finder", Mage::getModel("zolagobanner/finder", $placements));
		}
		return $this->getData("finder");
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
/*                        foreach ($imagesToScaleData as $sliderImageData) {
                            foreach ($sliderImageData as $sliderImage) {

                                $imageSliderPath = Mage::getBaseDir('media') . $sliderImage['path'];
                                $imageSliderResizePath = Mage::getBaseDir('media') . DS . $this->getImageResizePath($type) . $sliderImage['path'];
                                $imageSliderResizePathMobile = Mage::getBaseDir('media') . DS . $this->getImageResizeMobilePath($type) . $sliderImage['path'];

                                //Desktop
                                $this->scaleBannerImage($imageSliderPath, $imageSliderResizePath, self::BANNER_SLIDER_WIDTH);
                                //Mobile
                                $this->scaleBannerImage($imageSliderPath, $imageSliderResizePathMobile, self::BANNER_SLIDER_M_WIDTH);

                            }
                        }
                        unset($sliderImageData);*/
                        break;
                    case Zolago_Banner_Model_Banner_Type::TYPE_BOX:
/*                        foreach ($imagesToScaleData as $boxImageData) {
                            foreach ($boxImageData as $boxImage) {
                                $imageBoxPath = Mage::getBaseDir('media') . $boxImage['path'];
                                $imageBoxResizePath = Mage::getBaseDir('media') . DS . $this->getImageResizePath($type) . $boxImage['path'];

                                $this->scaleBannerImage($imageBoxPath, $imageBoxResizePath, self::BANNER_BOX_WIDTH, self::BANNER_BOX_HEIGHT);
                            }
                        }
                        unset($sliderImageData);*/
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


    public function getImageResizeMobilePath($type)
    {
        return self::BANNER_RESIZE_M_DIRECTORY . DS . $type;
    }

    public function scaleBannerImage($imagePath, $imageResizePath, $width, $height=null)
    {
        try
        {
            $image = new Varien_Image($imagePath);
	        if(!is_null($height)) {
		        $image->constrainOnly(false);
		        $image->keepFrame(true);
		        $image->backgroundColor(array(255, 255, 255));
	        }
            $image->keepAspectRatio(true);
            $image->resize($width, $height);
            $image->save($imageResizePath);
        }
        catch(Exception $e)
        {
            //Mage::log('No banner image', Zend_Log::ALERT);
        }
    }

	public function imageExists($imagePath) {
		$image = getcwd().'/media'.$imagePath;
		return file_exists($image);
	}

	public function getImageSize($imagePath) {
		$image = getcwd().'/media'.$imagePath;
		if(file_exists($image)) {
			try {
				return getimagesize($image);
			} catch(Exception $e) {
				//do nothing
			}
		}
		return false;
	}

	public function getImageRatio($imagePath) {
		$data = $this->getImageSize($imagePath);
		if(is_array($data)) {
			return $data[0] / $data[1];
		}
		return 0;
	}

	public function getImageUrl($imagePath) {
		return rtrim(Mage::getBaseUrl('media'),'/') . $imagePath;
	}
}