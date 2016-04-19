<?php
/**
  
 */

/**
* Currently not in use
*/
class ZolagoOs_OmniChannelVacation_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{
    const MODE_NOT_VACATION     = 0;
    const MODE_VACATION_NOTIFY  = 1;
    const MODE_VACATION_DISABLE = 2;
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udvacation');

        switch ($this->getPath()) {

        case 'vacation_mode':
            $options = array(
                0 => $hlpv->__('Not Vacation'),
                1 => $hlpv->__('Notify Customer On Availability'),
                2 => $hlpv->__('Disable Products'),
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