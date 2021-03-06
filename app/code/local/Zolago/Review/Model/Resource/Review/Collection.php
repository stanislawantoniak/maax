<?php

/**
 * Review collection resource model
 *
 * @category    Mage
 * @package     Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Zolago_Review_Model_Resource_Review_Collection extends Mage_Review_Model_Resource_Review_Collection
{
    /**
     * init select
     *
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->join(array('det' => $this->_reviewDetailTable),
                'main_table.review_id = det.review_id',
                array('detail_id', 'title', 'detail', 'nickname', 'customer_id', 'recommend_product'));
        return $this;
    }
}
