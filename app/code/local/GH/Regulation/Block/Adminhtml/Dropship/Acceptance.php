<?php
/**
 * read only page with acceptance settings
 */

class GH_Regulation_Block_Adminhtml_Dropship_Acceptance extends
    Mage_Adminhtml_Block_Widget_Form {

    protected $_form;
    

    protected function _prepareForm() {
    /*
        $form = new Varien_Data_Form();
	    $hlp = $this->_getHelper();
        $this->setForm($form);
        $vendorId = $this->getRequest()->get('id');
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
	    $data = $vendor->getData();
	    $json = $data["regulation_accept_document_data"];
	    unset($data["regulation_accept_document_data"]);
	    $block = Mage::getSingleton('core/layout')->createBlock("core/template");
	    $block->setTemplate("zolagodropship/vendor/helper/form/regulation.phtml");
	    $block->setValue($json);
	    $customHtml = $block->toHtml();;
		$settingsFieldset = $this->getForm()->addFieldset("details",array("legend"=>$hlp->__("Details")));
		$settingsFieldset->addField("regulation_confirm_request_sent_date","label",array(
			"label" => $hlp->__("Confirmation request send date"),
		));
		$settingsFieldset->addField("confirmation","label",array(
			"label" => $hlp->__("Confirmation token"),
		));
		$settingsFieldset->addField("regulation_accept_document_date","label",array(
			"label" => $hlp->__("Acceptation date")
		));
		$settingsFieldset->addField("regulation_accept_document_data","label",array(
			"label" => $hlp->__("Acceptation details"),
			"after_element_html" => $customHtml
		));
    /**/
        $form = new Varien_Data_Form();
	    $hlp = $this->_getHelper();
        $this->setForm($form);
        $vendorId = $this->getRequest()->get('id');
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        $yesNo = array(0 => Mage::helper('zolagocommon')->__("No"), 1 => Mage::helper('zolagocommon')->__("Yes"));

        $list = Mage::getResourceModel('ghregulation/regulation_kind_collection');
	    $data = $vendor->getData();
	    $address = $vendor->getBillingAddressObj(); // billing can use shipping
	    $data['zip'] = $address['zip'];
	    $data['city'] = $address['city'];	    
	    $data['street'] = $address['street'];
	    
	    $json = $data["regulation_accept_document_data"];
	    $data['regulation_accepted'] = $yesNo[$data['regulation_accepted']];
	    $pdf_link = '<a href="'.Mage::helper('adminhtml')->getUrl('zolagoosadmin/adminhtml_vendor/get_confirmation_pdf',array('id'=>$vendorId)).'">pdf link</a>';
	    unset($data["regulation_accept_document_data"]);
	    $block = Mage::getSingleton('core/layout')->createBlock("core/template");
	    $block->setTemplate("zolagodropship/vendor/helper/form/regulation.phtml");
	    $block->setValue($json);
	    $customHtml = $block->toHtml();;

		$settingsFieldset = $this->getForm()->addFieldset("settings",array("legend"=>$hlp->__("Details")));
		$settingsFieldset->addField("company_name","label",array(
			"label" => Mage::helper('zolagodropship')->__("Company name"),
		));
		$settingsFieldset->addField("street","label",array(
			"label" => Mage::helper('zolagodropship')->__("Street"),
		));
		$settingsFieldset->addField("city","label",array(
			"label" => Mage::helper('zolagodropship')->__("City"),
		));
		$settingsFieldset->addField("zip","label",array(
			"label" => Mage::helper('zolagodropship')->__("Zip / Postal code"),
		));
		$settingsFieldset->addField("tax_no","label",array(
			"label" => Mage::helper('zolagodropship')->__("NIP"),
		));
		/// empty space 
		$settingsFieldset->addField("dummy","label",array(
			"label" => '',
		));
		$settingsFieldset->addField("dumm2","label",array(
			"label" => '',
		));
		$settingsFieldset->addField("dumm3","label",array(
			"label" => '',
		));

		$settingsFieldset->addField("regulation_accepted","label",array(
			"label" => $hlp->__("Are regulations accepted"),
		));
		$settingsFieldset->addField("regulation_confirm_request_sent_date","label",array(
			"label" => $hlp->__("Confirmation request send date"),
		));
		$settingsFieldset->addField("confirmation","label",array(
			"label" => $hlp->__("Confirmation token"),
		));
		$settingsFieldset->addField("regulation_accept_document_date","label",array(
			"label" => $hlp->__("Acceptation date")
		));
		$settingsFieldset->addField("regulation_accept_document_data","label",array(
			"label" => $hlp->__("Acceptation details"),
			"after_element_html" => $customHtml
		));
		$settingsFieldset->addField("pdf_link","label",array(
			"label" => $hlp->__('Pdf link'),
                        "after_element_html" => $pdf_link,
		));
		
		

		


	    $form->setValues($data);


        return parent::_prepareForm();
    }

	/**
	 * @return GH_Regulation_Helper_Data
	 */
	public function _getHelper() {
		return Mage::helper('ghregulation');
	}

}