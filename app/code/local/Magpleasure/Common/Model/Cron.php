<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Abstract Model
 */
class Magpleasure_Common_Model_Cron
{
    protected $_timeout = 1800;
    protected $_cacheKey = "MAGPLEASURE_COMMON_CRON_KEY";

    protected function initCron(){}
    public function publicRun($schedule){}

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    public function setCacheKey($key)
    {
        $this->_cacheKey = $key;
    }

    public function setTimeout($value)
    {
        $this->_timeout = $value;
    }

    public function run($schedule)
    {
        $this->initCron();
        try {
            if($this->checkLock($this->_cacheKey)){
                $this->publicRun($schedule);
                Mage::app()->removeCache($this->_cacheKey);
            } else {
                echo "Extension's cron job has been locked";
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    protected function checkLock($lockKey)
    {
        if($time = Mage::app()->loadCache($lockKey)){
            if((time() - $time) <= $this->_timeout){
                return false;
            }
        }
        Mage::app()->saveCache(time(), $lockKey, array(), $this->_timeout);
        return true;
    }



}