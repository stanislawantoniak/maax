<?php

class Snowdog_Freshmail_Model_Log_Adapter extends Mage_Core_Model_Log_Adapter
{
    /**
     * Perform forced log data to file
     *
     * @param mixed $data
     *
     * @return Mage_Core_Model_Log_Adapter
     */
    public function log($data = null)
    {
        if ($data === null) {
            $data = $this->_data;
        } else {
            if (!is_array($data)) {
                $data = array($data);
            }
        }
        $data = $this->_filterDebugData($data);
        if (is_array($data) && count($data) === 1) {
            $data = array_pop($data);
        }
        Mage::log($data, null, $this->_logFileName, true);
        return $this;
    }
}
