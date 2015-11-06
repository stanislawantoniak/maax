<?php

/**
 * Class GH_Regulation_Block_Adminhtml_List_Edit_Form
 */
class GH_Regulation_Block_Adminhtml_List_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        /** @var GH_Regulation_Helper_Data $helper */
        $helper = Mage::helper('ghregulation');
        $model = $this->getModel();

        // Note: form is generated manually
        /** @see app/design/adminhtml/default/default/template/ghregulation/list/edit.phtml */
        $form = new Varien_Data_Form(array(
                'id'      => 'regulation_document_edit_form',
                'action'  => $this->getUrl('*/*/saveDocument'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__('Details'),
        ));

        $fieldset->addField('file', 'file', array(
            'label' => $helper->__('File'),
            'required' => $this->_isNew(),
            'name' => 'file',
        ));

        if (!$this->_isNew()) {
            $fieldset->addField('show_file', 'note', array(
                'label' => $helper->__('Uploaded file:'),
                'text' => "<a href='" . $model->getAdminUrl() . "'>" . $model->getFileName() . "</a>"
            ));
        }

        $fieldset->addField('regulation_type_id', 'select', array(
            'name' => 'regulation_type_id',
            'required' => true,
            'label' => $helper->__('Document kind'),
            'values' => $this->_getTypeValues(),
        ));

        /** @var Varien_Data_Form_Element_Date $dateField */
        $dateField = $fieldset->addField('date', 'date', array(
            'name' => 'date',
            'label' => $helper->__('Is valid from'),
            'required' => true,
            "maxlength" => 32,
            'class' => "form-control",
            'format' => 'yyyy-MM-dd',
            'image'		=> $this->getSkinUrl('images/grid-cal.gif'),
            'time'		=> false,
            'after_element_html' => Mage::helper('ghregulation')->__('<small>Date format (YYYY-MM-DD)</small>'),
        ));

        $form->setValues($model->getData());
        if ($this->_isNew()) {
            // Auto complete for new
            $dateField->setValue(Mage::getModel('core/date')->date('Y-m-d'));
        }
        return parent::_prepareForm();
    }

    /**
     * @return GH_Regulation_Model_Regulation_Document
     */
    protected function getModel() {
        return Mage::registry('ghregulation_current_document');
    }

    protected function _isNew() {
        return !(int)$this->getModel()->getId();
    }

    protected function _getTypeValues() {
        /** @var GH_Regulation_Model_Resource_Regulation_Type_Collection $coll */
        $coll = Mage::getResourceModel('ghregulation/regulation_type_collection');
        $coll->joinKind();
        $coll->setOrder("kind_name, name");

        $array = $coll->toArray();
        $out = array(
            '' => Mage::helper('ghregulation')->__(' --- choose document type --- '),
        );
        if (!empty($array['items'])) {
            foreach ($array['items'] as $item) {
                $out[$item['regulation_type_id']] = $item['kind_name'] . " | " . $item['name'];
            }
        }
        return $out;
    }
}

