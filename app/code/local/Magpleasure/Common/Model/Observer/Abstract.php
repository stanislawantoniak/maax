<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Abstract Model Observer
 */
class Magpleasure_Common_Model_Observer_Abstract
{
    protected $_lockName = 'magpleasure_common_lock';

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    public function setLockName($lockName)
    {
        $this->_lockName = $lockName;
        return $this;
    }

    public function getLockName()
    {
        return $this->_lockName;
    }

    public function lock()
    {
        Mage::register($this->getLockName(), true, true);
    }

    public function isLocked()
    {
        return !!Mage::registry($this->getLockName());
    }

    public function unlock()
    {
        if ($this->isLocked()){
            Mage::unregister($this->getLockName());
        }
    }

}
