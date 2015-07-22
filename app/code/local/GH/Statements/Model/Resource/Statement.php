<?php

/**
 * Resource for statement
 */
class GH_Statements_Model_Resource_Statement extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('ghstatements/statement','id');
    }

    /**
     * Perform actions after object delete
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $statementId = $object->getId();


        $write = $this->_getWriteAdapter();

        $write->update(
            $write->getTableName('udropship_po'),
            array("statement_id" => NULL),
            "statement_id=" . $statementId
        );
        $write->update(
            $write->getTableName("sales_flat_shipment_track"),
            array("statement_id" => NULL),
            "statement_id=" . $statementId
        );
        $write->update(
            $write->getTableName("urma_rma_track"),
            array("statement_id" => NULL),
            "statement_id=" . $statementId
        );

        return $this;
    }
}