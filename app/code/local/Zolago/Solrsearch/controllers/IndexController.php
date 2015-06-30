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
        Mage::register('is_search_mode', true);

        $params = $this->getRequest()->getParams();
        $params['q'] = strtolower(Mage::helper('solrsearch')->getParam('q'));
        $this->getRequest()->setParam('q', $params['q']);
        if (isset($params['Szukaj_x'])) unset($params['Szukaj_x']);
        if (isset($params['Szukaj_y'])) unset($params['Szukaj_y']);

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        // If "Everywhere" or specific category are selected
        // redirect to global context from vendor context
        if ($vendor && $vendor->getId()) {
            if(isset($params['scat']) && $params['scat'] == '0') {

                $_params['_query'] = $params;
                $_params["_no_vendor"] = true;
                $this->_redirect('search', $_params);
                return;
            }
        }

        // Checking scat
        // Should always to be
        // but if not, set to default
        if(isset($params['scat'])) {

            if($params['scat'] == '0') {
                unset($params['scat']);
            } elseif(!intval($params['scat'])) { //intval return int or 0 on failure
                unset($params['scat']);
            } else {
                $params['scat'] = intval($params['scat']);
            }
        }
        if(!isset($params['scat'])) {
            if($vendor && $vendor->getId()){
                /** @var Zolago_DropshipMicrosite_Helper_Data $helperZDM */
                $helperZDM = Mage::helper("zolagodropshipmicrosite");
                $vendorRootCategoryId = $helperZDM->getVendorRootCategoryObject()->getId();
                $params['scat'] = $vendorRootCategoryId;
            } else {
                $params['scat'] = Mage::app()->getStore()->getRootCategoryId();
            }
        }

        $searchCategory = Mage::getModel('catalog/category')->load($params['scat']);
        Mage::register('current_category', $searchCategory);

        // Reset sessions
        Mage::getSingleton('core/session')->setSolrFilterQuery(array());

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

                if ($query->getRedirect()) {
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

        // Use current vendor
        if ($vendor && $vendor->getId()) {
            $filterQuery['udropship_vendor'] = urlencode($vendor->getId());
            $vendor->rootCategory(); // set root vendor category as current
        }

        Mage::getSingleton('core/session')->setSolrFilterQuery($filterQuery);

        /** @var Zolago_Solrsearch_Model_Solr $solrModel */
        $solrModel = Mage::getModel('solrsearch/solr');
        $solrData = $solrModel->queryRegister($queryText);
        Mage::register('solrbridge_loaded_solr', $solrModel);
        
        $solrData = Mage::helper('zolagosolrsearch')->makeFallback($solrData,$queryText);
        if( isset($solrData['responseHeader']['params']['q']) && !empty($solrData['responseHeader']['params']['q']) ) {

            if ($queryText != $solrData['responseHeader']['params']['q']) {
                $queryText = $solrData['responseHeader']['params']['q']; //poniewaz moze byc ulepszone np: baleron zamieni na baleriny
                //Redirect to Url set for the search term
                /** @var Mage_CatalogSearch_Model_Query $query */
                $query = Mage::helper('catalogsearch')->getQuery();
                $query->setStoreId(Mage::app()->getStore()->getId());
                $query = $query->loadByQuery($queryText);
                if ($query->getQueryText() != '') {
                    if ($query->getRedirect()) {
                        $this->getResponse()->setRedirect($query->getRedirect());
                        return;
                    }
                }
            }
        }


        $this->loadLayout();
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