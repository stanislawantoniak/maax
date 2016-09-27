<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Adminhtml_Magpleasure_TreeviewController
    extends Magpleasure_Common_Controller_Adminhtml_Action_Service
{
    public function listAction()
    {
        $data = array();

        ///TODO

        $data[] = array(
            'text'  => 'Test',
            'id'    => 1,
            'cls'   => 'folder',
            'store' => 0,
            'allowDrag' => true,
            'allowDrop' => true,
            'children' => false,
        );


        return $this->_ajaxResponse($data);
    }
}