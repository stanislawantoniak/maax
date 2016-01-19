<?php

/**
 * Class GH_MageMonkey_Model_Subscriber
 */
class GH_MageMonkey_Model_Subscriber extends Ebizmarts_MageMonkey_Model_Subscriber {
    public function sendUnsubscriptionEmail()
    {
//        $store = Mage::helper('monkey')->getThisStore();
//        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $store)) {
//            return $this;
//        } else {
//            return parent::sendUnsubscriptionEmail();
//        }
        return parent::sendUnsubscriptionEmail();
    }

    public function sendConfirmationRequestEmail()
    {
//        $store = Mage::helper('monkey')->getThisStore();
//        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $store)) {
//            return $this;
//        } else {
//            return parent::sendConfirmationRequestEmail();
//        }
        Mage::log("GH_MageMonkey_Model_Subscriber", null, "GH_MageMonkey_Model_Subscriber.log");
        return parent::sendConfirmationRequestEmail();
    }

    public function sendConfirmationSuccessEmail()
    {
//        $store = Mage::helper('monkey')->getThisStore();
//        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $store)) {
//            return $this;
//        } else {
//            return parent::sendConfirmationSuccessEmail();
//        }
        return parent::sendConfirmationSuccessEmail();
    }
}