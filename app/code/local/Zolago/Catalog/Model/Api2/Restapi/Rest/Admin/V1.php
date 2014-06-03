<?php
class Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1 extends Zolago_Catalog_Model_Api2_Restapi {


    /*test*/
    public function createTest($data)
    {
        $json = json_encode($data);
        $log = Zolago_Catalog_Helper_Log::log($json, TRUE);
        return $log;
    }

    protected function _create($data)
    {
        Mage::log(microtime()." _create ", 0, 'converter_test.log');

        $json = json_encode($data);
        print_r($data);
        Zolago_Catalog_Helper_Log::log($json);

        /* @var $validator Mage_Catalog_Model_Api2_Product_Validator_Product */
        $validator = Mage::getModel('catalog/api2_product_validator_product', array(
            'operation' => self::OPERATION_CREATE
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $type = $data['type_id'];

        if ($type !== 'simple') {
            $this->_critical("Creation of products with type '$type' is not implemented",
                Mage_Api2_Model_Server::HTTP_METHOD_NOT_ALLOWED);
        }
        $set = $data['attribute_set_id'];
        $sku = $data['sku'];

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);

        foreach ($product->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $product->setData($mediaAttrCode, 'no_selection');
        }

        $this->_prepareDataForSave($product, $data);
        try {
            $product->validate();
            $product->save();
            $this->_multicall($product->getId());
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_UNKNOWN_ERROR);
        }

        return $this->_getLocation($product);

        //return $json;
    }

    /*--test*/


//    protected function _create() {
//        return json_encode(array("testing","hello"));
//    }
    protected function _retrieveCollection()
    {
        return json_encode(array("testing", "hello2"));
    }

    protected function _retrieve()
    {
        return json_encode($this->getRequest());
        //return json_encode(array("testing", "hello3"));
    }

    protected function _multiUpdate()
    {
        Mage::log(microtime()." _multiUpdate ", 0, 'converter_test.log');
        $skuAssoc = Zolago_Catalog_Helper_Data::getSkuAssoc();

        $data = $this->getRequest()->getBodyParams();

        echo "Start updateAttributes from XML " . date('h:i:s') . " " . microtime() . "<br />";
        if (!empty($data)) {
            $productAction = Mage::getSingleton('catalog/product_action');
            $merchant = $data['merchant'];

            $productsButch = $data['pos'];
            //$productsButch = array_slice($productsButch, 0, 3); //for test

            $idsForQueue = array();
            foreach ($productsButch as $productsButchItem) {
                $skuXML = $productsButchItem['sku'];
                $price = $productsButchItem['price'];
                $sku = $merchant . '-' . $skuXML;


                $productId = isset($skuAssoc[$sku]) ? $skuAssoc[$sku] : 0;
                $idsForQueue[$productId] = $productId;


                if (!empty($productId)) {
                    $productIds = array($productId);
                    $attrData = array('price' => $price);

                    $productAction->updateAttributesNoIndex($productIds, $attrData, 0);
                    //$productAction->updateAttributesNoIndex($productIds, $attrData, 1);
                    //$productAction->updateAttributesNoIndex($productIds, $attrData, 2);
                }
            }
            unset($productsButchItem);
        }
        echo "End updateAttributes from XML " . date('h:i:s') . " " . microtime() . "<br />";


//        Mage::getSingleton('index/indexer')->processEntityAction(
//            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
//        );

        Zolago_Catalog_Helper_Configurable::queue($idsForQueue);

        return json_encode($this->getRequest());
    }



}