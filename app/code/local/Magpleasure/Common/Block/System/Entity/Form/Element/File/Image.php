<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_File_Image
    extends Magpleasure_Common_Block_System_Entity_Form_Element_File_Upload
{
    public function getRenderer()
    {
        $control = parent::getRenderer();
        $control->setIsImage(true);
        return $control;
    }

}