<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('orba_informwhenavailable_entry')};
    CREATE TABLE IF NOT EXISTS {$this->getTable('orba_informwhenavailable_entry')} (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `customer_id` INT DEFAULT NULL,
        `store_id` INT NOT NULL,
        `email` VARCHAR(255) DEFAULT NULL ,
        `sku` VARCHAR(255) NOT NULL,
        `is_active` BOOL NOT NULL DEFAULT 1,
        `is_subscription` BOOL NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL
    ) ENGINE = InnoDB;
");

@mail('magento@orba.pl', '[Instalacja] Inform When Available 0.1.1', "IP: ".$_SERVER['SERVER_ADDR']."\r\nHost: ".gethostbyaddr($_SERVER['SERVER_ADDR']), "From: ".(Mage::getStoreConfig('general/store_information/email_address') ? Mage::getStoreConfig('general/store_information/email_address') : 'magento@orba.pl'));

$installer->endSetup();