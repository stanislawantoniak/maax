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
		$data = array();
        $q = $this->getRequest()->getParam("q");

        if(is_string($q) && strlen(trim($q))>0) {

            $po = $this->_registerPo();
            $storeId = $po->getOrder()->getStoreId();
            $vendorSku = Mage::helper('udropship')->getVendorSkuAttribute($storeId)->getAttributeCode();

            $collection = Mage::getResourceModel('catalog/product_collection');
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

            $collection->setStoreId($storeId);
            $collection->addAttributeToSelect("name", "left");
            $collection->addFinalPrice();
            $collection->addAttributeToSelect($vendorSku, "left");


            // START: Adding product flag to know that configurable product is in SALE or PROMOTION
            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attrProductFlag */
            $attrProductFlag = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, "product_flag");

            $collection->getSelect()
                ->joinLeft(
                    array ("cpr" => $collection->getTable('catalog/product_relation'))
                    ,"e.entity_id = cpr.child_id"
                    ,"parent_id"
                );

            $collection->getSelect()
                ->joinLeft(
                    array("cpei" => 'catalog_product_entity_int'),
                    'cpr.parent_id = cpei.entity_id'
                    .' AND ( cpei.store_id = ' . $storeId .') '
                    .' AND ( cpei.attribute_id = ' . $attrProductFlag->getAttributeId() . ')'
                    ,array(
                        'product_flag' => 'cpei.value'
                    )
                );
            // END

            // START: Adding product flag to know that simple product is in SALE or PROMOTION
            $collection->getSelect()
                ->joinLeft(
                    array("cpei2" => 'catalog_product_entity_int'),
                    'e.entity_id = cpei2.entity_id'
                    .' AND ( cpei2.store_id = ' . $storeId .') '
                    .' AND ( cpei2.attribute_id = ' . $attrProductFlag->getAttributeId() . ')'
                    ,array(
                        'product_flag_simple' => 'cpei2.value'
                    )
                );
            // END

            // START: adding url_path for configurable
            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attrProductFlag */
            $attrUrlPath = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, "url_path");

            $collection->getSelect()
                ->joinLeft(
                    array("cpev" =>'catalog_product_entity_varchar')
                    ,'cpev.entity_id = cpr.parent_id AND '.
                    '( cpev.store_id = ' . $storeId . ') AND '.
                    '( cpev.attribute_id = ' . $attrUrlPath->getAttributeId() . ') '
                    ,'cpev.value AS url_path_configurable'
                );
            // END

            // START: adding url_path for simple
            $collection->addAttributeToSelect('url_path', "left");
            // END

            $collection->addAttributeToFilter("udropship_vendor", $po->getUdropshipVendor());
            $collection->addFieldToFilter("type_id", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

            $collection->addAttributeToFilter(array(
                                                  array("attribute"=>$vendorSku,	"like"=>'%'.$q.'%'),
                                                  array("attribute"=>"sku",		"like"=>'%'.$q.'%'),
                                                  array("attribute"=>"name",		"like"=> '%'.$q.'%')
                                              ), "left");

            $data = $collection->getData();
        }

		foreach ($data as $idx => $val) {
			$key = $val['entity_id'];
			// Override when simple is super linked to couple of configurable
			if (isset($collArray[$key])) {
				// No matter witch url - first existing
				if (empty($collArray[$key]['url_path_configurable'])) {
					$collArray[$key]['url_path_configurable'] = $val['url_path_configurable'];
				}
				if (empty($collArray[$key]['url_path'])) {
					$collArray[$key]['url_path'] = $val['url_path'];
				}
			} else {
				// Only needed data
				$collArray[$key]['entity_id'] = $val['entity_id'];
				$collArray[$key]['name'] = $val['name'];
				$collArray[$key]['price'] = $val['price'];
				$collArray[$key]['skuv'] = $val['skuv'];
				// TODO: move this logic to sql query (IF statement)
				if ($val['url_path_configurable']) {
					$collArray[$key]['url_path'] = $val['url_path_configurable'];
				} else {
					$collArray[$key]['url_path'] = $val['url_path'] ? $val['url_path'] : "";
				}
				$flag = !is_numeric($val['product_flag']) ? $val['product_flag_simple'] : $val['product_flag'];
				if ($flag) {
					$collArray[$key]['product_flag'] = $flag;
				}
			}
		}
		$collArray = array_values($collArray);

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
	 * @throws Mage_Core_Exception
	 */
    protected function _registerPo($poId = null) {
        if(!Mage::registry("current_po")) {
			if (is_null($poId)) $poId = $this->getRequest()->getParam("id");
			/** @var Zolago_Po_Model_Po $po */
            $po = Mage::getModel("udpo/po")->load($poId);
            if(!$this->_vaildPo($po)) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("You are not allowed to operate this order"));
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
     * @return ZolagoOs_OmniChannel_Model_Vendor
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $this->_renderPage(null, 'udpo');
    }

    /**
     * @param string $action
     */
    protected function _processMass($action) {
		/** @var Zolago_Po_Helper_Data $hlp */
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
                $this->_getSession()->addError($hlp->__("Order #%s is not valid", $po->getIncrementId()));
            }
            foreach($notVaildPos['status'] as $po) {
                $this->_getSession()->addError($hlp->__("Order #%s has invalid status", $po->getIncrementId()));
            }
        }
        elseif($count) {
            $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
            /* @var $transaction Varien_Db_Adapter_Interface */
            try {
                $transaction->beginTransaction();

                if($action == self::ACTION_PRINT_AGGREGATED) {
                    // Action not based on status
                    $aggregated = $hlp->createAggregated($collection, $this->_getVendor());
                    $transaction->commit();                  
                    if ($id = $aggregated->getPrintId()) {
                        Mage::getSingleton('core/session')->setAggregatedPrintId($id);
                    }
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
                    Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
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
                Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
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
                Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
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
                Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
            );
            Mage::logException($e);
        }
        return $this->_redirectReferer();
    }

    /**
     * Gets param price and parse
     * If no set, default value taken
     * Note: null to float is zero
     *
     * @param null $default
     * @return float
     */
    protected function _getParamPrice($default = null) {
        $val = str_replace(",",".",$this->getRequest()->getParam("product_price", $default));
        return $this->_parseForFloat($val, $default);
    }

    /**
     * Gets param qty and parse
     * If no set, default value taken
     *
     * @param int $default
     * @return int
     */
    protected function _getParamQty($default = 1) {
        return (int)$this->getRequest()->getParam("product_qty", $default);
    }

    /**
     * Gets param discount and parse
     * If no set, default value taken
     *
     * @param int $default
     * @return mixed
     */
    protected function _getParamDiscount($default = 0) {
        $val = str_replace(",",".",$this->getRequest()->getParam("product_discount", $default));
        return $this->_parseForFloat($val, $default);
    }

    /**
     * Gets float value from string
     * If security problem return float from default
     *
     * @param string $val
     * @param $default
     * @return float
     */
    protected function _parseForFloat($val, $default) {
        if (substr_count($val, '.') < 2) {
            // security, from front this value always should by in format like: 999.99
            return round(floatval($val), 2);
        }
        return round(floatval($default), 2);
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            return $this->_redirectReferer();
        }

        $request = $this->getRequest();
        $itemId = $request->getParam("item_id");

        $item = $po->getItemById($itemId);
        /* @var $item Zolago_Po_Model_Po_Item */

        $price    = $this->_getParamPrice();
        $qty      = $this->_getParamQty();
        $discount = $this->_getParamDiscount();

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
            if(Mage::helper("core")->isModuleEnabled('ZolagoOs_OmniChannelTierCommission')) {
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
        }

        return $this->_redirectReferer();
    }
    /**
     * @todo move it into model
     * @return void
     */
    public function addItemAction() {
        /** @var Zolago_Po_Helper_Data $hlp */
        $hlp = Mage::helper("zolagopo");

        try {
            $po = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            return $this->_redirectReferer();
        }

        $store = $po->getOrder()->getStore();
        $request = $this->getRequest();

        $product = Mage::getModel("catalog/product")->
                   setStoreId($store->getId())->
                   load($request->getParam("product_id"));


        /** @var $product Mage_Catalog_Model_Product */

        $price    = $this->_getParamPrice();
        $qty      = $this->_getParamQty();
        $discount = $this->_getParamDiscount();

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


            /**
             * add child of configurable item
             * clone parentItem, unset order item, change & unset some data
             * set new child->setParentItem(parentItem)
             */
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($product->getId());
            $parentId = isset($parentIds[0]) ? $parentIds[0] : 0;

            if ($product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
                && ((int)$product->getData('visibility') !== Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
            ) {

                $itemSData = array(
                    'row_total' => $finalPriceExclTax * $qty,
                    'price' => $priceExclTax,
                    'weight' => $product->getWeight(),
                    'qty' => $qty,
                    'qty_shipped' => null,
                    'product_id' => $product->getId(),
                    'order_item_id' => null,
                    'additional_data' => null,
                    'description' => null,
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'base_cost' => $product->getCost(),
                    'qty_invoiced' => null,
                    'qty_canceled' => null,
                    'vendor_sku' => null,
                    'vendor_simple_sku' => null, // add by helper
                    'is_virtual' => $product->isVirtual(),
                    'commission_percent' => null, // ad by helper
                    'transaction_fee' => null, // add by helper
                    'price_incl_tax' => $priceInclTax,
                    'base_price_incl_tax' => $priceInclTax, // @todo use currency
                    'discount_amount' => $discountAmount,
                    'discount_percent' => $discountPrecent,
                    'row_total_incl_tax' => $priceInclTax * $qty,
                    'base_row_total_incl_tax' => $priceInclTax * $qty, // @todo use currency
                    'parent_item_id' => null
                );

                $item->addData($itemSData);
				$po->addItem($item);
				if(Mage::helper("core")->isModuleEnabled('ZolagoOs_OmniChannelTierCommission')) {
					Mage::helper("udtiercom")->processPo($this);
				}
                Mage::register('vendor_add_item_to_po_before', true, true);
            } else if (!empty($parentId)) {
                $productP = Mage::getModel('catalog/product')->load($parentId);

                //parent
                $itemData = array(
                    'row_total' => $finalPriceExclTax * $qty,
                    'price' => $priceExclTax,
                    'weight' => $product->getWeight(),
                    'qty' => $qty,
                    'qty_shipped' => null,
                    'product_id' => $productP->getId(),
                    'order_item_id' => null,
                    'additional_data' => null,
                    'description' => null,
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'base_cost' => $productP->getCost(),
                    'qty_invoiced' => null,
                    'qty_canceled' => null,
                    'vendor_sku' => $productP->getSkuv(),
                    'vendor_simple_sku' => $product->getSkuv(), // add by helper
                    'is_virtual' => $productP->isVirtual(),
                    'commission_percent' => null, // ad by helper
                    'transaction_fee' => null, // add by helper
                    'price_incl_tax' => $priceInclTax,
                    'base_price_incl_tax' => $priceInclTax, // @todo use currency
                    'discount_amount' => $discountAmount,
                    'discount_percent' => $discountPrecent,
                    'row_total_incl_tax' => $priceInclTax * $qty,
                    'base_row_total_incl_tax' => $priceInclTax * $qty, // @todo use currency
                    'parent_item_id' => null
                );

                $item->addData($itemData);

                $sizeCode = 'size';

                $productM = Mage::getModel('catalog/product')
                    ->setStoreId($store->getId())
                    ->setData($sizeCode, $product->getSize());
                $optionLabel = $product->getAttributeText($sizeCode);

                $attributeInfo = Mage::getSingleton("eav/config")
                    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $sizeCode);


                $productPptions = array(
                    'attributes_info' => array(
                        array(
                            'label' => $attributeInfo->getStoreLabel($store->getId()),
                            'value' => $optionLabel
                        )
                    )
                );

				$po->addItem($item);


                //simple
                $child = clone $item;
                $itemPData = array(
                    'row_total' => 0,
                    'price' => 0,
                    'qty' => $qty,
                    'product_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'base_cost' => $product->getCost(),
                    'vendor_sku' => $product->getSkuv(),
                    'vendor_simple_sku' => null,
                    'price_incl_tax' => null,
                    'base_price_incl_tax' => null, // @todo use currency
                    'row_total_incl_tax' => null,
                    'base_row_total_incl_tax' => null,
                    'discount_amount'		=> null,
                    'discount_percent'		=> null
                );

                Mage::register('vendor_add_item_to_po_before', true, true);

                $child->addData($itemPData);
                $child
                    ->getOrderItem()
                    ->setData('parent_item_id', $item->getOrderItem()->getId())
                    ->save();

				$po->addItem($child);

				// Process for simple and configurable at once
				if(Mage::helper("core")->isModuleEnabled('ZolagoOs_OmniChannelTierCommission')) {
					Mage::helper("udtiercom")->processPo($po);
				}

                $item
                    ->getOrderItem()
                    ->setProductOptions($productPptions)
                    ->save();
            }


            Mage::helper("udropship")->addVendorSkus($po);

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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
                Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
            );
            Mage::logException($e);
        }
        return $this->_redirectReferer(); //$this->_redirectUrl($this->_getAnchorEditUrl("comments"));
    }
    protected function _getAnchorEditUrl($anchor) {
        return Mage::getUrl("*/*/edit", array("id"=>$this->_registerPo()->getId()))."#".$anchor;
    }

    /**
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function saveShippingMethodAction() {
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            return $this->_redirectReferer();
        }

        /* @var $po Zolago_Po_Model_Po */
        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */


        if (!$po->getId()) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                "status" => 0,
                "content" => Mage::helper("zolagopo")->__("Wrong PO Id")
            )));
            return;
        }

        if ($po->getVendor()->getId() != $session->getVendor()->getId()) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                "status" => 0,
                "content" => Mage::helper("zolagopo")->__("You have no access to this PO")
            )));
            return;
        }

        $response = array(
            "status" => 1,
            "content" => array()
        );

        try {
            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Order cannot be edited."));
            }

            if(isset($data['add_own']) && $data['add_own']==1) {
                $orignAddress = $po->getOrder()->getShippingAddress();
                $oldAddress = $po->getShippingAddress();

                $newAddress = clone $orignAddress;

                $storeId =$po->getStoreId();
                $omniChannelMethodInfoByMethod = Mage::helper("udropship")
                    ->getOmniChannelMethodInfoByMethod($storeId, $data['udropship_method'], true, true);

                if ($omniChannelMethodInfoByMethod->getDeliveryCode() == GH_Inpost_Model_Carrier::CODE) {
                    $locker = Mage::getModel('ghinpost/locker')->load($data['inpost_delivery_point_name'], 'name');

                    $data['street'] = $locker->getStreet() . " " . $locker->getBuildingNumber();
                    $data['city'] = $locker->getTown();
                    $data['postcode'] = $locker->getPostcode();
                    $po->setDeliveryPointName($data['inpost_delivery_point_name']);
                } else if ($omniChannelMethodInfoByMethod->getDeliveryCode() == Orba_Shipping_Model_Packstation_Pwr::CODE) {
                    $pwrPoint = Mage::getModel("zospwr/point")->loadByName($data['pwr_delivery_point_name']);

                    $data['street'] = $pwrPoint->getStreet() . " " . $pwrPoint->getBuildingNumber();
                    $data['city'] = $pwrPoint->getTown();
                    $data['postcode'] = $pwrPoint->getPostcode();
                    $po->setDeliveryPointName($data['pwr_delivery_point_name']);
                } else if ($omniChannelMethodInfoByMethod->getDeliveryCode() == ZolagoOs_PickupPoint_Helper_Data::CODE) {
                    /* @var $pos  Zolago_Pos_Model_Pos */
                    $pos = Mage::getModel("zolagopos/pos")->load($data['pickuppoint_delivery_point_name']);

                    $data['street'] = $pos->getStreet();
                    $data['city'] = $pos->getCity();
                    $data['postcode'] = $pos->getPostcode();

                    $po->setDeliveryPointName($data['pickuppoint_delivery_point_name']);
                } else {
                    $po->setDeliveryPointName('');
                }

                //$condition = json_decode($omniChannelMethodInfoByMethod->getCondition());
                $oldUdropshipMethod = $po->getUdropshipMethod();
                $newUdropshipMethod = $data['udropship_method'];
                $po->setUdropshipMethod($newUdropshipMethod);

                //$po->setData('base_shipping_amount_incl', $condition[0]->price);
                //$po->setData('shipping_amount_incl', $condition[0]->price);

                //validate address data start
                $errors = false;
                $langHelper = Mage::helper("zolagopo");
                if(!$data['firstname']) {
                    $errors = true;
                    $session->addError($langHelper->__("Invalid first name"));
                }
                if(!$data['lastname']) {
                    $errors = true;
                    $session->addError($langHelper->__("Invalid last name"));
                }
                if(!$data['telephone'] && $type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $errors = true;
                    $session->addError($langHelper->__("Invalid telephone"));
                }
                if(!$data['street']) {
                    $errors = true;
                    $session->addError($langHelper->__("Invalid street"));
                }
                if(!$data['city']) {
                    $errors = true;
                    $session->addError($langHelper->__("Invalid city"));
                }
                if(!$data['postcode'] || !preg_match('/^\d{2}-\d{3}$/',$data['postcode'])) {
                    $errors = true;
                    $session->addError($langHelper->__("Invalid postcode"));
                }
                //validate address data end

                if($errors) {
                    $this->_redirectReferer();
                } else {
                    $newAddress->addData($data);
                    if ($type == Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                        $po->setOwnShippingAddress($newAddress);
                    } else {
                        $po->setOwnBillingAddress($newAddress);
                    }

                    Mage::dispatchEvent("zolagopo_po_shipping_method_change", array(
                        "po" => $po,
                        "new_udropship_method" => $newUdropshipMethod,
                        "old_udropship_method" => $oldUdropshipMethod,
                        "type" => $type
                    ));

                    Mage::dispatchEvent("zolagopo_po_address_change", array(
                        "po" => $po,
                        "new_address" => $newAddress,
                        "old_address" => $oldAddress,
                        "type" => $type
                    ));

                    $po->save();

                    $session->addSuccess(Mage::helper("zolagopo")->__("Address changed"));
                    $response['content']['reload'] = 1;
                }
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
                "content"=>Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
            );
            if(!$isAjax) {
                $session->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            }
        }
        if($isAjax) {
            $this->getResponse()->setHeader("content-type", "application/json");
            $this->getResponse()->setBody(Zend_Json::encode($response));
        } else {
            $this->_redirectReferer();
        }
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            return $this->_redirectReferer();
        }

        /* @var $po Zolago_Po_Model_Po */
        $session = $this->_getSession();
        /* @var $session Zolago_Dropship_Model_Session */


        if (!$po->getId()) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                "status" => 0,
                "content" => Mage::helper("zolagopo")->__("Wrong PO Id")
            )));
            return;
        }

        if ($po->getVendor()->getId() != $session->getVendor()->getId()) {
            $this->getResponse()->setBody(Zend_Json::encode(array(
                "status" => 0,
                "content" => Mage::helper("zolagopo")->__("You have no access to this PO")
            )));
            return;
        }

        $response = array(
            "status" => 1,
            "content" => array()
        );

        try {
            if(!$po->getStatusModel()->isEditingAvailable($po)) {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Order cannot be edited."));
            }
            /*if(isset($data['restore']) && $data['restore']==1) {
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
            else*/if(isset($data['add_own']) && $data['add_own']==1) {
                if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
                    $orignAddress = $po->getOrder()->getShippingAddress();
                    $oldAddress = $po->getShippingAddress();
                } else {
                    $orignAddress = $po->getOrder()->getBillingAddress();
                    $oldAddress = $po->getBillingAddress();
                }
                $newAddress = clone $orignAddress;

	            //validate address data start
	            $errors = false;
	            $langHelper = Mage::helper("zolagopo");
				if(!$data['firstname']) {
					$errors = true;
					$session->addError($langHelper->__("Invalid first name"));
				}
	            if(!$data['lastname']) {
		            $errors = true;
		            $session->addError($langHelper->__("Invalid last name"));
	            }
	            if(!$data['telephone'] && $type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
		            $errors = true;
		            $session->addError($langHelper->__("Invalid telephone"));
	            }
	            if(!$data['street']) {
		            $errors = true;
		            $session->addError($langHelper->__("Invalid street"));
	            }
	            if(!$data['city']) {
		            $errors = true;
		            $session->addError($langHelper->__("Invalid city"));
	            }
	            if(!$data['postcode'] || !preg_match('/^\d{2}-\d{3}$/',$data['postcode'])) {
		            $errors = true;
		            $session->addError($langHelper->__("Invalid postcode"));
	            }
	            //validate address data end

	            if($errors) {
		            $this->_redirectReferer();
	            } else {
		            $newAddress->addData($data);
		            if ($type == Mage_Sales_Model_Order_Address::TYPE_SHIPPING) {
			            $po->setOwnShippingAddress($newAddress);
		            } else {
			            $po->setOwnBillingAddress($newAddress);
		            }


		            Mage::dispatchEvent("zolagopo_po_address_change", array(
			            "po" => $po,
			            "new_address" => $newAddress,
			            "old_address" => $oldAddress,
			            "type" => $type
		            ));

		            $po->save();

		            $session->addSuccess(Mage::helper("zolagopo")->__("Address changed"));
		            $response['content']['reload'] = 1;
	            }
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
                            "content"=>Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
                        );
            if(!$isAjax) {
                $session->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            return $this->_redirectReferer();
        }
        /* @var $po ZolagoOs_OmniChannelPo_Model_Po */

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

        $this->getResponse()->setBody(Zend_Json::encode(array("status"=>0, "message"=>"There was a technical error. Please contact shop Administrator.")));
    }

    protected function _addShipping($carrier,$udpo,$shipment) {
        $session = $this->_getSession();
        $shippingManager = Mage::helper('orbashipping')->getShippingManager($carrier);
        
        $r = $this->getRequest();
        $settings = $shippingManager->prepareSettings($r,$shipment,$udpo);

        $pos = $udpo->getDefaultPos();
        $shippingManager->setSenderAddress($pos->getSenderAddress());
        $receiver = $udpo->getShippingAddress()->getData();
        $shippingManager->setReceiverCustomerAddress($receiver);
        if ($carrier == Orba_Shipping_Model_Carrier_Dhl::CODE) {
            $this->getRequest()->setParam("shipping_source_account", $settings["account"]);
            //Assign Client Number to Gallery Or To Vendor
            if (isset($settings["gallery_shipping_source"])
                && ($settings["gallery_shipping_source"] == 1)
            ) {
                $this->getRequest()->setParam("gallery_shipping_source", 1);
            }
        }

        $result = $shippingManager->createShipments();
        $number = null;
        if ($result['shipmentId']) {
            $number = $result['shipmentId'];
        } else {
            Mage::helper('udropship')->addShipmentComment(
                $shipment,
                $result['message']
            );
            $shipment->save();
            Mage::helper('orbashipping/carrier')->addUdpoComment($udpo, $result['message'], false, true, false);

            $session->addError(Mage::helper('zolagopo')->__('Service Error. Shipment Canceled. Please try again later.'));
        }

        if (!$number) {
            $session->addError(Mage::helper('zolagopo')->__('Shipping creation fail'));
            Mage::helper('udpo')->cancelShipment($shipment, true);
            $udpo->getStatusModel()->processStartPacking($udpo, true);
            return null;
        }
        return $number;
    }

    /**
     * creating manual shipping
     * @return string
     */
    protected function _addShippingManually() {
        $number = $this->getRequest()->getParam('tracking_id');
        return $number;
    }
    
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
        $highlight = array();

        try {            
            $udpo = $this->_registerPo();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
            return $this->_redirectReferer();
        }        
        if (!$id = $udpo->getId()) {
            return;
        }
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
             $connection->beginTransaction();
	        /** @var Zolago_Po_Helper_Shipment $manager */
            $manager = Mage::helper('zolagopo/shipment');
            if ($r->getParam('use_label_shipping_amount')) {
                $udpo->setUseLabelShippingAmount(true);
            }
            elseif ($r->getParam('shipping_amount')) {
                $udpo->setShipmentShippingAmount($r->getParam('shipping_amount'));
            }

            $poStatus = $r->getParam('status');
            $manager->setPoStatus($poStatus);

            $carrier = $r->getParam('carrier');
            $carrierTitle = $r->getParam('carrier_title');
            $manager->setCarrierData($carrier,$carrierTitle);


            $udpo->setUdpoNoSplitPoFlag(true);
            $manager->setUdpo($udpo);

            $shipment = $manager->getShipment();
            $number = $this->_addShipping($carrier,$udpo,$shipment);
            if (!$number) {                            
                $connection->commit();
                $this->_redirectReferer();
	            return;
            }

            $manager->setNumber($number);

	        //add track type to request
	        $r->setParam('track_type',GH_Statements_Model_Track::TRACK_TYPE_ORDER);

            $manager->processSaveTracking($r->getParams());
            $session->addSuccess($this->__('Tracking ID has been added'));
            $highlight['tracking'] = true;

            $printLabel = $r->getParam('print_label');
            if (!$printLabel && $manager->checkChangeStatus($printLabel)) {
                if ($r->getParam('force_status_change_flag')) {
                    $udpo->setForceStatusChangeFlag(true);
                }
                // set cancel params
                if ($poStatus == ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_CANCELED) {
                    $udpo->setFullCancelFlag($r->getParam('full_cancel'));
                    $udpo->setNonshippedCancelFlag($r->getParam('nonshipped_cancel'));
                }
                 // save new status
                 $poStatusChanged = $manager->processSetStatus();   
                 if ($poStatusChanged) {
                    $session->addSuccess($this->__('Purchase order status has been changed'));
                } else {
                    $session->addError($this->__('Cannot change purchase order status'));
                }
            }            
            $manager->invoiceShipment();
            $comment = $r->getParam('comment');
            if ($comment) {
            
                Mage::helper('udpo')->sendVendorComment($udpo, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }
            $session->setHighlight($highlight);
            $connection->commit();    
        } catch (Exception $e) {
            // cancel shipment if exists
            if (!empty($shipment)) {
                Mage::helper('udpo')->cancelShipment($shipment, true);
                
            }
            $udpo->getStatusModel()->processStartPacking($udpo, true);
            $session->addError($e->getMessage());
            $connection->rollback();
        }
        
        return $this->_redirectReferer();
    }

    
    /**
     * change status and send mail to customer (pick up is ready)
     */

    public function sendPickUpInfoAction() {
        try {
            $udpo = $this->_registerPo();
            $udpo->getStatusModel()->changeStatus($udpo,Zolago_Po_Model_Po_Status::STATUS_TO_PICK);            
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("You have send mail to customer"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $udpo->getStatusModel()->processStartPacking($udpo, false, true);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Packing started"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
        }
        return $this->_redirectReferer();
    }

    public function confirmPickUpAction() {
        try {
            $udpo = $this->_registerPo();
            if (!$udpo->isPaid()) {
                $this->_getSession()->addError(Mage::helper("zolagopo")->__("The order should be paid."));
                return $this->_redirectReferer();
            }

            $udpo->getStatusModel()->confirmPickUp($udpo);
            $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Order Pick Up has been successfully confirmed."));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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

        /** @var Zolago_Po_Helper_Data $_helper */
        $_helper = Mage::helper("zolagopo");

        try {
            $udpo = $this->_registerPo();
            $statusModel = $udpo->getStatusModel();
            if(!$statusModel->isCancelShippingAvailable($udpo)) {
                throw new Mage_Core_Exception("Status cannot be changed.");
            }
            /** @var Mage_Sales_Model_Order_Shipment $shipment */
            $shipment = Mage::getModel("sales/order_shipment")->load($r->getParam("shipping_id"));

            //do not allow to cancel shipments that have already been sent
            $track = $shipment->getTracksCollection()->setOrder("created_at", "DESC")->getFirstItem();
            if($track && $track->getId() && !is_null($track->getData('shipped_date'))) {
                Mage::throwException("You cannot cancel shipment that has been already sent.");
            }

            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            if($shipment->getId() && $shipment->getUdpoId()==$udpo->getId()) {
                $udpoHlp = Mage::helper('udpo');
                /* @var $udpoHlp ZolagoOs_OmniChannelPo_Helper_Data */
                $udpoHlp->cancelShipment($shipment, true);
                $udpo->getStatusModel()->processCancelShipment($udpo);
                $this->_getSession()->addSuccess($_helper->__("Shipping canceled."));
            } else {
                throw new Mage_Core_Exception("Wrong shipment.");
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(
                $_helper->__($e->getMessage())
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(
                $_helper->__("There was a technical error. Please contact shop Administrator.")
            );
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
                                  "message" => Mage::helper('zolagocommon')->nToBr($message),
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
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
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator."));
        }
    }



    /**
     * @param $customerName
     * @param $customerEmail
     * @param $template
     * @param array $templateParams
     * @param null $storeId
     * @return Zolago_Common_Model_Core_Email_Template_Mailer
     */
    protected function _sendEmailTemplate($customerName, $customerEmail,
                                          $template, $templateParams = array(), $storeId = null)
    {
        $templateParams['use_attachments'] = true;

        $mailer = Mage::getModel('core/email_template_mailer');
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

	/**
	 * Save new InPost locker do PO
	 */
	public function updateInpostDataAction() {
		try {
			$inpostName = $this->getRequest()->getParam("inpostName");
			$poId = $this->getRequest()->getParam("poId");
			$po = $this->_registerPo($poId);
			/* @var $session Zolago_Dropship_Model_Session */
			$session = $this->_getSession();

			if ($inpostName && $po->getId() && ($po->getUdropshipVendor() == $session->getVendor()->getId())) {

                $locker = Mage::getModel('ghinpost/locker')->load($inpostName, 'name');

                $shippingAddress = Mage::getModel('sales/order_address')->load($po->getShippingAddressId());

                $shippingAddress->setStreet($locker->getStreet() . " " . $locker->getBuildingNumber())
                    ->setCity($locker->getTown())
                    ->setPostcode($locker->getPostcode())
                    ->save();

                $po->setDeliveryPointName($inpostName)
                    ->save();
				
				Mage::dispatchEvent("zolagopo_po_inpost_locker_name_change", array(
					"po" => $po,
					"inpost_name" => $inpostName,
					"type" => Mage_Sales_Model_Order_Address::TYPE_SHIPPING
				));
				
				$this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Correctly written a new delivery address to InPost locker."));
			} else {
				throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("There is no such PO"));
			}

		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		} catch (Exception $e) {
			Mage::logException($e);
			$this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
		}
	}
}


