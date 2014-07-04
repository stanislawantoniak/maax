<?php
/**
 *
 * @category    Zolago
 * @package     Zolago_Solrsearch
 */
class Zolago_Solrsearch_ContextController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        $categoryP = Mage::app()->getRequest()->getParam('cat', 0);
        if (empty($categoryP)) {
            Mage::getSingleton('core/session')->setSolrFilterQuery(array());
        } else {
            /* Load category by id*/
            $category_model = Mage::getModel('catalog/category');
            $_category = $category_model->load($categoryP);
            $allChildCategories = $category_model->getResource()->getAllChildren($_category);

            if (!empty($allChildCategories)) {
                $categoryP = $allChildCategories;
            }

            $filterQuery = array('category_id' => $categoryP);
            Mage::getSingleton('core/session')->setSolrFilterQuery(array_unique($filterQuery));
        }
        Mage::getSingleton('core/session')->setData('solr_change_context', 1);

    }
}