<?php

class Modago_Integrator_Model_Client
{
    const MODAGO_INTEGRATOR_URL = "http://127.0.0.1/ghintegrator/communication";

    protected $_conf = array();

    /**
     * @return array (
     *    'secret'        => 'string',
     *    'external_id'    => 'string'
     * )
     */
    public function getConfig($field = null)
    {
        if (!$this->_conf) {
            $this->_conf = Mage::getStoreConfig("modagointegrator/authentication");
        }
        return $field ? trim($this->_conf[$field]) : $this->_conf;
    }

    public function getResponse()
    {
        return $this->_makeConnection();
    }

    /**
     * @param $action
     * @return mixed|null
     */
    protected function _makeConnection()
    {
        $return = null;
        try {
            $process = curl_init(self::MODAGO_INTEGRATOR_URL);
            curl_setopt($process, CURLOPT_POST, true);
            $data = array("secret" => $this->getConfig('secret'), "external_id" => $this->getConfig('external_id'));
            curl_setopt($process, CURLOPT_POSTFIELDS, $data);
            $return = curl_exec($process);

            curl_close($process);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return json_decode($return,1);
    }

}