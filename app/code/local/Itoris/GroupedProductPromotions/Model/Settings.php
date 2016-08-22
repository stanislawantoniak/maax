<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_GROUPEDPRODUCTPROMOTIONS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

  

class Itoris_GroupedProductPromotions_Model_Settings extends Varien_Object {

	/** @var Mage_Core_Model_Resource */
	private $resource;
	/** @var Varien_Db_Adapter_Pdo_Mysql */
	private $dbAdapter;

	private $tableView = 'itoris_groupedproductpromotions_rules_view';
	private $tableSettings = 'itoris_groupedproductpromotions_rules_settings';
	private $tableTextSettings = 'itoris_groupedproductpromotions_rules_settings_text';

	private $textOptions = array();

	private $checkboxOptions = array();

	private $scope;
	private $scopeId;
	private $viewId;
	private $productId = 0;
	private $isLoaded = false;
	private $settings = array(
		'by_default' => array(
			'enabled' => 1,
		),
	);
	private $scopeViewIds = array(
		'store'   => null,
		'website' => null,
		'default' => null
	);


	public function __construct() {
		$this->resource = Mage::getSingleton('core/resource');
		$this->dbAdapter = $this->resource->getConnection('core_write');
		$this->tableView = $this->resource->getTableName($this->tableView);
		$this->tableSettings = $this->resource->getTableName($this->tableSettings);
		$this->tableTextSettings = $this->resource->getTableName($this->tableTextSettings);
	}

	public function save($settings, $scope = 'default', $scopeId = 0, $productId = 0) {
		$this->scope = $this->dbAdapter->quote($scope);
		$this->scopeId = (int)$scopeId;
		$this->productId = (int)$productId;
		$this->setViewId();

		$this->deleteSettings();

		$newSettings = array();

		foreach($settings as $key => $value) {
			if (in_array($key, $this->checkboxOptions)) {
				$value = 1;
			} else {
				$value = isset($value['value']) ? $value['value'] : '';
			}
			if(!(isset($settings[$key]['use_parent']))  || $scope == 'default'){
				if ($key == 'password') {
					$value = base64_encode($value);
				}
				$newSettings[$key] = array('value' => $value, 'type' => 'default');
				if($this->isTextOption($key)){
					$newSettings[$key]['type'] = 'text';
				}
			}
		}

		foreach ($this->checkboxOptions as $key) {
			if (isset($settings[$key]) || isset($newSettings[$key])) {
				continue;
			}
			$newSettings[$key] = array(
				'value' => 0,
				'type'  => 'default',
			);
		}

		if (!empty($newSettings)) {
			$this->saveSettings($newSettings);
		}
		$this->scope = null;
		$this->scopeId = null;
	}

	/**
	 * Get view id for current scope if it exists in db, else write view id in db and get it
	 *
	 * @return int
	 */
	private function setViewId() {
		$this->viewId = (int)$this->dbAdapter->fetchOne("select view_id from {$this->tableView} where scope = {$this->scope} and scope_id = {$this->scopeId}");
		if (!$this->viewId) {
			$this->dbAdapter->query("insert into {$this->tableView} (scope, scope_id) values ({$this->scope}, {$this->scopeId})");
			$this->setViewId();
		}
		return $this->viewId;
	}

	/**
	 * Retrieve view id for scope view
	 *
	 * @return int
	 */
	public function getViewId() {
		if (!$this->viewId) {
			$this->setViewId();
		}
		return $this->viewId;
	}

