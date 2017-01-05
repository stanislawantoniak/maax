<?php

/**
 * Class GetresponseIntegration_Getresponse_Helper_GrShop
 */
class GetresponseIntegration_Getresponse_Helper_GrShop extends Mage_Core_Helper_Abstract
{
    /** @var GetresponseIntegration_Getresponse_Helper_Api $apiHelper */
    private $apiHelper;

    /** @var GetresponseIntegration_Getresponse_Model_Settings $settings */
    private $settingsModel;

    /** @var array */
    private $settings;

    /** @var GetresponseIntegration_Getresponse_Model_Shop $shopModel */
    private $shopModel;

    private $shop;

    public function __construct()
    {
        $this->apiHelper = Mage::helper('getresponse/api');

        /** @var GetresponseIntegration_Getresponse_Helper_Data $data */
        $data = Mage::helper('getresponse');

        $this->settingsModel = Mage::getModel('getresponse/settings');

        $shopId = $data->getStoreId();
        $this->settings = $this->settingsModel->load($shopId)->getData();

        // only, if subscription via registration page is active
        if ($this->settings['active_subscription'] == 0) {
            return false;
        }

        $this->shopModel = Mage::getModel('getresponse/shop');
        $this->shop = $this->shopModel->load($shopId)->getData();

        if (!empty($this->settings)) {
            $this->apiHelper->setApiDetails(
                $this->settings['api_key'],
                $this->settings['api_url'],
                $this->settings['api_domain']
            );
        }
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function addNewCartToGetResponse($email)
    {
        $registrationCampaignId = $this->settings['campaign_id'];

        // only, if subscription via registration page campaign exists
        if (
            empty($registrationCampaignId)
            || empty($this->settings)
            || empty($this->settings['api_key'])
            || empty($this->shop['gr_shop_id'])
            || $this->shop['is_enabled'] == 0) {
            return false;
        }

        $apiHelper = $this->apiHelper;
        /** @var GetresponseIntegration_Getresponse_Helper_GrApi $api */
        $api = $apiHelper::instance();

        $contacts = $api->get_contacts(array('query' => array('email' => $email)));

        // only, if contact exists
        if (empty($contacts)) {
            return false;
        }

        $customerContact = reset($contacts);

        foreach ($contacts as $contact) {
            if ($contact->campaign->campaignId === $registrationCampaignId) {
                $customerContact = $contact;
            }
        }

        try {
            // add new cart to GetResponse
            $this->apiHelper->addNewCart(
                $this->shop['gr_shop_id'],
                $customerContact->contactId,
                '1',
                Mage::getBaseUrl()
            );
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $email
     *
     * @param $params
     * @return bool
     */
    public function addNewPurchaseToGetResponse($email, $params)
    {
        $registrationCampaignId = $this->settings['campaign_id'];

        if (empty($registrationCampaignId)
            || empty($this->settings)
            || empty($this->settings['api_key'])
            || empty($this->shop['gr_shop_id'])
            || $this->shop['is_enabled'] == 0) {
            return false;
        }

        $this->apiHelper->setApiDetails(
            $this->settings['api_key'],
            $this->settings['api_url'],
            $this->settings['api_domain']
        );

        $apiHelper = $this->apiHelper;
        /** @var GetresponseIntegration_Getresponse_Helper_GrApi $api */
        $api = $apiHelper::instance();

        $contacts = $api->get_contacts(array('query' => array('email' => $email)));

        // only, if contact exists
        if (empty($contacts)) {
            return false;
        }

        $customerContact = reset($contacts);

        foreach ($contacts as $contact) {

            if ($contact->campaign->campaignId === $registrationCampaignId) {
                $customerContact = $contact;
            }
        }

        $apiHelper->addNewPurchase(
            $this->shop['gr_shop_id'],
            $customerContact->contactId,
            '1',
            Mage::getBaseUrl(),
            $params
        );

        return true;
    }
}