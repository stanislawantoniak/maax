<?php


/**
 * Grid widget massaction  block
 *
 * @category   Zolago
 * @package    Zolago_Catalog
 */
class Zolago_Catalog_Block_Widget_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{

    /**
     * Retrieve JSON string of selected checkboxes
     *
     * @return string
     */
    public function getSelectedJson()
    {
//        if($selected = $this->getRequest()->getParam($this->getFormFieldNameInternal())) {
        if($selected = $this->getRequest()->getPost($this->getFormFieldNameInternal())) {
            $selected = explode(',', $selected);
            return join(',', $selected);
        } else {
            return '';
        }
    }
}
