<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();


// Add Product Blog Post attribute
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$numbers = range(1, 5);

foreach ($numbers as $n) {
    $setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, "product_blog_post_id_{$n}", array(
        'group' => 'Blog',
        'input' => 'text',
        'type' => 'int',
        'label' => "Product Blog Post {$n}",
        'backend' => '',
        'visible' => true,
        'required' => false,
        'visible_on_front' => true,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    ));
}


$installer->endSetup();