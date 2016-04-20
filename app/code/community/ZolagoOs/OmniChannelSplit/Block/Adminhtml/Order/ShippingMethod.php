<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Block_Adminhtml_Order_ShippingMethod
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    public function getShippingRates()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::getShippingRates();
        }

        if (empty($this->_rates)) {
            $groups = array();

            // prepare vendor items
            $q = $this->getQuote();
            $qItems = $q->getAllVisibleItems();
            Mage::helper('udropship/protected')->prepareQuoteItems($qItems);
            foreach ($qItems as $item) {
                if ($item->getIsVirtual()) {
                    continue;
                }
                $groups[$item->getUdropshipVendor()]['items'][] = $item;
                $groups[$item->getUdropshipVendor()]['rates'] = array();
            }

            // prepare vendor rates
            $methods = array();
            $details = $this->getAddress()->getUdropshipShippingDetails();
            if ($details) {
                $details = Zend_Json::decode($details);
                $methods = isset($details['methods']) ? $details['methods'] : array();
            }
            $qRates = $this->getAddress()->getGroupedAllShippingRates();
            foreach ($qRates as $cCode=>$cRates) {
                foreach ($cRates as $rate) {
                    $vId = $rate->getUdropshipVendor();
                    if (!$vId) {
                        continue;
                    }
                    $rate->setIsSelected(!empty($methods[$vId]['code'])
                        && ($methods[$vId]['code']==$rate->getCarrier().'_'.$rate->getMethod()));
                    $groups[$vId]['rates'][$cCode][] = $rate;
                }
            }
            return $this->_rates = $groups;
        }
        return $this->_rates;
    }

    public function _beforeToHtml()
    {
        parent::_beforeToHtml();
        if (Mage::helper('udsplit')->isActive()) {
            $this->setTemplate('udsplit/order_create_shipping.phtml');
        }
    }
}