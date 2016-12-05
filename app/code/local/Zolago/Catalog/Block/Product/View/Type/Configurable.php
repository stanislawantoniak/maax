<?php

class Zolago_Catalog_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_salableCount = 0;
    protected $_config = null;
    protected function _prepareConfig() {
        $return = Mage::helper("core")->jsonDecode($this->getJsonConfigData());
        $currentProduct = $this->getProduct();
        $strikeoutType = $currentProduct->getData('campaign_strikeout_price_type');

        $attributes = $return['attributes'];

        foreach($attributes as $keyAttr=>$attribute) {
            if(is_array($attribute['options'])) {
                //add info about product is salable
                foreach($attribute['options'] as $keyValue=>$value) {
                    if ($return['attributes'][$keyAttr]['options'][$keyValue]['is_salable'] =
                                   $this->getIsOptionSalable($attribute['code'], $value['id'])) {
                        $this->_salableCount++;
                    }
                }
                //if product is in campaign (promo or sale) and strikeout price type is msrp
                //the old price (strikeout price) need to be msrp, don't need delta's
                if (Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_MSRP_PRICE == $strikeoutType) {
                    foreach($attribute['options'] as $keyValue=>$value) {
                        $return['attributes'][$keyAttr]['options'][$keyValue]['oldPrice'] = "0";
                    }
                }
            }
        }

        //if product is in campaign (promo or sale) and strikeout price type is msrp
        //the old price (strikeout price) need to be msrp, don't need delta's
        $campaignId = (int)$currentProduct->getData("campaign_regular_id");
        $flag = $currentProduct->getData("product_flag");
        if (Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_MSRP_PRICE == $strikeoutType
                || (empty($campaignId) &&  $flag > 0)

           ) {
            $return['oldPrice'] = '' . (float) $currentProduct->getStrikeoutPrice();
        }

        $this->_config = $return;
    }

    /**
     * number of salable sizes
     * @return int
     */
    public function getSalableCount() {
        if (is_null($this->_config)) {
            $this->_prepareConfig();
        }
        return $this->_salableCount;
    }
    /**
     * 1) Add is salable flag to product option
     * Flag is positive of any option-valued product is salable
     * 2) Update strikeout price if product in campaign (promo or sale)
     * @return array
     */
    public function getJsonConfig() {
        $config = $this->getConfig();
        return Mage::helper("core")->jsonEncode($config);
    }

    public function getConfig() {
        if (is_null($this->_config)) {
            $this->_prepareConfig();
        }
        return $this->_config;
    }

    /**
     * @param string $attributeId
     * @param int $attributeValue
     */
    public function getIsOptionSalable($attributeCode, $attributeValue) {
        foreach($this->getAllowProducts() as $product) {
            /* @var $product Mage_Catalog_Model_Product */
            if($product->getData($attributeCode)==$attributeValue) {
                if($product->isSalable()) {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Add not se
     * @return array
     */
    public function getAllowProducts()
    {
        if(!$this->getIncludeNotSalable()) {
            return parent::getAllowProducts();
        }

        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                           ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                // Set all enabled products
                if($this->getProduct($product)->getStatus()!=Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    continue;
                }
                $products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    /**
     * Should include not salable products?
     * @return boolean
     */
    public function getIncludeNotSalable() {
        // Do not use it in admin
        if(Mage::app()->getStore()->isAdmin()) {
            return false;
        }

        return (bool)(int)Mage::getStoreConfig("cataloginventory/options/include_not_salable");
    }

    /**
     * Composes configuration for js
     *
     * @return string
     */
    public function getJsonConfigData()
    {
        $attributes = array();
        $options    = array();
        $minQty    = array();
        $maxQty    = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        $currentProduct = $this->getProduct();

        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }

        foreach ($this->getAllowProducts() as $product) {
            $productId = $product->getId();
            $inventory = Mage::getModel('cataloginventory/stock_item')
                         ->loadByProduct($product);

            $maxQtyData = (int)$inventory->getQty();
            if (!!$inventory->getBackorders()) {
                $maxQtyData = (int)$inventory->getMaxSaleQty();
            }

            $minQtyData = (int)$inventory->getMinSaleQty();
            if (!$minQtyData) {
                $minQtyData = 1;
            }
            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
                $minQty[$attributeValue] = (int)$minQtyData;
                $maxQty[$attributeValue] = $maxQtyData;
            }
        }

        $this->_resPrices = array(
                                $this->_preparePrice($currentProduct->getFinalPrice())
                            );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();

            $attributOptions = $productAttribute->getSource()->getAllOptions(false);
            $optionPositions = array();
            foreach ($attributOptions as $i => $_option) {
                $optionPositions[$_option['value']] = $i;
            }


            $info = array(
                        'id'        => $productAttribute->getId(),
                        'code'      => $productAttribute->getAttributeCode(),
                        'label'     => $attribute->getLabel(),
                        'options'   => array()
                    );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }
                    // delivery info (if more that one, get only last)
                    $deliveryData = '';
                    foreach ($productsIndex as $pId) {
                        $productTmp = Mage::getModel('catalog/product')->load($pId);
                        $deliveryData = Mage::helper('zolagocatalog')->getStoreDeliveryHeadline($productTmp);
                    }
                    $info['options'][] = array(
                                             'id'        => $value['value_index'],
                                             'position' => $optionPositions[$value['value_index']],
                                             'label'     => $value['label'],
                                             'price'     => $configurablePrice,
                                             'oldPrice'  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                                             'maxQty'    => $maxQty[$value['value_index']],
                                             'minQty'    => $minQty[$value['value_index']],
                                             'products'  => $productsIndex,
                                             'delivery'  => $deliveryData,
                                         );
                    $optionPrices[] = $configurablePrice;
                }
            }

            $this->arraySortByColumn($info['options'], 'position');

            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getDefaultRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
                         'includeTax'        => $taxHelper->priceIncludesTax(),
                         'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
                         'showBothPrices'    => $taxHelper->displayBothPrices(),
                         'defaultTax'        => $defaultTax,
                         'currentTax'        => $currentTax,
                         'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
                     );

        $config = array(
                      'attributes'        => $attributes,
                      'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
                      'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
                      'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
                      'productId'         => $currentProduct->getId(),
                      'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
                      'taxConfig'         => $taxConfig
                  );

        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }
        $config = array_merge($config, $this->_getAdditionalConfig());
        return Mage::helper('core')->jsonEncode($config);
    }


    function arraySortByColumn(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = array();
        foreach ($arr as $key=> $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }


}

