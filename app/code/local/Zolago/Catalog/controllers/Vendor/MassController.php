<?php

class Zolago_Catalog_Vendor_MassController
    extends Zolago_Dropship_Controller_Vendor_Abstract {
    /**
     * Index
     */
    public function indexAction() {
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_saveHiddenColumns();

        $useLazyLoad = $this->getRequest()->getParam('uselazyload');

        if(isset($useLazyLoad)) {
            $this->loadLayout();

            $block = $this->getLayout()->createBlock("zolagocatalog/vendor_mass_grid", "vendor_mass_grid", array('area' => 'adminhtml'));

            $block->setTemplate('zolagocatalog/widget/grid/rowsblock.phtml');

            $this->getResponse()->setBody($block->toHtml());
        }
        else {
            $this->_renderPage(null, 'udprod_mass');
        }

    }
    protected function _saveHiddenColumns() {
        if ($this->getRequest()->isPost()) {
            $listColumns = $this->getRequest()->getParam('listColumn',array());
            $attributeSet = $this->getRequest()->getParam('attribute_set','');
            $hiddenColumns = $this->getRequest()->getParam('hideColumn',array());
            foreach ($hiddenColumns as $key=>$dummy) {
                unset($listColumns[$key]);
            }
            $session = Mage::getSingleton('udropship/session');

            $list = $session->getData('denyColumnList');
            if (!$list) {
                $list = array();
            }
            $list[$attributeSet] = $listColumns;
            $session->setData('denyColumnList',$list);
        }
    }
    public function saveAjaxAction() {
        $response = array();
        if($this->getRequest()->isPost()) {

            // Products Ids
            $productIds = array_unique(
                              explode(",", $this->getRequest()->getPost("product_ids", ""))
                          );
            // Attributes data array('code'=>'value',...)
            $attributesData = $this->getRequest()->getPost("attributes");
            // Attrbiute modes
            $attributesMode = $this->getRequest()->getPost("attributes_mode");
            // Attribure set
            $attributeSet = $this->_getAttributeSet();
            // Store scope
            $store = $this->_getStore();

            $helper = Mage::helper("zolagocatalog");

            $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

            try {
                if(!is_array($attributesData) || !count($attributesData) ||
                        !$attributeSet || !$attributeSet->getId() ||
                        !$store || !is_array($productIds) || !count($productIds)) {

                    throw new Mage_Core_Exception(
                        $helper->__("No required data passed")
                    );
                }
                if(!$this->_validateAttributes($attributesData, $attributeSet, $notMatched, $collection)) {
                    throw new Mage_Core_Exception(
                        $helper->__("There is problem with attribute premission (%s)", implode(",", $notMatched))
                    );
                }
                if(!$this->_validateProductIds($productIds, $attributeSet, $store)) {
                    throw new Mage_Core_Exception(
                        $helper->__("You are trying save not Your product(s).")
                    );
                }

                foreach ($attributesData as $attributeCode => $value) {

                    $attribute =$collection->getItemByColumnValue("attribute_code", $attributeCode);

                    if(!$attribute || !$attribute->getId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    // Prepare date fileds
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput    = new Zend_Filter_LocalizedToNormalized(array(
                                        'date_format' => $dateFormat
                                    ));
                            $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                                        'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
                                    ));
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                    elseif ($attribute->getFrontendInput() == 'multiselect') {
                        if (is_array($value)) {
                            $attributesData[$attributeCode] = implode(',', $value);
                        }

                        // Unset value if add mode active
                        if(isset($attributesMode[$attributeCode])) {
                            switch ($attributesMode[$attributeCode]) {
                            case "add":
                            case "sub":
                                Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addValueToMultipleAttribute(
                                    $productIds,
                                    $attribute,
                                    is_array($value) ? $value : array(),
                                    $store,
                                    $attributesMode[$attributeCode]
                                );
                                unset($attributesData[$attributeCode]);
                                break;
                            }
                        }
                    }
                }

                // Write attribs & make reindex
                Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, $attributesData, $store->getId());

                $response = array(
                                "status"=>1,
                                "content"=>array(
                                    "attributes"=>	array_keys($attributesData),
                                    "count"		=>	count($productIds)
                                )
                            );
            } catch(Mage_Core_Exception $e) {
                $response = array(
                                "status"=>0,
                                "content"=>$e->getMessage()
                            );
            } catch(Exception $e) {
                $response = array(
                                "status"=>0,
                                "content"=>$helper->__("Some error occured. Contact administrator.")
                            );
                Mage::logException($e);
            }
        } else {
            $response = array(
                            "status"=>0,
                            "content"=>Mage::helper("zolagocatalog")->__("Wrogn HTTP method")
                        );
        }
        // Send response
        $this->getResponse()->
        setBody(Zend_Json::encode($response))->
        setHeader('content-type', 'application/json');
    }


    public function gridAction() {
        $design = Mage::getDesign();
        $design->setArea("adminhtml");
        $this->loadLayout();
        $block = $this->getLayout()->createBlock("zolagocatalog/vendor_mass_grid");

        $this->getResponse()->setBody($block->toHtml());
    }

    public function massDeleteAction() {
        var_export($this->getRequest()->getParams());
    }

    /**
     * Update product(s) status action
     *
     */
    public function massStatusAction()
    {
        $productIds			= array_unique(explode(',', $this->getRequest()->getParam('product_ids', '')));
        $storeId			= (int)$this->getRequest()->getParam('store', 0);
        $attributeSet		= (int)$this->getRequest()->getParam('attribute_set', null);
        $status				= (int)$this->getRequest()->getParam('status');
        $staticFiltersCount	= (int)$this->getRequest()->getParam('staticFilters');
        $productReview		= (int)$this->getRequest()->getParam('review', null);

        $staticFilters		= $this->_getCurrentStaticFilterValues();
        $postParams			= array('store'=> $storeId, 'attribute_set' => $attributeSet, 'staticFilters' => $staticFiltersCount);
        $postParams			= array_merge($postParams, $staticFilters);

        $response = array();

        try {

            if ($productReview) {
                $this->_validateProductAttributes($productIds, $attributeSet, $storeId);
                $response = array(
                                "status"=>1,
                                "content"=>$this->__('Total of %d record(s) have been validated.', count($productIds))
                            );
            }

            $status = Mage::helper('zolagodropship')->getProductStatusForVendor($this->_getSession()->getVendor());
            $this->_validateMassStatus($productIds, $status);
            Mage::getSingleton('catalog/product_action')
            ->updateAttributes($productIds, array('status' => $status), $storeId);

            $response = array(
                            "status"=>1,
                            "content"=>$this->__('Total of %d record(s) have been updated.', count($productIds))
                        );
        }
        catch (Mage_Core_Model_Exception $e) {
            $response = array(
                            "status"=>0,
                            "content"=>Mage::helper("zolagocatalog")->__($e->getMessage())
                        );
        } catch (Mage_Core_Exception $e) {
            $response = array(
                            "status"=>0,
                            "content"=>Mage::helper("zolagocatalog")->__($e->getMessage())
                        );
        } catch (Exception $e) {
            $response = array(
                            "status"=>0,
                            "content"=>Mage::helper("zolagocatalog")->__($this->__('An error occurred while updating the product(s) status.'))
                        );
        }

        // Send response
        $this->getResponse()->
        setBody(Zend_Json::encode($response))->
        setHeader('content-type', 'application/json');
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @throws Mage_Core_Exception
     * @param  array $productIds
     * @param  int $status
     * @return void
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            if (!Mage::getModel('catalog/product')->isProductsHasSku($productIds)) {
                throw new Mage_Core_Exception(
                    $this->__('Some of the processed products have no SKU value defined. Please fill it prior to performing operations on these products.')
                );
            }
        }
    }

    /**
     * @param type $attributes
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    protected function _validateAttributes($attributes, $attributeSet, &$notMatched, &$collection) {

        $collection = Mage::getResourceModel("catalog/product_attribute_collection");
        /* @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */

        $collection->setAttributeSetFilter($attributeSet->getId());

        $collection->addFieldToFilter("grid_permission",
                                      Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION);

        $keys = array_keys($attributes);
        $collection->addFieldToFilter("attribute_code", array("in"=>$keys));
        $collection->addIsNotUniqueFilter();

        $notMatched = array();

        foreach($keys as $attributeCode) {
            if(!$collection->getItemByColumnValue("attribute_code", $attributeCode)) {
                $notMatched[] = $attributeCode;
            }
        }

        return count($notMatched)==0;
    }


    protected function _validateProductIds($productIds, $attributeSet, $store) {
        $collection = Mage::getResourceModel("catalog/product_collection");
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection->setFlag("skip_price_data", true);
        if($store->getId()) {
            $collection->setStoreId($store->getId());
        }
        $collection->addIdFilter($productIds);
        $collection->addAttributeToFilter("attribute_set_id", $attributeSet->getId());
        $collection->addAttributeToFilter("udropship_vendor", $this->_getSession()->getVendor()->getId());
        $collection->addAttributeToFilter("visibility", array("in"=>array(
                                              Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                                              Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                                              Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                                          )));
        return count(array_diff($productIds, $collection->getAllIds()))==0;
    }


    /**
     * @return Mage_Core_Model_Store
     */
    protected function _getStore() {
        $storeId = Mage::app()->getRequest()->getParam("store");
        $candidate = Mage::app()->getStore($storeId);
        if($candidate->getId()==$storeId) {
            return $candidate;
        }
        return Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE);
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute_Set
     */
    protected function _getAttributeSet() {
        return Mage::getModel("eav/entity_attribute_set")->load(
                   Mage::app()->getRequest()->getParam("attribute_set")
               );
    }

    protected function _getCurrentStaticFilterValues() {
        $staticFilters			= Mage::app()->getRequest()->getParam("staticFilters", 0);
        $staticFiltersValues	= array();

        for ($i = 1; $i <= $staticFilters; $i++) {
            if (Mage::app()->getRequest()->getParam("staticFilterId-".$i) && Mage::app()->getRequest()->getParam("staticFilterValue-".$i)) {
                $staticFiltersValues["staticFilterId-".$i] = Mage::app()->getRequest()->getParam("staticFilterId-".$i);
                $staticFiltersValues["staticFilterValue-".$i] = Mage::helper('core')->escapeHtml(Mage::app()->getRequest()->getParam("staticFilterValue-".$i));
            }
        }
        return $staticFiltersValues;
    }

    protected function _validateProductAttributes($productIds, $attributeSetId, $storeId) {
        $errorProducts	= array();
        $collection		= Mage::getResourceModel('zolagocatalog/product_collection');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection->setFlag("skip_price_data", true);
        $collection->setStoreId($storeId);
        $collection->addIdFilter($productIds);
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToFilter("attribute_set_id", $attributeSetId);
        $collection->addAttributeToFilter("udropship_vendor", $this->_getSession()->getVendor()->getId());
        $collection->addAttributeToSelect('image');

        foreach ($collection as $product) {
            $imageValidation		= $this->_validateBaseImage($product);
            $attributeValidation	= $this->_validateRequiredAttributes($product, $storeId);
            if (!$imageValidation || $attributeValidation > 0) {
                $errorProducts[$product->getId()]['name']		= $product->getName();
                $errorProducts[$product->getId()]['image']		= $imageValidation;
                $errorProducts[$product->getId()]['missing']	= $attributeValidation;
            }
        }

        $errorProductCount = count($errorProducts);
        if ($errorProductCount) {
            switch ($errorProductCount) {
            case 1:
                throw new Mage_Core_Exception(
                    $this->__('%d selected product has empty required attribute(s) and/or is missing a base image.', $errorProductCount)
                );
            case $errorProductCount > 1:
                throw new Mage_Core_Exception(
                    $this->__('%d selected products have empty required attribute(s) and/or are missing a base image.', $errorProductCount)
                );
            default:
                break;
            }
        }
    }

    protected function _validateRequiredAttributes($product, $storeId)
    {
        $missingAttributes = 0;
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if(in_array($attribute->getFrontendInput(), array("gallery", "weee"))) {
                continue;
            }
            if ($attribute->getIsRequired()) {
                $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $attribute->getAttributeCode(), $storeId);
                if ($attribute->isValueEmpty($value)) {
                    ++$missingAttributes;
                }
            }
        }

        return $missingAttributes;
    }

    protected function _validateBaseImage($product)
    {
        $validateImage = true;
        $baseImage = $product->getImage();
        if (empty($baseImage) || $baseImage == 'no_selection') {
            $validateImage = false;
        }

        return $validateImage;
    }
}
