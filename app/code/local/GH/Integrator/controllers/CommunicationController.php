<?php

class GH_Integrator_CommunicationController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $data = $this->getRequest();

        $vendorId = isset($data['external_id']) ? $data['external_id'] : null;
        $secret = isset($data['secret']) ? $data['secret'] : null;

        Mage::getModel("ghintegrator/communication")->connect($secret, $vendorId);
    }
}