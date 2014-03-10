<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install path to wsdl 
 */

$configData = array(
        'scope' => 'default',
        'scope_id' => 0,
        'path' => 'zolagodhl/wsdl_path',
        'value' => 'https://testowy.dhl24.com.pl/webapi',
        );

$installer->getConnection()->
        insert($installer->getTable("core/config_data"), $configData);
