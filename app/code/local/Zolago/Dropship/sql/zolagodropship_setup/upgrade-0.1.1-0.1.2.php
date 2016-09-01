<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
update `cms_block` set identifier = REPLACE(identifier, 'udropship-help-pl-udropship', 'zolagoos-help-pl-zolagoos') where identifier like 'udropship-help-pl-%';
update `cms_block` set identifier = REPLACE(identifier, 'udropship-help-en-udropship', 'zolagoos-help-en-zolagoos') where identifier like 'udropship-help-en-%';
update `cms_block` set identifier = REPLACE(identifier, 'udropship-help', 'zolagoos-help') where identifier like 'udropship-help%';

");

$installer->endSetup();