<?php

/**
 * Class Zolago_Campaign_Model_Resource_Placement
 */
class Zolago_Campaign_Model_Resource_Placement extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagocampaign/campaign_placement', "placement_id");
    }

}