<?php

/**
 * Class GH_MageMonkey_Helper_Data
 */
class GH_MageMonkey_Helper_Data extends Ebizmarts_MageMonkey_Helper_Data {

    /**
     * Subscribe to list only on MailChimp side
     *
     * @param $listId
     * @param $email
     * @param $mergeVars
     * @param $isConfirmNeed
     * @param $db
     */
    public function _subscribe($listId, $email, $mergeVars, $isConfirmNeed, $db)
    {
        if ($db) {
            if ($isConfirmNeed) {
                //Mage::getSingleton('core/session')->addSuccess(Mage::helper('monkey')->__('Confirmation request will be sent soon.[GH_MageMonkey_Helper_Data]'));
            }
            $subs = Mage::getModel('monkey/asyncsubscribers');
            $subs->setMapfields(serialize($mergeVars))
                ->setEmail($email)
                ->setLists($listId)
                ->setConfirm($isConfirmNeed)
                ->setProcessed(0)
                ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
                ->save();
        } else {
            if ($isConfirmNeed) {
                //Mage::getSingleton('core/session')->addSuccess(Mage::helper('monkey')->__('Confirmation request has been sent.'));
            }
            Mage::getSingleton('monkey/api')->listSubscribe($listId, $email, $mergeVars, 'html', $isConfirmNeed, TRUE);
        }
    }

}