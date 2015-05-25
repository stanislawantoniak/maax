<?php

class GH_Rewrite_CsvController extends Mage_Adminhtml_Controller_Action
{

	public function importAction()
	{
		try {
			$hlp = $this->_getHelper();
			$warningEmptyLines = 0;
			if (isset($_FILES['file'])) {
				$file = $_FILES['file'];

				//validate csv mimetype
				if (!in_array($file['type'], $this->_getCsvMimeTypes())) {
					$this->_exception("Incorrect file type provided");
				} else {
					$csvData = array_map("str_getcsv", file($file['tmp_name'])); //convert csv to array
					if(is_array($csvData) && count($csvData)) { //if it's array then proceed

                        $ghUrlRewriteColumns = $hlp->getGhUrlRewriteCsvColumns();
                        $filtersColumnName = $hlp::GH_URL_REWRITE_FILTERS_COLUMN;
                        $hashIdColumnName = $hlp::GH_URL_REWRITE_HASH_ID_COLUMN;

                        $collumnsValidations = $ghUrlRewriteColumns;
                        $collumnsValidations[] = $filtersColumnName;

                        if (isset($csvData[0]) && $csvData[0] == $collumnsValidations) {
                            unset($csvData[0]);
                        } else {
                            Mage::throwException($this->_getHelper()->__('Wrong CSV header format. First line in file should be: store_id,category_id,title,meta_description,meta_keywords,category_name,text_field_category,text_field_filter,listing_title,url,filters'));
                        }

						//get all categories ids from csv alongside store_ids
						$storesCategoriesIds = array_map(array($this,'_getCategoryIdFromArray'),$csvData);

						$validationData = array(); //convert those ids to checking array[store_id][category_id][filter_types][filter_values]
						$categoriesIds = array(); //get only categories ids
						$storesIds = array(); //get only stores ids
						foreach($storesCategoriesIds as $data) {
							if(!in_array($data[1],$categoriesIds)) {
								$categoriesIds[] = $data[1];
							}
							if(!in_array($data[0],$storesIds)) {
								$storesIds[] = $data[0];
							}
							if(!isset($validationData[$data[0]][$data[1]])) {
								$validationData[$data[0]][$data[1]] = array();
							}
						}


						//get all filter types by previously selected categories ids
						/* @var $filtersCollection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
						$filtersCollection = Mage::getResourceModel('zolagocatalog/category_filter_collection');
						$filtersCollection->addCategoryFilter($categoriesIds);

						foreach($filtersCollection as $filter) { //fill $validationData array with attributes types and values for every selected store
							/** @var Zolago_Catalog_Model_Category_Filter $filter */
							$attribute = $filter->getAttribute();
							$attributeValues = array();
							foreach($storesIds as $storeId) {
								foreach ($attribute->setStoreId($storeId)->getSource()->getAllOptions(false) as $values) {
									$attributeValues[] = $values['label'];
								}
								$validationData[$storeId][$filter->getCategoryId()][$attribute->getAttributeCode()] = $attributeValues;
							}
						}

						$ghUrlRewrite = array(); //array that will hold data prepared for db insert
						$ghUrlRewriteHashes = array(); //store all generated hashes for validation later on

						foreach ($csvData as $num => $row) {
							if (is_array($row) && count($row)) {
								$filters = array();
								foreach ($row as $i => $value) {
									if (isset($ghUrlRewriteColumns[$i])) {
										$ghUrlRewrite[$num][$ghUrlRewriteColumns[$i]] = ($i < 2) ? (int)$value : $value; //assign fields that are available in gh_rewrite_url table
									} else {
										$filters[] = $value; //assign filters
									}
								}

								if (count($filters)) {
									$filtersOut = array();
									foreach ($filters as $i => $filterType) {
										if (($i+1) % 2 === 0) { //if its divisible by 2 then it's filter value so continue
											continue;
										} elseif (in_array($filterType, array_keys($validationData[$row[0]][$row[1]]))) { //check if this filter type exists in this store and category
											if(in_array($filters[$i + 1],$validationData[$row[0]][$row[1]][$filterType])) { //check if this filter value exists in this store, category and filter type
												$filtersOut[$filterType][] = $filters[$i + 1];
											} else {
												$this->_exception("Invalid filter value: %s in line %s",$filters[$i + 1],$num+1);
											}
										} else {
											$this->_exception("Invalid filter type: %s in line %s",$filterType,$num+1);
										}
									}
									if (count($filtersOut)) {
										ksort($filtersOut);
										foreach($filtersOut as &$filterValues) {
											sort($filterValues);
										}
										$ghUrlRewrite[$num][$filtersColumnName] = json_encode($filtersOut); //encode filters for putting them in db and generating hash from them
										$hash = hash('md5',$ghUrlRewrite[$num][$ghUrlRewriteColumns[0]] . $ghUrlRewrite[$num][$ghUrlRewriteColumns[1]] . $ghUrlRewrite[$num][$filtersColumnName]);
										$ghUrlRewrite[$num][$hashIdColumnName] = $hash;
										$ghUrlRewriteHashes[$num] = $hash;
									} else {
										$this->_exception("Line %s in provided file has no filters",$num+1);
									}
								} else {
									$this->_exception("Line %s in provided file has no filters",$num+1);
								}
							} else {
								$warningEmptyLines++;
							}
						}
						if($warningEmptyLines) {
							$this->_getSession()->addWarning($hlp->__("Skipped %s empty lines in provided file",$warningEmptyLines));
						}
						//here we should have all csv rows processed to array that we can put directly in db, but we still need to check
						//if there is no already existing rewrite with same store, category and filters combination or if there are no duplicates in file

						//check for duplicates in file
						$ghUrlRewriteHashesReversed = array();
						$duplicateLines = array();
						foreach($ghUrlRewriteHashes as $line => $hash) {
							if(!isset($ghUrlRewriteHashesReversed[$hash])) {
								$ghUrlRewriteHashesReversed[$hash] = $line;
							} else {
								$duplicateLines[] = $ghUrlRewriteHashesReversed[$hash] + 1;
								$duplicateLines[] = $line + 1;
							}
						}
						if(count($duplicateLines)) { //this means that exacly same hash was calculated from more than one lines in csv - we cant allow that
							$this->_exception("Provided file contains duplicated lines: %s",implode(', ',array_unique($duplicateLines)));
						}


						//check in db
						/** @var GH_Rewrite_Model_Url $ghrewriteUrl */
						$ghRewriteUrlModel = Mage::getModel('ghrewrite/url');
						/** @var GH_Rewrite_Model_Resource_Url_Collection $ghrewriteUrlCollection */
						$ghrewriteUrlCollection = $ghRewriteUrlModel->getCollection();
						$ghrewriteUrlCollection->loadByHashId($ghUrlRewriteHashes);
						if($ghrewriteUrlCollection->getSize()) { //if there are results then someone is trying to place identical rewrite in db
							$hashesErrorLines = array();
							foreach($ghrewriteUrlCollection as $duplicate) {
								$hashesErrorLines[] = array_search($duplicate->getHashId(),$ghUrlRewriteHashes) + 1;
							}
							$this->_exception("File contains rewrites which already exists in database. Lines: %s",implode(", ",$hashesErrorLines));
						} else {
							//all is checked so now we can put everything to database
							/** @var GH_Rewrite_Model_Resource_Url $resource */
							$resource = $ghRewriteUrlModel->getResource();
							$resource->appendRewrites($ghUrlRewrite);
							$this->_getSession()->addSuccess($hlp->__("File was imported successfully. Imported rewrites: %s",count($ghUrlRewrite)));
						}

					} else {
						$this->_exception("Provided file is empty or corrupt");
					}
				}
			} else {
				$this->_exception("No file provided");
			}
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
		$this->_redirectReferer();
	}

