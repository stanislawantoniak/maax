<?php

/**
 * Class GH_AttributeRules_Model_Resource_AttributeRule_Collection
 */
class GH_AttributeRules_Model_Resource_AttributeRule_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gh_attributerules/attributeRule');
    }

}