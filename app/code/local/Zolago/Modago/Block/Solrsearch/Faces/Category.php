<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 21.08.2014
 */

class Zolago_Modago_Block_Solrsearch_Faces_Category extends Zolago_Solrsearch_Block_Faces_Category
{
    /**
     * Returns model of current category.
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getCurrentCategory();
    }

    /**
     * Returns URL for parent category.
     *
     * @return null|string
     */
    public function getParentCategoryUrl()
    {
        $rootCategory = Mage::app()->getStore()->getRootCategoryId();
        if ($rootCategory == $this->getCurrentCategory()->getId()) {
            return null;
        }
        $category = $this->getCurrentCategory()->getParentCategory();
        
        $params = $this->getRequest()->getParams();
        $parentCategoryUrl = null;
        if($this->getParentBlock()->getMode()==Zolago_Solrsearch_Block_Faces::MODE_CATEGORY){
            $parentCategoryUrl = Mage::getUrl('',
                array(
                    '_direct' => Mage::getModel('core/url_rewrite')->loadByIdPath('category/' . $category->getId())->getRequestPath(),
// refs #440                    '_query' => $params   
                )
            );
        }
        else {
//            $parentCategoryUrl = $this->getFacesUrl(array('scat' => $category->getId()));
            //because _parseQueryData(...) in app/code/local/Zolago/Solrsearch/Block/Faces.php
            //is bugged (always take paramss from Mage::app()->getRequest()->getParams();
            //url is need to be filter

            $id = $category->getId();

            $_solrDataArray = $this->getSolrData();
            $q = Mage::app()->getRequest()->getParam('q');

            if( isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q']) ) {
                $q = "&q=" . $_solrDataArray['responseHeader']['params']['q'];
            }

            $parentCategoryUrl = Mage::getUrl("search/index/index") . "?scat={$id}{$q}";
        }

        return $parentCategoryUrl;
    }

    /**
     * Returns name(label) for parent category.
     *
     * @return string
     */
    public function getParentCategoryLabel()
    {
        return $this->getCurrentCategory()->getParentCategory()->getName();
    }

} 