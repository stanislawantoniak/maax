<?php

class Modago_Integrator_Model_Client
{
    const MODAGO_INTEGRATOR_URL = "http://modago.pl/ghapi/communication";

    public function getResponse()
    {
        return $this->_makeConnection();
    }

    /**
     * @return mixed|null
     */
    protected function _makeConnection()
    {
        $return = null;
	    /** @var Modago_Integrator_Helper_Data $helper */
	    $helper = Mage::helper('modagointegrator');
        try {
            $process = curl_init(self::MODAGO_INTEGRATOR_URL);
            curl_setopt($process, CURLOPT_POST, true);
            $data = array("secret" => $helper->getSecret(), "external_id" => $helper->getExternalId());
            curl_setopt($process, CURLOPT_POSTFIELDS, $data);
	        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1 );
            $return = curl_exec($process);

            curl_close($process);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return json_decode($return,1);
    }

}