<?php

class Snowdog_Freshmail_Model_Api_Action_SubscriberStatusUpdate
    extends Snowdog_Freshmail_Model_Api_Action_Abstract
    implements Snowdog_Freshmail_Model_Api_Action_Interface
{
    /**
     * Update a subscriber status
     *
     * @param Snowdog_Freshmail_Model_Api_Request $request
     *
     * @throws Exception
     */
    public function execute(Snowdog_Freshmail_Model_Api_Request $request)
    {
        $this->getApi()->call(
            'subscriber/edit',
            $request->getActionParameters()
        );
    }
}
