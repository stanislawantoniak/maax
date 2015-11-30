<?php
/**
 * assign document type to vendor
 */

class GH_Regulation_Block_Adminhtml_Dropship_Settings_Type extends
    Mage_Adminhtml_Block_Widget_Form {


    protected $_form;
    
    /**
     * fieldset with documents kinds
     */
    protected function _createFieldset($item) {
        $hlp = Mage::helper('ghregulation');
        $form = $this->getForm();

        $fieldset = $form->addFieldset('kind_'.$item->getId(),
        array (
            'legend' => $item->getName(),
        ));
        $fieldset->addType('document_list','GH_Regulation_Block_Adminhtml_Dropship_Settings_Type_List');
        /*
        $fieldset->addField('active_'.$item->getId(),'checkbox',array(
            'label' => $hlp->__('Is active'),
            'name' => 'vendor_kind[]',            
            'checked' => $item->getIsActive(),
            'value' => $item->getRegulationKindId(),
            
        ));
        */
        $fieldset->addField('typelist_'.$item->getId(),'document_list',array(
            'label' => $hlp->__('Document type list'),            
            'createUrl' => $this->getUrl('*/*/kindEdit',array('kind_id'=> $item->getId(),'id'=>$this->getRequest()->get('id'))),
            'vendor_id' => $this->getRequest()->get('id'),
            'regulation_kind_id' => $item->getId(),
        ));
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
	    $hlp = $this->_getHelper();
        $this->setForm($form);
        $vendorId = $this->getRequest()->get('id');
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);

        $list = Mage::getResourceModel('ghregulation/regulation_kind_collection');
        /*
        $list->getSelect()->
            joinLeft(
                array ('vendor_kind' => Mage::getSingleton('core/resource')->getTableName('ghregulation/regulation_vendor_kind')),
                'vendor_kind.regulation_kind_id = main_table.regulation_kind_id AND vendor_kind.vendor_id = '.$vendorId,
                array('is_active' => 'IF(ISNULL(vendor_kind.regulation_kind_id),0,1)')
            );
        */ // not use vendor_kind

	    $data = $vendor->getData();
	    $json = $data["regulation_accept_document_data"];
	    unset($data["regulation_accept_document_data"]);
	    $block = Mage::getSingleton('core/layout')->createBlock("core/template");
	    $block->setTemplate("zolagodropship/vendor/helper/form/regulation.phtml");
	    $block->setValue($json);
	    $customHtml = $block->toHtml();;

		$settingsFieldset = $this->getForm()->addFieldset("settings",array("legend"=>$hlp->__("Settings")));
		$settingsFieldset->addField("regulation_accepted","select",array(
			"label" => $hlp->__("Are regulations accepted"),
			"name" => "regulation_accepted",
			"values" => array(0 => $hlp->__("No"), 1 => $hlp->__("Yes"))
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
	    $settingsFieldset->addField("regulation_accept_text_top","textarea",array(
		    "label" => $hlp->__("Accept page top text"),
		    "name" => "regulation_accept_text_top",
		    "note" => $hlp->__("Overrides part of vendor_regulations_accept cms block")
	    ));
	    $settingsFieldset->addField("regulation_proxy_assignment_url","text",array(
		    "label" => $hlp->__("Proxy assignment url"),
		    "name" => "regulation_proxy_assignment_url",
		    "note" => $hlp->__("Overrides part of vendor_regulations_accept cms block")
	    ));
	    $settingsFieldset->addField("regulation_proxy_assignment_url_text","text",array(
		    "label" => $hlp->__("Proxy assignment url text"),
		    "name" => "regulation_proxy_assignment_url_text",
		    "note" => $hlp->__("Overrides part of vendor_regulations_accept cms block")
	    ));
	    $settingsFieldset->addField("regulation_proxy_assignment_override","text",array(
		    "label" => $hlp->__("Proxy assignment text override"),
		    "name" => "regulation_proxy_assignment_override",
		    "note" => $hlp->__("Replaces %value% in this text: ").$hlp->__("I have full powers to accept the %s regulations","%value%")
	    ));
	    $settingsFieldset->addField("regulation_accept_text_agreement","textarea",array(
		    "label" => $hlp->__("Agreement text"),
		    "name" => "regulation_accept_text_agreement",
		    "note" => $hlp->__("Overrides default agreement text (next to checkbox)")
	    ));




	    $form->setValues($data);

	    foreach ($list as $key => $item) {
            $this->_createFieldset($item);
        }


        return parent::_prepareForm();
    }

	/**
	 * @return GH_Regulation_Helper_Data
	 */
	public function _getHelper() {
		return Mage::helper('ghregulation');
	}

}