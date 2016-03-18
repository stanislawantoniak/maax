<?php

/**
 * generating description file
 */
class Modago_Integrator_Model_Generator_Description
    extends Modago_Integrator_Model_Generator
{

    protected $_attributeType = array();
    protected $_getList = true;
    protected $_getListBatch = 300;
    protected $_getListPage = 1;
    protected $_getListLastPage;

    protected $_categories = array();
    protected $_attributeSets = array();
    protected $_attributesForConfigurable = array();
    protected $_attributesSelect = array();
    
    protected $_valuesToInsertDirectly = array(
            'name',
            'color',
            'short_description',
            'description',
            'vat',
            'weight',
            'sku'
                                         );
    protected $_valuesToInsertRaw = array(
                                        'visibility',
                                        'sku',
                                        'weight'
                                    );
    protected $_defaultValues = array(
                                    'stockItem' => 0,
                                );
    protected $_valuesToSkip = array(
                                   'entity_id',
                                   'entity_type_id',
                                   'has_options',
                                   'required_options',
                                   'created_at',
                                   'updated_at',
                                   'url_key',
                                   'image',
                                   'small_image',
                                   'thumbnail',
                                   'options_container',
                                   'page_layout',
                                   'msrp_enabled',
                                   'msrp_display_actual_price_type',
                                   'custom_layout_update'
                               );
    protected $_keysThatHaveOtherNames = array(
            'short_description' => 'shortDescription',
                                         );
    protected $_cdataKeys = array(
                                'description',
                                'short_description'
                            );

    protected $_resource;
    protected $_productTable;
    protected $_mediaGalleryBackend;
    protected $_collection;
    protected $_integrationStore;

    protected $_header;
    protected $_footer;

    protected $_outData = array();
    protected $_key = 0;


    protected function _construct() {
        $this->setFileNamePrefix('description');
    }

    public function getHeader()
    {
        if (!$this->_header) {
            $this->_header = "<mall><version>".$this->getHelper()->getModuleVersion().
                " </version><merchant>" . $this->getExternalId() . "</merchant><products>";
        }
        return $this->_header;
    }

    public function getFooter()
    {
        if (!$this->_footer) {
            $this->_footer = "</products></mall>";
        }
        return $this->_footer;
    }

    protected function _saveOldStore() {
        $this->getHelper()->saveOldStore();
    }
    protected function _restoreOldStore() {
        $this->getHelper()->restoreOldStore();
    }
    
    /**
     * prepare attribute test from select and multiselect
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $code
     * @param int $value
     * @return 
     */

    protected function _getAttributeValue($product,$code,$value) {
        if (!isset($this->_attributesSelect[$code][$value])) {
            $attribute = $product->getResource()->getAttribute($code);
            if ($attribute) {
                $attribute->setStoreId($this->getIntegrationStore()->getId());            
                $attributeText = $attribute->getSource()->getOptionText($value);                
                $this->_attributesSelect[$code][$value] = $attributeText;
            } else {
                $this->_attributesSelect[$code][$value] = false;
            }
        }
        return $this->_attributesSelect[$code][$value];
    }
    /**
     * check if flat catalog product is enabled
     *
     * @return bool
     */
    protected function _isFlat() {
        return $this->getHelper()->isFlat();
    }
    
    /**
     * prepare content
     * should return array similar to this:
     *  array(
     *      array(
     *          "sku"           => "value",
     *          "name"          => "value",
     *          "brand"         => "value",
     *          "description"   => "value",
     *          "vat"           => "value",
     *          "stockItem"     => 1/0
     *          "categories"    => array(
     *              "categoryName1",
     *              "categoryName2",
     *              "categoryName3"
     *          ),
     *          "attributes"    => array(
     *              "attributeName1" => "attributeValue1",
     *              "attributeName2" => "attributeValue2",
     *              "attributeName3" => "attributeValue3",
     *              "attributeName4" => "attributeValue4",
     *          ),
     *          "sizes"         => array(
     *              "size1",
     *              "size2",
     *              "size3",
     *              "size4",
     *              "size5"
     *          ),
     *          "images"        => array(
     *              array(
     *                  "sequence"  => 1,
     *                  "default"   => 1,
     *                  "value"     => "imageUrl1"
     *              ),
     *              array(
     *                  "sequence"  => 2,
     *                  "default"   => 0,
     *                  "value"     => "imageUrl2"
     *              ),
     *              array(
     *                  "sequence"  => 3,
     *                  "default"   => 0,
     *                  "value"     => "imageUrl3"
     *              ),
     *          ),
     *          "cross_selling"  => array(
     *              "cross selling sku",
     *              "cross selling sku",
     *              "cross selling sku"
     *          )
     *      ),
     *      (...)
     *  );
     *
     * @return array
     */
    public function prepareList()
    {
        if ($this->_getList) {
            $this->_outData = array();
            $this->_saveOldStore();
            //init collection
            $this->getCollection();
            $this->setCollectionPage($this->_getListPage++);

            if (!$this->_getListLastPage) {
                $this->_getListLastPage = $this->getCollection()->getLastPageNumber();
            }

            if ($this->_getListPage > $this->_getListLastPage) {
                $this->_getList = false;
            }

            foreach ($this->getCollection() as $product) {
                $product->setStoreId($this->getIntegrationStore()->getId());
                /** @var Mage_Catalog_Model_Product $product */
                $this->_outData[$this->_key] = $this->_defaultValues;
                $this->_processEavCatalog($product);

                //categories start
                $categoriesIds = $product->getCategoryIds();
                foreach ($categoriesIds as $categoryId) {
                    $this->_outData[$this->_key]['categories'][] = "<![CDATA[".$this->getCategoryName($categoryId)."]]>";
                }
                unset($categoriesIds);
                //categories end


                if ($product->getTypeId() == "configurable") {
                    //simples start todo: key should be named like this?
                    /** @var Mage_Catalog_Model_Product_Type_Configurable $conf */
                    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                    $simples = $conf->getUsedProductCollection();
                    foreach ($simples as $simple) {
                        $this->_outData[$this->_key]['simples'][] = $simple->getSku();
                    }
                    unset($conf,$sizes);
                    //simples end
                } else {
                    //parentSKU start
                    $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                    if ($parentIds && is_array($parentIds) && count($parentIds)) {

                        $readConnection = $this->getResource()->getConnection('core_read');
                        $query = "SELECT {$this->getProductTable()}.sku AS sku FROM {$this->getProductTable()}" .
                                 " WHERE entity_id IN (" . implode(",", $parentIds) . ") AND type_id = 'configurable' ORDER BY sku ASC LIMIT 1";

                        $parentResult = $readConnection->fetchAll($query);

                        if (is_array($parentResult) && count($parentResult) && isset($parentResult[0]['sku']) && $parentResult[0]['sku']) {
                            $this->_outData[$this->_key]['parentSKU'] = $parentResult[0]['sku'];

                            //options start
                            $parentModel = Mage::getModel('catalog/product')->loadByAttribute('sku',$parentResult[0]['sku']);
                            $attributes = $parentModel->getTypeInstance(true)->getSetAttributes($parentModel);
                            foreach ($attributes as $attribute) {
                                if ($parentModel->getTypeInstance(true)->canUseAttribute($attribute, $parentModel)) {
                                    $dataKey = $attribute->getAttributeCode();
                                    $this->_outData[$this->_key]['options'][$dataKey] = $this->getAttributeText($product,$dataKey);
                                }
                            }
                        }


                        unset($readConnection,$query,$parentResult);
                    }
                    unset($parentIds);
                    //parentSKU end
                }

                //images start
                $lowestPosition = false;
                $lowestPositionKey = -1;
                $galleryCollection = $this->getGalleryImages($product);
                foreach ($galleryCollection as $k=>&$image) {
                    $imagePosition = $image->getPosition();
                    if($lowestPosition === false || $lowestPosition > $imagePosition) {
                        $lowestPosition = $imagePosition;
                        $lowestPositionKey = $k;
                    }
                    $this->_outData[$this->_key]['images'][$k] = array(
                                'sequence' => $image->getPosition(),
                                'value' => $image->getUrl()
                            );
                    unset($image);
                }
                if($lowestPositionKey >= 0) {
                    $this->_outData[$this->_key]['images'][$lowestPositionKey]['default'] = 1;
                }

                $this->clearMediaGallery($product);
                unset($lowestPosition,$lowestPositionKey,$galleryCollection);
                //images end

                //cross_selling start
                /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection $crossSellingCollection */
                $crossSellingCollection = $product->getCrossSellProductCollection();
                if($crossSellingCollection instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection) {
                    $crossSellingCollection->addStoreFilter($this->getIntegrationStore());
                    foreach ($crossSellingCollection as $crossProduct) {
                        $this->_outData[$this->_key]['cross_selling'][] = $crossProduct->getSku();
                    }
                }
                unset($crossSellingCollection);
                //cross_selling end

                ksort($this->_outData[$this->_key]);
                $this->_key++;
                unset($product);
            }
            $this->clearCollection(); //free the memory
            $this->clearBackend();
            $this->_restoreOldStore();
            return $this->_outData;
        }
        return array();
    }

    /**
     * clear table using skipValues
     *
     * @param array $data
     */
    protected function _clearTable(&$data) {
        foreach ($this->_valuesToSkip as $skip) {
            unset($data[$skip]);
        }
    }

    /**
     * process attributes when flat catalog is enabled
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _processFlatCatalog($product) {
        $data = $product->getData();
        //remove valuesToSkip
        $this->_clearTable($data);
        // assign values to proper keys
        $newData = $data;
        foreach ($newData as $key=>$val) {
            $valueKey = $key.'_value';
            if (isset($data[$valueKey])) {
                $data[$key] = $data[$valueKey];
                unset($data[$valueKey]);
            }
        }
        foreach ($data as $dataKey=>$value) {
            $this->_processParam($product,$dataKey,$value);
        }
    }


    /**
     * process one param from product data
     * @param Mage_Catalog_Model_Product $product
     * @param string $dataKey
     * @param string $value
     */
    protected function _processParam($product,$dataKey,$value) {
        if (in_array($dataKey, $this->_valuesToInsertDirectly)) { //insert directly overrides 'is_configurable'
            $keyToInsert = isset($this->_keysThatHaveOtherNames[$dataKey]) ? $this->_keysThatHaveOtherNames[$dataKey] : $dataKey;
            if (in_array($dataKey, $this->_cdataKeys)) {
                $dataValue = "<![CDATA[$value]]>";
            } else {
                $dataValue = $this->getAttributeText($product, $dataKey);
            }
            $this->_outData[$this->_key][$keyToInsert] = $dataValue;
        } else {
            switch ($dataKey) {
            case "status":
                $this->_outData[$this->_key]['status'] = $value == "1" ? 1 : 0;
                break;

            case "tax_class_id":
                $store = $this->getIntegrationStore();
                $request = Mage::getSingleton('tax/calculation')->getRateRequest(null, null, null, $store);
                $percent = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($value));
                $this->_outData[$this->_key]['vat'] = $percent;
                unset($store, $request, $percent);
                break;

            case "stock_item":
                $this->_outData[$this->_key]['stockItem'] = 1;
                break;

            case "manufacturer":
                $this->_outData[$this->_key]['brand'] = $this->getAttributeText($product, $dataKey);
                break;

            case "attribute_set_id":
                $attributeSetId = $product->getAttributeSetId();
                /** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSetModel */
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($attributeSetId);
                if (is_object($attributeSetModel) && $attributeSetModel->getId()) {
                    if (!isset($this->_attributeSets[$attributeSetId])) {
                        $this->_attributeSets[$attributeSetId] = "<![CDATA[" . $attributeSetModel->getAttributeSetName() . "]]>";
                    }
                    $this->_outData[$this->_key]['attribute_set'] = $this->_attributeSets[$attributeSetId];
                }
                unset($attributeSetId, $attributeSetModel);
                break;

            case 'type_id':
                $this->_outData[$this->_key]['type'] = $value;
                break;

            default:
                if ($value !== "" && !is_null($value)) {
                    $this->_outData[$this->_key]['attributes'][$dataKey] = $this->getAttributeText($product, $dataKey);
                }
            }
        }
    }
    /**
     * process attributes when flat catalog is disabled
     *
     * @param Mage_Catalog_Model_Product $product
     * @return
     */
    protected function _processEavCatalog($product) {
        $data = $product->getData();
        $this->_clearTable($data);
        foreach ($data as $dataKey => $value) {
            $this->_processParam($product,$dataKey,$value);
        }
    }
    /**
     * check attribute type
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $code
     * @return string
     */

    public function getAttributeType($product,$code) {
        if (!isset($this->_attributeType[$code])) {
            $attribute = $product->getResource()
                         ->getAttribute($code);
            if ($attribute) {
                $type = $attribute->getFrontend()
                        ->getInputType();
            } else {
                $type = 'unknown';
            }
            $this->_attributeType[$code] = empty($type)? 'text':$type;
        }
        return $this->_attributeType[$code];
    }
    /**
     *
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeCode
     * @param bool $cdata use cdata
     * @returns string
     */
    protected function getAttributeText($product, $attributeCode, $attributeValue=false,$cdata = true)
    {
        
        if ($product instanceof Mage_Catalog_Model_Product) {
            $attributeValue = $attributeValue !== false ? $attributeValue : $product->getData($attributeCode);
            if (is_array($attributeValue)) { // array of attributes
                $return = "";
                foreach($attributeValue as $attrVal) {
                    if ($cdata) {
                        $return .= "<value><![CDATA[".$this->getAttributeText($product,$attributeCode,$attrVal,false)."]]></value>";
                    } else {
                        $return .= sprintf("<value>%s</value>",$this->getAttributeText($product,$attributeCode,$attrVal,false));
                    }
                }
                return $return;
            }
            if(in_array($attributeCode,$this->_valuesToInsertRaw)) {
                return $attributeValue;
            }
            // simple type
            $type = $this->getAttributeType($product,$attributeCode); // get type            
            switch ($type) {
            case 'multiselect':
                $values = explode(',',$attributeValue);
                $attributeText = array();
                foreach ($values as $val) {
                    if ($val) {
                        $attributeText[] = $this->_getAttributeValue($product,$attributeCode,$val);                
                    }
                }
                $code = $attributeCode.'_value';
                return $this->getAttributeText($product,$code,$attributeText);
            case 'select':
                $attributeText = $this->_getAttributeValue($product,$attributeCode,$attributeValue);
                $return = "";                       
                        if (is_array($attributeText)) {
                            foreach($attributeText as $attrVal) {
                                if ($cdata) {
                                    $return .= "<value><![CDATA[".$attrVal."]]></value>";
                                } else {
                                    $return .= sprintf("<value>%s</value>",$attrVal);
                                }
                            }
                        } else {
                            if ($cdata) {
                                $return = "<![CDATA[$attributeText]]>";
                            } else {
                                $return = $attributeText;
                            }
                        }
                return $return;
                break;
            default:
                if ($cdata) {
                    return "<![CDATA[$attributeValue]]>";
                } else {
                    return $attributeValue;
                }
                break;
            }
        }
        return '';
    }

    /**
     *    prepare xml block
     *
     * @var array $item
     * @return string
     */
    public function prepareXmlBlock($key,$item)
    {
        $xml = "<product>";
        foreach ($item as $key => $val) {
            $xml .= "<$key>";
            switch ($key) {
            case "simples":
                foreach ($val as $size) {
                    $xml .= "<sku>$size</sku>";
                }
                break;

            case "categories":
                foreach ($val as $category) {
                    $xml .= "<category>$category</category>";
                }
                break;

            case "attributes":
                foreach ($val as $attributeName => $attributeValue) {
                    $xml .= "<$attributeName>$attributeValue</$attributeName>";
                }
                break;

            case "images":
                foreach ($val as $image) {
                    $xml .= "<img";
                    if(isset($image['sequence'])) {
                        $xml .= " sequence=\"{$image['sequence']}\"";
                    }
                    if(isset($image['default'])) {
                        $xml .= " default=\"{$image['default']}\"";
                    }
                    $xml .= ">{$image['value']}</img>";
                }
                break;

            case "cross_selling":
                foreach ($val as $cross_product_sku) {
                    $xml .= "<sku>$cross_product_sku</sku>";
                }
                break;

            case "options":
                foreach ($val as $attributeName => $attributeValue) {
                    $xml .= "<$attributeName>$attributeValue</$attributeName>";
                }
                break;

            default:
                $xml .= $val;
            }
            $xml .= "</$key>";
        }
        $xml .= "</product>";

        return $xml;
    }

    /**
     * @return string
     */
    protected function getProductTable() {
        if(!$this->_productTable) {
            $this->_productTable = $this->getResource()->getTableName("catalog_product_entity");
        }
        return $this->_productTable;
    }

    /**
     * @return Mage_Core_Model_Resource
     */
    protected function getResource() {
        if(!$this->_resource) {
            $this->_resource = Mage::getSingleton('core/resource');
        }
        return $this->_resource;
    }

    /**
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Media
     */
    protected function getBackend() {
        if (!$this->_mediaGalleryBackend) {

            $mediaGallery = Mage::getSingleton('eav/config')
                            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'media_gallery');

            $this->_mediaGalleryBackend = $mediaGallery->getBackend();
        }

        return $this->_mediaGalleryBackend;
    }

    protected function clearBackend() {
        $this->_mediaGalleryBackend = null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    protected function getGalleryImages(&$product)
    {
        $this->getBackend()->afterLoad($product);
        return $product->getMediaGalleryImages();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function clearMediaGallery(&$product) {
        $product->unsData('media_gallery');
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getCollection() {
        if(!$this->_collection) {
            $this->_collection = Mage::getResourceModel('catalog/product_collection');
            $this->_collection->setStoreId($this->getIntegrationStore())
                                 ->addAttributeToSelect("*")
                                 ->addAttributeToFilter(
                                     'status',
                                     array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                                 )
                                 ->setPageSize($this->_getListBatch);
        }
        return $this->_collection;
    }

    protected function clearCollection() {
        if($this->_collection) {
            $this->_collection->clear();
            $this->_collection = null;
        }
    }

    protected function setCollectionPage($number) {
        if($this->_collection) {
            $this->_collection->setCurPage($number);
        }
    }

    protected function getCategoryName($categoryId) {
        if (!isset($this->_categories[$categoryId])) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category) {
                $path = $category->getPath();
                $list = explode('/',$path);
                $cid = array_pop($list); // actual category
                $name = $category->getData('name');
                if (count($list)) {
                    $cid = array_pop($list);
                    $name = $this->getCategoryName($cid).' / '.$name;
                }
                $this->_categories[$categoryId] = $name;
            } else {
                $this->_categories[$categoryId] = false;
            }
            unset($category);
        }
        return $this->_categories[$categoryId];
    }


    protected function getIntegrationStore()
    {
        if(!$this->_integrationStore) {
            $this->_integrationStore = Mage::app()->getStore($this->getHelper()->getIntegrationStore());
        }
        return $this->_integrationStore;
    }
}
