<?php
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Product Carousel widget block
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Productcarousel_Widget_View extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface{
	protected $_htmlTemplate = 'magpassion_productcarousel/productcarousel/widget/view.phtml';
	
    /**
	 * Prepare a for widget
	 * @access protected
	 * @return MagPassion_Productcarousel_Block_Productcarousel_Widget_View
	 * @author MagPassion.com
	 */
	protected function _beforeToHtml() {
		parent::_beforeToHtml();
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addCss('myfile.css');
            $head->addJs('myfile.js');
        }
		$productcarouselId = $this->getData('productcarousel_id');
		if ($productcarouselId) {
			$productcarousel = Mage::getModel('productcarousel/productcarousel')
				->setStoreId(Mage::app()->getStore()->getId())
				->load($productcarouselId);
		if ($productcarousel->getStatus()) {
				$this->setCurrentProductcarousel($productcarousel);
				$this->setTemplate($this->_htmlTemplate);
			}
		}
		return $this;
	}
    
    /**
	 * get the list of products
	 * @access public
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 * @author MagPassion.com
	 */
    public function getProductCollection($type = 'custom', $category_id = 0, $count=6){
        if ($type == 'new') {
            $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $todayEndOfDayDate  = Mage::app()->getLocale()->date()
                ->setTime('23:59:59')
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
            
            if ($category_id > 0) {
                $cate = Mage::getModel('catalog/category')->load($category_id);
                $collection->addCategoryFilter($cate);
            }

            $collection = $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter()
                ->addAttributeToFilter('news_from_date', array('or'=> array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('news_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                        )
                  )
                ->addAttributeToSort('news_from_date', 'desc')
                ->setPageSize($count);
        }
        else if ($type == 'mostview') {
            $collection = Mage::getResourceModel('reports/product_collection') 
                        ->addStoreFilter()
                        ->addViewsCount();
            if ($category_id > 0) {
                $cate = Mage::getModel('catalog/category')->load($category_id);
                $collection->addCategoryFilter($cate);
            }
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $collection->setPageSize($count);
        }
        else {
            $collection = $this->getCurrentProductcarousel()->getSelectedProductsCollection();
            $collection->addAttributeToSelect('name');
            $collection->addUrlRewrite();
            $collection->getSelect()->order('related.position');
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $collection->setPageSize($count);
        }
        
		return $collection;
	}
	
}