<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run("
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('ipko',1,'Płacę z iPKO','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('pbs',1,'Płacę z PBS','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('orange',1,'Płacę z Orange','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('blik',1,'BLIK','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('bps',1,'Banki Spółdzielcze','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('bplus',1,'Płacę z Plus Bank','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('bgetin',1,'Getin Bank PBL','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('noblepay',1,'Noble Pay','gateway');
INSERT INTO `zolago_payment_provider` (`code`,`is_active`,`name`,`type`) VALUES ('ideacloud',1,'Idea Cloud','gateway');

UPDATE  `zolago_payment_provider`   SET is_active=1 WHERE zolago_payment_provider.code='p';
UPDATE  `zolago_payment_provider`   SET is_active=0 WHERE zolago_payment_provider.code='m';

");

$installer->endSetup();