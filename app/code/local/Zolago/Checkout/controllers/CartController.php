<?php

require_once Mage::getModuleDir("controllers", "Mage_Checkout") . DS . "CartController.php";

/**
 * Shopping cart controller
 */
class Zolago_Checkout_CartController extends Mage_Checkout_CartController
{

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        //fix for removing items from cart and quote if they are out of stock
        $cart = $this->_getCart();
        $cart->getCheckoutSession()->resetCheckout();
        if ($cart->getQuote()->getItemsCount()) {

            $items = $cart->getItems();
            foreach ($items as $item) {
                /** @var Mage_Sales_Model_Quote_Item $item */
                /** @var Zolago_CatalogInventory_Helper_Data $helperZCI */
                $helperZCI = Mage::helper("zolagocataloginventory");

                /** @var Zolago_Catalog_Model_Product $product */
                $product = $item->getProduct();

                $noStock = in_array($helperZCI->getQuoteItemAvailableFlag($item),
                    array(Zolago_CatalogInventory_Helper_Data::FLAG_OUT_OF_STOCK,
                        Zolago_CatalogInventory_Helper_Data::FLAG_NO_STOCK_INFO));
                $isSalable = $product->isSalable();
                $isEnabled = $product->isEnabled();

                //item can have status different then `enabled`
                //so we need to remember for children to remove them too
                if ($noStock || !$isSalable || !$isEnabled) {
                    $children = $item->getChildren();
                    foreach ($children as $childItem) {
                        /** @var Mage_Sales_Model_Quote_Item $childItem */
                        $childItem->setData('remove', true);
                    }
                }

                // if below is true we need to remove such item and inform customer about that
                if ($noStock || !$isSalable || !$isEnabled || $item->getData('remove')) {

                    // remove errors for current item
                    $this->_removeErrorsFromQuoteAndItem($item, Mage_CatalogInventory_Helper_Data::ERROR_QTY);

                    // remove errors for all children for this item
                    foreach ($item->getChildren() as $p) {
                        $this->_removeErrorsFromQuoteAndItem($p, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
                    }

                    // we need to inform customer about that we removed it
                    $parentItem = $item->getParentItem();
                    if (!empty($parentItem)) {
                        $session = Mage::getSingleton('customer/session');
                        $sizeText = $item->getProduct()->getAttributeText('size');
                        if (empty($sizeText)) {
                            $session->addError(Mage::helper('zolagocheckout')
                                ->__('Product %s was removed from cart because is out of stock.',
                                    $item->getName(),
                                    $sizeText));
                        } else {
                            $session->addError(Mage::helper('zolagocheckout')
                                ->__('Product %s size %s was removed from cart because is out of stock.',
                                    $item->getName(),
                                    $sizeText));
                        }
                    }

                    // now we remove item (and all children if exists)
                    $cart->removeItem($item->getItemId());
                }
            }
            $cart->save();
            /** @var Zolago_Checkout_Helper_Data $helper */
            $helper = Mage::helper("zolagocheckout");
            $helper->fixCartShippingRates();

            $cart->save();
            //after save, items with status disabled are removed from card so we need to remove last error message
            $this->_getQuote()->removeMessageByText('error', Mage::helper('cataloginventory')->__('Some of the products are currently out of stock'));
        }
        //end fix
        parent::indexAction();
    }

    /**
     * Removes error statuses from quote and item, set by observer Mage_CatalogInventory_Model_Observer
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param int $code
     * @return Mage_Sales_Model_Quote_Item $item
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code)
    {
        $params = array(
            'origin' => 'cataloginventory',
            'code' => $code
        );

        if ($item->getHasError()) {
            $item->removeErrorInfosByParams($params);
        }

        $quote = $item->getQuote();
        $quoteItems = $quote->getItemsCollection();
        $canRemoveErrorFromQuote = true;

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getItemId() == $item->getItemId()) {
                continue;
            }

            $errorInfos = $quoteItem->getErrorInfos();
            foreach ($errorInfos as $errorInfo) {
                if ($errorInfo['code'] == $code) {
                    $canRemoveErrorFromQuote = false;
                    break;
                }
            }

            if (!$canRemoveErrorFromQuote) {
                break;
            }
        }

        if ($quote->getHasError() && $canRemoveErrorFromQuote) {
            $quote->removeErrorInfosByParams(null, $params);
        }

        return $item;
    }

}
