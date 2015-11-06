<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Kind_Collection
 */
class GH_Regulation_Model_Resource_Regulation_Kind_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_kind');
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
        $arr += parent::_toOptionHash('regulation_kind_id', 'name');
        return $arr;
    }
}