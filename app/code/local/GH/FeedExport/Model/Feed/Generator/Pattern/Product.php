<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Pattern_Product
 */
class GH_FeedExport_Model_Feed_Generator_Pattern_Product extends Mirasvit_FeedExport_Model_Feed_Generator_Pattern_Product
{
    //Category separator  (e.g. Apparel/Men/T-Shirts)
    const FEEDEXPORT_CS = '/';


    public function getValue($pattern, $product, $row = array())
    {
        $value   = null;
        $pattern = $this->parsePattern($pattern);

        $this->evalValue($pattern, $value, $product);

        if ($pattern['type'] == 'parent') {
            $product = $this->_getParentProduct($product);
        }

        if ($pattern['type'] == 'grouped') {
            $products             = $this->_getChildProducts($product);
            $values               = array();
            $childPattern         = $pattern;
            $childPattern['type'] = null;
            foreach ($products as $child) {
                $child = $child->load($child->getId());
                $value = $this->getValue($childPattern, $child);
                if ($value) {
                    $values[] = $value;
                }
            }

            $value = implode(',', $values);

            return $value;
        }

        switch($pattern['key']) {
            case 'url':
                $value = Mage::helper('feedexport')->getProductUrl($product, $this->getFeed()->getStoreId());

                if ($this->getFeed()) {
                    $getParams = array();

                    if ($this->getFeed()->getReportEnabled()) {
                        $getParams['fee'] = $this->getFeed()->getId();
                        $getParams['fep'] = $product->getId();
                    }

                    $patternModel = Mage::getSingleton('feedexport/feed_generator_pattern');
                    if ($this->getFeed()->getGaSource()) {
                        $getParams['utm_source'] = $patternModel->getPatternValue($this->getFeed()->getGaSource(), 'product', $product);
                    }
                    if ($this->getFeed()->getGaMedium()) {
                        $getParams['utm_medium'] = $patternModel->getPatternValue($this->getFeed()->getGaMedium(), 'product', $product);
                    }
                    if ($this->getFeed()->getGaName()) {
                        $getParams['utm_campaign'] = $patternModel->getPatternValue($this->getFeed()->getGaName(), 'product', $product);
                    }
                    if ($this->getFeed()->getGaTerm()) {
                        $getParams['utm_term'] = $patternModel->getPatternValue($this->getFeed()->getGaTerm(), 'product', $product);
                    }
                    if ($this->getFeed()->getGaContent()) {
                        $getParams['utm_content'] = $patternModel->getPatternValue($this->getFeed()->getGaContent(), 'product', $product);
                    }

                    if (count($getParams)) {
                        $value .= strpos($value, '?') !== false ? '&' : '?';
                        $value .= http_build_query($getParams);
                    }
                }

                break;

            case 'image':
            case 'thumbnail':
            case 'small_image':
                $this->imageValue($pattern, $value, $product);
                break;

            case 'image2':
            case 'image3':
            case 'image4':
            case 'image5':
            case 'image6':
            case 'image7':
            case 'image8':
            case 'image9':
            case 'image10':
            case 'image11':
            case 'image12':
            case 'image13':
            case 'image14':
            case 'image15':
                $this->imageGalleryValue($pattern, $value, $product);

                break;

            case 'qty':
                $value = intval($row["stock_qty"]);
                break;

            case 'is_in_stock':
                $value = intval($row["is_in_stock"]);
                break;

            case 'category_id':
                $this->_prepareProductCategory($product);
                $value = $product->getData('category_id');
                break;

            case 'category':
                $this->_prepareProductCategory($product);
                $value = $product->getCategory();
                break;

            case 'category_url':
                $this->_prepareProductCategory($product);
                if ($product->getCategoryModel()) {
                    $value = $product->getCategoryModel()->getUrl();
                }
                break;

            case 'category_path':
                $this->_prepareProductCategory($product);
                $value = $product->getCategoryPath();
                break;

            case 'price':
                $value = Mage::helper('tax')->getPrice($product, $product->getPrice());
                break;

            case 'final_price':
                if ($product->getTypeId() == 'bundle') {
                    $bundle = Mage::getModel('bundle/product_price');
                    $prices = $bundle->getTotalPrices($product);
                    if (isset($prices[0])) {
                        $value = $prices[0];
                        break;
                    }
                } else {
                    $value = Mage::helper('tax')->getPrice($product, $product->getFinalPrice());
                }

                break;

            case 'store_price':
                $value = $this->getStore()->convertPrice($product->getFinalPrice(), false, false);
                break;

            case 'base_price':
                $value = $product->getPrice();
                break;

            case 'tier_price':
                $tierPrice = $product->getTierPrice();
                if (count($tierPrice)) {
                    $value = $tierPrice[0]['price'];
                }
                break;

            case 'group_price':
                $groupPrice = $product->getData('group_price');
                if (count($groupPrice)) {
                    $value = $groupPrice[0]['price'];
                }
                break;

            case 'attribute_set':
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($product->getAttributeSetId());

                $value = $attributeSetModel->getAttributeSetName();
                break;

            case 'weight':
                if ($product->getTypeId() == 'bundle') {
                    $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product), $product
                    );
                    $productIds = array(0);
                    foreach($selectionCollection as $option) {
                        $productIds[] = $option->product_id;
                    }
                    $collection = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect('weight')
                        ->addFieldToFilter('entity_id', array('in' => $productIds));
                    $value = 0;
                    foreach ($collection as $subProduct) {
                        $value += $subProduct->getWeight();
                    }
                } else {
                    $value = $product->getData('weight');
                }
                break;

