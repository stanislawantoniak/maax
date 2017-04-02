<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Vendor_Question extends Mage_Core_Block_Template
{
    protected $_form;
    protected $_question;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
		
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('udqa/vendor_question_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('udqa/vendor_question_renderer_fieldsetElement')
        );

        return $this;
    }
	
	public function isEditable(Zolago_DropshipVendorAskQuestion_Model_Question $question) {
		return !$question->getAnswerText();
	}
	
    public function getForm()
    {
        if (null === $this->_form) {
            $question = $this->getQuestion();
            $this->_form = Mage::getModel('zolagodropship/form');
            $this->_form->setDataObject($question);
            $values = $question->getData();

            if (($udFormData = Mage::getSingleton('udropship/session')->getUdqaData(true))
                && is_array($udFormData)
            ) {
                $values = array_merge($values, $udFormData);
            }

            $this->_addDetailsFieldset($question, $values);

            $this->_form->addValues($values);

            $this->_form->setFieldNameSuffix('question');
        }
        return $this->_form;
    }
    public function getStoreName($id) {
        if ($id) {
            return Mage::getModel('core/store')->load($id)->getName();
        } else {
            return Mage::app()->getDefaultStoreView()->getName();
        }
    }
    public function getQuestion()
    {
        if (null === $this->_question) {
            $this->_question = Mage::getModel('udqa/question')->load(
                Mage::app()->getRequest()->getParam('id')
            );
        }
        return $this->_question;
    }
    protected function _addDetailsFieldset($question, &$values)
    {
        $fieldset = $this->_form->addFieldset('details',
            array(
                'legend'=>Mage::helper('udprod')->__('Question Details'),
                'class'=>'fieldset-wide',
        ));
        $this->_addElementTypes($fieldset);

        $data = new Varien_Object($values);

        $fieldset->addField('store_name' , 'note' ,array (
            'name' => 'store_name',
            'label' => $this->__('Store name'),
            'text' => $this->getStoreName($data->getStoreId()),
            'is_wide'=>true,
            'is_top'=>true,
        ));
            
        $fieldset->addField('question_date', 'note', array(
            'name' => 'question_date',
            'label' => $this->__('QUESTION DATE'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'text' => $this->formatDate($data->getQuestionDate()),
            'is_wide'=>true,
			'class'	=> 'note',
            'is_top'=>true,
            'disabled'=>'disabled'
        ));
        $fieldset->addField('customer_name', 'note', array(
            'name' => 'customer_name',
            'label' => $this->__('CUSTOMER NAME'),
            'text' => $data->getCustomerName().' (<a href="mailto:'.$data->getCustomerEmail().'">'.$data->getCustomerEmail().'</a>)',
            'is_wide'=>true,
            'is_top'=>true,
        ));

        if ($data->getPoId()) {
            $fieldset->addField('increment_id', 'note', array(
                'name' => 'increment_id',
                'label' => $this->__('Order id'),
                'style' => 'padding-top:7px',
                'text' => $this->getOrderLink($data),
                'is_wide'=>true,
                'is_top'=>true,
            ));
        }

        if ($data->getProductId()) {
            $fieldset->addField('product_id', 'note', array(
                'name' => 'product_id',
                'label' => $this->__('PRODUCT'),
				'class'	=> 'note',
                //'text' => '<a href="'.$this->getProductUrl($data).'">SKU: '.$data->getProductSku().' '.$data->getProductName().'</a>',
                'text' => 'SKU: '.$data->getProductSku().' '.$data->getProductName(),
                'is_wide'=>true,
                'is_top'=>true,
            ));
        }

        /*
        $fieldset->addField('customer_email', 'text', array(
            'name' => 'customer_email',
            'label' => $this->__('Customer email'),
            'disabled'=>'disabled'
        ));
        */

        $fieldset->addField('vendor_id', 'hidden', array(
            'name' => 'vendor_id',
            'label' => $this->__('Vendor'),
            'is_wide'=>true,
            'is_hidden'=>true
        ));

        $fieldset->addField('question_text', 'note', array(
            'name' => 'question_text',
            'label' => $this->__('Question Text'),
            'required' => true,
            'class' => 'note',
            'is_wide' => true,
            'is_bottom' => true,
            'text' => Mage::helper('zolagocommon')->nToBr($data->getQuestionText())
        ));

        $fieldset->addField('answer_text', $this->isEditable($question) ? 'editor' : 'note', array(
            'name' => 'answer_text',
            'label' => $this->__('Answer Text'),
            'title' => $this->__('Answer Text'),
			'class' => 'form-control',
            'wysiwyg' => false,
            'required' => true,
            'is_wide'=>true,
            'is_bottom'=>true,
			'text' => !$this->isEditable($question)  ? Mage::helper('zolagocommon')->nToBr($question->getAnswerText()) : ""
        ));
        
        $this->_prepareFieldsetColumns($fieldset);
    }
    public function getOrderLink($question) {
        $order = Mage::getModel('udropship/po')->
            load($question->getPoId());
        $template = '<a href="%s">%s</a>';
        if ($order->getIncrementId()) {
            $url =  Mage::getUrl("udpo/vendor/edit",array("id"=>$question->getPoId()));
            $out = sprintf($template,$url,$order->getIncrementId());
        } else {
            $out = sprintf($template,Mage::getUrl("udpo/vendor"),Mage::helper('zolagocommon')->__('Order list'));
        }
        
        return $out;
    }
    public function getShipmentUrl($question)
    {
		$shipping = Mage::getModel("sales/order_shipment")->
			load($question->getShipmentId());
		
		if($shipping->getUdpoId()){
			
			return Mage::getUrl("udpo/vendor/edit", array("id"=>$shipping->getUdpoId()));
		}
		return Mage::getUrl("udpo/vendor");
		
        //return Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$question->getOrderIncrementId().'&filter_order_id_to='.$question->getOrderIncrementId()));
    }
    public function getProductUrl($question)
    {
        if (Mage::helper('udropship')->isModuleActive('udprod')) {
            return Mage::getUrl('udprod/vendor/products', array('_query'=>'filter_sku='.$question->getProductSku()));
        } elseif (Mage::helper('udropship')->isModuleActive('umicrosite')
            && Mage::getSingleton('udropship/session')->getVendor()->getShowProductsMenuItem()
        ) {
            $params = array();
            $hlp = Mage::getSingleton('adminhtml/url');
            if ($hlp->useSecretKey()) {
                $params[Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME] = $hlp->getSecretKey();
            }
            $params['id'] = $question->getProductId();
            return $hlp->getUrl('adminhtml/catalog_product/edit', $params);
        } else {
            return Mage::getUrl('udropship/vendor/product', array('_query'=>'filter_sku='.$question->getProductSku()));
        }
    }

    protected function _prepareFieldsetColumns($fieldset)
    {
        $elements = $fieldset->getElements()->getIterator();
        reset($elements);
        $fullCnt = count($elements);
        $wideElementsBottom = $wideElements = $lcElements = $rcElements = array();
        while($element=current($elements)) {
            if ($element->getIsWide()) {
                if ($element->getIsBottom()) {
                    $wideElementsBottom[] = $element->getId();
                } else {
                    $wideElements[] = $element->getId();
                }
                $fullCnt--;
            }
            next($elements);
        }
        $halfCnt = ceil($fullCnt/2);
        reset($elements);
        $i=0; while ($element=current($elements)) {
            if (!$element->getIsWide()) {
                $lcElements[] = $element->getId();
                $i++;
            }
            next($elements);
            if ($i>=$halfCnt) break;
        }
        while ($element=current($elements)) {
            if (!$element->getIsWide()) {
                $rcElements[] = $element->getId();
            }
            next($elements);
        }
        $fieldset->setWideColumnTop($wideElements);
        $fieldset->setWideColumnBottom($wideElementsBottom);
        $fieldset->setLeftColumn($lcElements);
        $fieldset->setRightColumn($rcElements);
        reset($elements);
        return $this;
    }
    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
            $result = array();

            $response = new Varien_Object();
            $response->setTypes(array());
            Mage::dispatchEvent('udqa_question_edit_element_types', array('response'=>$response));

            foreach ($response->getTypes() as $typeName=>$typeClass) {
                $result[$typeName] = $typeClass;
            }
            $this->_additionalElementTypes = $result;
        }
        return $this;
    }

    protected function _getAdditionalElementTypes()
    {
        $this->_initAdditionalElementTypes();
        return $this->_additionalElementTypes;
    }
    public function addAdditionalElementType($code, $class)
    {
        $this->_initAdditionalElementTypes();
        $this->_additionalElementTypes[$code] = Mage::getConfig()->getBlockClassName($class);
        return $this;
    }

    protected function _addElementTypes(Varien_Data_Form_Abstract $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }
}