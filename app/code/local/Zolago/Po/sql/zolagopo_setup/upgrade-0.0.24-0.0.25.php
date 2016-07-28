<?php


$installer = $this;
$installer->startSetup();

$installer->run("UPDATE core_email_template  SET template_text = REPLACE(template_text,'order.firstPo().getInpostLockerName()','order.firstPo().getDeliveryPointName()');");


$installer->endSetup();