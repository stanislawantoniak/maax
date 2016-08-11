<?php

class Orba_Common_Ajax_CartController extends Orba_Common_Controller_Ajax {

    /**
     * Gets data of shopping cart and its items and set it to response body in JSON format
     */
    public function getAction() {
        try {
            $result = $this->_generateGetResponse();
            if ($message = $this->_getFlashMessage()) {
                $result['content']['message'] = $message;
            }
            if ($details = $this->_getFlashDetails()) {
                $result['details'] = $details;
            }
            $this->_setSuccessResponse($result);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    public function getDetailsForGTM($superAttribute) {
		/** @var Zolago_Dropship_Helper_Data $udropHlp */
        $udropHlp = Mage::helper('udropship');
        /** @var Zolago_Common_Helper_Data $zcHlp */
        $zcHlp = Mage::helper('zolagocommon');
        /** @var Zolago_Solrsearch_Helper_Data $solrHlp */
        $solrHlp = Mage::helper("zolagosolrsearch");

        $savedInfo = Mage::registry('gtm-last-added-product-info');
        /** @var Mage_Sales_Model_Quote_Item $item */
        $item = $savedInfo['quote_item'];
        /** @var Zolago_Catalog_Model_Product $product */
        $product = $savedInfo['product'];
        $vendor    = $udropHlp->getVendor($product->getUdropshipVendor())->getVendorName();
        $brandshop = $udropHlp->getVendor($product->getbrandshop())->getVendorName();

        $attributeSize = $product->getResource()->getAttribute('size');
        $attributeSizeId = $attributeSize ? $attributeSize->getAttributeId() : false;
		$variant = ($attributeSizeId && isset($superAttribute[$attributeSizeId])) ? $superAttribute[$attributeSizeId] : false;

        /** @var Zolago_Catalog_Model_Category $cat */
        $cat = $solrHlp->getDefaultCategory($product, Mage::app()->getStore()->getRootCategoryId());
        $productCategories = array();
        if (!empty($cat)) {
            // Only categories after root category
            $productCategories = array_slice($cat->getPathIds(),1 + array_search(Mage::app()->getStore()->getRootCategoryId(), $cat->getPathIds()));
        }

        $categories = array();
        foreach ($productCategories as $category) {
            $categories[] = trim(Mage::helper('core')->escapeHtml(Mage::getModel('catalog/category')->load($category)->getName()));
        }


        $details = array(
                       'currencyCode' => Mage::app()->getStore()->getBaseCurrencyCode(),
                       'products' => array(
                           'name' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($product->getName())),
                           'id' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($product->getData('sku'))),
                           'skuv' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($zcHlp->getSkuvFromSku($product->getData('sku'),$product->getUdropshipVendor()))),
                           'simple_sku' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($product->getSku())),
                           'simple_skuv' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($zcHlp->getSkuvFromSku($product->getSku(),$product->getUdropshipVendor()))),
                           'category' => implode('/', $categories),
                           'price' => (double)number_format($item->getBaseCost() / $item->getQty(), 2, '.', ''),
                           'quantity' => (int)$item->getQty(),
                           'vendor' => Mage::helper('core')->escapeHtml($vendor),
                           'brandshop' => Mage::helper('core')->escapeHtml($brandshop),
                           'brand' => Mage::helper('core')->escapeHtml($product->getAttributeText('manufacturer')),
                           'variant' => $variant ? (string)$product->getResource()->getAttribute('size')->getSource()->getOptionText($variant) : ''
                       )
                   );
        return $details;
    }

    /**
     * Adds product to the shopping cart. Available params:
     * - product_id (required if sku left blank)
     * - sku (required if product_id left blank)
     * - qty (required)
     * - super_attribute (required for configurable products)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     *
     * @throws Orba_Common_Exception
     */
    public function addAction() {
        /** @var Orba_Common_Model_Ajax_Cart $ajaxCart */
        $ajaxCart = Mage::getSingleton('orbacommon/ajax_cart');
        $request = $this->getRequest();
        $qty = $request->getParam('qty', null);
        $superAttribute = $request->getParam('super_attribute', array());
        $productId = $this->_getProductIdFromRequest();
        try {
            if ($productId) {
                $ajaxCart->addProduct($productId, $qty, $superAttribute);
                $this->_setFlashDetails($this->getDetailsForGTM($superAttribute));
                $this->_setCartSuccessResponse('Product has been added to the shopping cart.');
            } else {
                throw Mage::exception('Orba_Common', 'No product has been specified.');
            }
        } catch (Mage_Core_Exception $e) {
            $this->_prepareException($e,$superAttribute);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_prepareException($e,$superAttribute);
        }
    }

    protected function _prepareException($e,$superAttribute) {
        /** @var Mage_Checkout_Helper_Cart $cartHelper */
        $cartHelper = Mage::helper('checkout/cart');
        $quote = $cartHelper->getQuote();
        $items = $quote->getAllItems(); // In this array they are items from quota + items that could not be added to quota (they don't have item_id)
		$productId = $this->_getProductIdFromRequest();
        // Default out of stock message
        $e->setMessage("We apologize, but someone just placed an order for the last item. Availability status will be updated at the next time entering to the product card.");
        $details = array("error_type" => "product-unavailable", 'title_section' => $this->__("The product is temporarily unavailable"));
        // Checking if in quote is similar product
		foreach ($items as $item) {
			/** @var Mage_Sales_Model_Quote_Item $item */
			/** @var Zolago_Catalog_Model_Product $product */
			$product = $item->getProduct();
			$parentItem = $item->getParentItem();
			// skip items that could not be added to cart (they don't have item_id)
			// and with that same product_id as just tried added product
			if ($item->getId() && ($item->getProduct()->getId() == $productId ||
					(	$parentItem instanceof Mage_Sales_Model_Quote_Item &&
						$parentItem->getProduct() instanceof Mage_Catalog_Model_Product &&
						$parentItem->getProduct()->getId() == $productId))
			) {
				$inBasketFlag = false;
				foreach ($superAttribute as $attributeId => $value) {
					$attribute = Mage::getSingleton('eav/config')->getAttribute($product->getResource()->getEntityType(), $attributeId);
					$attributeCode = $attribute->getAttributeCode();
					if ($product->getData($attributeCode) == $value) {
						$inBasketFlag = true;
						break;
					}
				}
				if ($inBasketFlag) {
					/** @var $e Mage_Core_Exception */
					$e->setMessage("Unfortunately, you can't add another product to the cart because it already contains last item. Quickly place the order before the product will disappear from the store.");
					$details = array("error_type" => "already-in-the-basket", 'title_section' => $this->__("The product is already in the basket"));
					break;
				}
			}
		}
        $this->_processException($e, $details);
    }

    /**
     * Updates shopping cart item quantity. Available params:
     * - item_id (required)
     * - qty (required)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     *
     * @throws Orba_Common_Exception
     */
    public function updateAction() {
        try {
            $request = $this->getRequest();
            $itemId = $request->getParam('item_id', null);
            if ($itemId) {
                $qty = $request->getParam('qty', null);
                Mage::getSingleton('orbacommon/ajax_cart')->updateItem($itemId, $qty);
                $this->_setCartSuccessResponse('Shopping cart item has been updated.');
            } else {
                throw Mage::exception('Orba_Common', 'No item has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Removes item form shopping cart. Available params:
     * - item_id (required)
     * - get_after (optional, default true; if true there is a forward to getAction after successfull product adding)
     *
     * @throws Orba_Common_Exception
     */
    public function removeAction() {
        try {
            $request = $this->getRequest();
            $itemId = $request->getParam('item_id', null);
            if ($itemId) {
                Mage::getSingleton('orbacommon/ajax_cart')->removeItem($itemId);
                $this->_setCartSuccessResponse('Shopping cart item has been removed.');
            } else {
                throw Mage::exception('Orba_Common', 'No item has been specified.');
            }
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    public function applycouponAction()
    {
        $response = array();
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $response['error_message'] = Mage::helper('zolagomodago')->__("There is no items in your cart.");
            $response['status'] = false;
            $this->_setSuccessResponse($response);
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $response['error_message'] = Mage::helper('zolagomodago')->__("There is nothing to apply.");
            $response['status'] = false;
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
            ->collectTotals()
            ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $response['label'] = $couponCode;
                    $this->_formatSuccessContentForResponse($response);
                } else {
                    $response['error_message'] = Mage::helper('zolagomodago')->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode));
                    $response['status'] = false;
                    $this->_setSuccessResponse($response);
                    return ;
                }
            } else {
                $response['message'] = Mage::helper('zolagomodago')->__('Coupon code was canceled.');
            }

            $this->_formatSuccessContentForResponse($response);
        } catch (Mage_Core_Exception $e) {
            $response['error_message'] = Mage::helper('zolagomodago')->__('Cannot apply the coupon code.');
            $response['status'] = false;
        } catch (Exception $e) {
            $response['error_message'] = Mage::helper('zolagomodago')->__('Cannot apply the coupon code.');
            $response['status'] = false;
        }

        $this->_setSuccessResponse($response);
        return ;
    }

    /**
     * Geneartes response array for get method
     *
     * @return array
     */
    protected function _generateGetResponse() {
        $data = Mage::getSingleton('orbacommon/ajax_cart')->getCartData();
        $result = $this->_formatSuccessContentForResponse($data);
        return $result;
    }

    /**
     * Prepares JSON response with results of add, update and remove actions.
     * If get_after param is set to true, the whole shopping cart content will be added to the response.
     *
     * @param string $message
     */
    protected function _setCartSuccessResponse($message) {
        $getAfter = $this->getRequest()->getParam('get_after', true);
        if ($getAfter) {
            $this->_setFlashMessage($this->__($message));
            $this->_forward('get');
        } else {
            $result = $this->_generateBasicSuccessResponse($message);
            $this->_setSuccessResponse($result);
        }
    }

    public function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    public function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

}