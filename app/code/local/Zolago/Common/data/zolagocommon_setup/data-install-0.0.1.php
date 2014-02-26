<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

/**
 * Install Polish Regions
 */

$regionData = array(
    array('country_id'=>'PL', 'code'=>'DO', 'default_name'=>'Dolnośląskie'),
    array('country_id'=>'PL', 'code'=>'KP', 'default_name'=>'Kujawsko-pomorskie'),
    array('country_id'=>'PL', 'code'=>'LU', 'default_name'=>'Lubelskie'),
    array('country_id'=>'PL', 'code'=>'LE', 'default_name'=>'Lubuskie'),
    array('country_id'=>'PL', 'code'=>'LU', 'default_name'=>'Łódzkie'),
    array('country_id'=>'PL', 'code'=>'MA', 'default_name'=>'Małopolskie'),
    array('country_id'=>'PL', 'code'=>'MZ', 'default_name'=>'Mazowieckie'),
    array('country_id'=>'PL', 'code'=>'OP', 'default_name'=>'Opolskie'),
    array('country_id'=>'PL', 'code'=>'PO', 'default_name'=>'Podkarpackie'),
    array('country_id'=>'PL', 'code'=>'PD', 'default_name'=>'Podlaskie'),
    array('country_id'=>'PL', 'code'=>'PM', 'default_name'=>'Pomorskie'),
    array('country_id'=>'PL', 'code'=>'SL', 'default_name'=>'Śląskie'),
    array('country_id'=>'PL', 'code'=>'SW', 'default_name'=>'Świętokrzyskie'),
    array('country_id'=>'PL', 'code'=>'WM', 'default_name'=>'Warmińsko-mazurskie'),
    array('country_id'=>'PL', 'code'=>'WP', 'default_name'=>'Wielkopolskie'),
    array('country_id'=>'PL', 'code'=>'ZC', 'default_name'=>'Zachodniopomorskie')
);

$installer->getConnection()->
        insertMultiple($installer->getTable("directory/country_region"), $regionData);
