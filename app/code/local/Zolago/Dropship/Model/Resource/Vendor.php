<?php
class Zolago_Dropship_Model_Resource_Vendor extends ZolagoOs_OmniChannel_Model_Mysql4_Vendor
{
	
	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return array
	 */
	public function getChildVendorIds(Mage_Core_Model_Abstract $object) {
		$select = $this->getReadConnection()->select();
		$select->from(array("vendor"=>$this->getMainTable()), array("vendor_id"));
		$select->where("vendor.super_vendor_id=?", $object->getId());
		return $this->getReadConnection()->fetchCol($select);
	}
	
	
   	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return array
	 */
	public function getAllowedPos(Mage_Core_Model_Abstract $object) {
		if(!$object->getId()){
			return array();
		}
		$select = $this->getReadConnection()->select();
		$select->from(array("pos_vendor"=>$this->getTable("zolagopos/pos_vendor")), array("pos_id"));
		$select->where("pos_vendor.vendor_id=?", $object->getId());
		return $this->getReadConnection()->fetchCol($select);
	}
   	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return array
	 */
	public function getActivePos(Mage_Core_Model_Abstract $object) {
		if(!$object->getId()){
			return array();
		}
		$select = $this->getReadConnection()->select();
		$select->from(array("pos_vendor"=>$this->getTable("zolagopos/pos_vendor")), array("pos_id"));
		$select->join(
		    array("pos" => $this->getTable("zolagopos/pos")),
		    "pos.pos_id = pos_vendor.pos_id",
		    array("pos.external_id")
                );
		$select->where("pos_vendor.vendor_id=?", $object->getId());
		$select->where("pos.is_active = 1");
		return $this->getReadConnection()->fetchAll($select);
	}

    /**
     * @param $vendorId
     *
     * @return array
     */
    public function getSuperVendorAgentEmails($vendorId)
    {
        $agents = array();

        if (empty($vendorId)) {
            return $agents;
        }
        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("vendor" => 'udropship_vendor'),
            array()
        );
        $select->join(
            array("operator" => $this->getTable("zolagooperator/operator")),
            "operator.vendor_id=vendor.super_vendor_id",
            array(
                 "operator_email" => "operator.email",
                 "firstname"      => "operator.firstname",
                 "lastname"       => "operator.lastname"
            )
        );
        $select->where("vendor.vendor_id=?", $vendorId);
        $select->where("operator.is_active=?", 1);
        $select->where("operator.roles LIKE '%" . Zolago_Operator_Model_Acl::ROLE_RMA_OPERATOR . "%'");

        try {
            $agents = $readConnection->fetchAssoc($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching agents");
        }
        return $agents;
    }

    /**
     * @param $vendorId
     *
     * @return array
     */
    public function getVendorAgentEmails($vendorId)
    {
        $agents = array();

        if (empty($vendorId)) {
            return $agents;
        }
        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("vendor" => 'udropship_vendor'),
            array()
        );
        $select->join(
            array("operator" => $this->getTable("zolagooperator/operator")),
            "operator.vendor_id=vendor.vendor_id",
            array(
                 "operator_email" => "operator.email",
                 "firstname"      => "operator.firstname",
                 "lastname"       => "operator.lastname"
            )
        );
        $select->where("vendor.vendor_id=?", $vendorId);
        $select->where("operator.is_active=?", 1);
        $select->where("operator.roles LIKE '%" . Zolago_Operator_Model_Acl::ROLE_RMA_OPERATOR . "%'");

        try {
            $agents = $readConnection->fetchAssoc($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching agents");
        }
        return $agents;
    }
}
