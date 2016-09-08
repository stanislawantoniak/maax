<?php
class Zolago_Po_Model_Resource_Aggregated_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagopo/aggregated');
    }
    /**
     * @param array $posIds
     * @return Zolago_Po_Model_Resource_Aggregated_Collection
     */
    public function addPosFilter(array $posIds = array()) {
        if($posIds) {
            $this->addFieldToFilter("main_table.pos_id", array("in"=>$posIds));
        } else {
            $this->addFieldToFilter("aggregated_id", -1); // Epty collection
        }
        return $this;
    }
    /**
     * @param ZolagoOs_OmniChannel_Model_Vendor | int $vendor
     * @return \Zolago_Po_Model_Resource_Aggregated_Collection
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor) {
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter("vendor_id", $vendor);
        return $this;
    }

    public function joinPosNames() {
        $select = $this->getSelect();

        $select->joinLeft(
            array("pos"=>$this->getTable('zolagopos/pos')),
            "pos.pos_id=main_table.pos_id",
            array("name")
        );
        return $this;
    }
}
