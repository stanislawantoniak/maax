<?php

class Snowdog_Freshmail_Model_Config
{
    /**#@+
     * Paths to module config
     */
    const XML_PATH_MESSAGE_ALREADY_SUBSCRIBED   = 'snowfreshmail/messages/already_subscribed';
    const XML_PATH_MESSAGE_SUBMISSION_SUCCESS   = 'snowfreshmail/messages/submission_success';
    const XML_PATH_MESSAGE_SUBMISSION_FAILURE   = 'snowfreshmail/messages/submission_failure';
    const XML_PATH_MESSAGE_INVALID_EMAIL        = 'snowfreshmail/messages/invalid_email';
    const XML_PATH_MESSAGE_EMPTY_FIELD_VALUE    = 'snowfreshmail/messages/empty_field_value';
    const XML_PATH_CUSTOM_FIELD_MAPPINGS        = 'snowfreshmail/lists/custom_fields';
    const XML_PATH_SEGMENT_MAPPINGS             = 'snowfreshmail/lists/segments';
    const XML_PATH_LIST                         = 'snowfreshmail/lists/list';
    const XML_PATH_KEY                          = 'snowfreshmail/connect/api_key';
    const XML_PATH_SECRET                       = 'snowfreshmail/connect/api_secret';
    const XML_PATH_QUEUE_CLEAN_AFTER_DAY        = 'snowfreshmail/request_logs/clean_after_day';

    /**
     * Retrieve config value for store by path
     *
     * @param string    $path
     * @param mixed     $store
     *
     * @return mixed
     */
    protected function _getStoreConfig($path, $store = null)
    {
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * Retrieve custom field mappings
     *
     * @return mixed
     */
    public function getCustomFieldMappings()
    {
        $value = $this->_getStoreConfig(self::XML_PATH_CUSTOM_FIELD_MAPPINGS);
        return unserialize($value);
    }

    /**
     * Retrieve a message to display if email is already subscribed
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getAlreadySubscribedMessage($store = null)
    {
        return $this->_getStoreConfig(
            self::XML_PATH_MESSAGE_ALREADY_SUBSCRIBED,
            $store
        );
    }

    /**
     * Retrieve a message to display after successful newsletter submission
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getSubmissionSuccessMessage($store = null)
    {
        return $this->_getStoreConfig(
            self::XML_PATH_MESSAGE_SUBMISSION_SUCCESS,
            $store
        );
    }

    /**
     * Retrieve a message to display after newsletter submission failure
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getSubmissionFailureMessage($store = null)
    {
        return $this->_getStoreConfig(
            self::XML_PATH_MESSAGE_SUBMISSION_FAILURE,
            $store
        );
    }

    /**
     * Retrieve a message to display if invalid email provided
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getInvalidEmailMessage($store = null)
    {
        return $this->_getStoreConfig(
            self::XML_PATH_MESSAGE_INVALID_EMAIL,
            $store
        );
    }

    /**
     * Retrieve a message to display if required field is empty
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getEmptyFieldValue($store = null)
    {
        return $this->_getStoreConfig(
            self::XML_PATH_MESSAGE_EMPTY_FIELD_VALUE,
            $store
        );
    }

    /**
     * Retrieve segment mappings configuration
     *
     * @return array
     */
    public function getCustomerSegmentMappings()
    {
        $value = $this->_getStoreConfig(self::XML_PATH_SEGMENT_MAPPINGS);
        return unserialize($value);
    }

    /**
     * Retrieve api secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_SECRET);
    }

    /**
     * Retrieve api key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_getStoreConfig(self::XML_PATH_KEY);
    }

    /**
     * Retrieve a subscription list hash
     *
     * @param mixed $store
     *
     * @return mixed
     */
    public function getListHash($store = null)
    {
        return $this->_getStoreConfig(self::XML_PATH_LIST, $store);
    }

    /**
     * @return mixed
     */
    public function getCleanQueueAfterDay()
    {
        return $this->_getStoreConfig(self::XML_PATH_QUEUE_CLEAN_AFTER_DAY);
    }
}
