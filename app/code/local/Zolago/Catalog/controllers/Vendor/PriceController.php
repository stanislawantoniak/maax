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
		
		foreach(array("converter_price_type", "converter_msrp_type", "price_margin") as $key){
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
			
			$priceCollection = $this->_prepareCollection();
			if($global && is_array($query)){
				foreach($this->_getRestQuery($query) as $key=>$value){
					$priceCollection->addAttributeToFilter($key, $value, "left");
				}
			}elseif($productsIds){
				$priceCollection->addIdFilter($productsIds);
			}else{
				// empty collection if no result found
				$priceCollection->addIdFilter(-1);
			}

            // Skip products if in valid campaigns sale or promo
            /** @var Zolago_Catalog_Model_Product $collection */
            $prodCollection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('entity_id', array('in' => $priceCollection->getAllIds()))
                ->addAttributeToSelect(array('skuv','name','campaign_regular_id'));

            $allValidIds = array();
            $productsInCampaignsData = array();

            foreach ($prodCollection as $prod) {
                /** @var Zolago_Catalog_Model_Product $prod */
                $cid = $prod->getData('campaign_regular_id');
                if ($cid) {
                    $productsInCampaignsData[] = array(
                        'entity_id' => (int)$prod->getId(),
                        'name'      => $prod->getName(),
                        'skuv'      => $prod->getSkuv()
                    );
                } else {
                    // This mean product is in campaign with types:
                    // Zolago_Campaign_Model_Campaign_Type::TYPE_SALE
                    // Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
                    // For TYPE_INFO @see campaign_info_id attribute
                    $allValidIds[] = (int)$prod->getId();
                }
            }

			if($allValidIds && $attributeData){
				$this->_processAttributresSave($allValidIds, $attributeData, $storeId, array());
			}
			
			// Prepare response data
			$data = array(
				"status"	=> 1,
				"content"	=> array(
					"changed_ids" => $priceCollection->getAllIds(),
					"changes"	  => $attributeData,
					"global"	  => (int)$global,
					"time"		  => microtime(true)-$time,
					"skipped"     => count($productsInCampaignsData) ? 1 : 0,
                    "skipped_msg" => $this->getShippedMessage($productsInCampaignsData)
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

    /**
     * ex. $productsInCampaignsData: array[] = array(
     *    'entity_id' => (int)$prod->getId(),
     *    'name'      => $prod->getName(),
     *    'skuv'      => $prod->getSkuv() );
     *
     * @param $productsInCampaignsData
     * @param int $maxShow
     * @return string
     */
    private function getShippedMessage($productsInCampaignsData, $maxShow = 10) {
        /** @var Zolago_Catalog_Helper_Data $hlp */
        $hlp = Mage::helper("zolagocatalog");
        $skippedProducts = '';

        $i = 0;
        foreach ($productsInCampaignsData as $prod) {
            if ($i >= $maxShow) {
                break;
            }
            $skippedProducts .= $prod['name'] . ' (SKU: ' . $prod['skuv'] . '), ';
            $i++;
        }
        $skippedProducts = rtrim($skippedProducts, ', ');
        if ($i >= $maxShow) {
            $skippedProducts .= ' ...';
        }

        return $hlp->__("Products skipped because they are in the campaign:<br/>%s", $skippedProducts);
    }
}



