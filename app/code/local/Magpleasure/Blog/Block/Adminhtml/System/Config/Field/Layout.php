<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Blog Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getSidebarBlocks()
    {
        return $this
            ->_helper()
            ->getLayoutConfig()
            ->getBlocks('sidebar');
    }

    protected function _getContentBlocks()
    {
        return $this
            ->_helper()
            ->getLayoutConfig()
            ->getBlocks('content');
    }

    protected function _getLayouts()
    {
        $config = array(
            'one-column' => $this->__("One Column"),
            'two-columns-left' => $this->__("Two Columns and Left Sidebar"),
            'two-columns-right' => $this->__("Two Columns and Right Sidebar"),
            'three-columns' => $this->__("Three Columns"),
        );

        return $this
            ->_helper()
            ->getCommon()
            ->getArrays()
            ->paramsToValueLabel($config);
    }

    protected function _wrapSkinImages(array $blocks)
    {
        $data = array();
        foreach ($blocks as $block) {

            if (isset($block['backend_image'])){

                $backendImage = $block['backend_image'];
                $backendImage = $this->getSkinUrl($backendImage);
                $block['backend_image'] = $backendImage;
            }
            $data[] = $block;
        }

        return $data;
    }

    public function getLayoutConfig()
    {
        $contentBlocks = $this->_getContentBlocks();
        $sidebarBlocks = $this->_getSidebarBlocks();

        return array(
            'content' => $this->_wrapSkinImages($contentBlocks),
            'sidebar' => $this->_wrapSkinImages($sidebarBlocks),
            'layouts' => $this->_getLayouts(),
            'delete_message' => $this->__("Are you sure?"),
        );
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Renderer $renderer */
        $renderer = $this
            ->getLayout()
            ->createBlock('mpblog/adminhtml_system_config_field_layout_renderer')
        ;

        if ($renderer) {

            $renderer
                ->setElementId($element->getHtmlId())
                ->setElementName($element->getName())
                ->setElementValue($element->getValue())
                ->setLayoutConfig($this->getLayoutConfig());

            return $renderer->toHtml();
        }

        return false;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = "<td colspan=\"5\">";

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $html .= "<div class=\"label\">";
        $html .= $element->getLabel();
        $html .= "</div>";

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        } elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        $html .= '<div class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html .= '</div>';


        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();

            // default value
            $html .= '<div class="use-default">';
            $html .= '<input id="' . $id . '_inherit" name="'
                . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                . $inherit . ' " /> ';
            $html .= '<label for="' . $id . '_inherit" class="inherit" title="'
                . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html .= '</div>';
        }


        $html .= "<div class=\"fixed\"></div>";

        $html .= "<div class=\"layout-element\">";
        $html .= $this->_getElementHtml($element);
        $html .= "</div>";


        $html .= "</td>";

        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }


}