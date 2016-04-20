<?php

class Zolago_DropshipSplit_Block_Cart extends ZolagoOs_OmniChannelSplit_Block_Cart
{
    protected $_htmlBlock = 'udsplit/cart_vendor';	
    
    //{{{ 
    /**
     * change name html block for getItemHtml
     * @param string $name
     */
    public function setHtmlBlock($name) {
        $this->_htmlBlock = $name;
    }
    //}}}
    public function getItems()
    {
		if(!$this->hasData("items")){
			$a = $this->getQuote()->getShippingAddress();
			$aRates = $a->getGroupedAllShippingRates();
			/**
			 * Fix rate quto query
			 */
			if(!$aRates){
				$a->setCountryId(Mage::app()->getStore()->getConfig("general/country/default"));
				$a->setCollectShippingRates(true);
				$a->collectShippingRates();
				$aRates = $a->getGroupedAllShippingRates();
			}

			$this->setData("items", parent::getItems());
		}
		return $this->getData("items");
    }
	
	
	public function getItemHtml(Mage_Sales_Model_Quote_Item $item)
    {
	    $vendorsKey = 'checkoutVendors';
        if ($item instanceof ZolagoOs_OmniChannelSplit_Model_Cart_Vendor) {
	        if(!is_null(Mage::registry($vendorsKey)) && is_array(Mage::registry($vendorsKey)) && Mage::registry($vendorsKey)) {
		        $vendors = Mage::registry($vendorsKey);
		        Mage::unregister($vendorsKey);
	        } else {
		        $vendors = array();
	        }

	        if(!isset($vendors[$item->getVendor()->getId()])) {
		        $vendors[$item->getVendor()->getId()] = $item->getVendor()->getLegalEntity();
	        }

            $blockName = "vendor_{$item->getVendor()->getId()}_{$item->getPart()}";
            $block = $this->getLayout()->createBlock($this->_htmlBlock, $blockName)
                ->addData($item->getData())
                ->setQuote($item->getQuote1());
			
			// Increment shippign total here :(
			// It wrong place but architecture of unirgy ...
			if($item->getPart()=="footer"){
				/* @var $block Zolago_DropshipSplit_Block_Cart_Vendor */
				if(($rate=$block->getMinimalShippingRate()) && !$this->getIgnoreTotal()){
					if(!$this->hasData("shipping_total")){
						$this->setData("shipping_total", 0);
					}
					$this->setData("shipping_total", $this->getData("shipping_total") + $rate->getPrice());
				}else{
					$this->setIgnoreTotal(true);
					$this->setData("shipping_total", null);
				}
			}

	        Mage::register($vendorsKey,$vendors);
			
			return $block->toHtml();
        }

        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

}