<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Model_Mysql4_Lookbookslider_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('lookbookslider/lookbookslider');
    }
    
    /**
     * Add Filter by position
     *
     * @param string $position
     * @return Altima_Lookbookslider_Model_Mysql4_Lookbookslider_Collection
     */
    public function addPositionFilter($position) {
        $this->getSelect()->where('main_table.position = ?', $position);
        return $this;
    }

    /**
     * Add Filter by category
     *
     * @param int $category
     * @return Altima_Lookbookslider_Model_Mysql4_Lookbookslider_Collection
     */
    public function addCategoryFilter($category) {
        $this->getSelect()->join(
                array('category_table' => $this->getTable('lookbookslider/category')),
                'main_table.lookbookslider_id = category_table.lookbookslider_id',
                array()
                )
                ->where('category_table.category_id = ?', $category);
        return $this;
    }

    /**
     * Add Filter by page
     *
     * @param int $page
     * @return Altima_Lookbookslider_Model_Mysql4_Lookbookslider_Collection
     */
    public function addPageFilter($page) {
        $this->getSelect()->join(
                array('page_table' => $this->getTable('lookbookslider/page')),
                'main_table.lookbookslider_id = page_table.lookbookslider_id',
                array()
                )
                ->where('page_table.page_id = ?', $page);
        return $this;
    }


    /**
     * Add Filter by status
     *
     * @param int $status
     * @return Altima_Lookbookslider_Model_Mysql4_Lookbookslider_Collection
     */
    public function addEnableFilter($status = 1) {
        $this->getSelect()->where('main_table.status = ?', $status);
        return $this;
    }
}