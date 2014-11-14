<?php
class Zolago_Catalog_Vendor_PriceController extends Zolago_Catalog_Controller_Vendor_Price_Abstract
{
	/**
	 * Grid action
	 */
	public function indexAction() {
		$this->_renderPage(null, 'udprod_price');
	}
	
	
	/**
	 * Handle additional get action
	 */
	public function getAction() {
		$productId = $this->getRequest()->getParam("entity_id");
		$this->_handleRestGet($productId);
	}
	
	/**
	 * Handle mass
	 */
	public function massAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Handle mass save
	 */
	public function massSaveAction() {
		
		$time = microtime(true);
		
		$response = $this->getResponse();
		$request = $this->getRequest();
		
		$storeId = $this->_getStoreId();
		$global = $request->getParam("global");
		$productsIds = explode(",", $request->getParam("selected", ""));
		$query = Mage::helper("core")->jsonDecode(base64_decode($request->getParam("encoded_query")));
		$attributeData = array();
		
		foreach(array("converter_price_type", "price_margin") as $key){
			$value = $request->getParam($key);
			if(!is_null($value)){
				$attributeData[$key] = $value;
			}
		}
		
		// Parse commma
		if(isset($attributeData['price_margin'])){
			$attributeData['price_margin'] = $this->_formatNumber($attributeData['price_margin']);
		}
		
		try{
			
			$collection = $this->_prepareCollection();
			if($global && is_array($query)){
				foreach($this->_getRestQuery($query) as $key=>$value){
					$collection->addAttributeToFilter($key, $value);
				}
			}elseif($productsIds){
				$collection->addIdFilter($productsIds);
			}else{
				// empty collection if no result found
				$collection->addIdFilter(-1);
			}
			$allIds = $collection->getAllIds();
			$allIds = array_map(function($item){return (int)$item;}, $allIds);
			
			if($allIds && $attributeData){
				$this->_processAttributresSave($allIds, $attributeData, $storeId);
			}
			
			// Prepare response data
			$data = array(
				"status"	=> 1,
				"content"	=> array(
					"changed_ids" => $allIds,
					"changes"	  => $attributeData,
					"global"	  => (int)$global,
					"time"		  => microtime(true)-$time
				)
			);
			
			$response->setBody(Mage::helper('core')->jsonEncode($data));
		} catch (Exception $ex) {
			$response->setHttpResponseCode(500);
			$response->setBody($ex->getMessage());
		}
		$this->_prepareRestResponse();
	}
	
	/**
	 * Handle rest put action
	 */
	protected function _handleRestPut() {
		
		$reposnse = $this->getResponse();
		$data = Mage::helper("core")->jsonDecode(($this->getRequest()->getRawBody()));
				
		try{
			$productIds = $data['entity_id'];
			$attributeChanged = $data['changed'];
			$attributeData = array();
			$storeId = $data['store_id'];

			foreach($attributeChanged as $attribute){
				if(isset($data[$attribute])){
					$attributeData[$attribute] = $data[$attribute];
				}
			}
			if($attributeData){
				$this->_processAttributresSave(array($productIds), $attributeData, $storeId);
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

		if($productId){
			$collection->addIdFilter($productId);
		}

		// Make filters
		foreach($this->_getRestQuery() as $key=>$value){
			$collection->addAttributeToFilter($key, $value);
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
	 * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
	 */
	protected function _getCollection() {
		if(!$this->_collection){
			// Add extra fields
			$collection = $this->_prepareCollection();
			$collection->addAttributes();
			$collection->joinAdditionalData();
			$this->_collection = $collection;
			
		}
		return $this->_collection;
	}
	
}



