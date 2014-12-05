<?php
class Zolago_Solrsearch_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List {

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection() {
        return $this->getListModel()->getCollection();
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_List
     */
    public function getListModel() {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
    }

    /**
     * @return string
     */
    public function getJsonProducts() {
        return Mage::helper("core")->jsonEncode(
                   Mage::helper("zolagosolrsearch")->prepareAjaxProducts($this->getListModel())
               );
    }

    /**
     * Returns how many products will be loaded on start.
     *
     * @return int
     */
    public function getLoadLimit()
    {
        $limit = (int) Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/load_on_start"
                                            , Mage::app()->getStore());

        if ($limit === 0) {
            $limit = Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_LIMIT;
        }

        return $limit;
    }

    /**
     * Returns how many products are appended to listing when user is scrolling.
     *
     * @return int
     */
    public function getAppendWhenScroll()
    {
        $limit = (int) Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/appended_when_scroll"
                                            , Mage::app()->getStore());

        if ($limit === 0) {
            $limit = Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_APPEND_WHEN_SCROLL;
        }

        return $limit;
    }

    /**
     * Returns how many products will be loaded when user click on Load More Button.
     *
     * @return int
     */
    public function getLoadMoreOffset()
    {
        $limit = (int) Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/load_more_offset"
                                            , Mage::app()->getStore());

        if ($limit === 0) {
            $limit = Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_LOAD_MORE_OFFSET;
        }

        return $limit;
    }

    /**
     * Returns how many pixels from bottom of the page append to listing will start.
     *
     * @return int
     */
    public function getPixelsBeforeAppend()
    {
        $limit = (int) Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/pixels_before_append"
                                            , Mage::app()->getStore());

        if ($limit === 0) {
            $limit = Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_PIXELS_BEFORE_APPEND;
        }

        return $limit;
    }

    /**
     * Returns link for non-javascript browsers (ex. googlebot)
     * @return
     */
    public function getNoAjaxLink() {
        if (!$this->getListModel()->isGoogleBot()) {
            return false;
        }
        if ($this->getListModel()->isLastPage()) {
            return false;
        }
        $request = $this->getRequest();
        $page = $request->getParam('page',1);
        $query = $request->getQuery();
        $query['page'] = $page+1;
        $url = $this->getUrl('*/*/*',
                             array(
                                 '_current'=>false,
                                 '_use_rewrite' => true,
                                 '_query' => $query,
                             )
                            );
        return $url;
    }
}
