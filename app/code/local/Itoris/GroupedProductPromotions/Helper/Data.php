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

  

class Itoris_GroupedProductPromotions_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $alias = 'grouped_products_promotions';

	public function isAdminRegistered() {
		try {
			return Itoris_Installer_Client::isAdminRegistered($this->getAlias());
		} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			return false;
		}
	}

	public function isRegisteredAutonomous($website = null) {
		return Itoris_Installer_Client::isRegisteredAutonomous($this->getAlias(), $website);
	}

	public function registerCurrentStoreHost($sn) {
		return Itoris_Installer_Client::registerCurrentStoreHost($this->getAlias(), $sn);
	}

	public function isRegistered($website) {
		return Itoris_Installer_Client::isRegistered($this->getAlias(), $website);
	}

	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Get store id by parameter from the request
	 *
	 * @return int
	 */
	public function getStoreId() {
		if (Mage::app()->getRequest()->getParam('store')) {
			return Mage::app()->getStore(Mage::app()->getRequest()->getParam('store'))->getId();
		}
		return 0;
	}

	/**
	 * Get website id by parameter from the request
	 *
	 * @return int
	 */
	public function getWebsiteId() {
		if (Mage::app()->getRequest()->getParam('website')) {
			return Mage::app()->getWebsite(Mage::app()->getRequest()->getParam('website'))->getId();
		}
		return 0;
	}

	/**
	 * Get settings
	 *
	 * @return Itoris_GroupedProductPromotions_Model_Settings
	 */
	public function getSettings($backend = false) {
		/** @var $settingsModel Itoris_GroupedProductPromotions_Model_Settings */
		$settingsModel = Mage::getSingleton('itoris_groupedproductpromotions/settings');
		$productId = 0;
		/*if (($product = Mage::registry('current_product')) && $product instanceof Mage_Catalog_Model_Product) {
			$productId = $product->getId();
		}*/
		if ($backend || !Mage::app()->getWebsite()->getId()) {
			$settingsModel->load($this->getWebsiteId(), $this->getStoreId(), $productId);
		} else {
			$settingsModel->load(Mage::app()->getWebsite()->getId(), Mage::app()->getStore()->getId(), $productId);
		}

		return $settingsModel;
	}

	public function getScopeData() {
		if ($this->getStoreId()) {
			return array(
				'scope'    => 'store',
				'scope_id' => $this->getStoreId(),
			);
		} elseif ($this->getWebsiteId()) {
			return array(
				'scope'    => 'website',
				'scope_id' => $this->getWebsiteId(),
			);
		} else {
			return array(
				'scope'    => 'default',
				'scope_id' => 0
			);
		}
	}

	public function isRegisteredFrontend() {
		return !Mage::app()->getStore()->isAdmin()
			&& $this->getSettings()->getEnabled()
			&& $this->isRegisteredAutonomous();
	}

	public function isRegisteredAdmin() {
		return Mage::app()->getStore()->isAdmin()
			&& $this->getSettings()->getEnabled()
			&& $this->isAdminRegistered();
	}

    public function getDate($dateOrigValue) {
        $dateOrig = new Zend_Date($dateOrigValue, Zend_Date::ISO_8601);
        $dateWithTimezone = new Zend_Date($dateOrig, Zend_Date::ISO_8601);
        $currentTimezone = Mage::app()->getLocale()->date()->getTimezone();
        if ($dateWithTimezone->getTimezone() != $currentTimezone) {
            $dateWithTimezone->setTimezone(Mage::app()->getLocale()->date()->getTimezone());
            $dateWithTimezone->setYear($dateOrig->getYear());
            $dateWithTimezone->setMonth($dateOrig->getMonth());
            $dateWithTimezone->setDay($dateOrig->getDay());
            $dateWithTimezone->setHour($dateOrig->getHour());
        }

        return $dateWithTimezone;
    }

    public function prepareDates($array, $dateFields) {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }

        return $array;
    }
	
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function prepareTierPrices(Mage_Catalog_Model_Product $product) {
        $product->setData('tier_price', null);
        $prices = array();
        if (($tierPrices = $product->getFormatedTierPrice()) && is_array($tierPrices)) {
            foreach ($tierPrices as $tierPrice) {
                if (isset($tierPrice['price_qty'])) {
                    $prices['qty'][] = (int)$tierPrice['price_qty'];
                    $prices[(int)$tierPrice['price_qty']] = $product->getTierPrice($tierPrice['price_qty']);
                }
            }
        }
        //$tier = $prices[$prices['qty'][count($prices['qty']) - 1]];
        return $prices;
    }

	public function getTierPrice(Mage_Catalog_Model_Product $product, $config) {
		$tierPrices = $this->prepareTierPrices($product);
		$tierPrice = 0;
		if (!empty($tierPrices) && !empty($config)) {
			$prices = $tierPrices;
			foreach ($prices['qty'] as $keyQty => $qty) {
				if ($keyQty == 0 && $config['qty'] < $qty) {
					$tierPrice = 0;
					break;
				}
				if ($keyQty != count($prices['qty']) - 1) {
					if ($config['qty'] >= $qty && $config['qty'] < $prices['qty'][$keyQty + 1]) {
						$tierPrice = $prices[$qty];
					}
				}  elseif ($config['qty'] >= $qty) {
					$tierPrice = $prices[$qty];
				}
			}
		}
		return $tierPrice;
	}
	
	public function getProductConfig(Mage_Catalog_Model_Product $product, $configs) {
		$config = array();
		foreach ($configs as $_config) {
			if ($product->getId() == $_config['product_id']) {
				$config = $_config;
				break;
			}
		}
		return $config;
	}

}

?>