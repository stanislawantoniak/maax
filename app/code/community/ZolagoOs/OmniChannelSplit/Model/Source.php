<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpc = Mage::helper('udsplit');

        switch ($this->getPath()) {

        case 'carriers/udsplit/free_method':
            $options = array(
                'total' => $hlpc->__('Total'),
            );
            break;

        case 'split_shipping_methods':
            $options = array(
                'test' => 'test',
            );
            break;

        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }
}