<?php

$this->startSetup();

$this->run("

ALTER TABLE `{$this->getTable('udqa/question')}`
ADD `is_vendor_agents_notified` TINYINT(1) NOT NULL DEFAULT '0';

");

$this->endSetup();

