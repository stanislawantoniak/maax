<?php
/**
 *
 * @category    Zolago
 * @package     Zolago_Solrsearch
 */
require_once Mage::getModuleDir('controllers', "SolrBridge_Solrsearch") . DS . "IndexController.php";
class Zolago_Solrsearch_IndexController extends SolrBridge_Solrsearch_IndexController
{
	
	public function indexAction()
	{
		$baseUrl = Mage::helper('zolagodropshipmicrosite')->getBaseUrl();

    	$params = $this->getRequest()->getParams();

		// Set root category if in the vendor context
		$vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor && $vendor->getId()) {
        	
			// If "Everywhere" or specific category are selected
			// redirect to global context from vendor context
			if(isset($params['scat']) && $params['scat'] == '0'){
				
				$this->_redirectUrl($baseUrl . 'search?' . http_build_query($params));
				return $this;
			}
        }
		
		// Reset sessions
		Mage::getSingleton('core/session')->setSolrFilterQuery(array());
		
		// When in the search mode
		// Set current category to param['scat']		
		if(isset($params['scat'])){
        // override root category
        if ($params['scat'] == 0) {
            $params['scat'] = Mage::app()->getStore()->getRootCategoryId();            
        }
		if($params['scat'] != "0" && $params['scat'] != Zolago_Solrsearch_Helper_Data::ZOLAGO_SEARCH_CONTEXT_CURRENT_VENDOR){
				
				if(isset($params['is_search'])){
					Mage::register('is_current_category_context', TRUE);
				}
                $search_category = Mage::getModel('catalog/category')->load($params['scat']);
                Mage::register('current_category', $search_category);
		    }

		}

		//Redirect to Url set for the search term
		$query = Mage::helper('catalogsearch')->getQuery();
		$query->setStoreId(Mage::app()->getStore()->getId());
		if ($query->getQueryText() != '') {
			if (Mage::helper('catalogsearch')->isMinQueryLength()) {
				$query->setId(0)
				->setIsActive(1)
				->setIsProcessed(1);
			}
			else
            {
				if ($query->getId()) {
					$query->setPopularity($query->getPopularity()+1);
				}
				else {
					$query->setPopularity(1);
				}

				if ($query->getRedirect()){
					$query->save();
					$this->getResponse()->setRedirect($query->getRedirect());
					return;
				}
			}
		}
		
		//Redirect to Magento default search if ping solr server failed
	    $queryText = Mage::helper('solrsearch')->getParam('q');

		if (!Mage::helper('solrsearch')->pingSolrServer()) {
			$defaultCatalogSearchUrl = trim(Mage::helper('catalogsearch')->getResultUrl(),'/').'/?'.Mage::helper('catalogsearch')->getQueryParamName().'='.$queryText;
			$this->_redirectUrl($defaultCatalogSearchUrl);
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
			return $this;
		}

        $filterQuery = Mage::getSingleton('core/session')->getSolrFilterQuery();
		
		
		// Use selected category
		if(isset($params['scat'])){
			
			// Use current vendor
			if($params['scat'] == Zolago_Solrsearch_Helper_Data::ZOLAGO_SEARCH_CONTEXT_CURRENT_VENDOR){
				
				if($vendor){
					$filterQuery['udropship_vendor'] = urlencode($vendor->getVendorName());
				}
				
			}
			
			elseif($params['scat'] == '0'){
				if(isset($filterQuery['udropship_vendor'])) unset($filterQuery['udropship_vendor']);
				if(isset($filterQuery['category_id'])) unset($filterQuery['category_id']);
			}
			
		}
    	Mage::getSingleton('core/session')->setSolrFilterQuery($filterQuery);

    	$this->loadLayout();

    	$solrModel = Mage::getModel('solrsearch/solr');

    	$solrData = $solrModel->queryRegister($queryText);

//        var_dump($solrData);

    	Mage::register('solrbridge_loaded_solr', $solrModel);

		if( isset($solrData['responseHeader']['params']['q']) && !empty($solrData['responseHeader']['params']['q']) ) {

            Mage::register($solrModel::REGISTER_KEY . "_search_real_q", $solrData['responseHeader']['params']['q']);
            $isNoResult = $solrData['response']['numFound'] ? true : false;
            Mage::register($solrModel::REGISTER_KEY . "_search_is_no_result", $isNoResult);

        	if ($queryText != $solrData['responseHeader']['params']['q']) {
        		$queryText = $solrData['responseHeader']['params']['q']; //poniewaz moze byc ulepszone np: baleron zamieni na baleriny



                //Redirect to Url set for the search term
        		$query = Mage::helper('catalogsearch')->getQuery();
        		$query->setStoreId(Mage::app()->getStore()->getId());
        		$query = $query->loadByQuery($queryText);
        		if ($query->getQueryText() != '') {
                    if ($query->getRedirect()){
        				$this->getResponse()->setRedirect($query->getRedirect());
        				return;
        			}
        		}
        	}
        }

    	if (Mage::helper('solrsearch')->getSetting('allow_multiple_filter') > 0)
    	{
    		$this->saveLayerData($solrData, $queryText);
    	}

		
    	$filterQuery = (array)Mage::getSingleton('core/session')->getSolrFilterQuery();
    	if (isset($params['fq']))
    	{
    		$filterQuery = array($params['fq']);
    	}
    	if (isset($params['clear']) && $params['clear'] == 'yes') $filterQuery = array();
		
    	Mage::getSingleton('core/session')->setSolrFilterQuery($filterQuery);
		
    	$this->renderLayout();
    }

    /**
     * Save facet data in session for multiple selection
     */
    protected function saveLayerData($solrData, $queryText)
    {
    	$key = Mage::helper('solrsearch')->getKeywordCachedKey($queryText);

    	$originalSolrData = Mage::getSingleton('core/session')->getOriginSolrFacetData();

    	if (!isset($originalSolrData) || !isset($originalSolrData[$key])) {
    		$data = array($key => $solrData);

    		Mage::getSingleton('core/session')->setOriginSolrFacetData($data);
    	}
    }
}