<?php 
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Productcarousel module install script
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */

$installer = $this;
$installer->startSetup();

$installer->run("
		DROP TABLE IF EXISTS {$installer->getTable('productcarousel/productcarousel_store')};
		DROP TABLE IF EXISTS {$installer->getTable('productcarousel/productcarousel_product')};
		DROP TABLE IF EXISTS {$installer->getTable('productcarousel/productcarousel')};
        
        DELETE FROM {$installer->getTable('core/resource')} where code = 'magpassion_productcarousel_setup';
		");
 
$table = $installer->getConnection()
	->newTable($installer->getTable('productcarousel/productcarousel'))
	->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Product Carousel ID')
	->addColumn('blocktitle', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => false,
		), 'Block Title')

	->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => false,
		), 'Type')
        
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Category ID')
        
    ->addColumn('category', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'Caregory')
    
	->addColumn('skin', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'Navigation Skin')

	->addColumn('autoheight', Varien_Db_Ddl_Table::TYPE_INTEGER, 0, array(
		), 'auto height')
        
	->addColumn('imagewidth', Varien_Db_Ddl_Table::TYPE_INTEGER, 320, array(
		), 'Image width')

	->addColumn('imageheight', Varien_Db_Ddl_Table::TYPE_INTEGER, 270, array(
		), 'Image Height')

	->addColumn('numberproduct', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		), 'Number of products to get')
        
    ->addColumn('numberproductshow', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
		), 'Number of products to show')
        

	->addColumn('showblocktitle', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show block title')
        ->addColumn('newlabel', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'new label')
        ->addColumn('salelabel', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		), 'Sale label')

	->addColumn('showproductname', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show product name')

	->addColumn('showproductimage', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show product image')

	->addColumn('showproductprice', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show product price')
        ->addColumn('showreview', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show review summary')

	->addColumn('showproductaddtocart', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show add to cart button')

	->addColumn('showmoredes', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show more: product short description')
        
	->addColumn('showmoreaddtolink', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Show more: prouduct add to link')

	->addColumn('showpagination', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'show pagination')

	->addColumn('shownavigator', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'show navigator')

	->addColumn('showquickview', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'show quickview')

    ->addColumn('block_title_color', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
		), 'Block title color')
        
    ->addColumn('block_title_bg_color', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
		), 'Block title background color')
    
    ->addColumn('slidespeed', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		), 'slidespeed')
   ->addColumn('paginationspeed', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		), 'paginationspeed')
   ->addColumn('rewindspeed', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
		), 'rewindspeed')
    ->addColumn('direction', Varien_Db_Ddl_Table::TYPE_TEXT, 6, array(
		), 'direction')
        
	->addColumn('autoplay', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'auto play')
        
	->addColumn('pauseonhover', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'pause on hover')
        
	->addColumn('swipeontouch', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'swipe on touch')
        
	->addColumn('swipeonmouse', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'swipe on mouse')
        ->addColumn('customconfig', Varien_Db_Ddl_Table::TYPE_TEXT, 2047, array(
		), 'customconfig')
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		), 'Status')

	->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		), 'Product Carousel Creation Time')
	->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		), 'Product Carousel Modification Time')
	->setComment('Product Carousel Table');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
	->newTable($installer->getTable('productcarousel/productcarousel_store'))
	->addColumn('productcarousel_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false,
		'primary'   => true,
		), 'Product Carousel ID')
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Store ID')
	->addIndex($installer->getIdxName('productcarousel/productcarousel_store', array('store_id')), array('store_id'))
	->addForeignKey($installer->getFkName('productcarousel/productcarousel_store', 'productcarousel_id', 'productcarousel/productcarousel', 'entity_id'), 'productcarousel_id', $installer->getTable('productcarousel/productcarousel'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($installer->getFkName('productcarousel/productcarousel_store', 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Product Carousels To Store Linkage Table');
$installer->getConnection()->createTable($table);
$table = $installer->getConnection()
	->newTable($installer->getTable('productcarousel/productcarousel_product'))
	->addColumn('rel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Category ID')
	->addColumn('productcarousel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'default'   => '0',
	), 'Product Carousel ID')
	->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned'  => true,
		'nullable'  => false,
		'default'   => '0',
	), 'Product ID')
	->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,
		'default'   => '0',
	), 'Position')
	->addIndex($installer->getIdxName('productcarousel/productcarousel_product', array('product_id')), array('product_id'))
	->addForeignKey($installer->getFkName('productcarousel/productcarousel_product', 'productcarousel_id', 'productcarousel/productcarousel', 'entity_id'), 'productcarousel_id', $installer->getTable('productcarousel/productcarousel'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->addForeignKey($installer->getFkName('productcarousel/productcarousel_product', 'product_id', 'catalog/product', 'entity_id'),	'product_id', $installer->getTable('catalog/product'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
	->setComment('Product Carousel to Product Linkage Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
