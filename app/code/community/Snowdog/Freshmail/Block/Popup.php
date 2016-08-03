<?php

class Snowdog_Freshmail_Block_Popup extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    /**
     * Cookie names
     *
     * @const string
     */
    const SUCCESS_COOKIE = 'freshmail_popup_result';
    const COUNTER_COOKIE = 'freshmail_popup_counter';
    const TIMESTAMP_COOKIE = 'freshmail_popup_timestamp';

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('snowfreshmail/popup.phtml');
    }

    public function getCustomFieldsHtml()
    {
        if (Mage::helper('customer')->isLoggedIn()) {
            return '';
        }
        return $this->_prepareCustomFieldsHtml();
    }

    protected function _prepareCustomFieldsHtml()
    {
        $form = new Varien_Data_Form();
        $fieldRenderer = new Snowdog_Freshmail_Model_Renderer();

        $config = Mage::getSingleton('snowfreshmail/config')->getCustomFieldMappings();
        foreach ($config as $map) {
            if (!$map['use_in_form']) {
                continue;
            }

            $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $map['source_field']);
            if ($attribute->isStatic() || !$attribute->getIsVisible()) {
                continue;
            }

            $frontendModel = $attribute->getFrontend();
            $inputType = $frontendModel->getInputType();
            if ($inputType == 'date' || $inputType == 'datetime') {
                $inputType = 'text';
            }

            $config = array(
                'name' => $map['source_field'],
                'label' => $frontendModel->getLabel(),
            );

            if ($attribute->usesSource()) {
                if ($frontendModel->getSelectOptions()) {
                    $config['values'] = $frontendModel->getSelectOptions();
                }
            }

            $form->addField($map['source_field'], $inputType, $config)->setRenderer($fieldRenderer);
        }

        $form->setUseContainer(false);
        return $form->toHtml();
    }

    protected function _toHtml()
    {
        $customer = Mage::helper('customer')->getCustomer();
        if ($customer) {
            $subscriber = Mage::getModel('newsletter/subscriber')
                ->loadByEmail($customer->getEmail());
            if ($subscriber->isSubscribed()) {
                return '';
            }
        }
        return parent::_toHtml();
    }
}
