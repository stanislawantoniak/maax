<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Entity_Attribute_Frontend_Dropdown extends Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
{
    /**
     * Retrieve Input Renderer Class
     *
     * @return string
     */
    public function getInputRendererClass() {
        $this->getAttribute()->setData('frontend_input_renderer', 'magpleasure/system_entity_form_element_dropdown');
        return parent::getInputRendererClass();
    }

}