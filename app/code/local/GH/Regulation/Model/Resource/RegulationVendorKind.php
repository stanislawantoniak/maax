<?php

/**
 * Class GH_Regulation_Model_Resource_RegulationVendorKind
 */
class GH_Regulation_Model_Resource_RegulationVendorKind extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gh_regulation/regulation_vendor_kind', 'id');
    }

}