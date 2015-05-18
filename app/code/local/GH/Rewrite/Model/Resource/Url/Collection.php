<?php
/**
 * collection for url table
 */
 class GH_Rewrite_Model_Resource_Url_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct() {
        parent::_construct();
        $this->_init('ghrewrite/url');
    }                             
    
    
    public function joinRewriteUrl() {
         $table = $this->getTable("core/url_rewrite");
         $this->getSelect()
             ->join(
                array('url_rewrite' =>  $table),
                'main_table.url_rewrite_id = url_rewrite.url_rewrite_id',
                array('url_rewrite.request_path','url_rewrite.target_path','main_table.category_id','url_rewrite.store_id'));
                                                                     
    }
 }
 
