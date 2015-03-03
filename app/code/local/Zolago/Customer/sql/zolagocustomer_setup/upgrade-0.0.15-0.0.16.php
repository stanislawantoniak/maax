<?php

$installer = $this;
$installer->startSetup();

$this->updateAttribute('customer_address','lastname','is_required','false');
$this->updateAttribute('customer_address','firstname','is_required','false');

$installer->endSetup();