	public function load($websiteId, $storeId, $productId = 0) {
		$websiteId = (int)$websiteId;
		$storeId = (int)$storeId;
		$this->productId = (int)$productId;
		$this->setScope($websiteId, $storeId);
		$productSql = $this->productId ? ' and s.product_id = ' . $this->productId : ' and s.product_id is null';
		$settings = $this->dbAdapter->fetchAll("
			SELECT e.scope, e.view_id, s.key, if(STRCMP(s.type, 'text'), s.value, t.value) as value
			FROM {$this->tableView} as e
			INNER JOIN {$this->tableSettings} as s
				ON e.view_id = s.view_id {$productSql}
			LEFT JOIN {$this->tableTextSettings} as t
				ON s.setting_id = t.setting_id
			WHERE (e.scope = 'default' and e.scope_id = 0)
			OR (e.scope = 'website' and e.scope_id = $websiteId)
			OR (e.scope = 'store' and e.scope_id = $storeId)
		");
		if (count($settings)) {
			$this->isLoaded = true;
		}

		$this->saveSettingsIntoArray($settings);
		return $this;
	}

	private function saveSettingsIntoArray($settings) {
		foreach($settings as $value) {
			$this->settings[$value['scope']][$value['key']] = $value['value'];
			if (!$this->scopeViewIds[$value['scope']]) {
				$this->scopeViewIds[$value['scope']] = $value['view_id'];
			}
		}
	}

	public function getViewIdForSetting($key) {
		if (isset($this->settings['store'][$key])) {
			return $this->scopeViewIds['store'];
		} elseif (isset($this->settings['website'][$key])) {
			return $this->scopeViewIds['website'];
		} elseif (isset($this->settings['default'][$key])) {
			return $this->scopeViewIds['default'];
		}

		return 0;
	}

	public function setScope($websiteId, $storeId) {
		if ($storeId) {
			$this->scope = $this->dbAdapter->quote('store');
			$this->scopeId = $storeId;
		} elseif ($websiteId) {
			$this->scope = $this->dbAdapter->quote('website');
			$this->scopeId = $websiteId;
		} else {
			$this->scope = $this->dbAdapter->quote('default');
			$this->scopeId = 0;
		}
	}

	public function __call($method, $args) {
		if (substr($method, 0, 3) == 'get') {
			$key = $this->_underscore(substr($method,3));
			if (isset($this->settings['store'][$key])) {
				return $this->settings['store'][$key];
			} elseif (isset($this->settings['website'][$key])) {
				return $this->settings['website'][$key];
			} elseif (isset($this->settings['default'][$key])) {
				return $this->settings['default'][$key];
			}
			if (!$this->isLoaded && isset($this->settings['by_default'][$key])) {
				return $this->settings['by_default'][$key];
			}
			return $this->getData($key, isset($args[0]) ? $args[0] : null);
		} else {
			parent::__call($method,$args);
		}
	}

	public function getSettingsValue($key) {
		if ($this->isLoaded) {
			return $this->__call('get' . $key, array());
		} else {
			return isset($this->settings['by_default'][$key]) ? $this->settings['by_default'][$key] : null;
		}
	}

	/**
	 * Check setting value is value of parent scope view
	 *
	 * @param $key
	 * @param bool $checkWebsite
	 * @return bool
	 */
	public function isParentValue($key, $checkWebsite = false) {
		if (isset($this->settings['store'][$key])) {
			return false;
		}
		if ($checkWebsite) {
			if (isset($this->settings['website'][$key])) {
				return false;
			}
		}
		return true;
	}

	private function deleteSettings() {
		$productSql = $this->productId ? ' and product_id = ' . $this->productId : ' and product_id is null';
		$this->dbAdapter->query("DELETE FROM {$this->tableSettings} WHERE `view_id`={$this->viewId} {$productSql}");
	}

	private function saveSettings($settings) {
		$settingsValues = array();
		$textValues = array();
		foreach($settings as $key => $values){
			$value = 0;
			$type = $values['type'];
			if ($type != 'text') {
				$value = (int)$values['value'];
			} else {
				$textValues[$key] = $this->dbAdapter->quote($values['value']);
			}
			$settingsValues[] =  "($this->viewId, ". $this->dbAdapter->quote($key) .", $value, ". ($this->productId ? $this->productId : 'null') . ", " . $this->dbAdapter->quote($type) . ")";

		}
		$settingsValues = implode(',', $settingsValues);
		$this->dbAdapter->query("INSERT INTO {$this->tableSettings} (`view_id`, `key`, `value`, `product_id`, `type`)
								VALUES {$settingsValues}");
		if (!empty($textValues)) {
			$this->saveTextSettings($textValues);
		}
	}

	private function saveTextSettings($values) {
		$productSql = $this->productId ? (' and product_id = ' . $this->productId) : ' and product_id is null';
		$textSettings = $this->dbAdapter->fetchAll("SELECT `setting_id`, `view_id`, `key` FROM {$this->tableSettings}
									WHERE `type` = 'text' and `view_id` = {$this->viewId} {$productSql}
		");

		$textValues = array();
		foreach($textSettings as $setting){
			$key = $setting['key'];
			$textValues[] = "( {$setting['setting_id']}, {$values[$key]})";
		}
		$textValues = implode(',', $textValues);

		$this->dbAdapter->query("INSERT INTO {$this->tableTextSettings} (`setting_id`, `value`)
								VALUES {$textValues}
		");
	}

	public function _isValid($settings) {
		$errors = array();

		if (empty($errors)) {
			return true;
		}

		return $errors;
	}

	/**
	 * Check setting value type is text
	 *
	 * @param $key
	 * @return bool
	 */
	private function isTextOption($key) {
		return in_array($key, $this->textOptions);
	}
}
?>