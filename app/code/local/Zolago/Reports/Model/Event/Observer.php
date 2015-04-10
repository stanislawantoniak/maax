<?php

class Zolago_Reports_Model_Event_Observer extends Mage_Reports_Model_Event_Observer
{
    /**
     * Abstract Event observer logic
     *
     * Save event
     *
     * @param int $eventTypeId
     * @param int $objectId
     * @param int $subjectId
     * @param int $subtype
     * @return Zolago_Reports_Model_Event_Observer
     */
    protected function _event($eventTypeId, $objectId, $subjectId = null, $subtype = 0)
    {
        if (is_null($subjectId)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $subjectId = $customer->getId();
            }
            else {
                $subjectId = Mage::helper('zolagowishlist')->getWishlist()->getData('sharing_code');
                $subtype = 1;
            }
        }

        $eventModel = Mage::getModel('reports/event');
        $storeId    = Mage::app()->getStore()->getId();
        $eventModel
            ->setEventTypeId($eventTypeId)
            ->setObjectId($objectId)
            ->setSubjectId($subjectId)
            ->setSubtype($subtype)
            ->setStoreId($storeId);
        $eventModel->save();

        return $this;
    }

    /**
     * When ajax add product to last viewed
     *
     * @param Varien_Event_Observer $observer
     * @return Zolago_Reports_Model_Event_Observer
     */
    public function ajaxAddLastViewed(Varien_Event_Observer $observer)
    {
        $productId = $observer->getEvent()->getProduct()->getId();

        Mage::getModel('reports/product_index_viewed')
            ->setProductId($productId)
            ->save()
            ->calculate();

        return $this->_event(Mage_Reports_Model_Event::EVENT_PRODUCT_VIEW, $productId);
    }


}
