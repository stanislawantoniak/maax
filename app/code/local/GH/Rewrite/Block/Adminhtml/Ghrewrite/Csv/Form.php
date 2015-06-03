<?php

class GH_Rewrite_Block_Adminhtml_Ghrewrite_Csv_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        parent::__construct();
        $this->setId('ghrewrite_form');
        $this->setTitle($rewriteHelper->__('CSV Loader'));
    }

    protected function _prepareForm() {

        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $form = new Varien_Data_Form(array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('ghrewrite/csv/import'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('loadcsv_form', array('legend' => $rewriteHelper->__('CSV Loader')));

        $fieldset->addField('file', 'file', array(
            'label' => $rewriteHelper->__('CSV File'),
            'required' => false,
            'name' => 'file',
        ));

        for ($i = 1; $i < 30 ; $i++) {
            $key = 'help-for-csv-file-in-gh-rewrite-module-line-' . $i;
            $txt = $rewriteHelper->__($key);
            if ($txt != $key && !empty($txt)) {
                $fieldset->addField('note-' . $i, 'note', array(
                    'text' => $txt
                ));
            }
        }

        return parent::_prepareForm();
    }
}
