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
        if (isset($queryData['fq'])) {
            $tmp = $queryData['fq'];
        }        
        $rawUrl = $this->getRawUrlCategoryFromFilter($path,$categoryId,$tmp);
        $rewrite = Mage::getModel('core/url_rewrite');
        $rewrite->setStoreId(Mage::app()->getStore()->getId());
        $rewrite->setCategoryId($categoryId);
        $url = $rewrite->loadByRequestPathForFilters($categoryId,$rawUrl);
        if ($url) {
            // add other parameters
            unset($queryData['fq']);
            $query = http_build_query($queryData);
            $url = Mage::getUrl($url);
            $url .= '?'.$query;
        }
        return $url;

    }
}
