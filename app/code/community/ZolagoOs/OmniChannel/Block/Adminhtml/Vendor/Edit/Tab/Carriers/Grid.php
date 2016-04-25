<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Edit_Tab_Carriers_Grid
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;
    protected $_customerGroups = null;
    protected $_websites = null;

    public function __construct()
    {
        $this->setTemplate('udropship/carriers.phtml');
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getValues()
    {
        $values =array();
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            usort($data, array($this, '_sortCarriers'));
            $values = $data;
        }
        return $values;
    }

    protected function _sortCarriers($a, $b)
    {
        return 0;
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Add Carrier'),
                    'onclick'   => 'carrierControl.addItem()',
                    'class' => 'add'
                )));
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}