<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 24.07.2014
 */

class Zolago_Modago_Block_Review_Product_View_List extends Mage_Review_Block_Product_View_List
{
    protected function _construct()
    {
        parent::_construct();

        $collection = $this->getReviewsCollection();

        $pager = new Mage_Page_Block_Html_Pager();
        $pager->setCollection($collection)
            ->setShowPerPage(false)
            ->setShowAmounts(false)
            ->setUseContainer(false)
            ->setTemplate('page/html/pager-reviews.phtml');

        $this->setChild('product_review_list.toolbar', $pager);
    }
}