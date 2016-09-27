<?php

/**
 * @method setAction(string $path)
 * @method setCreatedAt(string $createdAt)
 * @method setProcessedAt(string $processedAt)
 * @method setStatus(int $status)
 * @method setDateExpires(string $dateExpires)
 */
class Snowdog_Freshmail_Model_Api_Request extends Mage_Core_Model_Abstract
{
    /**
     * Execution status
     *
     * @const string
     */
    const STATUS_FAILED = 'failed';
    const STATUS_NEW = 'new';
    const STATUS_SUCCESS = 'success';
    const STATUS_EXPIRED = 'expired';

    /**
     * Request lifetime on fail in seconds
     *
     * @const int
     */
    const LIFETIME = 86400;

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('snowfreshmail/api_request');
    }

    /**
     * Set initial values for new requests
     * and a finish date if request done successfully
     */
    protected function _beforeSave()
    {
        $dateModel = Mage::getSingleton('core/date');
        $now = $dateModel->gmtDate();

        if (!$this->getId()) {
            $this->setCreatedAt($now);
        }

        if (null === $this->getStatus()) {
            $this->setStatus(self::STATUS_NEW);
        }

        if (null === $this->getDateExpires()) {
            $expiresAt = $dateModel->gmtTimestamp() + self::LIFETIME;
            $this->setDateExpires(date('Y-m-d H:i:s', $expiresAt));
        }

        if (!empty($this->_responses)) {
            $this->setProcessedAt($dateModel->gmtDate());
        }

        return parent::_beforeSave();
    }

    /**
     * Expiry request
     *
     * @return $this
     */
    public function expiry()
    {
        $this->setStatus(self::STATUS_EXPIRED);
        $this->save();

        return $this;
    }

    /**
     * Set action parameters
     *
     * @param mixed $parameters
     *
     * @return $this
     */
    public function setActionParameters($parameters)
    {
        if (!is_string($parameters)) {
            $parameters = json_encode($parameters);
        }
        $this->setData('action_parameters', $parameters);

        return $this;
    }

    /**
     * Retrieve action parameters
     *
     * @param bool $asJson
     *
     * @return mixed|string
     */
    public function getActionParameters($asJson = false)
    {
        $parameters = json_decode($this->getData('action_parameters'), true);
        if (true === $asJson) {
            return json_encode($parameters, JSON_PRETTY_PRINT);
        }

        return $parameters;
    }

    /**
     * Retrieve status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $statuses = Mage::helper('snowfreshmail')->getItemStatusesArray();
        $status = $this->getData('status');

        if (isset($statuses[$status])) {
            return $statuses[$status];
        }

        return '';
    }

    /**
     * Check request should to be expired
     *
     * @return bool
     */
    public function needToBeExpired()
    {
        if (!$this->getDateExpires()) {
            return false;
        }

        if (strtotime($this->getDateExpires()) < time()) {
            return true;
        }

        return false;
    }
}
