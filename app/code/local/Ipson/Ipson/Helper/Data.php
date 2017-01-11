<?php
/**
 * Ipson module helper
 */
class Ipson_Ipson_Helper_Data extends Mage_Core_Helper_Data {

    public function getCeneoAgreementGuid() {
        return Mage::getStoreConfig('customer/agreements/ceneo_guid');
    }
    public function getOpineoAgreementGuid() {
        return Mage::getStoreConfig('customer/agreements/opineo_guid');
    }


    /**
     * get opinion service name
     */
    protected function _getAgreementsName() {
        $cookie = Mage::getSingleton('core/cookie');
        $value = $cookie->get('ghutm');
        if ($value) {
            $params = json_decode($value);
            if (!empty($params->utm_source)) {
                if ($this->getOpineoAgreementGuid() && !strstr($params->utm_source,'ceneo')) { 
                    return 'opineo';
                } 
                if ($this->getCeneoAgreementGuid()) {
                    return 'ceneo';
                }
                if ($this->getOpineoAgreementGuid()) {
                    return 'opineo';
                }
                
            }
        }
        return null;
    }

    /**
     * prepare html checkbox for opinion agreements
     */
    public function getOpinionAgreementHtml() {
        $name = $this->_getAgreementsName();
        if (!$name) {
            return '';
        }
        $html = '<div class="form-group form-checkbox small text-align-left">
                <input type="checkbox" id="'.$name.'_agreement" class="css-checkbox '.$name.'_agreement"
                name="'.$name.'_agreement" style="opacity: 0; visibility: visible;" checked="checked">
                <label for="'.$name.'_agreement" class="css-label '.$name.'_agreement-label">'.
                Mage::helper('zolagomodago')->getAgreementHtml($name) .
                '</label>
                </div>';
        return $html;
    }

}