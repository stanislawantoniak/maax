<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Form
    extends Magpleasure_Blog_Block_Adminhtml_Filterable_Widget_Form
{
    protected function _prepareForm()
    {
        $params = $this->_getCommonParams();

        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/massUpdateAttributeGo', $params),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}