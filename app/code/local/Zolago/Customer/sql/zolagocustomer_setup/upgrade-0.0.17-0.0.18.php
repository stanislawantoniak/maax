<?php

$installer = $this;
$installer->startSetup();

$this->updateAttribute('customer','lastname','is_required',0);
$this->updateAttribute('customer','firstname','is_required',0);

$installer->endSetup();