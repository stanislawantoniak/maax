<?php class Zolago_Campaign_Model_Source_Campaign
{
    protected $_options = null;

    /**
     * @param bool $withEmpty
     * @param null|int|array $ids
     * @return array|null
     */
    public function toOptionArray($withEmpty = true, $ids = null) {
        if ($this->_options === null) {
            $this->_options = $this->getCollection($ids)->toOptionArray();
            // Todo: Make it more usefully by:
            // label => $name ($customerName) $vendor (if Landing page vendor from LP)
        }
        if ($withEmpty) {
            return array_merge( $this->_options,array(array("value" => 0, "label"=>Mage::helper("zolagocampaign")->__("Please select"))));
        }
        return $this->_options;
    }

    /**
     * @param $ids
     * @return Zolago_Campaign_Model_Resource_Campaign_Collection
     */
    private function getCollection($ids) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        /** @var Zolago_Campaign_Model_Resource_Campaign_Collection $coll */
        $coll = Mage::getResourceModel("zolagocampaign/campaign_collection");
        $coll->addStatusFilter(array(Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE, Zolago_Campaign_Model_Campaign_Status::TYPE_INACTIVE));
        if (!empty($ids)) {
            $coll->addFieldToFilter("campaign_id", array(
                array("notnull" => true),
                array("in" => $ids) // Currently set id
            ));
        }
        $coll->setOrder("campaign_id");
        return $coll;
    }
}