            case 'rating_summary':
                $summaryData = Mage::getModel('review/review_summary')->load($product->getId());
                $value       = $summaryData->getRatingSummary() * 0.05;
                break;

            case 'reviews_count':
                $summaryData = Mage::getModel('review/review_summary')->load($product->getId());
                $value       = $summaryData->getReviewsCount();
                break;

            default:
                if (substr($pattern['key'], 0, strlen('group_price')) == 'group_price') {
                    $custId = substr($pattern['key'], strlen('group_price'));
                    $groupPrice = $product->getData('group_price');
                    if (is_array($groupPrice)) {
                        foreach ($groupPrice as $key => $price) {
                            if ($price['cust_group'] == $custId) {
                                $value = $price['price'];
                            }
                        }
                    }
                    break;
                }

                $attribute = $this->_getProductAttribute($pattern['key']);
                if ($attribute) {
                    if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                        $value = $product->getResource()
                            ->getAttribute($pattern['key'])
                            ->getSource()
                            ->getOptionText($product->getData($pattern['key']));
                        $value = implode(', ', (array) $value);
                    } else {
                        $value = $product->getData($pattern['key']);
                    }
                } else {
                    if ($product->hasData($pattern['key'])) {
                        $value = $product->getData($pattern['key']);
                    }
                }
        }

        $this->dynamicAttributeValue($pattern, $value, $product);
        $this->dynamicCategoryValue($pattern, $value, $product);
        $this->amastyMetaValue($pattern, $value, $product);

        $value = $this->applyFormatters($pattern, $value);

        return $value;
    }

    protected function _prepareProductCategory(&$product)
    {
        $category = null;
        $currentPosition = null;

        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->getSelect()
            ->joinInner(
                array('category_product' => $collection->getTable('catalog/category_product')),
                'category_product.category_id = entity_id AND category_product.product_id = ' . $product->getId(),
                array('product_position' => 'position')
            )
            ->order(new Zend_Db_Expr('`category_product`.`position` asc'));

        foreach ($collection as $cat) {
            $categoryInStoreTree = $this->getCategory($cat->getId());
            if ($categoryInStoreTree &&
                (is_null($category) || $cat->getLevel() > $category->getLevel()) &&
                (is_null($currentPosition) || $cat->getProductPosition() <= $currentPosition)
            ) {
                $category = $categoryInStoreTree;
                $currentPosition = $category->getProductPosition();
            }
        }

        if ($category) {
            $categoryPath = array($category->getName());
            $parentId = $category->getParentId();

            if ($category->getLevel() > $this->getRootCategory()->getLevel()) {
                $i = 0;
                while ($_category = $this->getCategory($parentId)) {

                    if ($_category->getLevel() <= $this->getRootCategory()->getLevel()) {
                        break;
                    }
                    $categoryPath[] = $_category->getName();
                    $parentId = $_category->getParentId();

                    $i++;
                    if ($i > 10 || $parentId == 0) {
                        break;
                    }
                }
            }

            $product->setCategory($category->getName());
            $product->setCategoryModel($category);
            $product->setCategoryId($category->getEntityId());
            $product->setCategoryPath(implode(self::FEEDEXPORT_CS, array_reverse($categoryPath)));
        } else {
            $product->setCategory('');
            $product->setCategorySubcategory('');
        }
    }

}