<?php
/**
 * block test
 */
class Zolago_Adminhtml_Block_SetTest extends Zolago_TestCase
{
    /**
     * @requires function no_coverage
     */
    public function testExtendedAttributeTreeJson() {
        if (!no_coverage()) { 
            $this->markTestSkipped('Coverage');
            
            return;
        }		
		static::setAttributeSet();
		$layout = Mage::app()->getLayout();
		$block = $layout->createBlock('zolagoadminhtml/catalog_product_attribute_set_main', null, array('template'=>'zolagoadminhtml/catalog/product/attribute/set/main.phtml'));
	
		$this->assertNotEmpty($block);
		$this->assertEquals(static::getEmptyJsonTree(), $block->getExtendedAttributeTreeJson(true));
		$this->assertNotNull($block->getExtendedAttributeTreeJson(true));
	}
	
    static public function getItem($name) {
        $model = Mage::getModel($name);
        $collection = $model->getCollection();
        $collection->setPageSize(1);
        $collection->load();
        return $collection->getFirstItem();
    }	
	
    /**
     * Get any Attribute Set
     */
    static public function setAttributeSet() {
        Mage::register('current_attribute_set', static::getItem('eav/entity_attribute_set'));
    }	
	
    static public function getEmptyJsonTree() {
        $data[] = array (
            'text' => 'Empty',
            'id' => 'empty',
            'cls' => 'folder',
            'allowDrop' => false,
            'allowDrag' => false
        );
        return Mage::helper('core')->jsonEncode($data);
    }	
}