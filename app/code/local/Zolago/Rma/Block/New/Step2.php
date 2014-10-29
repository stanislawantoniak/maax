<?php
class Zolago_Rma_Block_New_Step2 extends  Zolago_Rma_Block_New_Abstract{
    protected $_monthList = array();
    /**
     * list of possible pickup data
     * @return array
     */         
     public function getDateList($newZip = '') {
         return Mage::helper('zolagorma')->getDateList($this->getRequest()->getParam('po_id'), $newZip);
     }

    /**
     * is dhl enabled for rma
     * @return bool
     */
     public function isDhlEnabled() {
         $vendor = $this->getParentBlock()->getVendor();
         $helper = Mage::helper('orbashipping/carrier_dhl');
         return $helper->isEnabledForRma($vendor) || $helper->isEnabledForVendor($vendor);
     }
     /**
     * formatted date (using locale)
     * @param int timestamp
     * @return string
     */
     public function getFormattedDate($timestamp) {
         $list = $this->_getMonthList();         
         $date = explode('-',date('j-n-Y',$timestamp));
         $pattern = sprintf('%s %s %s',$date[0],$list[$date[1]],$date[2]);
         return $pattern;
     }
     /**
     * month list in proper language
     * @return array
     */
     protected function _getMonthList() {
         if (!$this->_monthList) {
             $locale = Mage::app()->getLocale();
             $list = $locale->getLocale()->getTranslationList('months',$locale->getLocale());
             $this->_monthList = $list['format']['wide'];
         }
         return $this->_monthList; 
     }
}