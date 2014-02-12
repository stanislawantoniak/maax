<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$hlp = Mage::helper('udropship');
$hlpr = Mage::helper('udratings');

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$conn = $this->_conn;
$installer->startSetup();

$conn->addColumn($installer->getTable('review/review'), 'rel_entity_pk_value', 'int(10)');
$conn->addKey($installer->getTable('review/review'), 'IDX_REL_ENTITY_PK_VALUE', 'rel_entity_pk_value');
$conn->addColumn($installer->getTable('rating/rating'), 'is_aggregate', 'tinyint(1) default 1');
$conn->modifyColumn($installer->getTable('rating/rating'), 'rating_code', 'varchar(255)');
$conn->dropKey($installer->getTable('rating/rating'), 'IDX_CODE');
$conn->addKey($installer->getTable('rating/rating'), 'IDX_CODE_ENTID', array('rating_code','entity_id'));

$myEt = Mage::helper('udratings')->myEt();

if (($row = $installer->getTableRow('rating/rating_entity', 'entity_id', $myEt))) {
    if ($row['entity_code']!='udropship_vendor') {
        Mage::throwException($hlpr->__("entity_id=%s is already used in rating/rating_entity. Change it in %s.",
            $myEt, 'Unirgy_DropshipVendorRatings_Helper_Data::$_myEt'
        ));
    }
} else {
    $conn->insert($installer->getTable('rating/rating_entity'), array('entity_id'=>10,'entity_code'=>'udropship_vendor'));
}
if (($row = $installer->getTableRow('review/review_entity', 'entity_id', $myEt))) {
    if ($row['entity_code']!='udropship_vendor') {
        Mage::throwException($hlpr->__("entity_id=%s is already used in review/review_entity. Change it in %s.",
            $myEt, 'Unirgy_DropshipVendorRatings_Helper_Data::$_myEt'
        ));
    }
} else {
    $conn->insert($installer->getTable('review/review_entity'), array('entity_id'=>10,'entity_code'=>'udropship_vendor'));
}

$conn->addColumn($installer->getTable('udropship/vendor'), 'allow_udratings', 'tinyint(1) default 1');

$conn->addColumn($installer->getTable('review/review'), 'helpfulness_yes', 'int(10) default 0');
$conn->addColumn($installer->getTable('review/review'), 'helpfulness_no', 'int(10) default 0');
$conn->addColumn($installer->getTable('review/review'), 'helpfulness_pcnt', 'decimal(10,2) default 0');

$this->_conn->addColumn($this->getTable('sales_flat_shipment'), 'udrating_emails_sent', 'tinyint unsigned not null default 0');
$this->_conn->addColumn($this->getTable('sales_flat_shipment'), 'udrating_date', 'datetime');
$this->_conn->addColumn($this->getTable('sales_flat_shipment'), 'udrating_date', 'datetime');
$this->_conn->addColumn($this->getTable('sales_flat_shipment_grid'), 'udrating_date', 'datetime');
$this->_conn->addKey($this->getTable('sales_flat_shipment_grid'), 'IDX_UDRATING_DATE', 'udrating_date');

$installer->endSetup();
