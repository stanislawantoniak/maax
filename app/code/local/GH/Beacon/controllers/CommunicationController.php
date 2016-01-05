<?php

class GH_Beacon_CommunicationController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        try {
            $data = $this->getRequest()->getParams();
            $email = $data['email'];

            if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new GH_Beacon_Exception("Incorrect email address");
            }

            if($data['event_type'] != GH_Beacon_Model_Source_Data_Eventtype::TYPE_INPUT &&
                $data['event_type'] != GH_Beacon_Model_Source_Data_Eventtype::TYPE_OUTPUT) {
                throw new GH_Beacon_Exception("Incorrect event type");
            }

            /** @var GH_Beacon_Model_Data $beaconModel */
            $beaconModel = Mage::getModel('ghbeacon/data');

            $beaconData = array(
                "beacon_id" => $data['beacon_id'],
                "email" => $email,
                "distance" => floatval(str_replace(",",".",$data['distance'])),
                "date" => $data['date'],
                "event_type" => $data['event_type']
            );

            $beaconModel
                ->setData($beaconData)
                ->save();

            if(!$beaconModel->getId()) {
                throw new GH_Beacon_Exception("Beacon data save failure, check logs");
            } else {
                echo "OK";
            }
        } catch(Exception $e) {
            Mage::logException($e);
            echo "ERR";
        }
        return;
    }
}