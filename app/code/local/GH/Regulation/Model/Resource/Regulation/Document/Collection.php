<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Document_Collection
 */
class GH_Regulation_Model_Resource_Regulation_Document_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct() {
        $this->_init('ghregulation/regulation_document');
    }

    public function joinType() {
        $table = $this->getTable('ghregulation/regulation_type');
        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()
                ->join(
                    array('type' => $table),
                    "main_table.regulation_type_id = type.regulation_type_id",
                    array(
                        "regulation_kind_id" => "regulation_kind_id",
                        "type_name" => "type.name"
                    )
                );
            $this->_joinedTables[$table] = true;
        }
        return $this;
    }

    public function joinKind() {
        $this->joinType();
        $table = $this->getTable('ghregulation/regulation_kind');
        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()
                ->join(
                    array('kind' => $table),
                    "type.regulation_kind_id = kind.regulation_kind_id",
                    array("kind_name" => "kind.name")
                );
            $this->_joinedTables[$table] = true;
        }
        return $this;
    }
}