<?php
/**
 * soap methods handler
 */
class GH_Api_Model_Soap extends Mage_Core_Model_Abstract {

    public function __construct() {
        try {
            Mage::register('GHAPI', true);
        } catch(Exception $e) {
            // Key already exist
        }
        return parent::__construct();
    }
    /**
     * message list
     *
     * @param stdClass $changeOrderMessageParameters
     * @return stdClass
     */
    public function getChangeOrderMessage($changeOrderMessageParameters) {
        $request = $changeOrderMessageParameters;
        $token = $request->sessionToken;
        $batchSize = $request->messageBatchSize;
        $messageType = empty($request->messageType)? null:$request->messageType;
        $orderId = empty($request->orderId)? null:$request->orderId;
        $model = $this->getMessageModel();

        try {
            $messages = $model->getMessages($token,$batchSize,$messageType,$orderId);

            $list = array();
            foreach($messages as $msg) {
                $m = new StdClass();
                $m->messageID = $msg['messageID'];
                $m->messageType = $msg['messageType'];
                $m->orderID = $msg['orderID'];
                $list[] = $m;
            }

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $list = array();
            $message = $e->getMessage();
            $status = false;
        }
        $obj = new StdClass();
        $obj->list = $list;
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * confirm messages
     *
     * @param stdClass $setChangeOrderMessageConfirmationParameters
     * @return stdClass
     */
    public function setChangeOrderMessageConfirmation($setChangeOrderMessageConfirmationParameters) {
        $request = $setChangeOrderMessageConfirmationParameters;
        $token = $request->sessionToken;

        try {
            if (!isset($request->messageID->ID)) {
                Mage::throwException('error_message_id_list_empty');
            }
            $messages = $request->messageID->ID;
            if (!is_array($messages)) {
                $messages = array($messages);
            }
            /** @var GH_Api_Model_Message $model */
            $model = $this->getMessageModel();

            $status = $model->confirmMessages($token, $messages);
            $message = 'ok';
        } catch(Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        $obj = new StdClass();
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * login handler
     * @param stdClass $loginParameters
     * @return stdClass
     */
    public function doLogin($loginParameters) {
        $vendorId = $loginParameters->vendorId;
        $password = $loginParameters->password;
        $apiKey = $loginParameters->webApiKey;

        $model = $this->getUserModel();

        $obj = new StdClass();
        try {
            $model->loginUser($vendorId,$password,$apiKey);
            $token = $model->getSession()->getToken();
            $obj->status = true;
            $obj->message = 'ok';
            $obj->token = $token;
        } catch (Exception $ex) {
            $obj->status = false;
            $obj->message = $ex->getMessage();
            $obj->token = '';
        }
        return $obj;
    }


    /**
     * Show PO for given increment id (or ids)
     *
     * @param stdClass $getOrdersByIDRequestParameters
     * @return StdClass
     */
    public function getOrdersByID($getOrdersByIDRequestParameters) {
        $request  = $getOrdersByIDRequestParameters;
        $token    = $request->sessionToken;
        $obj = new StdClass();
        
        try {
            if (!isset($request->orderID->ID)) {
                $this->throwOrderIDListEmpty();
            }
            $orderIds = $request->orderID->ID;
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            /** @var Zolago_Po_Model_Po $model */
            $model    = Mage::getModel('zolagopo/po');
            $user = $this->getUserByToken($token);
            $vendor = Mage::getModel('udropship/vendor')->load($user->getVendorId());
            $allData = $model->ghapiGetOrdersByIncrementIds($orderIds, $vendor);

            // Checking if ids are correct
            $allDataIds = array();
            foreach ($allData as $po) {
                $allDataIds[] = $po['order_id'];
            }
            if (count($orderIds) != count($allData)) {
                $idsCheck = array_diff($orderIds, $allDataIds);
                $this->throwOrderIdWrongError($idsCheck);
            }

            // Collecting orderList
            $poList = array();
            foreach ($allData as $data) {
	            $poList[] = $this->arrayToStdClass($data);
            }
            $obj->orderList = $poList;
            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $obj->orderList = array();
            $message = $e->getMessage();
            $status = false;
        }

        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * Set collected status
     *
     * @param $setOrderAsCollectedRequestParameters
     * @return StdClass
     */
    public function setOrderAsCollected($setOrderAsCollectedRequestParameters) {
        /** @var Zolago_Po_Model_Po $model */
        /** @var Zolago_Po_Helper_Data $hlpPo */
        $request  = $setOrderAsCollectedRequestParameters;
        $token    = $request->sessionToken;

        try {
            if (!isset($request->orderID->ID)) {
                $this->throwOrderIDListEmpty();
            }            
            $orderIds = $request->orderID->ID;
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            $user = $this->getUserByToken($token);
            $vendor = Mage::getModel('udropship/vendor')->load($user->getVendorId());
            $model = Mage::getModel('zolagopo/po');
            $hlpPo = Mage::helper('zolagopo');
            $coll = $model->getVendorPoCollectionByIncrementId($orderIds, $vendor);

            // START Checking if ids are correct
            $checkList = $hlpPo->massCheckIsStartPackingAvailable($coll);
            if (count($checkList)) {
                $this->throwOrderInvalidStatusError($checkList);
            }
            $allDataIds = array();
            foreach ($coll as $po) {
                /** @var Zolago_Po_Model_Po $po */
                $allDataIds[] = $po->getIncrementId();
            }
            if (count($orderIds) != $coll->count()) {
                $idsCheck = array_diff($orderIds, $allDataIds);
                $this->throwOrderIdWrongError($idsCheck);
            }
            // END Checking if ids are correct

            // Finally if no errors start masss processing
            $hlpPo->massProcessStartPacking($coll);

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $message = $e->getMessage();
            $status = false;
        }

        $obj = new StdClass();
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * Set order shipment
     *
     * @param $setOrderShipmentRequestParameters
     * @return StdClass
     */
    public function setOrderShipment($setOrderShipmentRequestParameters) {
        /** @var Zolago_Po_Model_Po $model */
        $request  = $setOrderShipmentRequestParameters;
        $token    = $request->sessionToken;
        $courier  = $request->courier;
        $dateShipped = $request->dateShipped;
        $shipmentTrackingNumber = $request->shipmentTrackingNumber;

        try {
            if (!isset($request->orderID)) {
                $this->throwOrderIDListEmpty();
            }
            $user = $this->getUserByToken($token);
            $orderId  = $request->orderID;
            $model = Mage::getModel('zolagopo/po');
            $po = $model->load($orderId, 'increment_id');
            if (!$this->getHelper()->validateDate($dateShipped)) {
                Mage::throwException('error_wrong_datetime_format');
            }
            if ($user->getVendorId() != $po->getUdropshipVendor()) {
                $this->throwOrderIdWrongError();
            }
            if(!$model->getStatusModel()->isShippingAvailable($po)) {
                $this->throwOrderInvalidStatusError(array($orderId));
            }
            $courierCode = $this->getCourierCode($courier);

            /** @var Zolago_Po_Helper_Shipment $manager */
            $manager = Mage::helper('zolagopo/shipment');
            $manager->setNumber($shipmentTrackingNumber);
            $manager->setCarrierData($courierCode,$courier);
            $manager->setUdpo($po);                        
            // save shippedData
            if ($dateShipped) {
                $track = $manager->getTrack();
                $track->setShippedDate($dateShipped);
            }

            $manager->processSaveTracking();
            $manager->invoiceShipment();
            
            // aggregated
            $collection = Mage::getResourceModel('zolagopo/po_collection');
                /* @var $collection Zolago_Po_Model_Resource_Po_Collection */
            $collection->addFieldToFilter("entity_id", $po->getId());

            $aggregated = Mage::helper('zolagopo')->createAggregated($collection, $manager->getVendor());

            $aggregated->confirm();
            
            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $message = $e->getMessage();
            $status = false;
        }

        $obj = new StdClass();
        $obj->message = $message;
        $obj->status = $status;
        return $obj;

    }

	public function setOrderReservation($setOrderReservationRequest) {
		$request = $setOrderReservationRequest;
		$token = $request->sessionToken;

		try {
			$user = $this->getUserByToken($token);

			/** @var Zolago_Po_Model_Po $po */
			$po = Mage::getModel('zolagopo/po')->load($request->orderID, 'increment_id');

			if ($user->getVendorId() != $po->getUdropshipVendor()) {
				$this->throwOrderIdWrongError();
			}

			$po->ghApiSetOrderReservation($request->reservationStatus, $request->reservationMessage);

			$message = 'ok';
			$status = true;
		} catch(Exception $e) {
			$message = $e->getMessage();
			$status = false;
		}

		$obj = new StdClass();
		$obj->message = $message;
		$obj->status = $status;
		return $obj;
	}

    /**
     * Method to export all attributes sets (with create products = yes)
     * Export id and name
     *
     * @param $getCategoriesParameters
     * @return StdClass
     */
    public function getCategories($getCategoriesParameters) {
        $request = $getCategoriesParameters;
        $token = $request->sessionToken;
        $obj = new StdClass();

        try {

            $user = $this->getUserByToken($token);
            $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection');
            $attributeSetCollection->addFilter('use_to_create_product', 1);
            $attributeSetCollection->load();

            $list = array();
            foreach ($attributeSetCollection as $id => $item) {
                $m = new StdClass();
                $m->categoryID   = $id;
                $m->categoryName = $item->getAttributeSetName();
                $list[] = $m;
            }

            $obj->categories = $list;
            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $message = $e->getMessage();
            $status = false;
        }

        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }
	
	public function updateProductsPricesStocks($request) {
		$token = $request->sessionToken;
		$priceData = $request->productsPricesUpdateList;
		$stockData = $request->productsStocksUpdateList;
		$obj = new StdClass();
		$message = 'ok';
		$status = true;

		$notValidSkus        = array();
		$notValidSkusByPrice = array();
		$notValidSkusByPoses = array();
		$notValidSkusByQtys  = array();

		try {
			$user = $this->getUserByToken($token); // Do loginBySessionToken
			$vendor = $user->getVendor();
			$vendorId = $vendor->getId();
			$externalId = $vendor->getExternalId();

			// Check if is sth to prepare
			if (empty($priceData) && empty($stockData)) Mage::throwException('error_empty_product_update_list');

			// Prepare data - from SKUV to SKU
			$priceBatch = $this->getHelper()->preparePriceBatch($priceData, $externalId);
			$stockBatch = $this->getHelper()->prepareStockBatch($stockData, $externalId);

			$notValidSkus = $this->getHelper()->getNotValidSkus(array_merge($priceBatch, isset($stockBatch[$vendorId]) ? $stockBatch[$vendorId] : array()), $vendorId);

			if (!empty($priceBatch)) {
				$notValidSkusByPrice = $this->getHelper()->getNotValidSkusByPrices($priceBatch, $externalId);
			}
			if (!empty($stockBatch)) {
				$notValidSkusByPoses = $this->getHelper()->getNotValidSkusByPoses($stockBatch[$vendorId], $externalId, $vendorId);
				$notValidSkusByQtys = $this->getHelper()->getNotValidSkusByQtys($stockBatch[$vendorId], $externalId);
			}
			
			// Remove invalid skus from batch price & stock
			foreach ($notValidSkus as $sku => $msg) {
				unset($priceBatch[$sku]);
				unset($stockBatch[$externalId][$sku]);
			}
			// custom for price
			if (!empty($priceBatch)) {
				foreach ($notValidSkusByPrice as $sku => $msg) {
					unset($priceBatch[$sku]);
				}
			}
			// custom for stock
			if (!empty($stockBatch)) {
				foreach ($notValidSkusByPoses as $sku => $msg) {
					unset($stockBatch[$externalId][$sku]);
				}
				foreach ($notValidSkusByQtys as $sku => $msg) {
					unset($stockBatch[$externalId][$sku]);
				}
			}

			// update it
			/** @var Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 $restApi */
			$restApi = Mage::getModel('zolagocatalog/api2_restapi_rest_admin_v1');
			if (!empty($priceBatch)) {
				$restApi::updatePricesConverter($priceBatch);
			}
			if (!empty($stockBatch[$externalId])) {
				$restApi::updateStockConverter($stockBatch);
			}

			// If any error occurs make error msg about incorrect products
			if (!empty($notValidSkus) || !empty($notValidSkusByPrice) || !empty($notValidSkusByPoses) || !empty($notValidSkusByQtys)) {
				$allNotValid[] = implode(',', $notValidSkus);
				$allNotValid[] = implode(',', $notValidSkusByPrice);
				$allNotValid[] = implode(',', $notValidSkusByPoses);
				$allNotValid[] = implode(',', $notValidSkusByQtys);
				Mage::throwException("error_invalid_update_products (" . implode(',', array_filter($allNotValid)) . ')');
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
			$status = false;
		}
		
		$obj->message = $message;
		$obj->status = $status;
		return $obj;
	}

    /**
     * @return GH_Api_Model_Message
     */
    protected function getMessageModel() {
        return Mage::getModel('ghapi/message');
    }

    /**
     * @return GH_Api_Model_User
     */
    protected function getUserModel() {
        return Mage::getModel('ghapi/user');
    }

    /**
     * @param string $token
     * @return GH_Api_Model_User
     */
    protected function getUserByToken($token) {
        return $this->getHelper()->getUserByToken($token);
    }

    /**
     * Gets main GH Api helper
     * @return GH_Api_Helper_Data
     */
    protected function getHelper() {
        return Mage::helper('ghapi');
    }

    /**
     * @param array $ids
     * @throws Mage_Core_Exception
     */
    protected function throwOrderIdWrongError(array $ids = array()) {
        $ids = count($ids) ? ' ('.implode(',',$ids).')' : '';
        Mage::throwException('error_order_id_wrong'.$ids);
    }

    /**
     * @param array $ids
     * @throws Mage_Core_Exception
     */
    protected function throwOrderInvalidStatusError(array $ids = array()) {
        $ids = count($ids) ? ' ('.implode(',',$ids).')' : '';
        Mage::throwException('error_order_invalid_status'.$ids);
    }

    /**
     * @throws Mage_Core_Exception
     */
    protected function throwOrderIDListEmpty() {
        Mage::throwException('error_order_id_list_empty');
    }

    /**
     * @throws Mage_Core_Exception
     */
    protected function throwWrongCourierName($_courier) {
		Mage::log("error_wrong_courier_name: " . $_courier);
        Mage::throwException('error_wrong_courier_name');

    }

    public function getCourierCode($courier) {
        $_courier = strtolower($courier);
        switch ($_courier) {
            case 'dhl':
                return Orba_Shipping_Model_Carrier_Dhl::CODE;
            case 'ups':
                return Orba_Shipping_Model_Carrier_Ups::CODE;
            case 'gls':
                return Orba_Shipping_Model_Carrier_Gls::CODE;
	    case 'dpd':
	    	return Orba_Shipping_Model_Carrier_Dpd::CODE;
        }

        $this->throwWrongCourierName($_courier);
    }

	protected function arrayToStdClass($array) {
		$obj = new StdClass();
		foreach($array as $k=>$v) {
			if(is_array($v) && isset($v[0])) {
				foreach($v as $l=>$b) {
					$v[$l] = $this->arrayToStdClass($b);
				}
				$obj->$k = $v;
			} elseif(is_array($v) && !is_numeric($k)) {
				$v = $this->arrayToStdClass($v);
				$obj->$k = $v;
			} else {
				$obj->$k = @trim($v);
			}
		}
		return $obj;
	}
}