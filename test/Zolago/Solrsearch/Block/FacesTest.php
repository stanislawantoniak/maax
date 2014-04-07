<?php
class Category_Mock {
    public function getCategories() {
        return array();
    }
    public function getId() {
        return 1;
    }
    public function getUsePriceFilter() {
        return true;
    }
    public function getUseFlagFilter() {
        return true;
    }
    public function getUseReviewFilter() {
        return true;
    }
}
class Zolago_Solrsearch_Block_FacesTest extends ZolagoDb_TestCase {
    protected function _getBlock() {
        $block = Mage::app()->getLayout()->createBlock('Zolago_Solrsearch_Block_Faces');
        $this->assertNotNull($block);
        return $block;
    }
    public function testCreate() {
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage');
            return;
        }


        $block = $this->_getBlock();
        $block->getSpecialMultiple();
    }
    protected function _prepareSolrData() {
        $solr = array (
            'responseHeader' => array (
                'params' => array (
                    'q' => '**',
                    'fq' => '(store_id:"1") AND (website_id:"1") AND (product_status:"1") AND (category_id:"18" OR category_id:"18" OR category_id:"19" OR category_id:"4" OR category_id:"5" OR category_id:"16" OR category_id:"17") AND (filter_visibility_int:"2" OR filter_visibility_int:"4")',
                ),
            ),
            'response' => array (
                
            ),
            'facet_counts' => array (
                'facet_fields' => array (   
                    'category_path' => array (
                        'Ubrania/18' => 12,
                        'Ubrania/18/Buty/5' => 7,
                        'Ubrania/18/Buty/5/Damskie/17' => 5,
                        'Ubrania/18/Buty/5/Meskie/19' => 2,
                        'Ubrania/18/Koszulki/4' => 4,
                        'Ubrania/18/Spodnie/22' => 1,
                    ),
                ),
            ),
            'stats' => array (
            )
        );
        return $solr;
    }
    public function testMultiple() {
        // mocking current category
        Mage::register('current_category',new Category_Mock());
        if (!no_coverage()) {
            $this->markTestSkipped('Coverage');
            return;
        }
        $block = $this->_getBlock();
        $data = $this->_prepareSolrData();
        $block->setSolrData($data);
        $filterBlock = $block->getFilterBlocks();
        $this->assertInternalType('array',$filterBlock);
        $class = array();
        foreach ($filterBlock as $item) {
            $class[] = get_class($item);
        }
        $this->assertContains('Zolago_Solrsearch_Block_Faces_Category',$class);
        $this->assertContains('Zolago_Solrsearch_Block_Faces_Price',$class);        
    }
}