	protected function _getCsvMimeTypes()
	{
		return array(
			'text/csv',
			'text/plain',
			'application/csv',
			'text/comma-separated-values',
			'application/excel',
			'application/vnd.ms-excel',
			'application/vnd.msexcel',
			'text/anytext',
			'application/octet-stream',
			'application/txt',
		);
	}

	/**
	 * @param string $msg
	 * @param null|int|string $var1
	 * @param null|int|string $var2
	 * @throws Mage_Core_Exception
	 */
	protected function _exception($msg,$var1=null,$var2=null)
	{
		Mage::throwException($this->_getHelper()->__($msg,$var1,$var2));
	}

	/**
	 * @return GH_Rewrite_Helper_Data
	 */
	protected function _getHelper()
	{
		return Mage::helper('ghrewrite');
	}

	/**
	 * @param array $value
	 * @return int|bool
	 * @throws Mage_Core_Exception
	 */
	private $_categories;
	protected function _getCategoryIdFromArray($value) {
		if(is_array($value) && isset($value[1]) && is_numeric($value[1]) && isset($value[0]) && is_numeric($value[0])) {
			/** @var Zolago_Catalog_Model_Category $category */
			if(!isset($this->_categories[$value[1]])) {
				$category = Mage::getModel('zolagocatalog/category')->load($value[1]);
				if($category->getId() == $value[1]) {
					return array((int)$value[0],(int)$value[1]);
				} else {
					$this->_exception("Category with id %s does not exist",$value[1]);
				}
			} else {
				return array((int)$value[0],(int)$value[1]);
			}
		} else {
			$this->_exception("File contains invalid store or category id");
			return false;
		}
	}

}