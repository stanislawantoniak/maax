<?php

/**
 * Last seen customer url's collection
 *
 * Collection mentioned only for grid in admin for customer section 'Viewed categories'
 *
 * NOTE: main object is Varien_Object not Zolago_Log_Model_Url
 */
class Zolago_Log_Model_Resource_Url_Varien_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    /**
     * Initialize collection model
     */
    protected function _construct() {
        // Trick: main object is Varien_Object not Zolago_Log_Model_Url
        // because i need to have list of visited url in time
        // traditional collection load would crash ( problem -> multiple unique ID )
        $this->_init('varien/object', 'zolagolog/url');
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function addCustomerUrlFilter($customerId) {

        $this->getSelect()->join(
            array("logcustomer" => $this->getTable("log/customer")),
            implode(" AND ", array(
                "logcustomer.visitor_id = main_table.visitor_id",
                $this->getConnection()->quoteInto("logcustomer.customer_id=?", $customerId)
            )),
            array()
        );
        $this->getSelect()->join(
            array("logurlinfo" => $this->getTable('log/url_info_table')),
            "main_table.url_id = logurlinfo.url_id",
            array("url", "referer")
        );

        // hard code
        // because of varnish we only have correct access to ajax
        $this->addFieldToFilter("url", array("like" => "%orbacommon/ajax_customer/get_account_information%"));
        $this->getSelect()->group("main_table.visit_time");
        return $this;
    }

}
