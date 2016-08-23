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

 

class Itoris_GroupedProductPromotions_Model_Mysql4_Rules_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $tableFileGroup = 'itoris_groupedproductpromotions_rules_group';
    protected $tableProduct = 'itoris_groupedproductpromotions_rules_product';

    protected function _construct() {
        $this->_init('itoris_groupedproductpromotions/rules');
        $this->tableFileGroup = $this->getTable('group');
        $this->tableProduct = $this->getTable('product');
    }

    protected function _initSelect() {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            array('group' => $this->tableFileGroup),
            'group.rule_id = main_table.rule_id',
            array('group_id' => 'group_concat(distinct group.group_id)')
        )->group('main_table.rule_id');
        return $this;
    }

    public function addFilterByProductId($id) {
        $this->getSelect()->where('product_id = ' . $id);
        return $this;
    }

    public function addGroupFilter($groupId) {
        $this->_select->having("group_id IS NULL OR FIND_IN_SET('" . intval($groupId) . "', group_id)");
        return $this;
    }

    public function addStoreFilter($storeId = 0) {
        $storeId = intval($storeId);

        if ($storeId) {
            $this->addFieldToFilter('main_table.store_id', array('in' => array($storeId, 0)));
            $parentIdsSelect = $this->getAllParentIds($storeId);
            if (!empty($parentIdsSelect)) {
                $this->addFieldToFilter('main_table.rule_id', array('nin' => $parentIdsSelect));
            }
        } else {
            $this->addFieldToFilter('main_table.store_id', array('eq' => 0));
        }
        return $this;
    }

    public function getAllParentIds($storeId) {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->reset(Zend_Db_Select::WHERE);
        $idsSelect->reset(Zend_Db_Select::HAVING);
        $idsSelect->reset(Zend_Db_Select::LEFT_JOIN);

        $idsSelect->columns('parent_id', 'main_table');
        $idsSelect->where('store_id=?', $storeId);
        return $this->getConnection()->fetchCol($idsSelect);
    }
}
?>