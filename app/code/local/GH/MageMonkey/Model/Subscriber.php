<?php

/**
 * Class GH_MageMonkey_Model_Subscriber
 */
class GH_MageMonkey_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    public function sendUnsubscriptionEmail()
    {
        return parent::sendUnsubscriptionEmail();
    }

    public function sendConfirmationRequestEmail()
    {
        return parent::sendConfirmationRequestEmail();
    }

    public function sendConfirmationSuccessEmail()
    {
        return parent::sendConfirmationSuccessEmail();
    }

    public function confirm($code)
    {
        if ($this->getCode() == $code) {
            $this->setStatus(self::STATUS_SUBSCRIBED)
                ->setIsStatusChanged(true)
                ->save();
            return true;
        }

        return false;
    }
}