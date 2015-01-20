<?php

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$custAttr = 'payment_method';

$setup->removeAttribute('customer', $custAttr);

$setup->endSetup();