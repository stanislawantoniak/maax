<?php

class Zolago_DropshipMicrosite_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{

    public function toOptionHash($selector = false)
    {

        $hlp = Mage::helper('udropship');
        $hlpc = Mage::helper('umicrosite');

        switch ($this->getPath()) {


            case 'websites_allowed':
                $collection = Mage::getModel('core/website')->getResourceCollection();
                $options = array('' => $hlpc->__('* None'));
                foreach ($collection as $w) {
                    $options[$w->getId()] = $w->getName();
                }
                break;


            default:
                Mage::throwException($hlp->__('Invalid request for source options: ' . $this->getPath()));
        }

        if ($selector) {
            $options = array('' => $hlp->__('* Please select')) + $options;
        }

        return $options;
    }

}
