<?php
class GH_Api_Helper_Data extends Mage_Core_Helper_Abstract {

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
	 * @param $vendorId
	 * @return array
	 */
	public function preparePriceBatch($data, $vendorId)
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
			$sku = $vendorId . "-" . $product['sku'];

			foreach ($product['pricesTypesList'] as $type) {
				if (isset($type['priceType'])){
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
	 * @param $vendorId
	 * @return array
	 */
	public function prepareStockBatch($data, $vendorId)
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
			$sku = $vendorId . "-" . $product['sku'];

			foreach ($product['posesList'] as $pos) {
				if (isset($pos['id'])){
					$pos = array($pos);
				}
				foreach ($pos as $item) {
					$batch[$vendorId][$sku][$item['id']] = $item['qty'];
				}
			}
		}

		return $batch;
	}
	/**
	 * Validate skus for vendor
	 *
	 * Throw exception if
	 * product is not connected to vendor
	 * product don't exist
	 *
	 * @param $data
	 * @param $vendorId
	 * @return bool
	 * @throws Mage_Core_Exception
	 */
	public function validateSkus($data, $vendorId) {
		$inputSkus = array();
		foreach ($data as $sku => $item) {
			$inputSkus[$sku] = $sku;
		}

		/* @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
		$coll = Mage::getResourceModel('zolagocatalog/product_collection');
		$coll->addFieldToFilter('sku', array( 'in' => $inputSkus));
		$coll->addAttributeToSelect('udropship_vendor', 'left');
		$coll->addAttributeToSelect('skuv', 'left');

		Mage::log((string)$coll->getSelect(), null, 'wojcik_sql.log');

		$_data = $coll->getData();
		$allSkusFromColl = array();
		$invalidOwnerSkus = array();

		Mage::log($inputSkus, null, 'wojcik2.log');
		Mage::log($_data, null, 'wojcik2.log');

		// wrong owner
		foreach ($_data as $product) {
			$allSkusFromColl[$product['sku']] = $product['sku'];
			Mage::log($product['udropship_vendor'] . " ? " . $vendorId, null, 'wojcik_test.log');

			if ($product['udropship_vendor'] != $vendorId) {
				Mage::log("is invalid", null, 'wojcik_test.log');
				$invalidOwnerSkus[$product['sku']] = $product['sku'];
			}
		}

		// not existing products
		$notExistingSkus = array_diff($inputSkus, $allSkusFromColl);
		Mage::log("$inputSkus", null, 'wojcik4.log');
		Mage::log($inputSkus, null, 'wojcik4.log');
		
		Mage::log("$allSkusFromColl", null, 'wojcik4.log');
		Mage::log($allSkusFromColl, null, 'wojcik4.log');
		
		Mage::log("array diff", null, 'wojcik4.log');
		Mage::log($notExistingSkus, null, 'wojcik4.log');

		$allErrorsSkus = array_merge($invalidOwnerSkus, $notExistingSkus);
		// get skuv from sku
		foreach ($allErrorsSkus as $key => $sku) {
			$allErrorsSkus[$key] = $this->getSkuvFromSku($sku, $vendorId);
			$allErrorsSkus = array_unique($allErrorsSkus);
		}
		if (!empty($allErrorsSkus)) {
			Mage::throwException('error_invalid_update_products_sku' . ' (' . implode(',', $allErrorsSkus) . ')');
		}

		return true;
	}

	public function getSkuvFromSku($sku, $vendorId) {
		return preg_replace('/' . preg_quote($vendorId . '-', '/') . '/', '', $sku, 1);
	}

	public function validatePrices($data, $vendorId) {
		$errorsSkus = array();
		foreach ($data as $sku => $item) {
			foreach ($item as $type => $price) {
				if ($price <= 0) {
					$errorsSkus[] = $sku;
				}
			}
		}
		foreach ($errorsSkus as $key => $sku) {
			$errorsSkus[$key] = $this->getSkuvFromSku($sku, $vendorId);
			$errorsSkus = array_unique($errorsSkus);
		}
		if (!empty($errorsSkus)) {
			Mage::throwException("error_invalid_update_products_price (". implode(',', $errorsSkus) . ')');
		}
		return true;
	}

	public function validateQtys($data, $vendorId) {
		$errorsSkus = array();
		foreach ($data as $sku => $pos) {
			foreach ($pos as $id => $qty) {
				if (!is_numeric($qty)) {
					$errorsSkus[] = $sku;
				}
			}
		}
		foreach ($errorsSkus as $key => $sku) {
			$errorsSkus[$key] = $this->getSkuvFromSku($sku, $vendorId);
			$errorsSkus = array_unique($errorsSkus);
		}
		if (!empty($errorsSkus)) {
			Mage::throwException("error_invalid_update_products_qty (". implode(',', $errorsSkus) . ')');
		}
		return true;
	}

	public function validatePoses($data, $vendorId) {
		/** @var Zolago_Pos_Helper_Data $helper */
		$helper = Mage::helper('zolagopos');
		$errorsSkus = array();
		foreach ($data as $sku => $pos) {
			foreach ($pos as $id => $qty) {
				if (!$helper->isValidForVendor($id, $vendorId)) {
					$errorsSkus[] = $sku . "[POS:{$id}]";
				}
			}
		}
		foreach ($errorsSkus as $key => $sku) {
			$errorsSkus[$key] = $this->getSkuvFromSku($sku, $vendorId);
			$errorsSkus = array_unique($errorsSkus);
		}
		if (!empty($errorsSkus)) {
			Mage::throwException("error_invalid_update_products_pos_id (". implode(',', $errorsSkus) . ')');
		}
		return true;
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

}