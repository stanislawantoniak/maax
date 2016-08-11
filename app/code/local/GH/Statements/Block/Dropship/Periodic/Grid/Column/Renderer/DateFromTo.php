<?php

class GH_Statements_Block_Dropship_Periodic_Grid_Column_Renderer_DateFromTo
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Date
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $column = $this->getColumn();
        $wrapperStart = '<div class="wrapper-date-from-to wrapper-date-from-to-'.$column->getIndex().'">';
        $wrapperEnd   = '</div>';

        $itemFrom = $column->getData('renderer_data')['from'];
        $itemTo   = $column->getData('renderer_data')['to'];

        $dataFrom = $row->getData($itemFrom['index']);
        $dataTo   = $row->getData($itemTo['index']);
        $format = $this->_getFormat();
        try {
            if($this->getColumn()->getGmtoffset()) {
                $dataFrom = empty($dataFrom)? '':Mage::app()->getLocale()->date($dataFrom, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
                $dataTo   = Mage::app()->getLocale()->date($dataTo  , Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            } else {
                $dataFrom = empty($dataFrom)? '':Mage::getSingleton('core/locale')->date($dataFrom, Zend_Date::ISO_8601, null, false)->toString($format);
                $dataTo   = Mage::getSingleton('core/locale')->date($dataTo  , Zend_Date::ISO_8601, null, false)->toString($format);
            }
        }
        catch (Exception $e) {
            if($this->getColumn()->getTimezone()) {
                $dataFrom = empty($dataFrom)? '':Mage::app()->getLocale()->date($dataFrom, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
                $dataTo   = Mage::app()->getLocale()->date($dataTo  , Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
            } else {
                $dataFrom = empty($dataFrom)? '':Mage::getSingleton('core/locale')->date($dataFrom, null, null, false)->toString($format);
                $dataTo   = Mage::getSingleton('core/locale')->date($dataTo  , null, null, false)->toString($format);
            }
        }

        $text = $wrapperStart;
        if ($dataFrom) {
            $text .=
                '<div>' .
                    '<div class="from '.(isset($itemFrom['css_class']) ? $itemFrom['css_class'] : '').'">' . $dataFrom . ' -</div>' .
                    '<div class="to '  .(isset($itemTo['css_class'])   ? $itemTo['css_class'] : '').'">'   . $dataTo   . '</div>' .
                '</div>';
        } else {
            $text .=
                '<div>' .
                    '<div class="to '  .(isset($itemTo['css_class'])   ? $itemTo['css_class'] : '').'"> do '   . $dataTo   . '</div>' .
                '</div>';
        }
        return $text . $wrapperEnd;
    }
}