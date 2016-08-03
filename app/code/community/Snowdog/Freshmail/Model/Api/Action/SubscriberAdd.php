<?php

class Snowdog_Freshmail_Model_Api_Action_SubscriberAdd
    extends Snowdog_Freshmail_Model_Api_Action_Abstract
{
    /**
     * Create a subscriber
     *
     * @param Snowdog_Freshmail_Model_Api_Request $request
     *
     * @throws Exception
     */
    public function execute(Snowdog_Freshmail_Model_Api_Request $request)
    {
        $data = $request->getActionParameters();
        Mage::helper('snowfreshmail/api')->initFields($data['list']);
        $this->getApi()->call('subscriber/add', $data);
    }
}
