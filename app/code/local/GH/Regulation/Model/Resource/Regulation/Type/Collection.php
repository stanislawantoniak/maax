<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Type_Collection
 */
class GH_Regulation_Model_Resource_Regulation_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct() {
        $this->_init('ghregulation/regulation_type');
    }

    public function joinKind() {
        $kindTable = $this->getTable('ghregulation/regulation_kind');
        $this->getSelect()
            ->join(
                array('kind' => $kindTable),
                "main_table.regulation_kind_id = kind.regulation_kind_id",
                array("kind_name" => "kind.name")
            );
        return $this;
    }

    /**
     * Retrieve option hash
     *
     * @param string $withEmptyText
     * @return array
     */
    public function toOptionHash($withEmptyText = '')
    {
        $arr =  array();
        if (!empty($withEmptyText)) {
            $arr[''] = $withEmptyText;
        }
        $arr += parent::_toOptionHash('regulation_type_id', 'name');
        return $arr;
    }
}