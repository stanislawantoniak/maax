<?php

/**
 * load marketing data
 */
class GH_Marketing_LoadController extends Mage_Core_Controller_Front_Action
{

	/**
	 * index
	 */
	public function indexAction()
	{
		try {
			if(!$this->getRequest()->isPost()) {
				Mage::throwException("Data should be send using POST");
			}

			$data = $this->getRequest()->getPost('data');
			$token = $this->getRequest()->getPost('token');

			$configToken = Mage::getStoreConfig('zolagoconverter/marketing/token');
			$configIp = Mage::getStoreConfig('zolagoconverter/marketing/ip');
			$ip = $this->getClientIp();

			if($ip !== $configIp) {
				Mage::throwException("Invalid marketing server's IP address! Should be $configIp but $ip was provided");
			}
			if ($configToken !== $token) {
				Mage::throwException("Invalid marketing server's security token!");
			}
			if(empty($data) || !is_array($data)) {
				Mage::throwException("Marketing server provided empty or invalid data!");
			}

			//array that stores vendors
			$vendors = array();
			//array that stores marketing cost types
			$types = array();

			//array that stores data to insert in db
			$cpcData = array();
			foreach($data as $cpc) {
				$collection = Mage::getModel("zolagocatalog/product")
					->getResourceCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('sku', $cpc['sku']);

				$productId = $collection->getFirstItem()->getData('entity_id');
				if(!$productId) {
					Mage::throwException("Invalid product sku in provided data!");
				}

				$vendorId = explode('-',$cpc['sku'])[0];
				$vendor = isset($vendors[$vendorId]) ? $vendors[$vendorId] : Mage::getModel('zolagodropship/vendor')->load($vendorId);
				if(!$vendor || !$vendor->getId()) {
					Mage::throwException("Invalid vendor id in sku in provided data!");
				} else {
					$vendors[$vendorId] = $vendor;
				}

				//todo: validate type
				if(!isset($types[$cpc['type']])) {
					/** @var GH_Marketing_Model_Marketing_Cost_Type $typeModel */
					$typeModel = Mage::getModel("ghmarketing/marketing_cost_type");
					$type = $typeModel->loadByCode($cpc['type']);
					if($type->getId()) {
						$types[$type->getCode()] = $type;
					} else {
						Mage::throwException("Invalid cost type in provided data!");
					}
				} else {
					$type = $types[$cpc['type']];
				}

				$cpcData[] = array(
					'vendor_id'     => $vendorId,
					'product_id'    => $productId,
					'date'          => $cpc['date'],
					'type_id'       => $type->getId(),
					'cost'          => str_replace(',','.',$cpc['cost']),
					'click_count'   => $cpc['click_count'],
					'billing_cost'  => round(($cpc['cost'] + ($cpc['cost'] * ($vendor->getCpcCommission()/100))),2,PHP_ROUND_HALF_UP)
				);
			}

			/** @var Gh_Marketing_Model_Marketing_Cost $marketingCostModel */
			$marketingCostModel = Mage::getModel("ghmarketing/marketing_cost");
			/** @var Gh_Marketing_Model_Resource_Marketing_Cost $marketingCostResource */
			$marketingCostResource = $marketingCostModel->getResource();
			$marketingCostResource->appendCosts($cpcData);

			echo 'OK';
		} catch(Mage_Core_Exception $e) {
			Mage::logException($e);
			echo 'ERR';
		}
	}

	protected function getClientIp() {
		Mage::log($_SERVER['REMOTE_ADDR'],null,'client_ip.log');

		$ipaddress = '';
		if ($_SERVER['HTTP_CLIENT_IP'])
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if($_SERVER['HTTP_X_FORWARDED_FOR'])
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if($_SERVER['HTTP_X_FORWARDED'])
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if($_SERVER['HTTP_FORWARDED_FOR'])
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if($_SERVER['HTTP_FORWARDED'])
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if($_SERVER['REMOTE_ADDR'])
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
}