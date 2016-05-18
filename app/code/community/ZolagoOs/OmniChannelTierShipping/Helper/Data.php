<?php

class ZolagoOs_OmniChannelTierShipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getV2RateObj($isVendor=false)
    {
        return $this->_getV2RateObj(ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2, $isVendor);
    }
    public function getV2SimpleRateObj($isVendor=false)
    {
        return $this->_getV2RateObj(ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2_SIMPLE, $isVendor);
    }
    public function getV2SimpleCondRateObj($isVendor=false)
    {
        return $this->_getV2RateObj(ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2_SIMPLE_COND, $isVendor);
    }
    protected function _getV2RateObj($type, $isVendor=false)
    {
        return Mage::getModel('udtiership/rate', array('__use_rate_setup'=>$type, '__use_vendor'=>$isVendor));
    }

    public function getV2Rates($deliveryType, $extraCond=array())
    {
        $useVendor = !empty($extraCond['__use_vendor']);
        unset($extraCond['__use_vendor']);
        return $this->_getV2Rates($this->getV2RateObj($useVendor), $deliveryType, $extraCond);
    }
    public function getV2SimpleRates($deliveryType, $extraCond=array())
    {
        $useVendor = !empty($extraCond['__use_vendor']);
        unset($extraCond['__use_vendor']);
        return $this->_getV2Rates($this->getV2SimpleRateObj($useVendor), $deliveryType, $extraCond);
    }
    public function getV2SimpleCondRates($deliveryType, $extraCond=array())
    {
        $useVendor = !empty($extraCond['__use_vendor']);
        unset($extraCond['__use_vendor']);
        return $this->_getV2Rates($this->getV2SimpleCondRateObj($useVendor), $deliveryType, $extraCond);
    }

    public function getVendorV2Rates($vendor, $deliveryType, $extraCond=array())
    {
        if (!is_array($extraCond)) {
            $extraCond = array();
        }
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $extraCond['__use_vendor'] = true;
        $extraCond['vendor_id=?'] = $vendor->getId();
        return $this->getV2Rates($deliveryType, $extraCond);
    }
    public function getVendorV2SimpleRates($vendor, $deliveryType, $extraCond=array())
    {
        if (!is_array($extraCond)) {
            $extraCond = array();
        }
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $extraCond['__use_vendor'] = true;
        $extraCond['vendor_id=?'] = $vendor->getId();
        return $this->getV2SimpleRates($deliveryType, $extraCond);
    }
    public function getVendorV2SimpleCondRates($vendor, $deliveryType, $extraCond=array())
    {
        if (!is_array($extraCond)) {
            $extraCond = array();
        }
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $extraCond['__use_vendor'] = true;
        $extraCond['vendor_id=?'] = $vendor->getId();
        return $this->getV2SimpleCondRates($deliveryType, $extraCond);
    }

    protected function _getV2Rates($rateObj, $deliveryType, $extraCond=array())
    {
        $tsHlp = Mage::helper('udtiership');
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $conn = $rHlp->getWriteConnection();
        $rateTable = $rateObj->getResource()->getMainTable();
        $fieldsData = $rHlp->myPrepareDataForTable($rateTable, array(), true);
        $fields = array_keys($fieldsData);
        $extraCondition = array();
        if (!empty($deliveryType)) {
            $extraCondition[] = $conn->quoteInto('delivery_type_id=?', $deliveryType);
        }
        if (is_array($extraCond)) {
            foreach ($extraCond as $__ecKey=>$__ecValue) {
                if (is_int($__ecKey)) {
                    if ($__ecValue instanceof Zend_Db_Expr) {
                        $__ecValue = $__ecValue->__toString();
                    }
                } else {
                    $__ecValue = $conn->quoteInto($__ecKey, $__ecValue);
                }
                $extraCondition[] = $__ecValue;
            }
        }
        $extraCondition = empty($extraCondition) ? '' : '( '.implode(' ) AND ( ', $extraCondition).' )';
        $existing = $rHlp->loadDbColumns($rateObj, true, $fields, $extraCondition);
        uasort($existing, array($this, 'sortBySortOrder'));
        foreach ($existing as $__k=>&$__v) {
            foreach (array('cost_extra','additional_extra','handling_extra','condition') as $__encKey) {
                if (isset($__v[$__encKey]) && !is_array($__v[$__encKey])) {
                    $__encValue = Mage::helper('udropship')->unserialize($__v[$__encKey]);
                    if (is_array($__encValue)) {
                        usort($__encValue, array($this, 'sortBySortOrder'));
                    }
                    $__v[$__encKey] = $__encValue;
                }
            }
        }
        return $existing;
    }

    public function saveVendorV2Rates($vendor)
    {
        $vId = $vendor->getId();
        $value = $vendor->getData('tiership_v2_rates');
        if (!empty($vId) && is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $saveHelper = new Varien_Object(array(
                'rate_obj' => $this->getV2RateObj(),
                'existing_data' => $this->getVendorV2Rates($vendor, $dtId),
                'fields_to_implode' => array('customer_shipclass_id','category_ids'),
                'fields_to_encode' => array('cost_extra','additional_extra','handling_extra')
            ));
            $this->_saveVendorV2Rates($vendor->getId(), $value, $saveHelper);
        }
        return $this;
    }
    public function saveVendorV2SimpleRates($vendor)
    {
        $vId = $vendor->getId();
        $value = $vendor->getData('tiership_v2_simple_rates');
        if (!empty($vId) && is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $saveHelper = new Varien_Object(array(
                'rate_obj' => $this->getV2SimpleRateObj(),
                'existing_data' => $this->getVendorV2SimpleRates($vendor, $dtId),
                'fields_to_implode' => array('customer_shipclass_id')
            ));
            $this->_saveVendorV2Rates($vendor->getId(), $value, $saveHelper);
        }
        return $this;
    }
    public function saveVendorV2SimpleCondRates($vendor)
    {
        $vId = $vendor->getId();
        $value = $vendor->getData('tiership_v2_simple_cond_rates');
        if (!empty($vId) && is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $saveHelper = new Varien_Object(array(
                'rate_obj' => $this->getV2SimpleCondRateObj(),
                'existing_data' => $this->getVendorV2SimpleCondRates($vendor, $dtId),
                'fields_to_implode' => array('customer_shipclass_id'),
                'fields_to_encode' => array('condition')
            ));
            $this->_saveVendorV2Rates($vendor->getId(), $value, $saveHelper);
        }
        return $this;
    }
    protected function _saveVendorV2Rates($vId, $value, $saveHelper)
    {
        if (!empty($vId) && is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $value = $value[$dtId];
            unset($value['$ROW']);
            unset($value['$$ROW']);
            $tsHlp = Mage::helper('udtiership');
            $rHlp = Mage::getResourceSingleton('udropship/helper');
            $rateObj = $saveHelper->getRateObj();
            $conn = $rHlp->getWriteConnection();
            $rateTable = $rateObj->getResource()->getMainTable();
            $fieldsData = $rHlp->myPrepareDataForTable($rateTable, array(), true);
            $fields = array_keys($fieldsData);
            $fieldsDataExId = $fieldsData;
            unset($fieldsDataExId['rate_id']);
            $fieldsExId = array_keys($fieldsDataExId);

            $existing = $saveHelper->getExistingData();
            if (!is_array($existing)) {
                $existing = array();
            }

            $insert = array();
            foreach ($value as $v) {
                $v['delivery_type_id'] = $dtId;
                $v['vendor_id'] = $vId;
                if (!empty($v['rate_id'])) {
                    unset($existing[$v['rate_id']]);
                } else {
                    $v['rate_id'] = null;
                }
                $fieldsToImplode = $saveHelper->getFieldsToImplode();
                if (!empty($fieldsToImplode) && is_array($fieldsToImplode)) {
                    foreach ($fieldsToImplode as $__sk) {
                        $v[$__sk] = isset($v[$__sk])&&is_array($v[$__sk]) ? implode(',', $v[$__sk]) : @$v[$__sk];
                    }
                }
                $fieldsToEncode = $saveHelper->getFieldsToEncode();
                if (!empty($fieldsToEncode) && is_array($fieldsToEncode)) {
                    foreach ($fieldsToEncode as $__sk) {
                        if (isset($v[$__sk])&&is_array($v[$__sk])) {
                            unset($v[$__sk]['$ROW']);
                            unset($v[$__sk]['$$ROW']);
                            usort($v[$__sk], array($this, 'sortBySortOrder'));
                            $v[$__sk] = Mage::helper('udropship')->serialize($v[$__sk]);
                        }
                    }
                }
                $insert[] = $rHlp->myPrepareDataForTable($rateTable, $v, true);
            }
            if (!empty($insert)) {
                $rHlp->multiInsertOnDuplicate($rateTable, $insert, array_combine($fieldsExId,$fieldsExId));
            }
            if (!empty($existing)) {
                $conn->delete($rateTable, array('rate_id in (?)'=>array_keys($existing)));
            }
        }
        return $this;
    }

    public function saveV2Rates($value)
    {
        if (is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $saveHelper = new Varien_Object(array(
                'rate_obj' => $this->getV2RateObj(),
                'existing_data' => $this->getV2Rates($dtId),
                'fields_to_implode' => array('customer_shipclass_id','category_ids','vendor_shipclass_id'),
                'fields_to_encode' => array('cost_extra','additional_extra','handling_extra')
            ));
            $this->_saveV2Rates($value, $saveHelper);
        }
        return $this;
    }
    public function saveV2SimpleRates($value)
    {
        if (is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $saveHelper = new Varien_Object(array(
                'rate_obj' => $this->getV2SimpleRateObj(),
                'existing_data' => $this->getV2SimpleRates($dtId),
                'fields_to_implode' => array('customer_shipclass_id')
            ));
            $this->_saveV2Rates($value, $saveHelper);
        }
        return $this;
    }
    public function saveV2SimpleCondRates($value)
    {
        if (is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $saveHelper = new Varien_Object(array(
                'rate_obj' => $this->getV2SimpleCondRateObj(),
                'existing_data' => $this->getV2SimpleCondRates($dtId),
                'fields_to_implode' => array('customer_shipclass_id'),
                'fields_to_encode' => array('condition')
            ));
            $this->_saveV2Rates($value, $saveHelper);
        }
        return $this;
    }
    protected function _saveV2Rates($value, $saveHelper)
    {
        if (is_array($value) && ($dtId = @$value['delivery_type']) && !empty($value[$dtId]) && is_array($value[$dtId])) {
            $value = $value[$dtId];
            unset($value['$ROW']);
            unset($value['$$ROW']);
            $tsHlp = Mage::helper('udtiership');
            $rHlp = Mage::getResourceSingleton('udropship/helper');
            $rateObj = $saveHelper->getRateObj();
            $conn = $rHlp->getWriteConnection();
            $rateTable = $rateObj->getResource()->getMainTable();
            $fieldsData = $rHlp->myPrepareDataForTable($rateTable, array(), true);
            $fields = array_keys($fieldsData);
            $fieldsDataExId = $fieldsData;
            unset($fieldsDataExId['rate_id']);
            $fieldsExId = array_keys($fieldsDataExId);

            $existing = $saveHelper->getExistingData();
            if (!is_array($existing)) {
                $existing = array();
            }

            $insert = array();
            foreach ($value as $v) {
                $v['delivery_type_id'] = $dtId;
                if (!empty($v['rate_id'])) {
                    unset($existing[$v['rate_id']]);
                } else {
                    $v['rate_id'] = null;
                }
                $fieldsToImplode = $saveHelper->getFieldsToImplode();
                if (!empty($fieldsToImplode) && is_array($fieldsToImplode)) {
                    foreach ($fieldsToImplode as $__sk) {
                        $v[$__sk] = isset($v[$__sk])&&is_array($v[$__sk]) ? implode(',', $v[$__sk]) : @$v[$__sk];
                    }
                }
                $fieldsToEncode = $saveHelper->getFieldsToEncode();
                if (!empty($fieldsToEncode) && is_array($fieldsToEncode)) {
                    foreach ($fieldsToEncode as $__sk) {
                        if (isset($v[$__sk]) && is_array($v[$__sk])) {
                            unset($v[$__sk]['$ROW']);
                            unset($v[$__sk]['$$ROW']);
                            usort($v[$__sk], array($this, 'sortBySortOrder'));
                            $v[$__sk] = Mage::helper('udropship')->serialize($v[$__sk]);
                        }
                    }
                }
                $insert[] = $rHlp->myPrepareDataForTable($rateTable, $v, true);
            }
            if (!empty($insert)) {
                $rHlp->multiInsertOnDuplicate($rateTable, $insert, array_combine($fieldsExId,$fieldsExId));
            }
            if (!empty($existing)) {
                $conn->delete($rateTable, array('rate_id in (?)'=>array_keys($existing)));
            }
        }
        return $this;
    }

    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }

    public function useRatesSetup()
    {
        return Mage::getStoreConfig('carriers/udtiership/use_simple_rates');
    }

    public function isV2Rates($value=null)
    {
        $value = $value !== null ? $value : $this->useRatesSetup();
        return in_array($value, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2,
            ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2_SIMPLE,
            ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2_SIMPLE_COND,
        ));
    }
    public function isV2SimpleRates($value=null)
    {
        $value = $value !== null ? $value : $this->useRatesSetup();
        return $value == ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2_SIMPLE;
    }
    public function isV2SimpleConditionalRates($value=null)
    {
        $value = $value !== null ? $value : $this->useRatesSetup();
        return $value == ZolagoOs_OmniChannelTierShipping_Model_Source::USE_RATES_V2_SIMPLE_COND;
    }
    public function processTiershipRates($vendor, $serialize=false)
    {
        $tiershipRates = $vendor->getData('tiership_rates');
        if ($serialize) {
            if (is_array($tiershipRates)) {
                $tiershipRates = serialize($tiershipRates);
            }
        } else {
            if (is_string($tiershipRates)) {
                $tiershipRates = unserialize($tiershipRates);
            }
            if (!is_array($tiershipRates)) {
                $tiershipRates = array();
            }
        }
        $vendor->setData('tiership_rates', $tiershipRates);
    }
    public function processTiershipSimpleRates($vendor, $serialize=false)
    {
        $tiershipRates = $vendor->getData('tiership_simple_rates');
        if ($serialize) {
            if (is_array($tiershipRates)) {
                $tiershipRates = serialize($tiershipRates);
            }
        } else {
            if (is_string($tiershipRates)) {
                $tiershipRates = unserialize($tiershipRates);
            }
            if (!is_array($tiershipRates)) {
                $tiershipRates = array();
            }
        }
        $vendor->setData('tiership_simple_rates', $tiershipRates);
    }
    public function getVendorTiershipRates($vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $value = $vendor->getTiershipRates();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }
    public function getVendorTiershipSimpleRates($vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $value = $vendor->getTiershipSimpleRates();
        if (is_string($value)) {
            $value = unserialize($value);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }
    public function getGlobalTierShipConfig()
    {
        $value = Mage::getStoreConfig('carriers/udtiership/rates');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }
    public function getGlobalTierShipConfigSimple()
    {
        $value = Mage::getStoreConfig('carriers/udtiership/simple_rates');
        if (is_string($value)) {
            $value = unserialize($value);
        }
        return $value;
    }
    public function getRateId($path)
    {
        return implode(':', $path);
    }
    public function getRateToUse($tierRates, $globalTierRates, $catId, $vscId, $cscId, $field)
    {
        $_curClassId = $this->getRateId(array($catId, $vscId, $cscId));
        return isset($tierRates[$_curClassId]) && isset($tierRates[$_curClassId][$field]) && $tierRates[$_curClassId][$field] !== ''
            ? $tierRates[$_curClassId][$field]
            : (isset($tierRates[$catId]) && isset($tierRates[$catId][$field]) && $tierRates[$catId][$field] !== ''
                ? $tierRates[$catId][$field]
                : (isset($globalTierRates[$_curClassId]) && isset($globalTierRates[$_curClassId][$field]) && $globalTierRates[$_curClassId][$field] !== ''
                    ? $globalTierRates[$_curClassId][$field]
                    : @$globalTierRates[$catId][$field]
        ));
    }

    protected $_topCats;
    public function getTopCategories()
    {
        if (null === $this->_topCats) {
            $cHlp = Mage::helper('udropship/catalog');
            $topCatId = Mage::getStoreConfig('carriers/udtiership/tiered_category_parent');
            $topCat = Mage::getModel('catalog/category')->load($topCatId);
            if (!$topCat->getId()) {
                $topCat = $cHlp->getStoreRootCategory();
            }
            $this->_topCats = $cHlp->getCategoryChildren(
                $topCat
            );
        }
        return $this->_topCats;
    }

    public function getFallbackRateValue($type, $store=null)
    {
        $cfgKey = sprintf('carriers/udtiership/fallback_rate_%s', $type);
        $cfgVal = Mage::getStoreConfig($cfgKey, $store);
        return $cfgVal;
    }

    public function isMultiplyCalculationMethod($store=null)
    {
        return $this->_isMultiplyCalculationMethod(
            Mage::getStoreConfig('carriers/udtiership/calculation_method', $store)
        );
    }

    protected function _isMultiplyCalculationMethod($calcMethod)
    {
        return in_array($calcMethod, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_MULTIPLY_FIRST,
        ));
    }

    public function isSumCalculationMethod($store=null)
    {
        return $this->_isSumCalculationMethod(
            Mage::getStoreConfig('carriers/udtiership/calculation_method', $store)
        );
    }

    protected function _isSumCalculationMethod($calcMethod)
    {
        return in_array($calcMethod, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_SUM_FIRST_ADDITIONAL,
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_SUM_FIRST,
        ));
    }

    public function isMaxCalculationMethod($store=null)
    {
        return $this->_isMaxCalculationMethod(
            Mage::getStoreConfig('carriers/udtiership/calculation_method', $store)
        );
    }

    protected function _isMaxCalculationMethod($calcMethod)
    {
        return in_array($calcMethod, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_MAX_FIRST_ADDITIONAL,
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_MAX_FIRST,
        ));
    }

    public function getCalculationMethod($store=null)
    {
        return Mage::getStoreConfig('carriers/udtiership/calculation_method', $store);
    }
    public function getFallbackLookupMethod($store=null)
    {
        return Mage::getStoreConfig('carriers/udtiership/fallback_lookup', $store);
    }

    public function useAdditional($store=null)
    {
        return $this->_useAdditional(
            Mage::getStoreConfig('carriers/udtiership/calculation_method', $store)
        );
    }

    public function isUseAdditional($calcMethod)
    {
        return $this->_useAdditional($calcMethod);
    }
    protected function _useAdditional($calcMethod)
    {
        return !in_array($calcMethod, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_MULTIPLY_FIRST,
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_SUM_FIRST,
            ZolagoOs_OmniChannelTierShipping_Model_Source::CM_MAX_FIRST
        ));
    }

    public function usePercentHandling($store=null)
    {
        return $this->_percentHandling(
            Mage::getStoreConfig('carriers/udtiership/handling_apply_method', $store)
        );
    }

    protected function _percentHandling($handling)
    {
        return in_array($handling, array(
            'percent',
        ));
    }

    public function useFixedHandling($store=null)
    {
        return $this->_fixedHandling(
            Mage::getStoreConfig('carriers/udtiership/handling_apply_method', $store)
        );
    }

    protected function _fixedHandling($handling)
    {
        return in_array($handling, array(
            'fixed',
        ));
    }

    public function useMaxFixedHandling($store=null)
    {
        return $this->_maxFixedHandling(
            Mage::getStoreConfig('carriers/udtiership/handling_apply_method', $store)
        );
    }

    protected function _maxFixedHandling($handling)
    {
        return in_array($handling, array(
            'fixed_max',
        ));
    }

    protected function _maxHandling($handling)
    {
        return in_array($handling, array(
            'fixed_max',
        ));
    }

    public function useHandling($store=null)
    {
        return $this->_useHandling(
            Mage::getStoreConfig('carriers/udtiership/handling_apply_method', $store)
        );
    }

    public function isUseHandling($applyMethod)
    {
        return $this->_useHandling($applyMethod);
    }
    protected function _useHandling($applyMethod)
    {
        return !$this->isNoneValue($applyMethod);
    }

    public function isShowPerVendorBaseRate($calcType)
    {
        return $this->_isCtBasePlusZone($calcType);
    }
    public function isCtBasePlusZone($calcType)
    {
        return $this->_isCtBasePlusZone($calcType);
    }
    protected function _isCtBasePlusZone($calcType)
    {
        return in_array($calcType, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CT_BASE_PLUS_ZONE_FIXED,
            ZolagoOs_OmniChannelTierShipping_Model_Source::CT_BASE_PLUS_ZONE_PERCENT,
        ));
    }
    public function getCalculationType($type, $store=null)
    {
        $cfgKey = sprintf('carriers/udtiership/%s_calculation_type', $type);
        $cfgVal = Mage::getStoreConfig($cfgKey, $store);
        return $cfgVal;
    }

    public function isCtCostBasePlusZone($store=null)
    {
        return $this->isCtBasePlusZone(
            $this->getCalculationType('cost', $store)
        );
    }
    public function isCtAdditionalBasePlusZone($store=null)
    {
        return $this->isCtBasePlusZone(
            $this->getCalculationType('additional', $store)
        );
    }
    public function isCtHandlingBasePlusZone($store)
    {
        return $this->isCtBasePlusZone(
            $this->getCalculationType('handling', $store)
        );
    }

    public function isCtCustomPerCustomerZone($type, $store=null)
    {
        return $this->_isCtCustomPerCustomerZone(
            $this->getCalculationType($type, $store)
        );
    }
    protected function _isCtCustomPerCustomerZone($calcType)
    {
        return in_array($calcType, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CT_SEPARATE,
        ));
    }

    public function isCtPercentPerCustomerZone($type, $store=null)
    {
        return $this->_isCtPercentPerCustomerZone(
            $this->getCalculationType($type, $store)
        );
    }
    public function isUseCtPercentPerCustomerZone($calcType)
    {
        return $this->_isCtPercentPerCustomerZone($calcType);
    }
    protected function _isCtPercentPerCustomerZone($calcType)
    {
        return in_array($calcType, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CT_BASE_PLUS_ZONE_PERCENT,
        ));
    }
    public function isCtFixedPerCustomerZone($type, $store=null)
    {
        return $this->_isCtFixedPerCustomerZone(
            $this->getCalculationType($type, $store)
        );
    }
    public function isUseCtFixedPerCustomerZone($calcType)
    {
        return $this->_isCtFixedPerCustomerZone($calcType);
    }
    protected function _isCtFixedPerCustomerZone($calcType)
    {
        return in_array($calcType, array(
            ZolagoOs_OmniChannelTierShipping_Model_Source::CT_BASE_PLUS_ZONE_FIXED,
        ));
    }

    public function getProductAttribute($key, $store=null)
    {
        $cfgKey = sprintf('carriers/udtiership/rate_%s_attribute', $key);
        $cfgVal = Mage::getStoreConfig($cfgKey, $store);
        return $cfgVal;
    }

    public function getApplyMethod($method, $store=null)
    {
        $cfgKey = sprintf('carriers/udtiership/%s_apply_method', $method);
        $cfgVal = Mage::getStoreConfig($cfgKey, $store);
        return $cfgVal;
    }

    public function isApplyMethodPercent($method, $store=null)
    {
        return $this->isPercentValue(
            $this->getApplyMethod($method, $store)
        );
    }

    public function isApplyMethodNone($method, $store=null)
    {
        return $this->isNoneValue(
            $this->getApplyMethod($method, $store)
        );
    }

    public function isPercentValue($type)
    {
        return in_array($type, array(
            'percent',
        ));
    }
    public function isNoneValue($type)
    {
        return in_array($type, array(
            'none',
        ));
    }

    public function getVendorEditUrl()
    {
        if ($this->isV2Rates()) {
            return Mage::app()->getStore()->getUrl('udtiership/vendor/v2rates');
        } elseif (Mage::getStoreConfigFlag('carriers/udtiership/use_simple_rates')) {
            return Mage::app()->getStore()->getUrl('udtiership/vendor/simplerates');
        } else {
            return Mage::app()->getStore()->getUrl('udtiership/vendor/rates');
        }
    }
    
    /**
     * get shipping type code by po udropship_method
     * @param 
     * @return 
     */
     public function getCodeByPoMethod($method) {
         $id = explode('_',$method,2);
         $out = null;
         if (!empty($id[1])) {
             $model = Mage::getModel('udtiership/deliveryType')->load($id[1]);
             if ($model->getId()) {
                 $out = $model->getDeliveryCode();
             }
         }
         return $out;
     }

}
