<?php

class GH_Marketing_Dropship_MarketingController extends Zolago_Dropship_Controller_Vendor_Abstract {

    /**
     * Sledzenie budżetu kosztów marketingu dla sprzedawcy
     */
    public function budgetAction() {
        parent::_renderPage(null,'budget_marketing');
    }
    /**
     *
     * @param
     * @return
     */
    public function saveAction() {
        try {
            $value = $this->getRequest()->getParam('budget');
            $date = $this->getRequest()->getParam('month');
            if (!$date) {
                $date = Mage::getModel('core/date')->date('Y-m');
            }
            if (is_array($value)) {
                $collection = $this->_getBudgetCollection($date);
                foreach ($value as $key => $val) {
                    if (isset($collection[$key])) {
                        $model = $collection[$key];
                    } else {
                        $model = Mage::getModel('ghmarketing/marketing_budget');
                        $model->setVendorId($this->getVendorId());
                        $model->setMarketingCostTypeId($key);
                        $model->setDate(date('Y-m-d H:i:s',strtotime($date)));
                    }
                    $model->setBudget($this->toFloat($val));
                    $model->save();
                }
            }
            $this->_getSession()->addSuccess(Mage::helper('ghmarketing')->__('Budget saved'));
        } catch (Exception $xt) {
            Mage::logException($xt);
            $this->_getSession()->addError(
                Mage::helper('ghmarketing')->__('Error: %s',$xt->getMessage()));
        }
        $this->_redirectReferer();
    }

    /**
     * change string to float
     *
     * @param string $val
     * @return float
     */
    public function toFloat($val) {
        return round(floatval(str_replace(',','.',$val)),2);
    }




    /**
     * return logged vendor id
     *
     * @return int
     */
    protected function getVendorId() {
        return Mage::getSingleton('udropship/session')->getVendor()->getId();
    }
    /**
     * get budget model list by type from selected month
     *
     * @param string $month
     * @return array
     */
    protected function _getBudgetCollection($date) {
        $collection = Mage::getModel('ghmarketing/marketing_budget')->getCollection();
        $collection->addFieldToFilter('vendor_id',$this->getVendorId())
        ->addFieldToFilter('date',array('like' => $date.'%'));
        $out = array();
        foreach ($collection as $model) {
            $out[$model->getMarketingCostTypeId()] = $model;
        }
        return $out;
    }
}

