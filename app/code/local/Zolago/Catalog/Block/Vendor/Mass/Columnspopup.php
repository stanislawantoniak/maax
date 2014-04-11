<?php 
/**
 * popup with columns visibility settings
 */
class Zolago_Catalog_Block_Vendor_Mass_Columnspopup extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('zolagocatalog/widget/columns/popup.phtml');        
    }
	protected function _prepareLayout() {
		$this->setChild('button_save',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
                    'label'     => Mage::helper('zolagocatalog')->__('Save'),
                    'onclick'   => 'javascript:saveHiddenColumns()',
                    'class'   => 'task'
				))
		);
        $this->setChild('button_cancel',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('zolagocatalog')->__('Cancel'),
                    'onclick'   => 'javascript:closeMyPopup()',
                    'class'   => 'task'
                ))
        );

		return parent::_prepareLayout();
	}

    public function getButtonsHtml() {
        $out =  $this->getChildHtml('button_save').' ';
        $out .= $this->getChildHtml('button_cancel');
        return $out;
        
    }
}
?>
