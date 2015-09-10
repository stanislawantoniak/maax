<?php

class Zolago_Solrsearch_Block_Catalog_Product_List_Toolbar extends Mage_Core_Block_Template
{

    /**
     * GET parameter start variable
     *
     * @var string
     */
    protected $_startVarName = 'start';



    public function _construct()
    {
        $this->setTemplate('zolagosolrsearch/catalog/product/list/toolbar.phtml');
        parent::_construct();
    }

    /**
     * @return int
     */
    public function getNumFound()
    {
        $num = $this->getCollection()->getSolrData("response", "numFound");
        if (is_numeric($num)) {
            return $num;
        }
        return 0;
    }

    /**
     * @return array
     */
    public function getSortOptions()
    {
        return $this->getListModel()->getSortOptions();
    }

    public function getCurrentOrder()
    {
        return $this->getListModel()->getCurrentOrder();
    }

    public function getCurrentDir()
    {
        return $this->getListModel()->getCurrentDir();
    }

    /**
     * @param type $option
     * @return array
     */
    public function getSortUrl($option)
    {
        return $this->getPagerUrl();
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection()
    {
        return $this->getListModel()->getCollection();
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_List
     */
    public function getListModel()
    {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
    }

    public function getRewriteUrl($options)
    {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $request = $this->getRequest()->getParams();
        $this->_parseUrlParams($request);
        $fields = array(
            'q',
            'fq',
            'scat',
        );
        $params = array();
        foreach ($fields as $key) {
            if (isset($request[$key])) {
                $params[$key] = $request[$key];
            }
        }

        if (!empty($options['sort'])) {
            $params['sort'] = $options['sort'];
        }
        if (!empty($options['dir'])) {
            $params['dir'] = $options['dir'];
        }

        $this->_prepareUrlParams($params);

        $rewrite = Mage::getModel('core/url_rewrite');
        $rewrite->setStoreId(Mage::app()->getStore()->getId());
        $route = $this->getListModel()->getUrl();
        $categoryId = $this->getListModel()->getCurrentCategory()->getId();

        $url = $rewriteHelper->prepareRewriteUrl($route, $categoryId, $params);

        if (!$url) {
            $rewriteHelper->sortParams($params);
            $rewriteHelper->clearParams($params);
            $route = Mage::registry('current_category')->getUrlPath();
            $query = http_build_query($params);
            $url = Mage::getBaseUrl() . $route;
            if ($query) {
                $url .= '?' . $query;
            }
        }
        return $url;
    }

    protected function _parseUrlParams(&$paramss)
    {
        $_solrDataArray = $this->getCollection()->getSolrData();
        if (isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q'])) {
            if (isset($paramss['q']) && $paramss['q'] != $_solrDataArray['responseHeader']['params']['q']) {
                $paramss['q'] = $_solrDataArray['responseHeader']['params']['q'];
            }
        }
    }

    protected function _prepareUrlParams()
    {
        $paramss = $this->getRequest()->getParams();
        $this->_parseUrlParams($paramss);
        $finalParams = $paramss;

        $urlParams = array();
        $urlParams['_current'] = false;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;


        if (isset($finalParams)) {
            if ($this->getListModel()->isCategoryMode()) {
                if (isset($finalParams['q'])) {
                    unset($finalParams['q']);
                }
                if (isset($finalParams['id'])) {
                    unset($finalParams['id']);
                }
            }

            /** @var Zolago_Solrsearch_Helper_Data $solrsearchHelper */
            $solrsearchHelper = Mage::helper('zolagosolrsearch');
            $urlParams['_query'] = $solrsearchHelper->processFinalParams($finalParams);
        }

        if ($this->getListModel()->isCategoryMode()) {
            $urlParams['_direct'] = $this->getListModel()->getUrlPathForCategory();
        }

        return $urlParams;


    }

    public function getPagerUrl($params = array())
    {
        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        // overwrite q
        $q = $this->getRequest()->getParam('q');
        if (!empty($q)) {
            $solr = $this->getListModel()->getSolrData();
            if (!empty($solr['responseHeader']['params']['q'])) {
                $params['q'] = $solr['responseHeader']['params']['q'];
                $urlParams['q'] = $solr['responseHeader']['params']['q'];
            }
        }

        /** @var Zolago_Solrsearch_Helper_Data $solrseachHelper */
        $solrseachHelper = Mage::helper('zolagosolrsearch');

        $urlParams['_query'] = $solrseachHelper->processFinalParams($params);
        $url = $this->getUrl('*/*/*', $urlParams);
        return $url;
    }


    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChild('product_list_toolbar_pager');

        if ($pagerBlock instanceof Varien_Object) {

            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock
                ->setPageVarName($this->getStartVarName())
                ->setLimit($this->getLimit())
                ->setFrameLength(Mage::getStoreConfig('design/pagination/pagination_frame'))
                ->setJump(Mage::getStoreConfig('design/pagination/pagination_frame_skip'))
                ->setCollection($this->getCollection());

            return $pagerBlock->toHtml();
        }
        return '';
    }


    /**
     * Getter for $_pageVarName
     *
     * @return string
     */
    public function getStartVarName()
    {
        return $this->_startVarName;
    }

}
