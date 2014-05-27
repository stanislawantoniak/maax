<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
CREATE OR REPLACE
VIEW `vw_product_relation_prices_sizes` AS
SELECT  `product_relation`.`parent_id` AS `parent`,  `product_relation`.`child_id` AS `child`,  `prices`.`store_id` AS `store`,
`prices`.`value` AS `child_price`,  `products`.`sku` AS `sku`,  `prices`.`attribute_id` AS `attribute_id`,  `sizes`.`value` AS `child_size` FROM (((`catalog_product_relation` `product_relation`
JOIN `catalog_product_entity_decimal` `prices`  ON ((`product_relation`.`child_id` = `prices`.`entity_id`)))
JOIN `catalog_product_entity_int` `sizes`  ON ((`product_relation`.`child_id` = `sizes`.`entity_id`)))

JOIN `catalog_product_entity` `products`  ON ((`products`.`entity_id` = `product_relation`.`child_id`))) WHERE ((`prices`.`attribute_id` = 75)  AND (`sizes`.`attribute_id` = 281))
");

$installer->endSetup();




