<?php


class Zolago_DropshipVendorAskQuestion_Model_Resource_Question extends ZolagoOs_OmniChannelVendorAskQuestion_Model_Mysql4_Question
{
    /**
     * @param $vendorId
     *
     * @return array
     */
    public function getSuperVendorHelpdeskAgentEmails($vendorId)
    {
        $agents = array();

        if (empty($vendorId)) {
            return $agents;
        }
        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("vendor" => $this->getTable("udropship/vendor")),
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
        $select->where("operator.roles LIKE '%" . Zolago_Operator_Model_Acl::ROLE_HELPDESK . "%'");

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
    public function getVendorHelpdeskAgentEmails($vendorId)
    {
        $agents = array();

        if (empty($vendorId)) {
            return $agents;
        }
        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("vendor" => $this->getTable("udropship/vendor")),
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
        $select->where("operator.roles LIKE '%" . Zolago_Operator_Model_Acl::ROLE_HELPDESK . "%'");

        try {
            $agents = $readConnection->fetchAssoc($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching agents");
        }
        return $agents;
    }

}