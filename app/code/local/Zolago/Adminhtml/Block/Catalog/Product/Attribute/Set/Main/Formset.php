<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Zolago
 * @package    Zolago_Adminhtml
 */

class Zolago_Adminhtml_Block_Catalog_Product_Attribute_Set_Main_Formset extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Prepares attribute set form
     *
     */
    protected function _prepareForm()
    {
        $data = Mage::getModel('eav/entity_attribute_set')
            ->load($this->getRequest()->getParam('id'));
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'set_name',
            array('legend' => Mage::helper('catalog')->__('Edit Set Name'))
        );
        $fieldset->addField(
            'attribute_set_name', 'text',
            array(
                 'label'    => Mage::helper('catalog')->__('Name'),
                 'note'     => Mage::helper('catalog')->__('For internal use.'),
                 'name'     => 'attribute_set_name',
                 'required' => true,
                 'class'    => 'required-entry validate-no-html-tags',
                 'value'    => $data->getAttributeSetName()
            )
        );

        $fieldset = $form->addFieldset(
            'set_use_to_create_product', array('legend' => Mage::helper('catalog')->__('Use to create product'))
        );
        $fieldset->addField(
            'attribute_set_use_to_create_product', 'select',
            array(
                 'label'    => Mage::helper('catalog')->__('Use to create product'),
                 'name'     => 'attribute_set_use_to_create_product',
                 'required' => true,
                 'class'    => 'required-entry',
                 'values'   => array(0 => Mage::helper('catalog')->__('No'), 1 => Mage::helper('catalog')->__('Yes')),
                 'value'    => $data->getUseToCreateProduct()
            )
        );

        $fieldset = $form->addFieldset(
            'set_use_sizebox_list', array('legend' => Mage::helper('catalog')->__('Set use sizebox list'))
        );
        $fieldset->addField(
            'attribute_set_use_sizebox_list', 'select',
            array(
                'label'    => Mage::helper('catalog')->__('Set use sizebox list'),
                'name'     => 'attribute_set_use_sizebox_list',
                'required' => true,
                'class'    => 'required-entry',
                'values'   => array(0 => Mage::helper('catalog')->__('No'), 1 => Mage::helper('catalog')->__('Yes')),
                'value'    => $data->getUseSizeboxList()
            )
        );


        if( !$this->getRequest()->getParam('id', false) ) {
            $fieldset->addField('gotoEdit', 'hidden', array(
                'name' => 'gotoEdit',
                'value' => '1'
            ));

            $sets = Mage::getModel('eav/entity_attribute_set')
                ->getResourceCollection()
                ->setEntityTypeFilter(Mage::registry('entityType'))
                ->load()
                ->toOptionArray();

            $fieldset->addField('skeleton_set', 'select', array(
                'label' => Mage::helper('catalog')->__('Based On'),
                'name' => 'skeleton_set',
                'required' => true,
                'class' => 'required-entry',
                'values' => $sets,
            ));
        }

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('set_prop_form');
        $form->setAction($this->getUrl('*/*/save'));
        $form->setOnsubmit('return false;');
        $this->setForm($form);
    }
}
