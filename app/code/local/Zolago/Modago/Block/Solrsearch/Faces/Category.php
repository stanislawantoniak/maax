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
            $parentCategoryUrl = $this->getFacesUrl(array('scat' => $category->getId()));
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