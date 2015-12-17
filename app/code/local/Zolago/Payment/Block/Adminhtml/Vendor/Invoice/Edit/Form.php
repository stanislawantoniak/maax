<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Invoice__Form
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Invoice_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @return Zolago_Payment_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper("zolagopayment");
    }

    protected function _prepareForm()
    {
        $hlp = $this->_getHelper();

        $request = $this->getRequest();

        $id = $request->get('id', NULL);


        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $id)),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('edit_form', array(
                'legend' => $hlp->__('General'),
                'class' => 'fieldset-wide'
            )
        );

        $fieldset->addField('is_invoice_correction', 'select', array(
            'label' => $hlp->__('Invoice correction'),
            'required' => true,
            'name' => 'is_invoice_correction',
            "options" => array(
                Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_ORIGINAL => $hlp->__("No"),
                Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_CORRECTION => $hlp->__("Yes")
            )
        ));

        $fieldset->addField('date', 'date', array(
            'label' => $hlp->__('Date'),
            'required' => true,
            'name' => 'date',
            'format' => 'yyyy-MM-dd',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'time' => false,
            'after_element_html' => "<small>" . $hlp->__('Allowed format: yyyy-mm-dd') . "</small>",
        ));
        $fieldset->addField('sale_date', 'date', array(
            'label' => $hlp->__('Sale Date'),
            'required' => true,
            'name' => 'sale_date',
            'format' => 'yyyy-MM-dd',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'time' => false,
            'after_element_html' => "<small>" . $hlp->__('Allowed format: yyyy-mm-dd') . "</small>",
        ));


        $fieldset->addField('vendor_id', 'select', array(
            'label' => $hlp->__('Vendor'),
            'required' => true,
            'name' => 'vendor_id',
            "options" => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash()
        ));

        //2. WFIRMA
        $fieldsetWfirma = $form->addFieldset('edit_form_wfirma', array(
                'legend' => $hlp->__('Wfirma data'),
                'class' => 'fieldset-wide'
            )
        );
        $fieldsetWfirma->addField('wfirma_invoice_id', 'text', array(
            'label' => $hlp->__('Invoice ID'),
            'required' => false,
            'name' => 'wfirma_invoice_id',
            'style' => 'max-width:100px;',
        ));
        $fieldsetWfirma->addField('wfirma_invoice_number', 'text', array(
            'label' => $hlp->__('Invoice #'),
            'required' => false,
            'name' => 'wfirma_invoice_number',
            'style' => 'max-width:200px;',
        ));

        //3. Cost
        $fieldsetCost = $form->addFieldset('edit_form_cost', array(
                'legend' => $hlp->__('Cost'),
                'class' => 'fieldset-wide'
            )
        );

	    $fieldsetNote = $form->addFieldset('edit_form_note',array(
		    'legend' => $hlp->__('Note'),
		    'class' => 'fieldset-wide'
	    ));

	    $fieldsetNote->addField('note','textarea', array(
		    'label' => $hlp->__('Note'),
		    'name' => 'note',
		    'after_element_html' => '<div>'.$hlp->__("Characters left: ").'<span id="invoice_note_number">'.GH_Wfirma_Model_Client::NOTE_FIELD_LENGTH.'</span></div>',
		    'onkeyup' => 'javascript: var charactersLeft = '.GH_Wfirma_Model_Client::NOTE_FIELD_LENGTH.' - this.value.length;'.
			    'document.getElementById(\'invoice_note_number\').innerHTML = charactersLeft > 0 ? charactersLeft : 0;'.
			    'if(charactersLeft < 0) this.value = this.value.substring(0,'.GH_Wfirma_Model_Client::NOTE_FIELD_LENGTH.');'
	    ));

        $costFields = array(
            //1. commission
            //"commission_netto" => $hlp->__('Commission netto'),
            "commission_brutto" => $hlp->__('Commission brutto'),

            //2. transport
            //"transport_netto" => $hlp->__('Transport netto'),
            "transport_brutto" => $hlp->__('Transport brutto'),

            //3. marketing
            //"marketing_netto" => $hlp->__('Marketing netto'),
            "marketing_brutto" => $hlp->__('Marketing brutto'),

            //3. other
            //"other_netto" => $hlp->__('Other netto'),
            "other_brutto" => $hlp->__('Other brutto')
        );
        foreach ($costFields as $name => $label) {
            $this->_addCostFormField($fieldsetCost, $name, $label);
        }
        //Cost

        $form->setUseContainer(true);
        $form->setValues($this->_getValues());
        $this->setForm($form);
        return parent::_prepareForm();

    }

    /**
     * @param $fieldset
     * @param $name
     * @param $label
     */
    private function _addCostFormField($fieldset, $name, $label)
    {
        $fieldset->addField($name, 'text', array(
            'label' => $label,
            'required' => true,
            'name' => $name,
            'style' => 'max-width:100px;'
        ));
    }

    protected function _getValues()
    {
        $data = $this->_getModel()->getData();
        foreach($data as $key=>$value) {
            if(strpos($key,'brutto') !== false || strpos($key,'brutto') !== false) {
                $data[$key] = number_format($value, 2, '.', '');
            }
        }

        return $data;
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _getModel()
    {
        return Mage::registry('zolagopayment_current_invoice');
    }
}