<?php

class Orba_Common_Ajax_CustomerController extends Orba_Common_Controller_Ajax {
	
	const MAX_CART_ITEMS_COUNT = 5;

    public function get_account_informationAction()
    {
        $q = Mage::getSingleton('checkout/cart')->getQuote();
        $q->getTotals();


        /*shipping_cost*/
        $cost = Mage::helper('zolagomodago/checkout')->getShippingCostSummary();

        $costSum = 0;
        if (!empty($cost)) {
            $costSum = array_sum($cost);
        }
        $formattedCost = Mage::helper('core')->currency($costSum, true, false);
        /*shipping_cost*/

		$quote = Mage::helper('checkout/cart')->getQuote();
		/* @var $quote Mage_Sales_Model_Quote */
		$totals = $quote->getTotals();
		
		
        $content = array(
            'user_account_url' => Mage::getUrl('customer/account', array("_no_vendor"=>true)),
            'user_account_url_orders' => Mage::getUrl('sales/order/process', array("_no_vendor"=>true)),
            'logged_in' => Mage::helper('customer')->isLoggedIn(),
            'favorites_count' => $this->_getFavorites(),
            'favorites_url' => Mage::getUrl("wishlist", array("_no_vendor"=>true)),
            'cart' => array(
                'all_products_count' => Mage::helper('checkout/cart')->getSummaryCount(),
                'products' => $this->_getShoppingCartProducts(),
                'total_amount' => round(isset($totals["subtotal"]) ? $totals["subtotal"]->getValue() : 0, 2),
                'shipping_cost' => $formattedCost,
                'show_cart_url' => Mage::getUrl('checkout/cart', array("_no_vendor"=>true)),
                'currency_code' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'currency_symbol' => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
            )
        );

        $result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($result);
    }
	
	public function _getShoppingCartProducts(){
		
		$quote = Mage::getSingleton('checkout/session')->getQuote();
        $cartItems = $quote->getAllVisibleItems();
		
		if(sizeof($cartItems) > self::MAX_CART_ITEMS_COUNT){
			$cartItems = array_slice($cartItems, 0, self::MAX_CART_ITEMS_COUNT);	
		}
		
		// Product load 
		$productsIds = array();
		foreach ($cartItems as $item){
            $productsIds[] = $item->getProductId();
		}
		
		$collection = Mage::getResourceModel("catalog/product_collection");
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		
		$collection->addAttributeToSelect("name");
		$collection->addAttributeToSelect("image");
		$collection->addAttributeToSelect("url_path");
		
		if($productsIds){
			$collection->addIdFilter($productsIds);
		}else{
			$collection->addIdFilter(-1);
		}
		
		$array = array();
        foreach ($cartItems as $item)
        {
            $productId = $item->getProductId();
            $product = $collection->getItemById($productId);
			/* @var $product Mage_Catalog_Model_Product */
			
			if($product && $product->getId()){
				$options = $this->_getProductOptions($item);
				$image = Mage::helper('catalog/image')->init($product, 'image')->resize(40, 50);

				$array[] = array(
					'name' => $product->getName(),
					'url' => $product->getNoVendorContextUrl(),
					'qty' => $item->getQty(),
					'unit_price' => round($item->getPriceInclTax(), 2),
					'image_url' => (string) $image,
					'options' => $options
				);
			}
			
        }
		
		return (sizeof($array) > 0) ? $array : 0;
	}
	
	public function _getFavorites(){
		$wishlist = Mage::helper('zolagowishlist')->getWishlist();
		return $wishlist->getItemsCount();
	}
	
	public function _getProductOptions($item){
		
		$product = $item->getProduct();
		
		$options = $product->getTypeInstance(true)->getOrderOptions($product);
		
		$array = array();
		if ($options){
			if(isset($options['attributes_info'])){
				foreach ($options['attributes_info'] as $attrib){
					
					$array[] = array(
						'label' => $attrib['label'],
						'value' => $attrib['value']
					);
				}
			}		
		}
            
		return $array;
	}
}