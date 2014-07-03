<?php

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
CREATE OR REPLACE
VIEW `vw_product_relation_prices_sizes` AS
select  `product_relation`.`parent_id` AS `parent`,  `product_relation`.`child_id` AS `child`,  `prices`.`store_id` AS `store`,  `websites`.`website_id` AS `website`,  `prices`.`value` AS `child_price`,  `products`.`sku` AS `sku`,  `prices`.`attribute_id` AS `attribute_id`,  `sizes`.`value` AS `child_size` from ((((`catalog_product_relation` `product_relation`  join `catalog_product_entity_decimal` `prices`  on ((`product_relation`.`child_id` = `prices`.`entity_id`)))  join `core_store` `websites`  on ((`websites`.`store_id` = `prices`.`store_id`)))  join `catalog_product_entity_int` `sizes`  on ((`product_relation`.`child_id` = `sizes`.`entity_id`)))  join `catalog_product_entity` `products`  on ((`products`.`entity_id` = `product_relation`.`child_id`))) where ((`prices`.`attribute_id` = 75)  and (`sizes`.`attribute_id` = 281));
");

$installer->endSetup();




