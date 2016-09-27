<?php

class Snowdog_Freshmail_Model_Api_Action_SubscriberDelete
    extends Snowdog_Freshmail_Model_Api_Action_Abstract
{
    /**
     * Delete a subscriber
     *
     * @param Snowdog_Freshmail_Model_Api_Request $request
     *
     * @throws Exception
     */
    public function execute(Snowdog_Freshmail_Model_Api_Request $request)
    {
        $this->getApi()->call(
            'subscriber/delete',
            $request->getActionParameters()
        );
    }
}
