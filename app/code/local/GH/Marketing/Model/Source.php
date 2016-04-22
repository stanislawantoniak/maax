<?php

class GH_Marketing_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{


    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('ghmarketing');

        $options = array();

        switch ($this->getPath()) {

        case 'cost_type':
            $options = $this->getCostTypes();
            break;
            


        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getCostTypes()
    {
        $out = array();
        /* @var $collection  GH_Marketing_Model_Resource_Marketing_Cost_Type_Collection */
        $collection = Mage::getResourceModel("ghmarketing/marketing_cost_type_collection");
        foreach ($collection as $collectionItem) {
            $out[$collectionItem->getId()] = $collectionItem->getName();
        }
        return $out;
    }

}
