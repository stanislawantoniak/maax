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
     * prepare html checkbox for opinion agreements
     */
     public function getOpinionAgreementHtml() {
         
         $cookie = Mage::getSingleton('core/cookie');
         $agreementsArray = array();
         if ($this->getOpineoAgreementGuid()) {
              $agreementsArray[] = 'opineo';
         }
         if ($this->getCeneoAgreementGuid()) {
              $agreementsArray[] = 'ceneo';
         }
         if (!count($agreementsArray)) {
              return ''; // no agreements
         }
         $default = current($agreementsArray);
         $name = empty($cookie->get('opinion_domain'))? $default:$cookie->get('opinion_domain');         
         if (!in_array($name,$agreementsArray)) {
              $name = $default;
         };
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