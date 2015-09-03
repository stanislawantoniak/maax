<?php

class Zolago_Banner_Model_Finder extends Varien_Object
{
	
	public function __construct() {
		$inItems = func_get_arg(0);
		
		if(!($inItems instanceof Zolago_Campaign_Model_Resource_Placement_Collection)){
			Mage::throwException("No banner items");
		}
		
		parent::__construct(array(
			"items" => $inItems
		));
	}

    /**
     * Filtering collection
     *
     * @param $filter
     * @return Varien_Data_Collection
     * @throws Exception
     */
    public function filter($filter) {
        $status     = $filter->getCampaignStatus();
        $date       = $filter->getDate();
        $type       = $filter->getType();
        $bannerShow = $filter->getBannerShow();
        $onlyValid  = $filter->getOnlyValid();
        $out        = array();
        /** @var Zolago_Campaign_Model_Resource_Placement_Collection $coll */
        $coll       = $this->getItems();

        /** @var Zolago_Campaign_Model_Placement $item */
        foreach ($coll as $item) {
            // Status filtering
            if ($status !== null) {
                if ($item->getData('campaign_status') != $status) {
                    continue;
                }
            }
            // Date filtering
            if ($date !== null) {
                if ($item->getData('campaign_date_from')) {
                    if ($date < $item->getData('campaign_date_from')) {
                        continue;
                    }
                }
                if ($item->getData('campaign_date_to')) {
                    if ($date > $item->getData('campaign_date_to')) {
                        continue;
                    }
                }
            }
            // Banner type filtering
            if ($type !== null) {
                if ($item->getData('type') != $type) {
                    continue;
                }
            }
            // Banner show filtering
            if ($bannerShow !== null) {
                if ($item->getData('banner_show') != $bannerShow) {
                    continue;
                }
            }

            // Validating
            // * Image data must be set correctly
            // * Image must exists
            if ($onlyValid !== null) {
                $bannerImageData = $item->getBannerImageData();
                if(empty($bannerImageData)) {
                    continue; // Only valid
                }

                // Image file must exist
                // Two cases
                // First:
                // array("url" => "...", "path" => "...")
                // Second:
                // array(array("url" => "...", "path" => "..."), [array("url" => "...", "path" => "...")])
                if (isset($bannerImageData["path"])) {
                    if (!is_file(getcwd() . '/media' . $bannerImageData["path"])) {
                        continue;
                    }
                } else {
                    foreach ($bannerImageData as $value) {
                        if (!is_file(getcwd() . '/media' . $value["path"])) {
                            continue;
                        }

                    }
                }
            }

            if (empty($out[$item->getData('position')]) || ($out[$item->getData('position')]['priority']>$item->getData('priority'))) {
                $out[$item->getData('position')] = $item;
            }
        }
        if ($out) {
            ksort($out);
        }

        // Make collection
        $filteredCollection = new Varien_Data_Collection();
        foreach ($out as $item) {
            $filteredCollection->addItem($item);
        }
        return $filteredCollection;
    }
}