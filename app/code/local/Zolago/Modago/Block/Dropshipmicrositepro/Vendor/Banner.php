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

    const BANNER_INSPIRATION_WIDTH = 600;
    const BANNER_INSPIRATION_HEIGHT = 418;

    const BANNER_RESIZE_DIRECTORY = 'bannerresized';
    const BANNER_RESIZE_M_DIRECTORY = 'bannerresized/mobile';

    /**
     * Cache for loaded vendors
     * @var array
     */
    protected $_vendors = array();

    protected $bannerTypeFilter = array(
        Zolago_Banner_Model_Banner_Type::TYPE_SLIDER,
        Zolago_Banner_Model_Banner_Type::TYPE_BOX,
        Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION,
    );

    /**
     * Set banner filter type
     * Param can by string or array of strings
     * For banner types @see Zolago_Banner_Model_Banner_Type
     *
     * @param $value
     * @return $this
     */
    public function setBannerTypeFilter($value) {
        if (!is_array($value)) {
            $value = array($value);
        }
        $this->bannerTypeFilter = $value;
        return $this;
    }

    /**
     * Get array of banner types for future filtering
     *
     * @return array
     */
    public function getBannerTypeFilter() {
        return $this->bannerTypeFilter;
    }

    public function getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }

    /**
     * @return Varien_Object
     */
    protected function _prepareFilter() {
        $filter = new Varien_Object();
        $filter->setBannerShow(Zolago_Banner_Model_Banner_Show::BANNER_SHOW_IMAGE);
        $filter->setCampaignStatus(Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE);
        $filter->setDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $filter->setOnlyValid(true);
        return $filter;
    }

    public function getPlacements($type) {
        $filter = $this->_prepareFilter();
        $filter->setType($type);
        $finder = $this->getFinder();
        return $finder->filter($filter);
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*',
            array(
                //'_current'=>true,       //_current	bool	Uses the current module, controller, action and parameters
                '_use_rewrite' => true,
                "_no_vendor" => TRUE      // home page but not vendor home
            )
        );
    }

	/**
	 * @return Zolago_Banner_Model_Finder
	 */
	public function getFinder() {
		if(!$this->hasData("finder")){
			$vendor = $this->getVendor();
            $isHomePage = $this->getIsHomePage();

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

            }elseif ($this->getSWVendorId()) {
                $vendorId = $this->getSWVendorId();

                $rootCatId = 0;
                if ($isHomePage) {
                    $rootCatId = Mage::app()->getStore()->getRootCategoryId();
                } else {
                    //get current category
                    $currentCategory = Mage::registry('current_category');
                    if (!empty($currentCategory) && $currentCategory->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                        $rootCatId = $currentCategory->getId();
                    }
                }
            }
            else {
                $vendorId = Mage::helper('udropship')->getLocalVendorId();

                $rootCatId = 0;
                if ($isHomePage) {
                    $rootCatId = Mage::app()->getStore()->getRootCategoryId();
                } else {
                    //get current category
                    $currentCategory = Mage::registry('current_category');
                    if (!empty($currentCategory) && $currentCategory->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                        $rootCatId = $currentCategory->getId();
                    }
                }
            }

            // Placements
            /** @var Zolago_Campaign_Model_Resource_Placement_Collection $placementsColl */
            $placementsColl = Mage::getResourceModel("zolagocampaign/placement_collection");
            $placementsColl->addPlacementForCategory($rootCatId, $vendorId, $this->bannerTypeFilter);
			$this->setData("finder", Mage::getModel("zolagobanner/finder", $placementsColl));
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

    public function getImageResizeUrl($type, $path) {
        return Mage::getBaseUrl('media')  . $this->getImageResizePath($type) . $path;
    }

    /**
     * Get website vendor owner
     * @return bool|int
     * @throws Mage_Core_Exception
     */
    public function getSWVendorId()
    {
        /* @var $collection Mage_Core_Model_Mysql4_Website_Collection */
        $collection = Mage::getModel("core/website")->getCollection();
        $collection->addFieldToFilter("have_specific_domain", 1);
        $collection->addFieldToFilter("website_id", Mage::app()->getWebsite()->getWebsiteId());
        $website = $collection->getFirstItem();

        return $website->getVendorId();
    }
}