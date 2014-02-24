<?php
/**
 * block test
 */
class Zolago_Operator_Dropship_OperatorTest extends Zolago_TestCase {
    
    /**
     * @requires function no_coverage
     */
    public function testBlockList() {
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('zolagooperator/dropship_operator',null,array('template'=>'zolagooperator/dropship/operator.phtml'));
        $this->assertNotEmpty($block);                
        $collection = $block->getCollection();
        $this->assertNotNull($collection);
        
    }
    /**
     * @requires function no_coverage
     */
    public function testBlockEdit() {        
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('zolagooperator/dropship_operator_edit',null,array('template'=>'zolagooperator/dropship/operator/edit.phtml'));
        $this->assertNotEmpty($block);                
    }
}