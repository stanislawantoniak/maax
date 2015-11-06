<?php
/**
 * helper for rewrite module
 */
class GH_Rewrite_Helper_Data extends Mage_Core_Helper_Abstract {

    protected function getRawUrlCategoryFromFilter($path,$categoryId,$data) {
        if (!empty($data)) {
            $query = http_build_query(array('fq'=>$data),'','&');
        } else {
            $query = '';
        }
        $rawUrl = urldecode($path.DS.'id'.DS.$categoryId.'?'.$query);
        return $rawUrl;
    }
    public function prepareRewriteUrl($path,$categoryId,$queryData) {
	    $tmp = null;
        if(isset($queryData['fq'])) {
            $tmp = $queryData['fq'];
        }

        $rawUrl = $this->getRawUrlCategoryFromFilter($path,$categoryId,$tmp);
	    /** @var GH_Rewrite_Model_Rewrite $rewrite */
        $rewrite = Mage::getModel('core/url_rewrite');
	    $rewrite->setStoreId(Mage::app()->getStore()->getId());
        $rewrite->setCategoryId($categoryId);
        $url = $rewrite->loadByRequestPathForFilters($categoryId,$rawUrl);
        if ($url) {
            // add other parameters
            unset($queryData['fq']);
            $query = http_build_query($queryData);
            $url = rtrim(Mage::getUrl($url),"/");
	        $url = str_replace('/?___SID=U','?___SID=U',$url);
            if($query) {
		        $url .= '?' . $query;
	        }
        }
        return $url;

    }

	/**
	 * @param $params
	 * @return array
	 */
	public function clearParams(&$params) {
		$listingModel = $this->getListModel();

		//if sort param is set to default one then we don't need it
		if(isset($params['sort']) &&
			(
				($params['sort'] == Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_ORDER && $listingModel->isCategoryMode()) ||
				($params['sort'] == Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_SEARCH_ORDER && $listingModel->isSearchMode()) ||
				!$params['sort']
			)
		) {
			unset($params['sort']);
		}

		//if sort param is not set (was unset above) then clear dir param if it contains default value
		if(!isset($params['sort']) &&
			isset($params['dir']) &&
			(
				($params['dir'] == Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_DIR && $listingModel->isCategoryMode()) ||
				($params['dir'] == Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_SEARCH_DIR && $listingModel->isSearchMode()) ||
				!$params['dir']
			)
		) {
			unset($params['dir']);
		}

		if(isset($params['Szukaj.x'])) {
			unset($params['Szukaj.x']);
		}

		if(isset($params['Szukaj.y'])) {
			unset($params['Szukaj.x']);
		}

		if(isset($params['label'])) {
			unset($params['label']);
		}

		if(isset($params['page'])) {
			unset($params['page']);
		}

		if(isset($params['_'])) {
			unset($params['_']);
		}

		if(isset($params['rows'])) {
			unset($params['rows']);
		}

		if($listingModel->isCategoryMode()) {
			//clear search queries in category mode
			if (isset($params['q'])) {
				unset($params['q']);
			}
			//clear scat, it's got via current_category anyway
			if (isset($params['scat'])) {
				unset($params['scat']);
			}
		}

		if($listingModel->isSearchMode() && isset($params['scat']) && !$params['scat']) {
			unset($params['scat']);
		}

		return $params;
	}

	public function sortParams(&$params) {
		ksort($params);
		if(isset($params['fq']) && is_array($params['fq'])) {
			ksort($params['fq']);
			foreach($params['fq'] as &$filter) {
				if(is_array($filter)) {
					sort($filter);
				}
			}
		}
		return $params;
	}

	/**
	 * @return Zolago_Solrsearch_Model_Catalog_Product_List
	 */
	public function getListModel() {
		return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
	}

	public function getGhUrlRewriteCsvColumns() {
		return array(
			'store_id',
			'category_id',
			'title',
			'meta_description',
			'meta_keywords',
			'category_name',
			'text_field_category',
			'text_field_filter',
			'listing_title',
			'url'
		);
	}

	const GH_URL_REWRITE_HASH_ID_COLUMN = 'hash_id';
	const GH_URL_REWRITE_FILTERS_COLUMN = 'filters';

    public function getCategoryRewriteData()
    {
        $url = $_SERVER["REQUEST_URI"];
        if(in_array("orbacommon", explode("/", $_SERVER["REQUEST_URI"]))){
            $url = Mage::registry("category_with_filters");
        }
        $url = str_replace(Mage::getBaseUrl() , "",$url);

        $path = strtok(trim($url,"/"), "?");

        $rewrite = Mage::getModel('core/url_rewrite');
        $collection = $rewrite->getCollection();
        $collection->addFieldToFilter("is_system", 0);
        $collection->addFieldToFilter("store_id", Mage::app()->getStore()->getId());
        $collection->addFieldToFilter("product_id", array('null' => true));
        $collection->addFieldToFilter("request_path", array('like' => $path.''));
        $data = $collection->getFirstItem();

        $url_rewrite_id = $data->getData("url_rewrite_id");

        $ghUrlRewriteModel = Mage::getModel('ghrewrite/url');
        $rewriteData = $ghUrlRewriteModel->load($url_rewrite_id, "url_rewrite_id")->getData();

        return $rewriteData;
    }

	const GH_URL_REWRITE_REDIRECTION_SUFFIX = '_redirect';
}
