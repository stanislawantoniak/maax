<?php

class GetresponseIntegration_Getresponse_Helper_GrApi
{
	/**
	 *
	 * Invalid API Key code.
	 *
	 * @var int
	 */
	public $invalid_apikey_code = 1014;

	/**
	 *
	 * API code on success.
	 *
	 * @var int
	 */
	public $success_api_code = 200;

	/**
	 *
	 * API Key
	 *
	 * @var string
	 */
	public $api_key = '';

	/**
	 *
	 * API url for 360.com.
	 *
	 * @var string
	 */
	public $api_url_360_com = 'https://api3.getresponse360.com/v3';

	/**
	 *
	 * API url for 360.pl.
	 *
	 * @var string
	 */
	public $api_url_360_pl = 'https://api3.getresponse360.pl/v3';

	/**
	 *
	 * API url.
	 *
	 * @var null|string
	 */
	public $api_url = 'https://api.getresponse.com/v3';

	/**
	 *
	 * Domain for GetResponse 360.
	 *
	 * @var null
	 */
	public $domain = null;

	/**
	 *
	 * Timeout.
	 *
	 * @var int
	 */
	public $timeout = 8;

	/**
	 *
	 * Https status code.
	 *
	 * @var int
	 */
	public $http_status;

	/**
	 *
	 * Ping result object.
	 *
	 * @var object
	 */
	public $ping;

	/**
	 *
	 * API status.
	 *
	 * @var bool
	 */
	public $status = true;

	/**
	 * Set api key and optionally API endpoint
	 *
	 * @param string $api_key API key.
	 * @param string $api_url API url.
	 * @param string $domain  API domain.
	 */
	public function __construct()
	{
	}

	/**
	 * Check, if api works properly.
	 *
	 * @param null $api_url
	 * @param null $domain
	 *
	 * @return bool
	 */
	public function check_api($api_url = null, $domain = null)
	{

		if ( !empty($api_url)) {
			$this->api_url = $api_url;
		}
		if ( !empty($domain)) {
			$this->domain = $domain;
		}

		return $this->call_ping();
	}

	/**
	 * We can modify internal settings.
	 *
	 * @param string $key   key.
	 * @param string $value value.
	 */
	function __set($key, $value)
	{
		$this->{$key} = $value;
	}

	/**
	 * Ping to api.
	 * @return mixed
	 */
	public function call_ping()
	{
		return $this->call('accounts');
	}

	/**
	 * Return features list
	 * @return mixed
	 */
	public function get_features()
	{
		return $this->call('accounts/features');
	}

	/**
	 * Return features list
	 * @return mixed
	 */
	public function get_tracking_code()
	{
		return $this->call('tracking');
	}

    /**
     * Return all campaigns
     * @return mixed
     */
    public function get_campaigns($params)
    {
        return $this->call('campaigns?' . $this->setParams($params));
    }

	/**
	 * get single campaign
	 *
	 * @param string $campaign_id retrieved using API
	 *
	 * @return mixed
	 */
	public function get_campaign($campaign_id)
	{
		return $this->call('campaigns/' . $campaign_id);
	}

	/**
	 * adding campaign
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function create_campaign($params)
	{
		return $this->call('campaigns', 'POST', $params);
	}

	/**
	 * add single contact into your campaign
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function add_contact($params)
	{
		return $this->call('contacts', 'POST', $params);
	}

	/**
	 * retrieving contact by id
	 *
	 * @param string $contact_id - contact id obtained by API
	 *
	 * @return mixed
	 */
	public function get_contact($contact_id)
	{
		return $this->call('contacts/' . $contact_id);
	}

	/**
	 * retrieving contact by params
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_contacts($params = array())
	{
		return $this->call('contacts?' . $this->setParams($params));
	}

	/**
	 * updating any fields of your subscriber (without email of course)
	 *
	 * @param       $contact_id
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function update_contact($contact_id, $params = array())
	{
		return $this->call('contacts/' . $contact_id, 'POST', $params);
	}

	/**
	 * drop single user by ID
	 *
	 * @param string $contact_id - obtained by API
	 *
	 * @return mixed
	 */
	public function delete_contact($contact_id)
	{
		return $this->call('contacts/' . $contact_id, 'DELETE');
	}

	/**
	 * retrieve account from fields
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_account_from_fields($params = array())
	{
		return $this->call('from-fields?' . $this->setParams($params));
	}

	/**
	 * get all subscription confirmation subjects
	 * @return mixed
	 */
	public function get_subscription_confirmations_subject($lang = 'EN')
	{
		return $this->call('subscription-confirmations/subject/' . $lang);
	}

	/**
	 * get all subscription confirmation bodies
	 * @return mixed
	 */
	public function get_subscription_confirmations_body($lang = 'EN')
	{
		return $this->call('subscription-confirmations/body/' . $lang);
	}

