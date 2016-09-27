<?php

interface Snowdog_Freshmail_Model_Api_Action_Interface
{
    /**
     * Execute action
     *
     * @param Snowdog_Freshmail_Model_Api_Request $request
     */
    public function execute(Snowdog_Freshmail_Model_Api_Request $request);
}
