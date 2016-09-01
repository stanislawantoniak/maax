<?php

class Zolago_Catalog_Vendor_ProductController
    extends Zolago_Catalog_Controller_Vendor_Product_Abstract
{

    const ERROR_LIST_LEN = 10;

    /**
     * Index
     */
    public function indexAction()
    {
        $this->_saveHiddenColumns();
        $this->_renderPage(null, 'udprod_product');
    }


    /**
     * Save attributes mass actions
     */
    public function massAction()
    {

        $request = $this->getRequest();
        $method = $request->getParam("method");
        $productIds = $request->getParam("product_ids");
        $attributeSetId = $request->getParam("attribute_set_id");
        $saveAsRule = $request->getParam("save_as_rule");
        $attributeMode = $request->getParam("attribute_mode");
        $storeId = $this->_getStoreId();
        $global = false;

        if (is_string($productIds)) {
            $productIds = explode(",", $productIds);
        }
        $restQuery = $this->_getRestQuery();
        if (is_array($productIds) && count($productIds)) {
            $ids = array_unique($productIds);
        } else {
            $collection = $this->_getCollection();
            foreach ($restQuery as $key => $value) {
                $collection->addAttributeToFilter($key, $value);
            }
            $global = true;
            $ids = array();
            $idName = $collection->getEntity()->getIdFieldName();
            foreach ($collection->getData() as $row) {
                $ids[] = (int)$row[$idName];
            }
        }

        try {
            $response = array(
                            "changed_ids" => $ids,
                            "global" => $global
                        );

            switch ($method) {
            /**
             *Handle mass save
             */
            case "attribute":
                $attributeCode = key($request->getParam("attribute"));
                $attributeValue = $request->getParam("attribute")[$attributeCode];

                /**
                 * Save attribute change history
                 */

                /* @var $descriptionHistoryModel Zolago_Catalog_Model_Description_History */
                $descriptionHistoryModel = Mage::getModel("zolagocatalog/description_history");
                $descriptionHistoryModel->updateChangesHistory(
                    $this->getVendorId(),
                    $ids,
                    $attributeCode,
                    $attributeValue,
                    $this->_getCollection(),
                    $attributeMode[$attributeCode]
                );

                /*Save attribute change history*/

                if ($attributeCode == "description" || $attributeCode == "short_description") {
                    $attributeValue = Mage::helper("zolagocatalog")->secureInvisibleContent($attributeValue);
                }
                elseif ($attributeCode == 'name') {
                    $attributeValue = Mage::helper("zolagocatalog")->cleanProductName($attributeValue);
                }
                $this->_processAttributresSave(
                    $ids,
                    array($attributeCode => $attributeValue),
                    $storeId,
                    array("attribute_mode" => $request->getParam("attribute_mode"))
                );

                break;
            /**
             * Handle status change
             */
            case "confirm":
                $attributeCode = "description_status";
                $attributeValue = $this->_getSession()->getVendor()->getData("review_status");
                $validationData = $this->_validateProductAttributes($ids, $attributeSetId, $storeId);

                $validatedIds = $validationData['ids'];
                $errorIds = array_values(array_diff($ids,$validatedIds));
                $error = $validationData['error'];
                $success = false;

                $validatedIdsCount = count($validatedIds);
                if($validatedIdsCount) {
                    $this->_processAttributresSave(
                        $validatedIds,
                        array("description_status" => $this->_getSession()->getVendor()->getData("review_status"),
                             ),
                        $storeId,
                        array("check_editable" => false)
                    );

                    $this->_generateUrlKeys($validatedIds, $storeId);

                    if ($error) {
                        $success = '<div class="alert alert-success">' .
                                   Mage::helper('zolagocatalog')->__('Descriptions for %d products have been accepted', $validatedIdsCount) .
                                   '</div>';
                    }
                }

                if ($error) {
                    $response['message'] = ($success ? $success : '') . ($error ? $error : '');
                }

                if(count($errorIds)) {
                    $response['changed_ids'] = $errorIds;
                    $response['global'] = false;
                }


                break;
            default:
                Mage::throwException("Invalid mass method");

            }
            /**
             * Save Rule
             * @see GH_AttributeRules_Model_Observer::saveProductAttributeRule()
             */
            $_restQuery = $this->processRestQueryForSave($restQuery);
            Mage::dispatchEvent(
                "change_product_attribute_after",
                array(
                    'store_id' => $storeId,
                    "attribute_code" => $attributeCode,
                    "vendor" => $this->getVendor(),
                    "attribute_mode" => $attributeMode[$attributeCode],
                    "attribute_value" => $attributeValue,
                    "rest_query" => $_restQuery,
                    "raw_rest_query" => $restQuery,
                    "save_as_rule" => $saveAsRule,
                    "method" => $method,
                    "product_ids" => $productIds,
                    "attribute_set_id" => $attributeSetId
                )
            );
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->getResponse()->setHttpResponseCode(500);
            $response = $ex->getMessage();
            //$response = "Something went wrong. Contact admin.";
        }

        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($response));
        $this->_prepareRestResponse();
    }

    /**
     * Set attribute set mass actions
     */
    public function massAttributeSetAction()
    {

        $request = $this->getRequest();

        $productIds = $request->getParam("product_ids");
        $attributeSetId = $request->getParam("attribute_set_id");
        $attributeSetMoveToId = $request->getParam("attribute_set_move_to");


        if (is_string($productIds)) {
            $productIds = explode(",", $productIds);
        }
        $restQuery = $this->_getRestQuery();
        if (is_array($productIds) && count($productIds)) {
            $ids = array_unique($productIds);
        } else {
            $collection = $this->_getCollection();
            foreach ($restQuery as $key => $value) {
                $collection->addAttributeToFilter($key, $value);
            }
            $ids = array();
            $idName = $collection->getEntity()->getIdFieldName();
            foreach ($collection->getData() as $row) {
                $ids[] = (int)$row[$idName];
            }
        }

        try {
            Mage::register(Zolago_Turpentine_Model_Observer_Ban::NO_BAN_AFTER_PRODUCT_SAVE,true,true);

            $helper = Mage::helper("zolagocatalog");

            //almost always attribute set is changed for configurable products so it doesn't hurt to get all related products always
            $children = Mage::getResourceModel('catalog/product')
                        ->getRelatedProducts($ids,true);

            $allIds = array_merge($ids,$children);

            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $tableName = $resource->getTableName('catalog/product');

            $query = "UPDATE `".$tableName."`".
                     "SET `attribute_set_id` = '".$attributeSetMoveToId."' WHERE `entity_id` IN (".implode(",",$allIds).")";

            $writeConnection->query($query);

            $helper = Mage::helper("zolagocatalog");


            Mage::getSingleton('catalog/product_action')
            ->updateAttributes($ids, array('description_status'=>1), Mage_Core_Model_App::ADMIN_STORE_ID);

            Mage::getModel("zolagomapper/queue_product")
            ->pushProductToMapperQueue($ids);

            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($attributeSetMoveToId);
            $attributeSetName = $attributeSetModel->getAttributeSetName();

            /*
             * clear changed ids after mass attribute set change because we don't want those items
             * to be reselected on description grid - they are not visible to vendor on his current view
             */
            $changedIds = array();

            $productIdsCount = count($ids);

            $response = array(
                            "status" => 1,
                            "changed_ids" => $changedIds,
                            "count" => $productIdsCount,
                            "message" => $helper->__("<b>%s</b> product(s) moved to category <b>%s</b>", $productIdsCount, $attributeSetName)
                        );

            Mage::unregister(Zolago_Turpentine_Model_Observer_Ban::NO_BAN_AFTER_PRODUCT_SAVE);
        } catch (GH_Common_Exception $e) {
            $response = array(
                            "status" => 0,
                            "message" => $e->getMessage()
                        );
        } catch (Exception $e) {
            $response = array(
                            "status" => 0,
                            "message" => $helper->__("Some error occurred. Contact administrator.")
                        );
            Mage::logException($e);
        }

        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($response));
        $this->_prepareRestResponse();
    }



    /**
     *
     * @param
     * @return
     */
    protected function _prepareActualUrlKeyByStore($ids) {
        $obj = Mage::getResourceModel('catalog/product');
        $list = $obj->getUrlKeysByStore($ids);
        return $list;
    }
    /**
     * re-generate short url for products
     *
     * @param array $ids
     * @param int $storeId
     */
    protected function _generateUrlKeys($ids, $storeId)
    {
        /** @var Mage_Catalog_Model_Product_Url $string */
        $string = Mage::getSingleton('catalog/product_url');
        /** @var Zolago_Catalog_Model_Url $url */
        $url = Mage::getSingleton('catalog/url');
        $url->setShouldSaveRewritesHistory(true);
        $resource = $url->getResource();
        $stores = Mage::app()->getStores();
        $storeIds = array($storeId);
        foreach ($stores as $store) {
            $storeIds[] = $store->getId();
        }
        $urlRewriteCollection = Mage::getModel('core/url_rewrite')->getCollection()
                                ->addFieldToFilter('product_id', array('in' =>  $ids));
        $urlList = array();
        foreach ($urlRewriteCollection as $itemUrl) {
            $urlList[$itemUrl->getProductId()][$itemUrl->getStoreId()] = $itemUrl->getRequestPath();
        }
        $productList = $this->_prepareActualUrlKeyByStore($ids);
        foreach ($ids as $id) {
            /** @var Zolago_Catalog_Model_Product $model */
            $refresh = false;
            foreach ($storeIds as $sId) {
                if ($key = empty($productList[$id][$sId])? (empty($productList[$id][0])? '':$productList[$id][0]):$productList[$id][$sId]) {
                    if (empty($urlList[$id][$sId]) || ($urlList[$id][$sId] != $key)) {
                        $model = Mage::getModel('catalog/product')->setStoreId($sId)->load($id);
                        $model->setData('store_id', $sId); // Trick
                        $model->setUrlKey($key);
                        $resource->saveProductAttribute($model, 'url_key');
                        $refresh = true;
                    }
                }
            }
            if ($refresh) {
                $url->refreshProductRewrite($id);
            }
        }
    }

    /**
     * Prepare rest query from params for saving conditions in attributes mapper
     * Remove "from" and "to" (images count),
     * Add name filter,
     * Add regexp for multiselect attributes
     * @see GH_AttributeRules_Model_Observer::saveProductAttributeRule()
     * @see Zolago_Catalog_Controller_Vendor_Product_Abstract::_getSqlCondition()
     *
     * @param $restQuery
     * @return mixed
     */
    protected function processRestQueryForSave($restQuery)
    {
        // Clear images count filter
        unset($restQuery["images_count"]);
        // Add custom filter by name
        $inParams = $this->getRequest()->getQuery();
        if (isset($inParams["name"])) {
            $restQuery["name"] = array("like" => "%" . $inParams["name"] . "%");
        }
        // Null (empty)
        foreach ($this->_getAvailableQueryParams() as $key) {
            $attribute = $this->getGridModel()->getAttribute($key);
            if ($attribute && $this->getGridModel()->isAttributeEnumerable($attribute) && $attribute->getAttributeCode() != "name") {
                if (isset($inParams[$key])) {
                    $value = $inParams[$key];
                    if (is_string($value) && trim($value) == "") {
                        continue;
                    }
                    elseif (is_array($value)) {
                        continue;
                    }
                    if ($value === self::NULL_VALUE) {
                        $restQuery[$key] = array("null" => true);
                    }
                }
            }
        }
        // Regexp for multi select attributes
        $multi = $this->_getRestQueryAttributesByFrontendInput("multiselect");
        foreach ($multi as $code => $attr) {
            if (isset($inParams[$code]) && $inParams[$code] !== self::NULL_VALUE) {
                /** @see Zolago_Catalog_Controller_Vendor_Product_Abstract::_getSqlCondition() */
                $restQuery[$code] = array("regexp" => "[[:<:]]" . $inParams[$code] . "[[:>:]]");
            }
        }
        return $restQuery;
    }

    /**
     * Get available query params attributes filtered by fronted input type
     *
     * @param string $type like "select" or "multiselect"
     * @return array
     */
    protected function _getRestQueryAttributesByFrontendInput($type)
    {
        $out = array();
        foreach ($this->getGridModel()->getColumns() as $column) {
            if ($column->getAttribute() &&
                    $column->getAttribute()->getFrontendInput() == $type &&
                    $column->getFilterable() !== false
               ) {
                $out[$column->getAttribute()->getAttributeCode()] = $column->getAttribute();
            }
        }
        return $out;
    }

    /**
     * Save hidden columns
     */
    protected function _saveHiddenColumns()
    {
        if ($this->getRequest()->isPost()) {
            $listColumns = $this->getRequest()->getParam('listColumn', array());
            $attributeSet = $this->getRequest()->getParam('attribute_set_id');
            $hiddenColumns = $this->getRequest()->getParam('hideColumn', array());

            if (!Mage::getModel("eav/entity_attribute_set")->load($attributeSet)->getId()) {
                return;
            }

            foreach ($hiddenColumns as $key => $dummy) {
                unset($listColumns[$key]);
            }
            $session = Mage::getSingleton('udropship/session');

            $list = $session->getData('denyColumnList');
            if (!$list) {
                $list = array();
            }
            $list[$attributeSet] = $listColumns;

            $session->setData('denyColumnList', $list);
        }
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
     * @param array $productIds
     * @param int $attributeSetId
     * @param int $storeId
     * @returns array
     * @throws Mage_Core_Exception
     */
    protected function _validateProductAttributes($productIds, $attributeSetId, $storeId)
    {
        $errorProducts = array();
        $idsToUpdate = array();
        $errorMsg = false;
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection->setFlag("skip_price_data", true);
        $collection->setStoreId($storeId);
        $collection->addIdFilter($productIds);
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('description_status');
        $collection->addAttributeToFilter("attribute_set_id", $attributeSetId);
        $collection->addAttributeToFilter("udropship_vendor", $this->_getSession()->getVendor()->getId());
        $collection->addAttributeToSelect('image');

        foreach ($collection as $product) {
            $productId = $product->getId();

            $productErr = array();

            if ($imageValidation = $this->_validateBaseImage($product)) {
                $productErr[] = sprintf('%s: %s', $product->getName(), $imageValidation);
            }
            if ($attributeValidation = $this->_validateRequiredAttributes($product, $storeId)) {
                $productErr[] = Mage::helper('zolagocatalog')->__('%s: Empty required attributes (%s)', $product->getName(), implode(',', $attributeValidation));
            }
            if ($this->_validateStatusAccepted($product)) {
                $productErr[] = Mage::helper('zolagocatalog')->__('%s: Product description already accepted', $product->getName());
            }
            if ($emptyValidation = $this->_validateEmptyImage($product)) {
                $productErr[] = sprintf('%s: %s', $product->getName(), implode(',', $emptyValidation));
            }

            if (count($productErr)) {
                $errorProducts[] = implode("<br/>", $productErr);
            } else {
                $idsToUpdate[] = $productId;
            }
        }

        $countErrorProducts = count($errorProducts);

        if ($countErrorProducts) {
            if ($countErrorProducts > self::ERROR_LIST_LEN) {
                $errorProducts = array_slice($errorProducts, 0, self::ERROR_LIST_LEN);
                $errorProducts[] = '...';
            }
            $class = count($idsToUpdate) ? "warning" : "danger";
            $errorMsg =
                '<div class="alert alert-' . $class . '">' .
                    Mage::helper('zolagocatalog')->__('Discovered %d validation problems:', $countErrorProducts) .
                    '</div>' .
                    implode("<br/>", $errorProducts);
        }

        return array('ids' => $idsToUpdate, 'error' => $errorMsg);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @return boolean
     */
    protected function _isVisibleInGrid(Mage_Catalog_Model_Resource_Eav_Attribute $attribute)
    {
        return in_array(
                   $attribute->getGridPermission(),
                   $this->getGridModel()->getGridAttributeTypes()
               );
    }

    /**
     * @param type $product
     * @param type $storeId
     * @return int
     */
    protected function _validateRequiredAttributes($product, $storeId)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $missingAttributes = array();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getFrontendInput(), array("gallery", "weee"))) {
                continue;
            }
            if ($attribute->getIsRequired() && $this->_isVisibleInGrid($attribute)) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
                             $product->getId(), $attribute->getAttributeCode(), $storeId);
                if ($attribute->isValueEmpty($value) ||
                        (in_array($attribute->getFrontendInput(), array("select", "multiselect")) && $value === false)
                   ) {
                    //Mage::log($attribute->getAttributeCode() . ": " . var_export($value, 1));
                    $missingAttributes[$attribute->getAttributeCode()] = $attribute->getStoreLabel(
                                $this->_getSession()->getVendor()->getLabelStore());
                }
            }
        }
        return $missingAttributes;
    }


    /**
     *
     * @param type $product
     * @return text
     */
    protected function _validateStatusAccepted($product)
    {
        return ((int)$product->getDescriptionStatus() >= (int)$this->_getSession()->getVendor()->getData("review_status"));
    }

    /**
     * @param type $product
     * @return text
     */
    protected function _validateBaseImage($product)
    {
        $validateImage = true;
        $baseImage = $product->getImage();
        if (empty($baseImage) || $baseImage == 'no_selection') {
            return Mage::helper('zolagocatalog')->__('No base image');
        }
        return null;
    }

    /**
     * @param type $product
     * @return array
     */
    protected function _validateEmptyImage($product)
    {
        $errors = array();
        $product->load('media_gallery');
        $gallery = $product->getMediaGalleryImages();
        foreach ($gallery as $image) {
            $path = $image->getPath();
            if (!@getimagesize($path)) {
                $errors[] = Mage::helper('zolagocatalog')->__('Wrong image file format %s', $image->getFile());
            }
        }
        return $errors;
    }

    public function manageattributesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }


    /**
     * Action to update Show Changes History modal
     */
    public function manageChangesHistoryAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     *
     */
    public function revertChangesHistoryAction()
    {
        $id = $this->getRequest()->getParam("id", null);

        /* @var $descriptionHistory Zolago_Catalog_Model_Description_History */
        $descriptionHistory = Mage::getModel("zolagocatalog/description_history");

        try {
            $changesHistory = $descriptionHistory->load($id);
            if (!$changesHistory->getId()) {
                echo json_encode(array("error" => Mage::helper('zolagocatalog')->__("Change not found")));
                return;
            }


            if ($changesHistory->getVendorId() !== $this->getVendorId()) {
                echo json_encode(array("error" => Mage::helper('zolagocatalog')->__("It is not your change")));
                return;
            }

            $descriptionHistory->revertChangesHistory($changesHistory);
            echo json_encode(array());
            return;

        } catch (Exception $e) {

        }
    }
}
