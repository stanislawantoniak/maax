<?php
/**
  
 */

/**
* Currently not in use
*/
class ZolagoOs_OmniChannelTierCommission_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $hlpv = Mage::helper('udtiercom');

        switch ($this->getPath()) {

        case 'udropship/tiercom/fixed_rule':
        case 'tiercom_fixed_rates':
            $options = array(
                'item_price' => $hlpv->__('Item Price')
            );
            if ($this->getPath()=='tiercom_fixed_rates') {
                $options = array('' => $hlp->__('* Use Global Config')) + $options;
            }
            break;

        case 'udropship/tiercom/fallback_lookup':
        case 'tiercom_fallback_lookup':
            $options = array(
                'vendor' => $hlpv->__('Vendor First'),
                'tier' => $hlpv->__('Tier First')
            );
            if ($this->getPath()=='tiercom_fallback_lookup') {
                $options = array('-1' => $hlp->__('* Use Global Config')) + $options;
            }
            break;

        case 'udropship/tiercom/fixed_calculation_type':
        case 'tiercom_fixed_calc_type':
            $options = array(
                'flat' => $hlpv->__('Flat (per po)'),
                'tier' => $hlpv->__('Tier (per item)'),
                'rule' => $hlpv->__('Rule Based (per item)'),
                'flat_rule' => $hlpv->__('Tier + Rule Based'),
                'flat_tier' => $hlpv->__('Flat + Tier'),
                'flat_rule' => $hlpv->__('Flat + Rule Based'),
                'flat_tier_rule' => $hlpv->__('Flat + Tier + Rule Based'),
            );
            if ($this->getPath()=='tiercom_fixed_calc_type') {
                $options = array('' => $hlp->__('* Use Global Config')) + $options;
            }
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