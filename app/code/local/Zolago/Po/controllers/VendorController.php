<?php

class Zolago_Po_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract {


    const XML_PATH_ZOLAGO_PO_COMPOSE_EMAIL_TEMPLATE = "udropship/purchase_order/zolagopo_compose";

    const ACTION_CONFIRM_STOCK = "confirm_stock";
    const ACTION_START_PACKING = "start_packing";
    const ACTION_DIRECT_REALISATION = "direct_realisation";
    const ACTION_PRINT_AGGREGATED = "print_aggregated";

    /**
     * Lazy loading handle
     */
    public function loadCollectionAction() {
        $collArray = array();
        $q = $this->getRequest()->getParam("q");

        if(is_string($q) && strlen(trim($q))>0) {

            $po = $this->_registerPo();
            $storeId = $po->getOrder()->getStoreId();
            $vendorSku = Mage::helper('udropship')->getVendorSkuAttribute($storeId)->getAttributeCode();

            $collection = Mage::getResourceModel('catalog/product_collection');
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

            $collection->setStoreId($storeId);
            $collection->addAttributeToSelect("name", "left");
            $collection->addAttributeToSelect("price");
            $collection->addAttributeToSelect($vendorSku, "left");

            $collection->addAttributeToFilter("udropship_vendor", $po->getUdropshipVendor());
            $collection->addFieldToFilter("type_id", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

            $collection->addAttributeToFilter(array(
				array("attribute"=>$vendorSku,	"like"=>'%'.$q.'%'),
				array("attribute"=>"sku",		"like"=>'%'.$q.'%'),
				array("attribute"=>"name",		"like"=> '%'.$q.'%')
			), "left");

            $collection->load();

            $collArray = array_values($collection->toArray());
        }


        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setBody(Zend_Json::encode($collArray));
    }

    public function preDispatch() {
        /**
         * @todo add secure to own PO
         */
        return parent::preDispatch();
    }

    /**
     * @return Zolago_Po_Model_Po
     */
    protected function _registerPo() {
        if(!Mage::registry("current_po")) {
            $poId = $this->getRequest()->getParam("id");
            $po = Mage::getModel("udpo/po")->load($poId);
            if(!$this->_vaildPo($po)) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("It's not your object"));
            }
            Mage::register("current_po", $po);
        }
        return Mage::registry("current_po");
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _registerPos() {
        if(!Mage::registry("current_pos")) {
            $posId = $this->getRequest()->getParam("pos");
            $pos = Mage::getModel("zolagopos/pos")->load($posId);
            Mage::register("current_pos", $pos);
        }
        return Mage::registry("current_pos");
    }

    /**
     * @return Unirgy_Dropship_Model_Vendor
     */
    protected function _getVendor() {
        return $this->_getSession()->getVendor();
    }

    public function indexAction() {
        // Override origin index
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_renderPage(array('default', 'adminhtml_head'), 'udpo');
    }

