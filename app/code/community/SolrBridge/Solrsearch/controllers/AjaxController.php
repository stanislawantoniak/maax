<?php
/**
 * @category SolrBridge
 * @package SolrBridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_AjaxController extends Mage_Core_Controller_Front_Action
{
	protected $ultility = null;

	public function queryAction()
	{
		$solrModel = Mage::getModel('solrsearch/solr');
        $helper = Mage::helper('solrsearch');
		$store = Mage::app()->getStore();
		$storeCurrency = $store->getCurrentCurrencyCode();

		$queryText = $helper->getParam('q');

		$cachedKey = 'solrbridge_solrsearch_autocomplete_keyword_'.$store->getId().'_'.$store->getWebsiteId().'_'.$storeCurrency.'_'.sha1($queryText);

		$priceFieldName = $helper->getPriceFieldName();

		$autocomplete_cache_enable = Mage::app()->useCache('solrbridge_solrsearch');

		if (false !== ($returnData = Mage::app()->getCache()->load($cachedKey)) && intval($autocomplete_cache_enable) > 0 )
		{
			$returnData = unserialize($returnData);
		}
		else
		{
		    $priceFields = $helper->getPriceFields();
			$solrFieldList = array_merge($priceFields, array('name_varchar', 'products_id', 'special_price_decimal', 'url_path_varchar'));
			//Set field list for using on autocomplete
			$solrModel->setFieldList($solrFieldList);

			//Products items limit
			$productLimit = 5;
			$productLimitConf = $helper->getSetting('autocomplete_product_limit');
			if ( isset($productLimitConf) && is_numeric($productLimitConf) && (int)$productLimitConf > 0 ) {
				$productLimit = $productLimitConf;
			}
            //Facet limit
			$facetLimit = 3;
			$facetLimitConf = array($facetLimit);
			$categoryLimitConf = $helper->getSetting('autocomplete_category_limit');
			if (is_numeric($categoryLimitConf)) {
				$facetLimitConf[] = $categoryLimitConf;
			}
			$brandLimitConf = $helper->getSetting('autocomplete_brand_limit');
			if (is_numeric($brandLimitConf)) {
				$facetLimitConf[] = $brandLimitConf;
			}
			$facetLimit = max($facetLimitConf);

			$options = array('rows' => (int)$productLimit, 'facetlimit' => (int)$facetLimit, 'autocomplete' => true);

			$returnData = $solrModel->query($queryText, $options);

			$returnData['keywordssuggestions'] = array();

			$display_keyword_suggestion = (int)$helper->getSetting('display_keyword_suggestion');

			if (!empty($display_keyword_suggestion) && $display_keyword_suggestion > 0) {

				$solrAutocompleteQuery = Mage::getModel('solrsearch/solr_autocomplete');

				$solrcore = Mage::helper('solrsearch')->getSetting('solr_index');

				$autocompleteoptions = array('solrcore' => $solrcore, 'rows' => -1, 'queryText' => $queryText, 'facetlimit' => 5, 'autocomplete' => true);

				$resultAutocomplete = $solrAutocompleteQuery->init($autocompleteoptions)->prepareQueryData()->execute();

				if (isset($resultAutocomplete['facet_counts']['facet_fields']['textSearchStandard']) && is_array($resultAutocomplete['facet_counts']['facet_fields']['textSearchStandard'])) {

				    $allow_ignore_term = (int) $helper->getSetting('allow_ignore_term');

				    if ($allow_ignore_term > 0) {
				        $ignoreSearchTerms = $helper->getSetting('ignoresearchterms');
				        if (!empty($ignoreSearchTerms)) {
				            $ignoreSearchTermsArray = explode(',', trim($ignoreSearchTerms));

				            foreach ($resultAutocomplete['facet_counts']['facet_fields']['textSearchStandard'] as $term => $val)
				            {
				                if (!in_array(strtolower($term), $ignoreSearchTermsArray))
				                {
				                    $returnData['keywordssuggestions'][] = Mage::helper('solrsearch')->hightlight($returnData['responseHeader']['params']['q'], trim($term, ','));
				                    $returnData['keywordsraw'][] = trim($term, ',');
				                }
				            }

				        }else{
				            foreach ($resultAutocomplete['facet_counts']['facet_fields']['textSearchStandard'] as $term => $val)
				            {
				                $returnData['keywordssuggestions'][] = Mage::helper('solrsearch')->hightlight($returnData['responseHeader']['params']['q'], trim($term, ','));
				                $returnData['keywordsraw'][] = trim($term, ',');
				            }
				        }
				    }else{
				        foreach ($resultAutocomplete['facet_counts']['facet_fields']['textSearchStandard'] as $term => $val)
				        {
				            $returnData['keywordssuggestions'][] = Mage::helper('solrsearch')->hightlight($returnData['responseHeader']['params']['q'], trim($term, ','));
				            $returnData['keywordsraw'][] = trim($term, ',');
				        }
				    }
				}

			}

			if (isset($returnData['facet_counts']['facet_fields']['category_facet'])) {
				if (!isset($returnData['facet_counts']['facet_fields']['category_path'])) {
					$returnData['facet_counts']['facet_fields']['category_path'] = $returnData['facet_counts']['facet_fields']['category_facet'];
				}
			}

			if (isset($returnData['response']['numFound']) && intval($returnData['response']['numFound']) > 0){
				Mage::app()->getCache()->save(serialize($returnData), $cachedKey, array('solrbridge_solrsearch'));
			}
		}

		$this->getResponse()->setHeader("Content-Type", "text/javascript", true);

		$jsonp_callback = isset($_GET['json_wrf']) ? $_GET['json_wrf'] : null;


		$timestamp = Mage::helper('solrsearch')->getParam('timestamp');
		if (isset($timestamp)) {
			$returnData['responseHeader']['params']['timestamp'] = $timestamp;
		}

		if (isset($returnData['response']['numFound']) && intval($returnData['response']['numFound']) > 0){
			foreach ($returnData['response']['docs'] as $k=>$document) {
				$price = '&nbsp;';
				if (isset($document[$priceFieldName])) {
					$price = $document[$priceFieldName];
				}
				$returnData['response']['docs'][$k]['price_decimal'] = (is_numeric($price))?number_format($price,2):$price;

				if (isset($returnData['response']['docs'][$k]['products_id']))
				{
					if ($specialPrice = $this->getSpecialPrice($returnData['response']['docs'][$k]['products_id'])) {
						$price = $specialPrice;
					}
				}

				$returnData['response']['docs'][$k]['special_price_decimal'] = (is_numeric($price))?number_format($price,2):$price;
				$returnData['response']['docs'][$k]['name_varchar'] = Mage::helper('solrsearch')->hightlight($returnData['responseHeader']['params']['q'], $returnData['response']['docs'][$k]['name_varchar']);
				$returnData['response']['docs'][$k]['time'] = time();
			}
		}

		echo $jsonp_callback.'('.json_encode($returnData).')';
		exit;
	}

	public function getSpecialPrice($productId)
	{
		$gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		if (empty($gId)) {
			$gId = 0;
		}

		$_product = Mage::getModel('catalog/product')->setCustomerGroupId($gId)->load($productId);

		$price = 0;
		$special = 0;
		$store = Mage::app()->getStore();
		$priceData = Mage::getModel('solrsearch/price')->getProductPrice($_product, $store, $gId);
		if(isset($priceData['special_price']) && $priceData['special_price'] > 0){
			$special = $priceData['special_price'];
		}
		return $special;
	}

	public function categoryAction(){
		$catId = $this->getRequest()->getParam('cat_id');
		$url = Mage::getModel('catalog/category')->load($catId)->getUrl(array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
		$this->_redirectUrl($url);
		$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		return $this;
	}

	public function productAction()
	{
		if ($curency = (string) $this->getRequest()->getParam('currency')) {
			Mage::app()->getStore()->setCurrentCurrencyCode($curency);
		}

		$productId = $this->getRequest()->getParam('productid');
		$_product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($productId);

		$productUrl = $_product->getProductUrl();

		$this->_redirectUrl($productUrl);
		$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		return $this;
	}

	public function fullqueryAction() {

	    $solrQuery = Mage::getModel('solrsearch/solr_autocomplete');

	    $options = array('queryText' => 'digi', 'rows' => 0, 'facetlimit' => 200, 'solrcore' => 'english');

	    $data = $solrQuery->init($options)->prepareQueryData()->execute();

	    print_r($data);
		exit;
	}

	public function viewdocAction()
	{
		$queryUrl = Mage::helper('solrsearch')->getSetting('solr_server_url');
		$solrcore = Mage::helper('solrsearch')->getSetting('solr_index');

		$arguments = array(
				'json.nl' => 'map',
				'wt'=> 'json',
		);
		$queryUrl = trim($queryUrl,'/').'/'.$solrcore;
		$url = trim($queryUrl,'/').'/select/?q=*:*';

		$productId = $this->getRequest()->getParam('productid');
		if(!empty($productId))
		{
			$url .= '&fq=products_id:'.$productId;
		}
		$resultSet = Mage::getResourceModel('solrsearch/solr')->doRequest($url, $arguments, 'array');

		print_r($resultSet);
		exit();
	}
}