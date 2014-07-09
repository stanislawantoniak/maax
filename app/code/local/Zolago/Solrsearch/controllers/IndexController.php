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
		//Redirect to Url set for the search term
		$query = Mage::helper('catalogsearch')->getQuery();
		$query->setStoreId(Mage::app()->getStore()->getId());
		if ($query->getQueryText() != '') {
			if (Mage::helper('catalogsearch')->isMinQueryLength()) {
				$query->setId(0)
				->setIsActive(1)
				->setIsProcessed(1);
			}
			else {
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
		
		// Set root category if in the vendor context
		$vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
			$vendor->rootCategory();
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
    	Mage::getSingleton('core/session')->setSolrFilterQuery($filterQuery);

    	$this->loadLayout();

    	$solrModel = Mage::getModel('solrsearch/solr');

    	$solrData = $solrModel->query($queryText);
		
    	Mage::register('solrbridge_loaded_solr', $solrModel);


		if( isset($solrData['responseHeader']['params']['q']) && !empty($solrData['responseHeader']['params']['q']) ) {
        	if ($queryText != $solrData['responseHeader']['params']['q']) {
        		$queryText = $solrData['responseHeader']['params']['q'];

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

    	$params = $this->getRequest()->getParams();

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