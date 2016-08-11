<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Customer_View extends Mage_Core_Block_Template
{
    public function getQuestion()
    {
        return Mage::registry('udqa_question');
    }
}
