<?php
class GH_Api_Helper_Data extends Mage_Core_Helper_Abstract {
    const GHAPI_RMA_PREFIX = 'RMA_';

    public function getWsdlUrl() {
        return Mage::getUrl('ghapi/wsdl');
    }

    public function getWsdlTestUrl() {
        return Mage::getUrl('ghapi/wsdl/test');
    }

    /**
     * function helps to read wsdl from self signed servers
     *
     * @param string $url wsdl file
     * @param array $params wsdl params
     * @return string
     */
    public function prepareWsdlUri($url,&$params) {
        $opts = array(
                    'ssl' => array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed' => true)
                );
        $params['stream_context'] = stream_context_create($opts);
        $file = file_get_contents($url,false,stream_context_create($opts));
        $dir = Mage::getBaseDir('var');
        $filename = $dir.'/'.uniqid().'.wsdl';
        file_put_contents($filename,$file);
        return $filename;
    }

    /**
     * Gets date based on timestamp or current one if timestamp is null
     * @param int|null $timestamp
     * @return bool|string
     */
    public function getDate($timestamp=null) {
        $time = Mage::getSingleton('core/date')->timestamp();
        $timestamp = is_null($timestamp) ? $time : $timestamp;
        return date('Y-m-d H:i:s',$timestamp);
    }

    /**
     * @param $date
     * @param string $format default Y-m-d H:i:s
     * @return bool
     */
    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * @param $data
     * @param $vendorId
     * @return array
     */
    public function prepareSku($data, $vendorId) {
        $batch = array();
        foreach ($data as $skuV => $item) {
            $sku = $vendorId . "-" . $skuV;
            $batch[$sku] = $item;
        }
        return $batch;
    }

    /**
     * @param $data
     * @param $externalId
     * @return array
     */
    public function preparePriceBatch($data, $externalId)
    {
        $data = json_decode(json_encode($data), true);
        $batch = array();

        if (!isset($data["product"]))
            return $batch;

        // single product
        if (isset($data["product"]["sku"])) {
            $data["product"] = array($data["product"]);
        }

        foreach ($data["product"] as $product) {
            $sku = $externalId . "-" . $product['sku'];

            foreach ($product['pricesTypesList'] as $type) {
                if (isset($type['priceType'])) {
                    $type = array($type);
                }
                foreach ($type as $item) {
                    $batch[$sku][$item['priceType']] = $item['priceValue'];
                }
            }
        }

        return $batch;
    }


    /**
     * @param $data
     * @param $externalId
     * @return array
     */
    public function prepareStockBatch($data, $externalId)
    {
        $data = json_decode(json_encode($data), true);
        $batch = array();

        if (!isset($data["product"]))
            return $batch;

        // single product
        if (isset($data["product"]["sku"])) {
            $data["product"] = array($data["product"]);
        }

        foreach ($data["product"] as $product) {
            $sku = $externalId . "-" . $product['sku'];

            foreach ($product['posesList'] as $pos) {
                if (isset($pos['id'])) {
                    $pos = array($pos);
                }
                foreach ($pos as $item) {
                    $batch[$externalId][$sku][$item['id']] = $item['qty'];
                }
            }
        }

        return $batch;
    }

