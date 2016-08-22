<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_GROUPEDPRODUCTPROMOTIONS
 * @copyright  Copyright (c) 2013 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

 

class Itoris_GroupedProductPromotions_Block_Admin_Product_Edit_Tab_Promotions
        extends Mage_Adminhtml_Block_Catalog_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('itoris/groupedproductpromotions/product/promotions.phtml');
        $this->setAfter('super');
    }

    public function getTabLabel() {
        return $this ->__('Promotion Rules');
    }

    public function getTabTitle() {
        return $this ->__('Promotion Rules');
    }

    public function canShowTab() {
        if($this->getDataHelper()->getSettings()->getEnabled() && $this->getDataHelper()->isAdminRegistered() && $this->getCurrentProduct()->getTypeId() == 'grouped') {
            return true;
        } else {
            return false;
        }
    }

    public function isHidden() {
        return false;
    }

    /**
     * @return Itoris_GroupedProductPromotions_Helper_Data
     */
    public function getDataHelper() {
        return Mage::helper('itoris_groupedproductpromotions');
    }

    public function getCurrentProduct() {
        return Mage::registry('current_product');
    }

    public function getAssociatedProducts() {
        $currentProduct = $this->getCurrentProduct();
        return $currentProduct->getTypeInstance(true)->getAssociatedProducts($currentProduct);
    }

    public function getRulesForLoad() {
        $rulesCollection = Mage::getModel('itoris_groupedproductpromotions/rules')->getCollection();
        $rulesCollection->addFilterByProductId($this->getCurrentProduct()->getId());
        $storeId = $this->getRequest()->getParam('store');
        $rulesCollection->addStoreFilter($storeId);
        $rulesCollection->getSelect()->order('main_table.position DESC');
        $rules = array();
        foreach ($rulesCollection as $model) {
            $rules[] = array(
                'rule_id'     => $model->getRuleId(),
                'title'       => $model->getTitle(),
                'position'    => $model->getPosition(),
                'status'      => $model->getStatus(),
                'active_from' => $model->getActiveFrom() ? $this->getDataHelper()->getDate($model->getActiveFrom())->toString($this->getDateFormatWithLongYear()) : null,
                'active_to'   => $model->getActiveTo() ? $this->getDataHelper()->getDate($model->getActiveTo())->toString($this->getDateFormatWithLongYear()) : null,
                'group_id'    => $model->getGroupId(),
                'parent_id'   => $model->getParentId(),
                'store_id'    => $model->getStoreId(),
                'price_method'      => (int)$model->getPriceMethod(),
                'discount_promoset' => $model->getDiscountPromoset(),
                'code_promoset'     => (int)$model->getCode(),
                'fixed_price'       => $model->getFixedPrice(),
            );
        }
        return $rules;
    }

	public function getDateFormatWithLongYear(){
		$locale = Mage::app()->getLocale();
        return preg_replace('/(?<!y)yy(?!y)/', 'yyyy', $locale->getTranslation($locale->FORMAT_TYPE_SHORT, 'date')); 
	}
	
    public function lastRuleIdFromDb() {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('read');
        $tableRules = $resource->getTableName('itoris_groupedproductpromotions_rules');
        return $connection->fetchOne("select max(rule_id) from {$tableRules}");
    }

    public function getSubProductForLoad() {
        $subProducts = $this->getAssociatedProducts();
        $product = array();
        $product['name'] = array();
        foreach ($subProducts as $subProduct) {
            $product['name'][$subProduct->getId()] = $subProduct->getName();
            $productCollection = Mage::getModel('itoris_groupedproductpromotions/product')->getCollection();
            foreach ($productCollection as $model) {
                if ($subProduct->getId() == $model->getProductId()) {
                    if (array_key_exists($model->getRuleId(), $product)) {
                        $product[$model->getRuleId()][$subProduct->getId()] = array(
                                'rule_product_id' => $model->getRuleProductId(),
                                'rule_id'         => $model->getRuleId(),
                                'product_id'      => $model->getProductId(),
                                'in_set'          => $model->getInSet(),
                                'qty'             => $model->getQty(),
                                'discount'        => $model->getDiscount(),
                                'type'            => $model->getType(),
                                'name'            => $subProduct->getName(),
                                'show_promoset'   => $model->getShowPromoset(),
                        );
                    } else {
                        $product[$model->getRuleId()] = array(
                            $subProduct->getId() => array(
                                'rule_product_id' => $model->getRuleProductId(),
                                'rule_id'         => $model->getRuleId(),
                                'product_id'      => $model->getProductId(),
                                'in_set'          => $model->getInSet(),
                                'qty'             => $model->getQty(),
                                'discount'        => $model->getDiscount(),
                                'type'            => $model->getType(),
                                'name'            => $subProduct->getName(),
                                'show_promoset'   => $model->getShowPromoset(),
                            )
                        );
                    }
                }
            }
        }
        return $product;
    }

    public function getIdSubProducts() {
        $subProducts = $this->getAssociatedProducts();
        $product = array();
        foreach ($subProducts as $subProduct) {
            $product[] = $subProduct->getId();
        }
        return $product;
    }

    public function canDisplayUseDefault() {
        $checkStore = Mage::app()->getRequest()->getParam('store');
        if ($checkStore) {
            return true;
        } else {
            return false;
        }
    }
}

?>