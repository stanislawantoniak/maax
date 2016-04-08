<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Review
	extends Zolago_Modago_Block_Checkout_Onepage_Abstract
{

    protected $_total;
    protected $_total_sum;
    protected $_total_shipping;
    protected $_shipping;
    protected $_block;
    
	/**
	 * 
	 */
	public function getCoupon() {
		return $this->getQuote()->getCouponCode();
	}
	
    public function getTotalSum() {
        return $this->_total_sum;
    }
        
    public function getShipping() {
        return $this->_shipping;
    }
    protected function _getCartBlock($type) {
        if (empty($this->_block[$type])) {
            $block = $this->_assignBlock($type);
            $this->_block[$type] = $block;
        }
        return $this->_block[$type];
    }
    
    //{{{ 
    /**
     * 
     * @param string $type
     * @return Mage_Core_Block_Abstract
     */

    //}}}
    public function _assignBlock($type) 
    {
        $block = null;
        switch ($type) {
            case 'cart':
                $block = $this->getLayout()->createBlock('checkout/cart');            
                $block->addItemRender('default','checkout/cart_item_renderer','checkout/cart/item/review.phtml');                
                $block->addItemRender('configurable','checkout/cart_item_renderer_configurable','checkout/cart/item/review.phtml');                
                $block->addItemRender('grouped','checkout/cart_item_renderer_grouped','checkout/cart/item/review.phtml');                
                break;
            case 'split':
                $block = $this->getLayout()->createBlock('udsplit/cart_vendor');            
                break;
        }
        return $block;
    }
	public function getStep3Sidebar()
    {
        return $this->getLayout()->createBlock("cms/block")->setBlockId("checkout-right-column-step-3")->toHtml();
    }
    
    
    //{{{ 
    /**
     * count total order value
     * @param array $items
     * @return 
     */
    protected function _calculateTotal($items) {
        $sum = 0;
        if (!empty($items)) {
            foreach ($items as $item) {
                $sum += $item->getData('row_total_incl_tax');
            }
            
        }
        $this->_total = $sum;
    }
    //}}}
    //{{{ 
    /**
     * @return array
     */

    //}}}
    public function getItems() {
        $block = $this->_getCartBlock('cart');
        $items = $block->getItems();        
        $this->_calculateTotal($items);
        return $items;
    }

    public function isCheckOutBlocked(){
        return Mage::getStoreConfig('checkout/options/checkout_order_deactivate');
    }
    
    public function getWaitForExternalGTMTags() {
        $value = Mage::getStoreConfig('checkout/options/checkout_wait_for_tags');
        return is_numeric($value) ? $value : 0;
    }
    
    
    //{{{ 
    /**
     * 
     * @param Mage_Sales_Model_Quote_Item
     * @return array
     */

    //}}}
    public function getItemHtml($item) {
        $block = $this->_getCartBlock('cart');
        $block->setHtmlBlock('udsplit/cart_review');
        $block->setItemsShippingCost($this->getItemsShippingCost());
        return $block->getItemHtml($item);
    }
    
    //{{{ 
    
    //{{{ 
    /**
     * footer info
     */
    public function getCheckoutReviewInfo() {
        $storeId = Mage::app()->getStore()->getId();
        return $this->getLayout()->createBlock('cms/block')->setBlockId('checkout-review-footer-'.$storeId)->toHtml();
    }
    public function getCheckoutReviewInfoCod() {
        $storeId = Mage::app()->getStore()->getId();
        return $this->getLayout()->createBlock('cms/block')->setBlockId('checkout-review-footer-cod-'.$storeId)->toHtml();
    }
    //}}}
    
    //{{{ 
    /**
     * url to basket
     * @return string
     */
     public function getBasketUrl() {
         return $this->getUrl('checkout/cart');
     }
    //}}}
    public function getFormattedShippingMethods() {
        $qRates = $this->getRates();
        $allMethodsByCode = array();
        foreach ($qRates as $cCode => $cRates) {
            foreach ($cRates as $rate) {

                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
                $vendors[$vId] = $vId;
                $methodsByCode[$rate->getCode()] = array(
                                                       'vendor_id' => $vId,
                                                       'code' => $rate->getCode(),
                                                       'carrier_title' => $rate->getData('carrier_title'),
                                                       'method_title' => $rate->getData('method_title')
                                                   );
                $allMethodsByCode[$rate->getCode()][] = array(
                        'vendor_id' => $vId,
                        'code' => $rate->getCode(),
                        'carrier_title' => $rate->getData('carrier_title'),
                        'method_title' => $rate->getData('method_title'),
                        'cost' => $rate->getPrice()
                                                        );

            }
            unset($cRates);
            unset($rate);
        }
        return $allMethodsByCode;
    }

	/**
	 * @return Mage_SalesRule_Model_Quote_Nominal_Discount | null
	 */
	public function getTotalDiscount() {
		$totals = $this->getQuote()->getTotals();
		return isset($totals['discount']) ? $totals['discount'] : null;
	}
	
    public function preparePresentation() {
        $total_shipping = array();
        $list = array();
        $methods = $this->getFormattedShippingMethods();
        foreach ($methods as $method) {	
            foreach ($method as $vendor) {
                $list[$vendor['code']][$vendor['vendor_id']] = array (
                    'name' => $vendor['method_title'],
                    'value' =>  Mage::helper('core')->formatPrice($vendor['cost']),
                );
                if (empty($total_shipping[$vendor['code']])) {
                    $total_shipping[$vendor['code']] = 0;
                }
                $total_shipping[$vendor['code']] += $vendor['cost'];
            }
        }
        $this->_shipping = $list;
        foreach ($total_shipping as $key=>$val) {
            $this->_total_sum[$key] = Mage::helper('core')->formatPrice($val+$this->_total);
            $this->_total_shipping[$key] = Mage::helper('core')->formatPrice($val);
        }
    }

} 