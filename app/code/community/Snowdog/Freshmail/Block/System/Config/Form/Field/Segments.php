<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_Segments
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Segment renderer block instance
     *
     * @var Snowdog_Freshmail_Block_System_Config_Form_Field_Segment
     */
    protected $_rendererBlock;

    protected function _prepareToRender()
    {
        $this->addColumn('segment_id', array(
            'label' => Mage::helper('snowfreshmail')->__('Segment'),
            'renderer' => $this->_getSegmentRendererBlock(),
        ));
        $this->addColumn('target_field', array(
            'label' => Mage::helper('snowfreshmail')->__('Freshmail Tag'),
            'style' => 'width:150px',
        ));
        $this->_addAfter = false;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $block = $this->_getSegmentRendererBlock();
        $row->setData(
            'option_extra_attr_' . $block->calcOptionHash($row->getSegmentId()),
            'selected="selected"'
        );
    }

    /**
     * Retrieve a segment field renderer block
     *
     * @return Snowdog_Freshmail_Block_System_Config_Form_Field_Segment
     */
    protected function _getSegmentRendererBlock()
    {
        if (null === $this->_rendererBlock) {
            $this->_rendererBlock = $this->getLayout()->createBlock(
                'snowfreshmail/system_config_form_field_segment',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_rendererBlock;
    }
}
