<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_File_Upload_Render_Template
    extends Mage_Adminhtml_Block_Template
{

    public function getRemoveButtonHtml()
    {
        /** @var $button Magpleasure_Common_Block_Widget_Button */
        $button = $this->getLayout()->createBlock('magpleasure/adminhtml_widget_button')
            ->setData(array(
                'label'                 => $this->__("Remove"),
                'title'                 => $this->__("Remove"),
                'class'                 => 'scalable delete',
                'additional_attributes' => 'ng-click="clearData()"',
                'onclick'               => 'return false;',
            ));

        return $button->toHtml();

    }
}