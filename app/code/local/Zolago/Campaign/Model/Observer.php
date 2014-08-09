<?php

class Zolago_Campaign_Model_Observer
{
    static public function processCampaignAttributes()
    {
        Mage::log(microtime() . " Starting processCampaignAttributes ", 0, 'processCampaignAttributes.log');
        Mage::getModel("zolagocampaign/campaign")->processCampaignAttributes();
    }
}