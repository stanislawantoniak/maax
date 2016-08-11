<?php

/**
 * Class Zolago_Solrsearch_Block_Catalog_Product_List_Pager
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Pager extends Mage_Page_Block_Html_Pager
{
    const DEFAULT_FIRST = 1;
    /**
     * GET parameter start variable
     *
     * @var string
     */
    protected $_startVarName = 'start';


    public function _construct(){
        $this
            ->setPageVarName($this->getStartVarName())
            ->setLimit($this->getLimit())
            ->setCollection($this->getCollection());
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

    public function getLimit(){
        return Mage::helper("zolagocatalog/listing_pagination")->productsCountPerPage();
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_List
     */
    public function getListModel()
    {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection()
    {
        return $this->getListModel()->getCollection();
    }

    /**
     * Return array of pages in frame
     *
     * @return array
     */
    public function getFramePages()
    {
        $frame = array();

        $end = $this->getTotalNum();
        $limit = $this->getLimit();
        $query = Mage::app()->getRequest()->getQuery();
        for ($i = 0; $i < ceil($end / $limit); $i++) {
            $query["start"] = $i * $limit + 1;
            $frame[] = $this->getPagerUrl($query);
        }
        return $frame;
    }

    public function getFirstNum()
    {
        $request = Mage::app()->getRequest();
        $first = (int)$request->getParam("start", 1);

        if ($first >= 1) {
            if ($first > $this->getTotalNum()) {
                $first = self::DEFAULT_FIRST;
            }
            return $first;
        }
        return self::DEFAULT_FIRST;
    }

    public function getLastNum()
    {
        $lastNum = ($this->getFirstNum() - 1) + $this->getLimit();
        $totalNum = $this->getTotalNum();
        if($lastNum >= $totalNum){
            $lastNum = $totalNum;
        }
        return $lastNum;
    }

    public function getPreviousPageUrl()
    {
        $first = $this->getFirstNum();
        $limit = $this->getLimit();


        $prev = $first - $limit;

        if ($prev <= 1) {
            $prev = null;
        }

        $url = $this->getPageUrl($prev);
        if(Mage::app()->getStore()->isCurrentlySecure()) {
            $url = str_replace('http://','https://',$url);
        }

        return $url;
    }

    public function getNextPageUrl()
    {
        $next = $this->getFirstNum() + $this->getLimit();
        if ($next > $this->getTotalNum()) {
            $next = self::DEFAULT_FIRST;
        }

        $url = $this->getPageUrl($next);
        if(Mage::app()->getStore()->isCurrentlySecure()) {
            $url = str_replace('http://','https://',$url);
        }

        return $url;
    }

    public function isFirstEnabled()
    {
        return ($this->getFirstNum() !== self::DEFAULT_FIRST);
    }

    public function isLastEnabled()
    {
        $next = $this->getFirstNum() + $this->getLimit();
        return ($next <= $this->getTotalNum());
    }

    public function getPagerUrl($params = array())
    {

        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;





        $generatedUrl = $this->getGeneratedUrl(); //ajax url

        if ($generatedUrl) {
            $parsedUrl = parse_url($generatedUrl);
            $query = http_build_query($params);
            if(isset($parsedUrl["query"])){
                return $generatedUrl . ($query ? "&" . $query : "");
            } else {
                return $generatedUrl . ($query ? "?" . $query : "");
            }
        }
        return $this->getUrl('*/*/*', $urlParams);
    }
}