	/**
	 * retrieve single custom field
	 *
	 * @param string $cs_id obtained by API
	 *
	 * @return mixed
	 */
	public function add_custom_field($params = array())
	{
		return $this->call('custom-fields', 'POST', $params);
	}

	/**
	 * drop single custom field
	 *
	 * @param string $custom_id obtained by API
	 *
	 * @return mixed
	 */
	public function delete_custom_field($custom_id)
	{
		return $this->call('custom-fields/' . $custom_id, 'DELETE');
	}

	/**
	 * retrieve account custom fields
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_custom_fields($params = array())
	{
		return $this->call('custom-fields?' . $this->setParams($params));
	}

	/**
	 * get single custom field
	 *
	 * @param string $custom_id obtained by API
	 *
	 * @return mixed
	 */
	public function get_custom_field($custom_id)
	{
		return $this->call('custom-fields/' . $custom_id, 'GET');
	}

	/**
	 * update single custom field
	 *
	 * @param string $custom_id obtained by API
	 * @param array  $params
	 *
	 * @return mixed
	 */
	public function update_custom_field($custom_id, $params = array())
	{
		return $this->call('custom-fields/' . $custom_id, 'POST', $params);
	}

	/**
	 * get single web form
	 *
	 * @param int $w_id
	 *
	 * @return mixed
	 */
	public function get_web_form($w_id)
	{
		return $this->call('webforms/' . $w_id);
	}

	/**
	 * retrieve all webforms
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_web_forms($params = array())
	{
		return $this->call('webforms?' . $this->setParams($params));
	}


	/**
	 * get single form
	 *
	 * @param int $form_id
	 *
	 * @return mixed
	 */
	public function get_form($form_id)
	{
		return $this->call('forms/' . $form_id);
	}

	/**
	 * retrieve all forms
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_forms($params = array())
	{
		return $this->call('forms?' . $this->setParams($params));
	}

	/**
	 * retrieve all forms
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_form_variants($form_id)
	{
		return $this->call('forms/' . $form_id . '/variants');
	}

	/**
	 * retrieve autoresponders
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function get_autoresponders($params = array())
	{
		return $this->call('autoresponders?' . $this->setParams($params));
	}

    /**
     * Retrieve shops.
     *
     * @return object
     */
    public function get_shops()
    {
        return $this->call('shops');
	}

    /**
     * Create new shop.
     *
     * @param array $params
     * @return object
     */
    public function add_shop($params)
    {
        return $this->call('shops', 'POST', $params);
    }

    /**
     * Create new cart.
     *
     * @param string $shopId
     * @param array $params
     *
     * @return object
     */
    public function add_new_cart($shopId, $params)
    {
        return $this->call('shops/'.$shopId.'/carts', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return object
     */
    public function add_new_purchase($shopId, $params)
    {
        return $this->call('shops/'.$shopId.'/purchase', 'POST', $params);
    }

    /**
     * Curl run request
     *
     * @param null $api_method
     * @param string $http_method
     * @param array $params
     *
     * @return object
     * @throws Exception
     */
	private function call($api_method = null, $http_method = 'GET', $params = array())
	{
		if (empty($api_method)) {
			return (object)array(
				'httpStatus' => '400',
				'code' => '1010',
				'codeDescription' => 'Error in external resources',
				'message' => 'Invalid api method'
			);
		}

		$params = json_encode($params);
		$url = $this->api_url . '/' . $api_method;

		$headers = array(
			'X-Auth-Token: api-key ' . $this->api_key,
			'Content-Type: application/json',
			'User-Agent: PHP GetResponse client 0 . 0 . 1',
			'X-APP-ID: 71609442-d357-47ea-8c7e-ebc36a6b1a7d'
		);

		// for GetResponse 360
		if (isset($this->domain)) {
			$headers[] = 'X-Domain: ' . $this->domain;
		}

		//also as get method
		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_ENCODING       => 'gzip,deflate',
			CURLOPT_FRESH_CONNECT  => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_HEADER         => false,
			CURLOPT_HTTPHEADER     => $headers
		);

		if ($http_method == 'POST') {
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $params;
		}
		else {
			if ($http_method == 'DELETE') {
				$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			}
		}

		try {
			$curl = curl_init();
			curl_setopt_array($curl, $options);

			$response = json_decode(curl_exec($curl));

			$this->http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			curl_close($curl);

		} catch (Exception $e) {

		}

		if ( !empty($response->codeDescription)) {
			$error_response =
				sprintf('GetResponseIntegration: %s method failed: %s (%s) - json_response: %s', $api_method,
					$response->codeDescription, $response->code, json_encode($response));
			Mage::log($error_response, null, 'getresponse_error.log');
		}

		return (object)$response;
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	private function setParams($params = array())
	{
		$result = array();
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$result[$key] = $value;
			}
		}

		return http_build_query($result);
	}

}