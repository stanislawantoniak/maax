<?php
/**
 * generating description file
 */
class Modago_Integrator_Model_Generator_Description
    extends Modago_Integrator_Model_Generator {

protected $_getList = true;
	protected $_getListBatch = 1000;
	protected $_getListPage = 1;
	protected $_getListLastPage;

	protected $_categories = array();

	protected $_header;
	protected $_footer;

	protected function _construct() {
		$this->setFileNamePrefix('description');
	}
	
	public function _getHeader()
	{
		if (!$this->_header) {
			$this->_header = "<mall><merchant>" . $this->getExternalId() . "</merchant><products>";
		}
		return $this->_header;
	}

	public function _getFooter()
	{
		if (!$this->_footer) {
			$this->_footer = "</products></mall>";
		}
		return $this->_footer;
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
	 *          "cross_seling"  => array(
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
	protected function _prepareList()
	{
		if($this->_getList) {
			/** @var Mage_Catalog_Model_Product $model */
			$model = Mage::getModel('catalog/product');

			/** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
			$collection = $model->getCollection();
			$collection
				->addAttributeToSelect("*")
				->setPageSize($this->_getListBatch)
				->setCurPage($this->_getListPage++);

			if (!$this->_getListLastPage) {
				$this->_getListLastPage = $collection->getLastPageNumber();
			}

			if($this->_getListPage > $this->_getListLastPage) {
				$this->_getList = false;
			}

			$data = array();
			$key = 0;
			$valuesToInsertDirectly = array( //scheme is "xml_key_name"=>"magento_key_name"
				'sku',
				'name',
				'color',
				'short_description',
				'description',
				'vat',
				'weight'
			);
			$valuesToSkip = array(
				'entity_id',
				'entity_type_id',
				'attribute_set_id',
				'type_id',
				'has_options',
				'required_options',
				'created_at',
				'updated_at'
			);
			$keysThatHaveOtherNames = array(
				'short_description' => 'shortDescription',
			);
			$cdataKeys = array(
				'description',
				'short_description'
			);
			$defaultValues = array(
				'stockItem' => 0,
			);

			//var_dump($collection->getFirstItem()->getData());

			foreach ($collection as $product) {
				/** @var Mage_Catalog_Model_Product $product */
				//set default values
				$data[$key] = $defaultValues;
				foreach ($product->getData() as $dataKey => $value) {
					if (in_array($dataKey, $valuesToInsertDirectly)) {
						$keyToInsert = isset($keysThatHaveOtherNames[$dataKey]) ? $keysThatHaveOtherNames[$dataKey] : $dataKey;
						if (in_array($dataKey, $cdataKeys)) {
							$dataValue = "<![CDATA[$value]]>";
						} else {
							$dataValue = $this->getAttributeText($product, $dataKey);
						}
						$data[$key][$keyToInsert] = $dataValue;
					} elseif (!in_array($dataKey, $valuesToSkip)) {
						switch ($dataKey) {
							case "status":
								$data[$key]['status'] = $value == "1" ? 1 : 0;
								break;

							case "tax_class_id":
								$store = Mage::app()->getStore('default');
								$request = Mage::getSingleton('tax/calculation')->getRateRequest(null, null, null, $store);
								$percent = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($value));
								$data[$key]['vat'] = $percent;
								break;

							case "stock_item":
								$data[$key]['stockItem'] = 1;
								break;

							case "manufacturer":
								$data[$key]['brand'] = $this->getAttributeText($product, $dataKey);
								break;

							default:
								if($value !== "" && !is_null($value)) {
									$data[$key]['attributes'][$dataKey] = $this->getAttributeText($product, $dataKey);
								}
						}
					}
				}
				//categories
				$categoriesIds = $product->getCategoryIds();
				foreach ($categoriesIds as $categoryId) {
					if (!isset($this->_categories[$categoryId])) {
						$category = Mage::getModel('catalog/category')->load($categoryId);
						if ($category) {
							$this->_categories[$categoryId] = $category->getData('name');
						} else {
							$this->_categories[$categoryId] = false;
						}
					}
					if ($this->_categories[$categoryId]) {
						$data[$key]['categories'][] = "<![CDATA[{$this->_categories[$categoryId]}]]>";
					}
				}


				if ($product->getTypeId() == "configurable") {
					//sizes
					/** @var Mage_Catalog_Model_Product_Type_Configurable $conf */
					$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
					$sizes = $conf->getUsedProductCollection();
					foreach ($sizes as $size) {
						$data[$key]['sizes'][] = $size->getSku();
					}
				} else {
					//parentSKU
					$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
					if ($parentIds && is_array($parentIds) && count($parentIds)) {
						/** @var Mage_Catalog_Model_Resource_Product_Collection $parentCollection */
						$parentCollection = Mage::getResourceModel('catalog/product_collection');
						$parentCollection
							->addFieldToFilter('entity_id', array('in' => $parentIds))
							->setOrder('sku', Zend_Db_Select::SQL_ASC)
							->setPageSize(1);

						if ($parentCollection->getSize()) {
							$data[$key]['parentSKU'] = $parentCollection->getFirstItem()->getData('sku');
						}
					}
				}

				//images //todo: try to optimize images loading - maybe there is a way to load all images for batch at once
				$product->load('media_gallery');
				foreach ($product->getMediaGalleryImages() as $image) {
					$data[$key]['images'][] = array(
						'sequence' => $image->getPosition(),
						'default' => $image->getPosition() ? 0 : 1,
						'value' => $image->getUrl()
					);
				}


				ksort($data[$key]);
				$key++;
			}

			return $data;
		}
		return false;
	}

	/**
	 *    prepare xml block
	 *
	 * @var array $item
	 * @return string
	 */
	protected function _prepareXmlBlock($item)
	{
		$xml = "<product>";
		foreach ($item as $key => $val) {
			$xml .= "<$key>";
			switch ($key) {
				case "sizes":
					foreach($val as $size) {
						$xml.= "<size>$size</size>";
					}
					break;
				case "categories":
					foreach($val as $category) {
						$xml.= "<category>$category</category>";
					}
					break;
				case "attributes":
					foreach($val as $attributeName=>$attributeValue) {
						$xml.= "<$attributeName>$attributeValue</$attributeName>";
					}
					break;
				case "images":
					foreach($val as $image) {
						$xml.= "<img sequence=\"{$image['sequence']}\" default=\"{$image['default']}\">{$image['value']}</img>";
					}
					break;
				case "cross_selling":
					foreach($val as $cross_product_sku) {
						$xml .= "<sku>$cross_product_sku</sku>";
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
	 *
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @param string $attributeCode
	 * @returns string
	 */
	protected function getAttributeText($product,$attributeCode) {
		if($product instanceof Mage_Catalog_Model_Product) {
			$attributeValue = $product->getData($attributeCode);
			if(is_numeric($attributeValue)) {
				$attribute = $product->getResource()->getAttribute($attributeCode);
				if ($attribute) {
					$attributeText = $attribute->getSource()->getOptionText($attributeValue);
					if ($attributeText) {
						return $attributeText;
					}
				}
			}
			return $attributeValue;
		}
		return '';
	}


}
