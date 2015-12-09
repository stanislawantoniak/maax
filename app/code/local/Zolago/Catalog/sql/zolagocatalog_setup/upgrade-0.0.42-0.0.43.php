<?php
/**
 * Add 'attribute_base_store' into Admin Store View Information edit form
 * If attribute_base_store will be specified labels will be taken if not present.
 * If you do not specify a store view then the default (Admin) labels will be used
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$table = $installer->getTable('core/store');

$this->getConnection()
    ->addColumn($table, 'attribute_base_store', array(
        "type"      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        "unsigned"  => true,
        "nullable"  => true,
        "comment"   => "From with store view should be taken labels/options",
    ));

$installer->endSetup();

?>
<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$tableCatalogCategory = $resource->getTableName('catalog_category_product');
$tableCatalogProduct = $resource->getTableName('catalog_product_entity');
$query = "DELETE FROM {$tableCatalogCategory} where product_id NOT IN (SELECT entity_id FROM ({$tableCatalogProduct}))";
$writeConnection->query($query);

$installer->endSetup();?>
