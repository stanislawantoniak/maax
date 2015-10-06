<?php

/**
 * Class GH_Regulation_Model_Resource_RegulationType
 */
class GH_Regulation_Model_Resource_RegulationType extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gh_regulation/regulation_type', 'regulation_type_id');
    }

}