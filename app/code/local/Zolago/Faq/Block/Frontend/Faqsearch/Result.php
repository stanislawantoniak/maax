<?php

class Zolago_Faq_Block_Frontend_Faqsearch_Result extends Inic_Faq_Block_Frontend_Faqsearch_Result {

    /**
     * Function to gather the searched terms in questions
     *
     * @return Inic_Faq_Model_Faq Collection
     */
    public function getSearch() {
        if (!$this->_search) {
            $keyword=$this->getRequest()->getParam('keyword');

            $this->_search = Mage :: getModel('faq/faq')->getCollection();
            if($this->getRequest()->getParam('cat_id')){
                $id=$this->getRequest()->getParam('cat_id');
                $category = Mage :: getModel('faq/category')->load($id);
                $this->_search=$category->getItemCollection()->addIsActiveFilter()->addStoreFilter(Mage::app()->getStore());
            }
            if($keyword!=""){
                $this->_search
                    ->getSelect()
                    ->where("(question LIKE ?) OR (answer LIKE ?)", "%".$keyword. "%")
                ;
            }
        }
        return $this->_search;
    }
}