    /**
     * Retrieve not valid skus
     * Not valid if:
     * product is not connected to vendor
     * product don't exist
     *
     * @param $data
     * @param $vendorId
     * @return array
     */
    public function getNotValidSkus($data, $vendorId) {
        $inputSkus = array();
        foreach ($data as $sku => $item) {
            $inputSkus[$sku] = $sku;
        }

        /* @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->addFieldToFilter('sku', array( 'in' => $inputSkus));
        $coll->addAttributeToSelect('udropship_vendor', 'left');
        $coll->addAttributeToSelect('skuv', 'left');

        $_data = $coll->getData();
        $allSkusFromColl = array();
        $invalidOwnerSkus = array();

        // wrong owner
        foreach ($_data as $product) {
            $allSkusFromColl[$product['sku']] = $product['sku'];
            if ($product['udropship_vendor'] != $vendorId) {
                $invalidOwnerSkus[$product['sku']] = $product['sku'];
            }
        }

        // not existing products
        $notExistingSkus = array_diff($inputSkus, $allSkusFromColl);

        $allErrorsSkus = array_merge($invalidOwnerSkus, $notExistingSkus);
        // get skuv from sku
        foreach ($allErrorsSkus as $key => $sku) {
            $allErrorsSkus[$key] = $this->getSkuvFromSku($sku, $vendorId)."[not existing]";
        }
        $allErrorsSkus = array_unique($allErrorsSkus);
        return $allErrorsSkus;
    }

    public function getSkuvFromSku($sku, $vendorId) {
        return preg_replace('/' . preg_quote($vendorId . '-', '/') . '/', '', $sku, 1);
    }

    /**
     * Retrieve not valid skus by price
     * Not valid if:
     * price is less equal 0
     *
     * @param $data
     * @param $externalId
     * @return array
     */
    public function getNotValidSkusByPrices($data, $externalId) {
        $errorsSkus = array();
        foreach ($data as $sku => $item) {
            foreach ($item as $type => $price) {
                if ($price <= 0) {
                    $errorsSkus[$sku] = $sku;
                }
            }
        }
        foreach ($errorsSkus as $key => $sku) {
            $errorsSkus[$key] = $this->getSkuvFromSku($sku, $externalId) . "[invalid price]";
        }
        $errorsSkus = array_unique($errorsSkus);
        return $errorsSkus;
    }

    /**
     * Retrieve not valid skus by Qtys
     * Not valid if:
     * qty is not numeric
     *
     * @param $data
     * @param $externalId
     * @return array
     */
    public function getNotValidSkusByQtys($data, $externalId) {
        $errorsSkus = array();
        foreach ($data as $sku => $pos) {
            foreach ($pos as $id => $qty) {
                if (!is_numeric($qty)) {
                    $errorsSkus[$sku] = $sku;
                }
            }
        }
        foreach ($errorsSkus as $key => $sku) {
            $errorsSkus[$key] = $this->getSkuvFromSku($sku, $externalId) . "[invalid quantity]";
        }
        $errorsSkus = array_unique($errorsSkus);
        return $errorsSkus;
    }

    /**
     * Retrieve not valid skus by POSes
     *
     * @param $data
     * @param $externalId
     * @param $vendorId
     * @return array
     */
    public function getNotValidSkusByPoses($data, $externalId, $vendorId) {
        /** @var Zolago_Pos_Helper_Data $helper */
        $helper = Mage::helper('zolagopos');
        $errorsSkus = array();
        foreach ($data as $sku => $pos) {
            foreach ($pos as $id => $qty) {
                if (!$helper->isValidForVendor($id, $vendorId)) {
                    $errorsSkus[$sku] = $sku;
                }
            }
        }
        foreach ($errorsSkus as $key => $sku) {
            $errorsSkus[$key] = $this->getSkuvFromSku($sku, $externalId) . "[invalid POS]";
        }
        $errorsSkus = array_unique($errorsSkus);
        return $errorsSkus;
    }

    /**
     * @return void
     * @throws Mage_Core_Exception
     */
    public function throwUserNotLoggedInException() {
        Mage::throwException('error_user_not_logged_in');
    }

    /**
     * @throws Mage_Core_Exception
     * @return void
     */
    public function throwDbError() {
        Mage::throwException('error_db_error');
    }

    /**
     * returns logged in user by session token
     * if session expired then throws error
     * @param $token
     * @return GH_Api_Model_User
     * @throws Mage_Core_Exception
     */
    public function getUserByToken($token) {
        /** @var GH_Api_Model_User $user */
        $user = Mage::getModel('ghapi/user');
        return $user->loginBySessionToken($token);
    }

    public function getApiOrderEmail($orderId) {
            return sprintf(Mage::getStoreConfig('ghapi_options/ghapi_general/ghapi_order_email'),$orderId);
    }
    
    /**
     * prepare delivery item for ghapi
     */

    protected function _getDeliveryItem($po) {
        
        $deliveryItem = array();

        // Shipping cost
        $deliveryItem['is_delivery_item']           = 1;
        $deliveryItem['item_sku']                   = '';
        $deliveryItem['item_name']                  = Mage::helper('ghapi')->__('Delivery and package');
        $deliveryItem['item_qty']                   = 1;
        $deliveryItem['item_value_before_discount'] = $po->getBaseShippingAmountIncl();
        $deliveryItem['item_discount']              = $po->getShippingDiscountIncl();
        $deliveryItem['item_value_after_discount']  = $po->getShippingAmountIncl();
        return $deliveryItem;
    }
    /**
     * prepare po items as array
     */
     
    protected function _getPoItems($po) {
        $orderItems = array();
        foreach ($po->getItemsCollection() as $item) {
            /** @var Zolago_Po_Model_Po_Item $item */
            if (!$item->isDeleted()) {
                $orderItem = array();
                if (!$item->getParentItemId()) {
                    if ($item->getAdditionalData('product_type') != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                        // all expect bundle
                        $orderItem['is_delivery_item']           = 0;
                        $orderItem['item_sku']                   = $item->getFinalSku();
                        $orderItem['item_name']                  = $item->getName();
                        $orderItem['item_qty']                   = $item->getQty();
                        $orderItem['item_value_before_discount'] = $item->getPriceInclTax() * $item->getQty();
                        $orderItem['item_discount']              = $item->getDiscount() * $item->getQty();
                        $orderItem['item_value_after_discount']  = ($item->getPriceInclTax() - $item->getDiscount()) * $item->getQty();
                        $orderItems[] = $orderItem;
                    }
                } else if ($data = $item->getAdditionalData('bundle_selection_attributes')) {
                    // simple products from bundle
                    $bundle = unserialize($data);
                    $parent = $item->getParentItem();
                    $qty = $parent->getQty();
                    $orderItem['is_delivery_item']           = 0;
                    $orderItem['item_sku']                   = $item->getSku();
                    $orderItem['item_name']                  = $item->getName();
                    $orderItem['item_qty']                   = $item->getQty() * $qty;
                    $orderItem['item_value_before_discount'] = $bundle['price'] * $qty; // price is for all items
                    $orderItem['item_discount']              = 0; //no discount for bundle element
                    $orderItem['item_value_after_discount']  = $bundle['price'] * $qty;
                    $orderItems[] = $orderItem;
                }
            }
        }
        return $orderItems;
    }

    
    /**
     * prepare po data as array
     */
    protected function _getPoDataArray($po,$vendor,$showCustomerEmail = false) {            
        $order = array();
        $dueAmount = ($po->getDebtAmount() > 0)? 0:abs($po->getDebtAmount());
        /** @var Zolago_Po_Model_Po $po */
        $order['vendor_id']                = $vendor->getId();
        $order['vendor_name']              = $vendor->getVendorName();
        $order['order_id']                 = $po->getIncrementId();
        $order['order_date']               = $po->getCreatedAt();
        $order['order_max_shipping_date']  = $po->getMaxShippingDate();
        $order['order_status']             = $po->getStatusModel()->ghapiOrderStatus($po->getUdropshipStatus());
        $order['order_total']              = $po->getGrandTotalInclTax();
        $order['payment_method']           = $po->ghapiPaymentMethod();
        $order['order_due_amount']         = $dueAmount;
        $order['delivery_method']          = $po->getApiDeliveryMethod();
        $order['shipment_tracking_number'] = $po->getShipmentTrackingNumber();
        $order['pos_id']                   = $po->getExternalId();
        $order['external_order_id']	  = $po->getExternalOrderId();
        $order['order_currency']           = $po->getStore()->getCurrentCurrencyCode();
        $order['order_email']              = $this->getApiOrderEmail($po->getIncrementId());
        $order['customer_id']              = $po->getCustomerId();

        if ($showCustomerEmail) {
            $order['customer_email'] = $po->getCustomerEmail();
            if (empty($order['order_email'])) {
                $order['order_email'] = $po->getCustomerEmail();
            }
        }
            

        $order['invoice_data']['invoice_required'] = $po->needInvoice();
        if ($order['invoice_data']['invoice_required']) {
            /** @var Zolago_Sales_Model_Order_Address $ba */
            $ba = $po->getBillingAddress();
            $order['invoice_data']['invoice_address']['invoice_first_name']   = $ba->getFirstname();
            $order['invoice_data']['invoice_address']['invoice_last_name']    = $ba->getLastname();
            $order['invoice_data']['invoice_address']['invoice_company_name'] = $ba->getCompany();
            $order['invoice_data']['invoice_address']['invoice_street']       = $ba->getStreet()[0];
            $order['invoice_data']['invoice_address']['invoice_city']         = $ba->getCity();
            $order['invoice_data']['invoice_address']['invoice_zip_code']     = $ba->getPostcode();
            $order['invoice_data']['invoice_address']['invoice_country']      = $ba->getCountryId();
            $order['invoice_data']['invoice_address']['invoice_tax_id']       = $ba->getVatId();
//                $order['invoice_data']['invoice_address']['phone']                = $ba->getTelephone(); // No telephone?
        }

        $order['delivery_data']['inpost_locker_id']                          = $po->getDeliveryInpostLocker()->getName();
        $order['delivery_data']['delivery_point_name']                       = $po->getApiDeliveryPointName();
        $sa = $po->getShippingAddress();
        $order['delivery_data']['delivery_address']['delivery_first_name']   = $sa->getFirstname();
        $order['delivery_data']['delivery_address']['delivery_last_name']    = $sa->getLastname();
        $order['delivery_data']['delivery_address']['delivery_company_name'] = $sa->getCompany();
        $order['delivery_data']['delivery_address']['delivery_street']       = $sa->getStreet()[0];
        $order['delivery_data']['delivery_address']['delivery_city']         = $sa->getCity();
        $order['delivery_data']['delivery_address']['delivery_zip_code']     = $sa->getPostcode();
        $order['delivery_data']['delivery_address']['delivery_country']      = $sa->getCountryId();
        $order['delivery_data']['delivery_address']['phone']                 = $sa->getTelephone();
        return $order;
    }
    /**
     * prepare array with po data
     */
    protected function _poToArray($po,$vendor,$showCustomerEmail = false,$rmaId = null) {
        $order = $this->_getPoDataArray($po,$vendor,$showCustomerEmail);
        if ($rmaId) {
            $order['order_id'] = $rmaId;
            $orderItems = array();
        } else {
            $orderItems = $this->_getPoItems($po);
        }
        $deliveryItem = $this->_getDeliveryItem($po);
        $orderItems[] = $deliveryItem;
        $order['order_items'] = $orderItems;
        return $order;
    }
    
    /**
     * check if rma should be added to api
     * @return bool
     */
    public function isRmaInApi() {
        return Mage::getStoreConfig('ghapi_options/ghapi_messages/ghapi_add_rma');
    }
    /**
     * get list of order details
     * @param
     * @return
     */
    public function ghapiGetOrdersByIncrementIds($ids, $vendor, $showCustomerEmail = FALSE) {

        if (is_numeric($ids)) $ids = array($ids);
        if (!is_array($ids)) return array();
        if (!$vendor->getId()) return array();
        $search = array();
        $rmaIds = array();
        // rma
        if ($this->isRmaInApi()) {
            $prefixLen = strlen(self::GHAPI_RMA_PREFIX);
            $rmaSearch = array();
            foreach ($ids as $key => $id) {
                if (!strncmp($id,self::GHAPI_RMA_PREFIX,$prefixLen)) {                    
                    $rmaSearch[] = substr($id,$prefixLen);
                    unset($ids[$key]);
                }
            }
            if ($rmaSearch) {
                $coll = Mage::getResourceModel('urma/rma_collection');
                $coll->addFieldToFilter('udropship_vendor',$vendor->getId());
                $coll->addFieldToFilter('increment_id',$rmaSearch);
                foreach ($coll as $item) {
                    $rmaIds[$item->getUdpoIncrementId()] = self::GHAPI_RMA_PREFIX.$item->getIncrementId();                    
                }
            }
            
        }
        $search += $ids + array_keys($rmaIds);
        $search = array_unique($search);        
        /** @var Zolago_Po_Model_Resource_Po_Collection $coll */
        $coll = Mage::getResourceModel('zolagopo/po_collection');
        $coll->addFieldToFilter('udropship_vendor', $vendor->getId());
        $coll->addFieldToFilter('increment_id', $search);
        $coll->addPosData("external_id");
        $list = array();
        /** @var Zolago_Po_Model_Po $po */
        foreach ($coll as $po) {
            // from rma
            $poId = $po->getIncrementId();
            if (isset($rmaIds[$poId])) {
                $list[] = $this->_poToArray($po,$vendor,$showCustomerEmail,$rmaIds[$poId]);
            }
            // standard query
            if (in_array($poId,$ids)) { 
                $list[] = $this->_poToArray($po,$vendor,$showCustomerEmail);                
            }
        }
        return $list;
    }

}