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

    const BANNER_INSPIRATION_WIDTH = 400;
    const BANNER_INSPIRATION_HEIGHT = 600;

    const BANNER_RESIZE_DIRECTORY = 'bannerresized';
    const BANNER_RESIZE_M_DIRECTORY = 'bannerresized/mobile';

    /**
     * Cache for loaded vendors
     * @var array
     */
    protected $_vendors = array();

    protected $boxTypes = array(
        Zolago_Banner_Model_Banner_Type::TYPE_SLIDER,
        Zolago_Banner_Model_Banner_Type::TYPE_BOX
    );


    public function getVendor($vendorId = null)
    {
        if ($vendorId) {
            if (!isset($this->_vendors[$vendorId])) {
                return $this->_vendors[$vendorId] = Mage::getModel("zolagodropship/vendor")->load($vendorId);
            }
        } else {
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            if (!empty($vendor) && $vendor->getId()) {
                return $this->_vendors[$vendor->getId()] = $vendor;
            } else {
                return $vendor;
            }
        }
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

			} else {
                $vendorId = Mage::helper('udropship')->getLocalVendorId();

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
            }

            /** @var Zolago_Campaign_Model_Resource_Campaign $campaignModel */
            $campaignModel = Mage::getResourceModel('zolagocampaign/campaign');
            $placements = $campaignModel->getCategoryPlacements($rootCatId, $vendorId, $this->boxTypes);
            foreach ($placements as $key => $value) {
                $placements[$key]["vendor"] = $this->getVendor($value["campaign_vendor"]);
            }

			$this->setData("finder", Mage::getModel("zolagobanner/finder", $placements));
		}
		return $this->getData("finder");
	}


    static public function getImageResizePath($type)
    {
        return self::BANNER_RESIZE_DIRECTORY . DS . $type;
    }


    static public function getImageResizeMobilePath($type)
    {
        return self::BANNER_RESIZE_M_DIRECTORY . DS . $type;
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