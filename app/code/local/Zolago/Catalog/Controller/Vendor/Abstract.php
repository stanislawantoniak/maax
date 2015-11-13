<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
abstract class Zolago_Catalog_Controller_Vendor_Abstract 
	extends Zolago_Dropship_Controller_Vendor_Abstract
{
	
	const NULL_VALUE = "-";
	
	protected $_collection;
	
	/**
	 * Handle whole JOSN API
	 */
	public function restAction() {
		switch ($this->getRequest()->getMethod()) {
			case "GET":
				$productId = null;
				if(preg_match("/\/([0-9]+)$/", $this->getRequest()->getPathInfo(), $matches)){
					$productId = $matches[1];
				}
				$this->_handleRestGet($productId);
			break;
			case "PUT":
				$this->_handleRestPut();
			break;
		}
		
	}
	
	/**
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	protected function _getCollection() {
		if(!$this->_collection){
			// Add extra fields
			$this->_collection = $this->_prepareCollection();
			
		}
		return $this->_collection;
	}
	
	/**
	 * Prepare colection
	 * @param Varien_Data_Collection $collection
	 * @return Varien_Data_Collection
	 */
	protected  function _prepareCollection(Varien_Data_Collection $collection=null){
		return $collection;
	}
	

	
	/**
	 * @param array $inParams
	 * @return array
	 */
	protected function _getRestQuery(array $inParams = array()) {
		if(empty($inParams)){
			$inParams = $this->getRequest()->getQuery();
		}
		$params = array();
		foreach($this->_getAvailableQueryParams() as $key){
			if(isset($inParams[$key])){
				$value = $inParams[$key];
				if(is_string($value) && trim($value)==""){
					continue;
				}elseif(is_array($value) && !$value){
					continue;
				}
				$processedParam = $this->_getSqlCondition($key, $value);
				if(!is_null($processedParam)){
					$params[$key] = $processedParam;
				}
			}
		}
		return $params;
	}
	
	protected function _handleRestPut() {
		
		$reposnse = $this->getResponse();
		$data = Mage::helper("core")->jsonDecode(($this->getRequest()->getRawBody()));

		try{
			$productId = $data['entity_id'];
			$attributeChanged = $data['changed'];
			$attributeData = array();
			$storeId = in_array('status', $attributeChanged) ? Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID : $data['store_id'];

			foreach($attributeChanged as $attribute){
				if(isset($data[$attribute])){
					$attributeData[$attribute] = $data[$attribute];
				}
			}
			if($attributeData){
                /** @var Zolago_Catalog_Model_Product $product */
                $product = Mage::getModel("zolagocatalog/product")->load($productId);
                if (in_array('status', $attributeChanged) && !$product->getIsProductCanBeEnabled() && $data['status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    $helper = Mage::helper("zolagocatalog");
                    $data['status']  = $product->getStatus();

                    $descAccepted = $product->getData('description_status') == Zolago_Catalog_Model_Product_Source_Description::DESCRIPTION_ACCEPTED;
                    $isValidPrice = $product->getFinalPrice() > 0 ? true : false;
                    if (!$descAccepted) {
                        $data['message']['text'] = $helper->__("Product %s can not change status to enabled because don't have accepted description.", $product->getName());
                    } elseif (!$isValidPrice) {
                        $data['message']['text'] = $helper->__("Product %s can not change status to enabled because don't have valid price.", $product->getName());
                    } else {
                        $data['message']['text'] = $helper->__("Product %s can not change status to enabled.", $product->getName());
                    }
                    $data['message']['type']    = 'warning';
                } else {
                    $this->_processAttributresSave(array($productId), $attributeData, $storeId, $data);

                    if (in_array('status', $attributeChanged) || in_array('politics', $attributeChanged)) {
                        /** @var Zolago_Turpentine_Helper_Ban $banHelper */
                        $banHelper = Mage::helper( 'turpentine/ban' );
                        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
                        $coll = $banHelper->prepareCollectionForMultiProductBan(array($productId));

                        Mage::dispatchEvent(in_array('status', $attributeChanged) ? "vendor_manual_save_status_after" : "vendor_manual_save_politics_after",
                            array(
                                "products"          => $coll,
                                'product_ids'       => $coll->getAllIds(),
                                'attributes_data'   => $attributeData,
                                'store_id'          => $storeId
                            ));
                    }
                }
            }

		} catch (Mage_Core_Exception $ex) {
			$reposnse->setHttpResponseCode(500);
			$reposnse->setBody($ex->getMessage());
			return;
		} catch (Exception $ex) {
			Mage::logException($ex);
			$reposnse->setHttpResponseCode(500);
			$reposnse->setBody("Some error occured");
			return;
		}

		/** dev tool **/
		$data['changed'] = array();

		$reposnse->setBody(json_encode($data));
		$this->_prepareRestResponse();
	}
	
	/**
	 * handle Get method
	 */
	protected function _handleRestGet($productId=null) {
		$reposnse = $this->getResponse();
		
		$collection = $this->_getCollection();

		if ($productId) {
			$collection->addIdFilter($productId);
		} else {
			// Make filters
			foreach ($this->_getRestQuery() as $key => $value) {
				$collection->addAttributeToFilter($key, $value);
			}
		}
		
		// Make order and limit
		$out = $collection->prepareRestResponse(
				$this->_getRestSort(), 
				$this->_getRestRange()
		);
		
		if($productId && $out['items']){
			$reposnse->
				setBody(Mage::helper("core")->jsonEncode($out['items'][0]));
		}else{
			$reposnse->
				setHeader('Content-Range', 'items ' . $out['start']. '-' . $out['end']. '/' . $out['total'])->
				setBody(Mage::helper("core")->jsonEncode($out['items']));
		}
		$this->_prepareRestResponse();
	}
	
	/**
	 * Prepare headers
	 */
	protected function _prepareRestResponse() {
		$this->getResponse()->setHeader('Content-type', 'application/json');
	}
	
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _getSqlCondition($key, $value) {
		if(is_array($value)){
			
			if(isset($value['to']) && is_numeric($value['to'])){
				$value['to'] = (float)$value['to'];
			}
			if(isset($value['from']) && is_numeric($value['from'])){
				$value['from'] = (float)$value['from'];
			}
			
			if(isset($value['to']) && is_numeric($value['to']) && 
					(!isset($value['from']) || (isset($value['from']) && $value['from']==0))){
				$value = array($value, array("null"=>true));
			}
			
			return $value;
		}
		
		return array("like"=>'%'.$value.'%');
	}
	
	
	
	/**
	 * @return array
	 */
	protected function _getRestRange() {
		$range = $this->getRequest()->getHeader("Range", 
			$this->getRequest()->getHeader("X-Range")
		);
		if($range){
			preg_match('/(\d+)-(\d+)/', $range, $matches);
			$start = $matches[1];
			$end = $matches[2];
		}else{
			$start = 0;
			$end = 100;
		}
		return array("start"=>$start, "end"=>$end);
	}
	
	/**
	 * 
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		return array();
	}
	
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		return array();
	}
	
	/**
	 * @return array
	 */
	protected function _getRestSort() {
		$query = $this->getRequest()->getServer('QUERY_STRING');
		//sort(-entity_id)
		if(preg_match("/sort\((\-|\+)(\w+)\)/", $query, $matches)){
			if(in_array($matches[2], $this->_getAvailableSortParams())){
				return array(
					"order"=>$matches[2], 
					"dir"=>$matches[1]=="+" ? 
						Varien_Db_Select::SQL_ASC : Varien_Db_Select::SQL_DESC
				);
			}
		}
		return array();
	}

	/**
	 * @param string $number
	 * @return float
	 */
	protected function _formatNumber($number) {
		return (float) str_replace(",", ".", $number);
	}
	
	/**
	 * @param int $productId
	 * @param int $storeId
	 * @return Mage_Catalog_Model_Product
	 * @throws Mage_Core_Exception
	 */
	protected function _getProduct($productId, $storeId) {
		$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($productId);
		/* @var $product Mage_Catalog_Model_Product */
		if($product->getUdropshipVendor()==$this->_getSession()->getVendorId()){
			return $product;
		}
		throw new Mage_Core_Exception("Product not allowed");
	}
	
	/**
	 * @return int
	 */
	protected function _getStoreId() {
		$storeId = $this->getRequest()->getParam("store_id");
		$store = Mage::app()->getStore($storeId);
		
		$allowedStores = $this->getAllowedStores();
		
		foreach($allowedStores as $_store){
			if($_store->getId()==$store->getId()){
				return (int)$store->getId();
			}
		}
		
		throw new Mage_Core_Exception("Unknow store");
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		return Mage::app()->getStore($this->_getStoreId());
	}
	
	/**
	 * @return array
	 */
	public function getAllowedStores() {
		return Mage::helper("zolagodropship")->getAllowedStores($this->getVendor());
	}

	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return int
	 */
	public function getVendorId() {
		return $this->getVendor()->getId();
	}

	/**
	 * @param array $productIds
	 * @param array $attributeData
	 * @param int $storeId
	 * @param array $data
	 * @return Zolago_Catalog_Controller_Vendor_Abstract
	 */
	protected function _processAttributresSave(array $productIds, array $attributeData, $storeId, array $data){
		return $this;
	}

}



