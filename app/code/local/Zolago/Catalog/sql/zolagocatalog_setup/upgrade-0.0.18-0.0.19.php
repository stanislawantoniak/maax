<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */
$entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
$priceCode = 'price';
$sizeCode = 'size';

$installer->startSetup();
$installer->run("
CREATE OR REPLACE VIEW `vw_product_relation_prices_sizes_relation` AS
SELECT
  `product_relation`.`child_id` AS `child`,
  `product_relation`.`parent_id` AS `parent`,
  `prices`.`store_id` AS `store`,
  `websites`.`website_id` AS `website`,
  `prices`.`value` AS `child_price`,
  `sizes`.`value` AS `child_size`
FROM
  `catalog_product_relation` `product_relation`
  JOIN `catalog_product_entity_decimal` `prices`
    ON `product_relation`.`child_id` = `prices`.`entity_id`
  JOIN `core_store` `websites`
    ON `websites`.`store_id` = `prices`.`store_id`
  JOIN `catalog_product_entity_int` `sizes`
    ON `product_relation`.`child_id` = `sizes`.`entity_id`
WHERE `prices`.`attribute_id` IN
  (SELECT
    `eav_attribute`.`attribute_id`
  FROM
    `eav_attribute`
  WHERE `eav_attribute`.`attribute_code` = '{$priceCode}'
    AND `eav_attribute`.`entity_type_id` = {$entityTypeID}
    AND `eav_attribute`.backend_type = 'decimal')
  AND `sizes`.`attribute_id` IN
  (SELECT
    `eav_attribute`.`attribute_id`
  FROM
    `eav_attribute`
  WHERE `eav_attribute`.`attribute_code` = '{$sizeCode}'
    AND `eav_attribute`.`entity_type_id` = {$entityTypeID}
    AND `eav_attribute`.backend_type = 'int')
");

$installer->endSetup();




