<?php class Zolago_Campaign_Model_Source_Campaign
{
    protected $_options = null;

    /**
     * @param bool $withEmpty
     * @param null|int|array $ids
     * @return array|null
     */
    public function toOptionArray($withEmpty = true, $ids = null,$vendorId = null) {
        if ($this->_options === null) {
            $this->_options = $this->getCollection($ids,$vendorId)->toOptionArray();
            // Todo: Make it more usefully by:
            // label => $name ($customerName) $vendor (if Landing page vendor from LP)
        }
        if ($withEmpty) {
            return array_merge(array(array("value" => 0, "label"=>Mage::helper("zolagocampaign")->__("-- empty --"))),$this->_options);
        }
        return $this->_options;
    }

    /**
     * @param $ids
     * @param $vendorId
     * @return Zolago_Campaign_Model_Resource_Campaign_Collection
     */
    private function getCollection($ids,$vendorId) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        /** @var Zolago_Campaign_Model_Resource_Campaign_Collection $coll */
        $coll = Mage::getResourceModel("zolagocampaign/campaign_collection");
        $coll->addStatusFilter(array(Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE, Zolago_Campaign_Model_Campaign_Status::TYPE_INACTIVE));
        if ($vendorId) {
            $coll->addVendorFilter($vendorId);
        }
        if (!empty($ids)) {
            $coll->addFieldToFilter("campaign_id", array(
                array("notnull" => true),
                array("in" => $ids) // Currently set id
            ));
        }
        $coll->setOrder("campaign_id");
        return $coll;
    }

    /**
     * Get websites used in campaigns
     * @return array
     */
    public function getCampaignWebsites()
    {
        $campaignWebsites = Mage::getResourceModel("zolagocampaign/campaign")
            ->getCampaignWebsites();
        return $campaignWebsites;
    }
}
