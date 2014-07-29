<?php

class Orba_Common_Ajax_CustomerController extends Orba_Common_Controller_Ajax {
	
	const MAX_CART_ITEMS_COUNT = 5;
	
	public function get_account_informationAction(){
		
		Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
		
		$content = array(
			'user_account_url' => Mage::getUrl('customer/account'),
			'logged_in' => Mage::helper('customer')->isLoggedIn(),
			'favorites_count' => $this->_getFavorites(),
            'favorites_url' => Mage::helper('zolagowishlist')->getListUrl(),
			'cart' => array(
				'all_products_count' =>	Mage::helper('checkout/cart')->getSummaryCount(),
				'products' => $this->_getShoppingCartProducts(),
				'total_amount' => round(Mage::helper('checkout/cart')->getQuote()->getGrandTotal(), 2),
				'shipping_cost' => 'gratis [dev]',
				'show_cart_url' => Mage::getUrl('checkout/cart'),
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
		
		$array = array();
        foreach ($cartItems as $item)
        {
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);
			
			$options = $this->_getProductOptions($item);
			$image = Mage::helper('catalog/image')->init($product, 'image')->resize(40, 50);
			
			$array[] = array(
				'name' => $product->getName(),
				'qty' => $item->getQty(),
				'unit_price' => round($item->getPrice(), 2),
				'image_url' => (string) $image,
				'options' => $options
			);
			
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