<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

// Add Category Blog Post attribute
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'category_blog_post_id', array(
    'group'         => 'Blog',
    'input'         => 'text',
    'type'          => 'int',
    'label'         => 'Category Blog Post',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
));

$installer->endSetup();