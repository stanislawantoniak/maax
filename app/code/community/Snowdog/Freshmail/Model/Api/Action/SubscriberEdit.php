<?php

class Snowdog_Freshmail_Model_Api_Action_SubscriberEdit
    extends Snowdog_Freshmail_Model_Api_Action_Abstract
{
    /**
     * Code if subscriber not exist
     *
     * @const int
     */
    const SUBSCRIBER_NOT_FOUND = 1331;

    /**
     * Edit or create a subscriber if not exist
     *
     * @param Snowdog_Freshmail_Model_Api_Request $request
     *
     * @throws Exception
     */
    public function execute(Snowdog_Freshmail_Model_Api_Request $request)
    {
        $data = $request->getActionParameters();
        Mage::helper('snowfreshmail/api')->initFields($data['list']);
        try {
            if (isset($data['old_email'])) {
                $this->getApi()->call('subscriber/delete', array(
                    'list' => $data['list'],
                    'email' => $data['old_email'],
                ));
                $data['confirm'] = 0;
                unset($data['old_email']);
                $this->getApi()->call('subscriber/add', $data);
            } else {
                $this->getApi()->call('subscriber/edit', $data);
            }
        } catch (Snowdog_Freshmail_Exception $e) {
            if ($e->getCode() === self::SUBSCRIBER_NOT_FOUND) {
                $this->getApi()->call('subscriber/add', $data);
                return;
            }
            throw $e;
        }
    }
}
