<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Post extends Magpleasure_Blog_Block_Adminhtml_Filterable
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_post';
        $this->_blockGroup = 'mpblog';
        $this->_headerText = $this->_helper()->__('Posts');
        $this->_addButtonLabel = $this->_helper()->__('New Post');
        parent::__construct();
    }
}