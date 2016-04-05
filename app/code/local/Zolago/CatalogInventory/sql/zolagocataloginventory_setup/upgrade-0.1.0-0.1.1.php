<?php

$installer = $this;

$installer->startSetup();
$table = $installer->getTable('zolago_cataloginventory_stock_website');
$installer->run("
CREATE TABLE `{$table}` (
    `website_id` int(11) unsigned NOT NULL,
    `product_id` int(11) unsigned NOT NULL,
    `is_in_stock` smallint(5) unsigned NOT NULL DEFAULT '0',
    KEY `fk_zolagocataloginventory_stock_website_website` (website_id),
    KEY `fk_zolagocataloginventory_stock_website_product` (product_id),
    UNIQUE KEY `website_id` (`website_id`,`product_id`),
    CONSTRAINT `fK_zolagocataloginventory_stock_website_website` FOREIGN KEY (website_id) REFERENCES {$this->getTable('core/website')} 
        (website_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fK_zolagocataloginventory_stock_website_product` FOREIGN KEY (product_id) REFERENCES {$this->getTable('catalog/product')} 
        (entity_id) ON DELETE CASCADE ON UPDATE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
    



$installer->endSetup();