	/**
	 * Edit po
	 * @return void
	 */
    public function editAction() {
        try {
			$this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch(Exception $e) {
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $this->_renderPage(null, 'udpo');
    }

    /**
     * @param string $action
     */
    protected function _processMass($action) {
        $hlp = Mage::helper("zolagopo");
        $ids = $this->_getMassIds();
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        /* @var $collection Zolago_Po_Model_Resource_Po_Collection */
        if(count($ids)) {
            $collection->addFieldToFilter("entity_id", array("in"=>$ids));
        } else {
            $collection->addFieldToFilter("entity_id", -1);
        }

        $notVaildPos = array(
                           'vendor' => array(),
                           'status' => array()
                       );
        $count = $collection->count();

        foreach($collection as $po) {
            /* @var $po Zolago_Po_Model_Po */
            if(!$this->_vaildPo($po)) {
                $notVaildPos['vendor'][] = $po;
            };

            switch ($action) {
            case self::ACTION_CONFIRM_STOCK:
                if(!$po->getStatusModel()->isConfirmStockAvailable($po)) {
                    $notVaildPos['status'][] = $po;
                }
                break;
            case self::ACTION_PRINT_AGGREGATED:
                if(!$po->getStatusModel()->isPrintAggregatedAvailable($po)) {
                    $notVaildPos['status'][] = $po;
                }
                break;
            case self::ACTION_DIRECT_REALISATION:
                if(!$po->getStatusModel()->isDirectRealisationAvailable($po)) {
                    $notVaildPos['status'][] = $po;
                }
                break;
            case self::ACTION_START_PACKING:
                if(!$po->getStatusModel()->isStartPackingAvailable($po)) {
                    $notVaildPos['status'][] = $po;
                }
                break;
            }
        }

        if(count($notVaildPos['vendor']) || count($notVaildPos['status'])) {
            foreach($notVaildPos['vendor'] as $po) {
                $this->_getSession()->addError($hlp->__("Order #%s is not vaild", $po->getIncrementId()));
            }
            foreach($notVaildPos['status'] as $po) {
                $this->_getSession()->addError($hlp->__("Order #%s has invaild status", $po->getIncrementId()));
            }
        }
        elseif($count) {
            $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
            /* @var $transaction Varien_Db_Adapter_Interface */
            try {
                $transaction->beginTransaction();

                if($action == self::ACTION_PRINT_AGGREGATED) {
                    // Action not based on status
                    $id = Mage::helper('zolagopo')->createAggregated($collection, $this->_getVendor());
                    $transaction->commit();
                    Mage::getSingleton('core/session')->setAggregatedPrintId($id);
                } else {
                    // All actions based on satatus
                    foreach($collection as $po) {
                        switch ($action) {
                        case self::ACTION_CONFIRM_STOCK:
                            $po->getStatusModel()->processConfirmStock($po);
                            break;
                        case self::ACTION_DIRECT_REALISATION:
                            $po->getStatusModel()->processDirectRealisation($po);
                            break;
                        case self::ACTION_START_PACKING:
                            $po->getStatusModel()->processStartPacking($po);
                            break;
                        }
                    }
                }
                $transaction->commit();
                $this->_getSession()->addSuccess($hlp->__("%d order processed", $count));
            } catch(Mage_Core_Exception $e) {
                $transaction->rollBack();
                $this->_getSession()->addError($e->getMessage());
            } catch(Exception $e) {
                $transaction->rollBack();
                $this->_getSession()->addError(
                    Mage::helper("zolagopo")->__("Some error occure")
                );
                Mage::logException($e);
            }
        }
        else {
            $this->_getSession()->addError(
                Mage::helper("zolagopo")->__("No selected orders")
            );
        }
    }

    /**
     * @return type
     */
    protected function _massRedirectReferer($defaultUrl=null) {
        $ids = $this->_getMassIds();
        $filter = "massaction=1&udropship_status=0";
        $this->getResponse()->setRedirect(Mage::getUrl("*/*/index", array(
                                              "filter" => Mage::helper("core")->urlEncode($filter),
                                              "internal_po" => implode(",",$ids)
                                          )));
        return $this;
    }

    /**
     * @return void
     */
    public function massConfirmStockAction() {
        $this->_processMass(self::ACTION_CONFIRM_STOCK);
        return $this->_massRedirectReferer();
    }

    /**
     * @return void
     */
    public function massStartPackingAction() {
        $this->_processMass(self::ACTION_START_PACKING);
        return $this->_massRedirectReferer();
    }

    /**
     * @return void
     */
    public function massPrintAggregatedAction() {
        $this->_processMass(self::ACTION_PRINT_AGGREGATED);
        return $this->_massRedirectReferer();
    }

    /**
     * @return void
     */
    public function massDirectRealisationAction() {
        $this->_processMass(self::ACTION_DIRECT_REALISATION);
        return $this->_massRedirectReferer();
    }

    /**
     * @param Zolago_Po_Model_Po $po
     * @return bool
     */
    public function _vaildPo(Zolago_Po_Model_Po $po) {
        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */
        return $po->isAllowed($session->getVendor(),
                              $session->isOperatorMode() ? $session->getOperator() : null);
    }

    /**
     * @return array
     */
    protected function _getMassIds() {
        return explode(",", $this->getRequest()->getParam('po', ''));
    }


    public function splitAction() {
        $hlp = Mage::helper("zolagopo");

        try {
            $po = $this->_registerPo();
            $items = $this->getRequest()->getParam("items");
            $newPo = $po->split($items);
            $this->_getSession()->addSuccess(
                Mage::helper("zolagopo")->__("Order has been split. New order: #%s.", $newPo->getIncrementId())
            );
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            $this->_getSession()->addError(
                Mage::helper("zolagopo")->__("Some error occure")
            );
            Mage::logException($e);
        }
        return $this->_redirectReferer();
    }

    public function shippingCostAction() {
        $hlp = Mage::helper("zolagopo");

        try {
            $po = $this->_registerPo();
            $price = $this->getRequest()->getParam("price");
            $oldPrice = $po->getShippingAmountIncl();
            $store = $po->getOrder()->getStore();

            if(is_null($price) || (float)$price<0) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Illegal price"));
            }
            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception($hlp->__("Cannot remove item of order in this status."));
            }

            /** Tax **/
            $taxCalculationModel = Mage::getSingleton('tax/calculation');
            /* @var $taxCalculationModel Mage_Tax_Model_Calculation */

            $customerGroup = Mage::getModel("customer/group")->load($po->getOrder()->getCustomerGroupId());
            /* @var $customerGroup Mage_Customer_Model_Group */

            $request = $taxCalculationModel->getRateRequest(
                           $po->getShippingAddress(),
                           $po->getBillingAddress(),
                           $customerGroup->getTaxClassId(),
                           $store
                       );

            $shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store);

            $shippingTax		= 0;
            $shippignInclTax	= 0;
            if ($shippingTaxClass) {
                if ($rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass))) {
                    if (!Mage::helper('tax')->shippingPriceIncludesTax()) {
                        $shippingTax   = $price * $rate/100;
                        $shippignInclTax = $price + $shippingTax;
                    } else {
                        $shippingTax  = $price * (1 - 1 / (($rate/100)+1));
                        $shippignInclTax = $price;
                    }
                    $shippingTax = $store->roundPrice($shippingTax);
                }
            } else {
                $shippignInclTax = $price;
            }

            $data = array(
                        "shipping_tax"				=> $shippingTax,
                        "base_shipping_tax"			=> $shippingTax,
                        "shipping_amount_incl"		=> $shippignInclTax,
                        "base_shipping_amount_incl"	=> $shippignInclTax
                    );

            $po->addData($data);

            Mage::dispatchEvent("zolagopo_po_shipping_cost", array(
                                    "po"			=> $po,
                                    "new_price"		=> $shippignInclTax,
                                    "old_price"		=> $oldPrice,
                                ));

            $po->updateTotals(true);

            $this->_getSession()->addSuccess(
                Mage::helper("zolagopo")->__("Shipping amount has been changed")
            );
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            $this->_getSession()->addError(
                Mage::helper("zolagopo")->__("Some error occure")
            );
            Mage::logException($e);
        }
        return $this->_redirectReferer();
    }

    /**
     * @todo move it into model
     * @return void
     */
    public function removeItemAction() {
        $hlp = Mage::helper("zolagopo");


        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        /* @var $transaction Varien_Db_Adapter_Interface */

        try {
            $po = $this->_registerPo();
            $itemId = $this->getRequest()->getParam("item_id");
            $item = Mage::getModel("zolagopo/po_item")->load($itemId);

            if(!$item->getId()) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Item doesn't exists"));
            }
            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception($hlp->__("Cannot remove item of order in this status."));
            }
            $transaction->beginTransaction();

            // Delete child items if exists
            $collection = Mage::getResourceModel('zolagopo/po_item_collection');
            /* @var $collection Zolago_Po_Model_Resource_Po_Item_Collection */

            $collection->addParentFilter($item);

            foreach($collection as $childItem) {
                $childItem->delete();
            }

            $itemName = $item->getOneLineDesc();


            Mage::dispatchEvent("zolagopo_po_item_remove", array(
                                    "po"		=> $po,
                                    "item"		=> $item,
                                ));

            $item->delete();

            $po->updateTotals(true);

            $this->_getSession()->addSuccess(
                Mage::helper("zolagopo")->__("Item %s has been removed", $itemName)
            );

            $transaction->commit();

        } catch(Mage_Core_Exception $e) {
            $transaction->rollback();
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            $transaction->rollback();
            $this->_getSession()->addError(
                Mage::helper("zolagopo")->__("Some error occure")
            );
            Mage::logException($e);
        }
        return $this->_redirectReferer();
    }

    /**
     * @todo move it into model
     * @return void
     */
    public function editItemAction() {
        $hlp = Mage::helper("zolagopo");

        try {
            $po = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
            return $this->_redirectReferer();
        }

        $request = $this->getRequest();
        $itemId = $request->getParam("item_id");

        $item = $po->getItemById($itemId);
        /* @var $item Zolago_Po_Model_Po_Item */

        $price = str_replace(",",".",$request->getParam("product_price"));
        $qty = (int)$request->getParam("product_qty", 1);
        $discount = str_replace(",",".",$request->getParam("product_discount", 0));

        $product = Mage::getModel("catalog/product");//

        if($item && $item->getId()) {
            $product->load($item->getProductId());
        }

        if(empty($discount) || $discount<0) {
            $discount = 0;
        }

        $errors = array();

        if(!$item || !$item->getId()) {
            $errors[] = $hlp->__("Wrong item");
        }

        if(empty($price) || !is_numeric($price) || $price<0) {
            $errors[] = $hlp->__("Price is incorrect");
        }

        if(empty($qty) || !is_numeric($qty) || $qty<1) {
            $errors[] = $hlp->__("Qty is inncorrect");
        }

        if(!is_numeric($discount) | (!empty($discount) && $discount>$price)) {
            $errors[] = $hlp->__("Discount is inncorrect");
        }

        if(!$product->getId() || $product->getUdropshipVendor()!=$this->_getVendor()->getId()) {
            $errors[] = $hlp->__("It's not your product");
        }


        if($errors) {
            foreach($errors as $error) {
                $this->_getSession()->addError($error);
            }
            return $this->_redirectReferer();
        }

        try {

            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception($hlp->__("Cannot edit order in this status."));
            }

            $taxHelper = Mage::helper('tax');
            /* @var $taxHelper Mage_Tax_Helper_Data */
            $product->setPrice($price);

            $finalPrice = $price-$discount;
            $baseRowPrice = $price * $qty;
            $finalRowPrice = $finalPrice * $qty;
			/**
			 * @todo this is real percent, magento use basic percent sum rule percent 1 + rule prcent 2
			 * so 10 + 10 = 20; the price is discoutne by price * 10% * 10%, so final  result is not sum!
			 */
            $discountPrecent = round(($discount/$price)*100, 2);

            $discountAmount = $baseRowPrice - $finalRowPrice;

            if($this->_getIsBruttoPrice()) {
                $priceInclTax = $price;
                $priceExclTax = $taxHelper->getPrice($product, $price, false, null, null, null, null, true);
                $finalPriceInclTax = $finalPrice;
                $finalPriceExclTax = $taxHelper->getPrice($product, $finalPrice, false, null, null, null, null, true);

            } else {
                $priceExclTax = $price;
                $priceInclTax = $taxHelper->getPrice($product, $price, true, null, null, null, null, false);
                $finalPriceExclTax = $finalPrice;
                $finalPriceInclTax = $taxHelper->getPrice($product, $finalPrice, true, null, null, null, null, false);
            }

            $itemData = array(
                            'row_total'				=> $finalPriceExclTax * $qty,
                            'price'					=> $priceExclTax,
                            'qty'					=> $qty,
                            'price_incl_tax'		=> $priceInclTax,
                            'base_price_incl_tax'	=> $priceInclTax, // @todo use currency
                            'discount_amount'		=> $discountAmount,
                            'discount_percent'		=> $discountPrecent,
                            'row_total_incl_tax'	=> $priceInclTax*$qty,
                            'base_row_total_incl_tax'=> $priceInclTax*$qty, // @todo use currency
                        );

            $oldItem = clone $item;

            $item->addData($itemData);

            Mage::helper("udropship")->addVendorSkus($po);
            if(Mage::helper("core")->isModuleEnabled('Unirgy_DropshipTierCommission')) {
                Mage::helper("udtiercom")->processPo($po);
            }

            Mage::dispatchEvent("zolagopo_po_item_edit", array(
                                    "po"		=> $po,
                                    "old_item"	=> $oldItem,
                                    "new_item"	=> $item
                                ));

            $po->updateTotals(true);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Item saved"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }
    /**
     * @todo move it into model
     * @return void
     */
    public function addItemAction() {
        $hlp = Mage::helper("zolagopo");

        try {
            $po = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
            return $this->_redirectReferer();
        }

        $store = $po->getOrder()->getStore();
        $request = $this->getRequest();

        $product = Mage::getModel("catalog/product")->
                   setStoreId($store->getId())->
                   load($request->getParam("product_id"));
        /* @var $prodcut Mage_Catalog_Model_Product */

        $price = $request->getParam("product_price");
        $qty = $request->getParam("product_qty", 1);
        $discount = $request->getParam("product_discount", 0);

        if(empty($discount) || $discount<0) {
            $discount = 0;
        }

        $errors = array();

        if(empty($price) || !is_numeric($price) || $price<0) {
            $errors[] = $hlp->__("Price is incorrect");
        }

        if(empty($qty) || !is_numeric($qty) || $qty<1) {
            $errors[] = $hlp->__("Qty is inncorrect");
        }

        if(!is_numeric($discount) | (!empty($discount) && $discount>$price)) {
            $errors[] = $hlp->__("Discount is inncorrect");
        }

        if(!$product->getId() || $product->getUdropshipVendor()!=$this->_getVendor()->getId()) {
            $errors[] = $hlp->__("It's not your product");
        }

        if($product->getTypeId()!=Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $errors[] = $hlp->__("It's not simple product");
        }

        if($errors) {
            foreach($errors as $error) {
                $this->_getSession()->addError($error);
            }
            return $this->_redirectReferer();
        }

        try {

            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception($hlp->__("Cannot edit order in this status."));
            }

            $taxHelper = Mage::helper('tax');
            /* @var $taxHelper Mage_Tax_Helper_Data */
            $product->setPrice($price);

            $finalPrice = $price-$discount;
            $baseRowPrice = $price * $qty;
            $finalRowPrice = $finalPrice * $qty;
            $discountPrecent = round(($discount/$price)*100, 2);

            $discountAmount = $baseRowPrice - $finalRowPrice;

            if($this->_getIsBruttoPrice()) {
                $priceInclTax = $price;
                $priceExclTax = $taxHelper->getPrice($product, $price, false, null, null, null, null, true);
                $finalPriceInclTax = $finalPrice;
                $finalPriceExclTax = $taxHelper->getPrice($product, $finalPrice, false, null, null, null, null, true);

            } else {
                $priceExclTax = $price;
                $priceInclTax = $taxHelper->getPrice($product, $price, true, null, null, null, null, false);
                $finalPriceExclTax = $finalPrice;
                $finalPriceInclTax = $taxHelper->getPrice($product, $finalPrice, true, null, null, null, null, false);
            }

            $item = Mage::getModel("zolagopo/po_item");
            /* @var $item Zolago_Po_Model_Po_Item */

            $itemData = array(
				'row_total'				=> $priceExclTax * $qty,
				'price'					=> $priceExclTax,
				'weight'				=> $product->getWeight(),
				'qty'					=> $qty,
				'qty_shipped'			=> null,
				'product_id'			=> $product->getId(),
				'order_item_id'			=> null,
				'additional_data'		=> null,
				'description'			=> null,
				'name'					=> $product->getName(),
				'sku'					=> $product->getSku(),
				'base_cost'				=> $product->getCost(),
				'qty_invoiced'			=> null,
				'qty_canceled'			=> null,
				'vendor_sku'			=> null, // add by helper
				'vendor_simple_sku'		=> null, // add by helper
				'is_virtual'			=> $product->isVirtual(),
				'commission_percent'	=> null, // ad by helper
				'transaction_fee'		=> null, // add by helper
				'price_incl_tax'		=> $priceInclTax,
				'base_price_incl_tax'	=> $priceInclTax, // @todo use currency
				'discount_amount'		=> $discountAmount,
				'discount_percent'		=> $discountPrecent,
				'row_total_incl_tax'	=> $priceInclTax*$qty,
				'base_row_total_incl_tax'=> $priceInclTax*$qty, // @todo use currency
				'parent_item_id'		=> null
			);
			
            $item->addData($itemData);
            $po->addItem($item);
			
			/**
			 * @todo add child of configurable item
			 * clone parentItem, unset order item, change & unset some data
			 * set new child->setParentItem(parentItem)
			 */

            Mage::helper("udropship")->addVendorSkus($po);
            if(Mage::helper("core")->isModuleEnabled('Unirgy_DropshipTierCommission')) {
                Mage::helper("udtiercom")->processPo($po);
            }

            Mage::dispatchEvent("zolagopo_po_item_add", array(
				"po"		=> $po,
				"item"		=> $item
			));

            $po->updateTotals(true);

            $po->getStatusModel()->processDirectRealisation($po, true);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Item added"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }

    public function addCommentAction() {
        try {
            $_po = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
            return $this->_redirectReferer();
        }

        $comment = $this->getRequest()->getParam("comment");

        if(empty($comment)) {
            $this->_getSession()->addError(
                Mage::helper("zolagopo")->__("Enter some comment")
            );
            return $this->_redirectReferer();
        }

        if($this->_getVendor()) {
            $comment = "[" .$this->_getVendor()->getVendorName() . "] " . $comment;
        }

        try {
            $_po->addComment($comment, false, true);
            $_po->saveComments();
            $this->_getSession()->addSuccess(
                Mage::helper("zolagopo")->__("Comment added")
            );
        } catch(Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch(Exception $e) {
            $this->_getSession()->addError(
                Mage::helper("zolagopo")->__("Some error occure")
            );
            Mage::logException($e);
        }
        return $this->_redirectReferer(); //$this->_redirectUrl($this->_getAnchorEditUrl("comments"));
    }
    protected function _getAnchorEditUrl($anchor) {
        return Mage::getUrl("*/*/edit", array("id"=>$this->_registerPo()->getId()))."#".$anchor;
    }


    public function saveAddressAction() {
        $req	=	$this->getRequest();
        $data	=	$req->getPost();
        $type	=	$req->getParam("type");
        $isAjax =	$req->isAjax();

        try {
            $po = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
            return $this->_redirectReferer();
        }

        /* @var $po Zolago_Po_Model_Po */
        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */


        if(!$po->getId()) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                                              "status"=>0,
                                              "content"=>Mage::helper("zolagopo")->__("Wrong PO Id")
                                          )));
            return;
        }

        if($po->getVendor()->getId()!=$session->getVendor()->getId()) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                                              "status"=>0,
                                              "content"=>Mage::helper("zolagopo")->__("You have no access to this PO")
                                          )));
            return;
        }

        $response = array(
                        "status"=>1,
                        "content"=>array()
                    );

        try {
            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Order cannot be edited."));
            }
            if(isset($data['restore']) && $data['restore']==1) {
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $po->clearOwnShippingAddress();
                } else {
                    $po->clearOwnBillingAddress();
                }

                Mage::dispatchEvent("zolagopo_po_address_restore", array(
                                        "po"		=> $po,
                                        "type"		=> $type
                                    ));

                $po->save();
                $session->addSuccess(Mage::helper("zolagopo")->__("Address restored"));
                $response['content']['reload']=1;
            }
            elseif(isset($data['add_own']) && $data['add_own']==1) {
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $orignAddress = $po->getOrder()->getShippingAddress();
                    $oldAddress = $po->getShippingAddress();
                } else {
                    $orignAddress = $po->getOrder()->getBillingAddress();
                    $oldAddress = $po->getBillingAddress();
                }
                $newAddress = clone $orignAddress;
                $newAddress->addData($data);
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $po->setOwnShippingAddress($newAddress);
                } else {
                    $po->setOwnBillingAddress($newAddress);
                }


                Mage::dispatchEvent("zolagopo_po_address_change", array(
                                        "po"			=> $po,
                                        "new_address"	=> $newAddress,
                                        "old_address"	=> $oldAddress,
                                        "type"			=> $type
                                    ));

                $po->save();

                $session->addSuccess(Mage::helper("zolagopo")->__("Address changed"));
                $response['content']['reload']=1;
            }
        } catch(Mage_Core_Exception $e) {
            $response = array(
                            "status"	=>0,
                            "content"	=>$e->getMessage()
                        );
            if(!$isAjax) {
                $session->addError($e->getMessage());
            }
        } catch(Exception $e) {
            Mage::logException($e);
            $response = array(
                            "status"=>0,
                            "content"=>Mage::helper("zolagopo")->__("Some errors occure. Check logs.")
                        );
            if(!$isAjax) {
                $session->addError(Mage::helper("zolagopo")->__("Some errors occure. Check logs."));
            }
        }
        if($isAjax) {
            $this->getResponse()->setHeader("content-type", "application/json");
            $this->getResponse()->setBody(Zend_Json::encode($response));
        } else {
            $this->_redirectReferer();
        }
    }

    public function updatePosAction() {

        $poId = $this->getRequest()->getParam("id");
        $posId = $this->getRequest()->getParam("pos_id");

        try {
            $po = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
            return $this->_redirectReferer();
        }
        /* @var $po Unirgy_DropshipPo_Model_Po */

        $pos = Mage::getModel("zolagopos/pos")->load($posId);
        /* @var $pos Zolago_Pos_Model_Pos */
        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */

        $reload = false;

        $this->getResponse()->setHeader("content-type", "application/json");

        if($po->getId() && $pos->getId() &&
                $po->getVendor()->getId()==$session->getVendor()->getId() &&
                $pos->isAssignedToVendor($session->getVendor())) {

            $po->setDefaultPosId($pos->getId());
            $po->setDefaultPosName($pos->getName());
            if($session->isOperatorMode()) {
                if(!in_array($pos->getId(), $session->getOperator()->getAllowedPos())) {
                    $reload = true;
                }
            }
            $po->save();
            $this->getResponse()->setBody(Zend_Json::encode(array(
                                              "status"=>1,
                                              "reload"=>$reload,
                                              "pos"=>$pos->getData()
                                          )));
            return;
        }

        $this->getResponse()->setBody(Zend_Json::encode(array("status"=>0, "message"=>"Some error occure")));
    }


    //{{{
    /**
     * creating dhl shipping
     * @param Zolago_Po_Model_Po $udpo
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param Unirgy_DropshipMicrosite_Model_Store $store
     * @return string
     */
    protected function _addShippingDhl($udpo,$shipment,$store) {
        /**
         * DHL: Make a WebApi Call to get T&T Data
         */
        $r = $this->getRequest();
        $hlp = Mage::helper('udropship');
        $udpoHlp = Mage::helper('udpo');

        $vendor = $hlp->getVendor($udpo->getUdropshipVendor());
        $session = $this->_getSession();

        $dhlSettings = $udpoHlp->getDhlSettings($vendor, $udpo->getDefaultPosId());
        $number = null;
        $weight =  $r->getParam("weight");

        if(empty($weight)) {
            if($shipment && $shipment->getTotalWeight()) {
                $weight = ceil($shipment->getTotalWeight());
            } else {
                $weight = Mage::helper('orbashipping/carrier_dhl')->getDhlDefaultWeight();
            }
        }


        $shipment->setTotalWeight($weight);

        if ($shipment && $dhlSettings) {

            $shipmentSettings = array(
                                    'type'			=> $r->getParam('specify_orbadhl_type'),
                                    'width'			=> $r->getParam('specify_orbadhl_width'),
                                    'height'		=> $r->getParam('specify_orbadhl_height'),
                                    'length'		=> $r->getParam('specify_orbadhl_length'),
                                    'weight'		=> $weight,
                                    'quantity'		=> Orba_Shipping_Model_Carrier_Client_Dhl::SHIPMENT_QTY,
                                    'nonStandard'	=> $r->getParam('specify_orbadhl_custom_dim'),
                                    'shipmentDate'  => $this->_porcessDhlDate($r->getParam('specify_orbadhl_shipping_date')),
                                    'shippingAmount'=> $r->getParam('shipping_amount')
                                );
            $number = $this->_createShipments($dhlSettings, $shipment, $shipmentSettings, $udpo);
            if (!$number) {
                $session->addError($this->__('Shipping creation fail'));
                $udpoHlp->cancelShipment($shipment, true);
                $udpo->getStatusModel()->processStartPacking($udpo, true);
                return null;
            }
        }
        return $number;

    }
    //}}}

    //{{{
    /**
     * creating manual shipping
     * @return string
     */
    protected function _addShippingManually() {
        $number = $this->getRequest()->getParam('tracking_id');
        return $number;
    }
    //}}}
    
    //{{{ 
    /**
     * creating ups shipping
     * @return string 
     */
    protected function _addShippingUps($udpo,$shipment) {
        $number = $this->getRequest()->getParam('tracking_id');
            if (!$number) {                
                $udpoHlp = Mage::helper('udpo');
                $session = $this->_getSession();
                $session->addError($this->__('Shipping creation fail'));
                $udpoHlp->cancelShipment($shipment, true);
                $udpo->getStatusModel()->processStartPacking($udpo, true);
                return null;
            }
        return $number;
    }
    //}}}
    /**
     * @return void
     * @throws Mage_Core_Exception
     * @throws Exception
     */
    public function saveShippingAction()
    {

        $r = $this->getRequest();
        $udpoHlp = Mage::helper('udpo');
        $session = $this->_getSession();
        $hlp = Mage::helper('udropship');

        try {
            $udpo = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
            return $this->_redirectReferer();
        }

        if (!$id = $udpo->getId()) {
            return;
        }
        $vendor = $hlp->getVendor($udpo->getUdropshipVendor());

        try {

            if(!$udpo->getStatusModel()->isShippingAvailable($udpo)) {
                throw new Mage_Core_Exception(
                    Mage::helper("zolagopo")->__("Shipment cannot be created with this stauts.")
                );
            }

            $store = $udpo->getOrder()->getStore();

            $track = null;
            $highlight = array();

            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');

            $printLabel = $r->getParam('print_label');
            $number = $r->getParam('tracking_id');

            $carrier = $r->getParam('carrier');
            $carrierTitle = $r->getParam('carrier_title');

            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $poAutoComplete = Mage::getStoreConfig('udropship/vendor/auto_complete_po', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $poStatusShipped = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED;
            $poStatusDelivered = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED;
            $poStatusCanceled = Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED;
            $poStatuses = Mage::getSingleton('udpo/source')->setPath('po_statuses')->toOptionHash();
            $poStatus = $r->getParam('status');




            //if ($printLabel || $number || ($partial=='ship' && $partialQty)) {
            $partialQty = $partialQty ? $partialQty : array();
            if ($r->getParam('use_label_shipping_amount')) {
                $udpo->setUseLabelShippingAmount(true);
            }
            elseif ($r->getParam('shipping_amount')) {
                $udpo->setShipmentShippingAmount($r->getParam('shipping_amount'));
            }
            $udpo->setUdpoNoSplitPoFlag(true);

            $shipment = $udpoHlp->createShipmentFromPo($udpo, $partialQty, true, true, true);

            if ($shipment) {
                $shipment->setNewShipmentFlag(true);
                $shipment->setDeleteOnFailedLabelRequestFlag(true);
                $shipment->setCreatedByVendorFlag(true);
            } else {
                Mage::throwException("Cannot create shipment");
            }

            //}

            switch($carrier) {
            case Orba_Shipping_Model_Carrier_Dhl::CODE:
                $number = $this->_addShippingDhl($udpo,$shipment,$store);
                break;
            case Orba_Shipping_Model_Carrier_Ups::CODE:
                $number = $this->_addShippingUps($udpo,$shipment);
                break;
            default:
                $number = $this->_addShippingManually();
                ;
            }
            if (!$number) {
                return $this->_redirectReferer();

            }
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);
            $poStatus = $r->getParam('status');


            $isShipped = $poStatus == $poStatusShipped || $poStatus==$poStatusDelivered || $autoComplete && ($poStatus==='' || is_null($poStatus));
            $method = explode('_', $shipment->getUdropshipMethod(), 2);
            $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            $_carrier = $method[0];
            if (!empty($carrier) && !empty($carrierTitle)) {
                $_carrier = $carrier;
                $title = $carrierTitle;
            }
            $track = Mage::getModel('sales/order_shipment_track')
                     ->setNumber($number)
                     ->setCarrierCode($_carrier)
                     ->setTitle($title);

            $shipment->addTrack($track);

            Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);
            Mage::helper('udropship')->addShipmentComment(
                $shipment,
                $this->__('%s added tracking ID %s', $vendor->getVendorName(), $number)
            );
            $shipment->save();
            $session->addSuccess($this->__('Tracking ID has been added'));

            $highlight['tracking'] = true;


            $udpoStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_udpo_status')) {
                $udpoStatuses = Mage::getStoreConfig('udropship/vendor/restrict_udpo_status');
                if (!is_array($udpoStatuses)) {
                    $udpoStatuses = explode(',', $udpoStatuses);
                }
            }

            if (!$printLabel && !is_null($poStatus) && $poStatus!=='' && $poStatus!=$udpo->getUdropshipStatus()
                    && (!$udpoStatuses || (in_array($udpo->getUdropshipStatus(), $udpoStatuses) && in_array($poStatus, $udpoStatuses)))
               ) {
                $oldStatus = $udpo->getUdropshipStatus();
                $poStatusChanged = false;
                if ($r->getParam('force_status_change_flag')) {
                    $udpo->setForceStatusChangeFlag(true);
                }
                if ($oldStatus==$poStatusCanceled && !$udpo->getForceStatusChangeFlag()) {
                    Mage::throwException(Mage::helper('udpo')->__('Canceled purchase order cannot be reverted'));
                }
                if ($poStatus==$poStatusShipped || $poStatus==$poStatusDelivered) {
                    foreach ($udpo->getShipmentsCollection() as $_s) {
                        $hlp->completeShipment($_s, true, $poStatus==$poStatusDelivered);
                    }
                    if (isset($_s)) {
                        $hlp->completeOrderIfShipped($_s, true);
                    }
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                }
                elseif ($poStatus == $poStatusCanceled) {
                    $udpo->setFullCancelFlag($r->getParam('full_cancel'));
                    $udpo->setNonshippedCancelFlag($r->getParam('nonshipped_cancel'));
                    Mage::helper('udpo')->cancelPo($udpo, true, $vendor);
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                }
                else {
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                }
                $udpo->getCommentsCollection()->save();
                if ($poStatusChanged) {
                    $session->addSuccess($this->__('Purchase order status has been changed'));
                } else {
                    $session->addError($this->__('Cannot change purchase order status'));
                }
            }

            if (!empty($shipment) && $shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
                $shipment->setNoInvoiceFlag(false);
                $udpoHlp->invoiceShipment($shipment);
            }

            $comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($udpo->getAllItems() as $item) {
                        if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                //$udpo->addComment($comment, false, true)->getCommentsCollection()->save();
                Mage::helper('udpo')->sendVendorComment($udpo, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            // Carrier saved
            $udpo->setCurrentCarrier($carrier);
            $udpo->getResource()->saveAttribute($udpo, "current_carrier");

            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }

        return $this->_redirectReferer();
    }

    /**
     *
     * @return type
     */
    public function setConfirmStockAction() {
        try {
            $udpo = $this->_registerPo();
            $udpo->getStatusModel()->processConfirmStock($udpo);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("You have confirmed reservation"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }

    /**
     *
     * @return type
     */
    public function setConfirmReleaseAction() {
        try {
            $udpo = $this->_registerPo();
            $udpo->getStatusModel()->processConfirmRelease($udpo);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Order release confirmed"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }

    /**
     *
     * @return type
     */
    public function startPackingAction() {
        try {
            $udpo = $this->_registerPo();
            $udpo->getStatusModel()->processStartPacking($udpo);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Packing started"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
        return $this->_redirectReferer();
    }

    public function directRealisationAction() {
        try {
            $udpo = $this->_registerPo();
            $udpo->getStatusModel()->processDirectRealisation($udpo);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Order moved to fulfilment. Note that stock check is cleared."));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
        return $this->_redirectReferer();
    }

    /**
     *
     * @return type
     * @throws Mage_Core_Exception
     */
    public function changeStatusAction() {
        try {
            $udpo = $this->_registerPo();
            $statusModel = $udpo->getStatusModel();

            if(!$statusModel->isManulaStatusAvailable($udpo)) {
                throw new Mage_Core_Exception(
                    Mage::helper("zolagopo")->__("Status cannot be changed.")
                );
            }

            $newStatus = $this->getRequest()->getParam('status');



            if(!in_array($newStatus, array_keys($statusModel->getAvailableStatuses($udpo)))) {
                throw new Mage_Core_Exception(
                    Mage::helper("zolagopo")->__("Requested status is wrong")
                );
            }
            $statusModel->changeStatus($udpo, $newStatus);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Status has been changed"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
        return $this->_redirectReferer();

    }

    /**
     *
     * @return type
     * @throws Mage_Core_Exception
     */
    public function cancelShippingAction() {
        $r = $this->getRequest();

        try {
            $udpo = $this->_registerPo();
            $statusModel = $udpo->getStatusModel();
            if(!$statusModel->isCancelShippingAvailable($udpo)) {
                throw new Mage_Core_Exception(
                    Mage::helper("zolagopo")->__("Status cannot be changed.")
                );
            }
            $shipment = Mage::getModel("sales/order_shipment")->load($r->getParam("shipping_id"));
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            if($shipment->getId() && $shipment->getUdpoId()==$udpo->getId()) {
                $udpoHlp = Mage::helper('udpo');
                /* @var $udpoHlp Unirgy_DropshipPo_Helper_Data */
                $udpoHlp->cancelShipment($shipment, true);
                $udpo->getStatusModel()->processCancelShipment($udpo);
                $this->_getSession()->addSuccess("Shipping canceled.");
            } else {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Wrong shipment."));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }

    public function composeAction() {

        $r = $this->getRequest();

        try {
            $udpo = $this->_registerPo();
            $order = $udpo->getOrder();
            $store = $order->getStore();
            $vendor = $this->_getVendor();
            $message = $r->getParam("message");

            $templateParams = array(
                                  "po" => $udpo,
                                  "order" => $order,
                                  "store" => $store,
                                  "vendor" => $vendor,
                                  "message" => $message,
                                  "use_attachments" => true,
                                  "store_name" => $store->getFrontendName(),
                              );

            $this->_sendEmailTemplate(
                $order->getCustomerName(),
                $order->getCustomerEmail(),
                self::XML_PATH_ZOLAGO_PO_COMPOSE_EMAIL_TEMPLATE,
                $templateParams,
                $store->getId()
                );

            Mage::dispatchEvent("zolagopo_po_compose", array(
                                    "po"		=> $udpo,
                                    "message"	=> $message,
                                    "recipient"	=> $order->getCustomerName())
                               );

            $this->_getSession()->addSuccess((Mage::helper("zolagopo")->__("Message sent via email")));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }

    public function changePosAction() {
        try {
            $po=$this->_registerPo();
            $pos=$this->_registerPos();
            $oldPos = $po->getPos();
            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Order cannot be edited."));
            }

            if($pos->getId()==$oldPos->getId()) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("New POS and old POS are the same"));
            }

            $po->setPos($pos);
            $po->setDefaultPosId($pos->getId());
            $po->setDefaultPosName($pos->getName());
            $po->save();
            $po->getStatusModel()->processDirectRealisation($po, true);

            Mage::dispatchEvent("zolagopo_po_change_pos", array(
                                    "po"=>$po,
                                    "old_pos"=>$oldPos,
                                    "new_pos"=>$pos
                                ));

            $this->_getSession()->addSuccess((Mage::helper("zolagopo")->__("POS has been changed.")));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }

        return $this->_redirectReferer();
    }

    public function getPosStockAction() {
        try {
            $this->_registerPo();
            $this->_registerPos();
            $this->loadLayout();
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
    }

    protected function _porcessDhlDate($date) {
        $_date = explode("-", $date);
        if(count($_date)==3) {
            if(count($_date[0])==4) {
                return $date;
            }
            return $_date[2] . "-" . $_date[1] . "-" . $_date[0];
        }
    }

    protected function _createShipments($dhlSettings, $shipment, $shipmentSettings, $udpo) {
        $number		= false;
        $dhlClient	= Mage::helper('orbashipping/carrier_dhl')->startClient($dhlSettings);
        $posModel	= Mage::getModel('zolagopos/pos')->load($udpo->getDefaultPosId());
        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */

        if ($posModel && $posModel->getId()) {
            $dhlClient->setPos($posModel);
            $dhlResult	= $dhlClient->createShipments($shipment, $shipmentSettings,$udpo);
            $result		= $dhlClient->processDhlShipmentsResult('createShipments', $dhlResult);

            if ($result['shipmentId']) {
                $number = $result['shipmentId'];
            } else {
                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $result['message']
                );
                $shipment->save();
                Mage::helper('zolagodhl')->addUdpoComment($udpo, $result['message'], false, true, false);

                $session->addError($this->__('DHL Service Error. Shipment Canceled. Please try again later.'));
            }
        }

        return $number;
    }

    /**
     * @param $customerName
     * @param $customerEmail
     * @param $template
     * @param array $templateParams
     * @param null $storeId
     * @return Zolago_Po_VendorController
     */
    protected function _sendEmailTemplate($customerName, $customerEmail,
                                        $template, $templateParams = array(), $storeId = null)
    {
        $templateParams['use_attachements'] = true;

        $mailer = Mage::getModel('zolagocommon/core_email_template_mailer');
        /* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */

        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($customerEmail, $customerName);
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(array(
            'name' => Mage::getStoreConfig('trans_email/ident_support/name', $storeId),
            'email' => Mage::getStoreConfig('trans_email/ident_support/email', $storeId)));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId(Mage::getStoreConfig($template, $storeId));
        $mailer->setTemplateParams($templateParams);

        return $mailer->send();
    }

    /**
     * @param Mage_Core_Model_Store|null $store
     * @return bool
     */
    protected function _getIsBruttoPrice($store=null) {
        return Mage::getStoreConfig('tax/calculation/price_includes_tax', $store);
    }

}


