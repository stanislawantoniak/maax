<?php

/**
 * Class GH_Regulation_Model_Resource_RegulationKind
 */
class GH_Regulation_Model_Resource_RegulationKind extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gh_regulation/regulation_kind', 'regulation_kind_id');
    }

}