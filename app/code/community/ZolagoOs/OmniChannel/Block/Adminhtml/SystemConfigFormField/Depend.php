<?php
/**
  
 */
 
class ZolagoOs_OmniChannel_Block_Adminhtml_SystemConfigFormField_Depend extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::render($element);
        $fc = (array)$element->getData('field_config');
        if (isset($fc['depend_value']) && isset($fc['depend_field']) && ($df = $fc['depend_field'])) {
            if (!is_array($df)) {
                $df = explode(',', $df);
            }
            $dfJson = Zend_Json::encode($df);
            $dfValueJson = Zend_Json::encode($fc['depend_value']);
            $html .=<<<EOT
<script type="text/javascript">
document.observe("dom:loaded", function() {
	var df = \$A($dfJson)
	var dfVal = $dfValueJson
	var syncDependFields = function() {
		for (i=0; i<df.size(); i++) {
			if ($(df[i])) {
				if ($('{$element->getHtmlId()}').value == dfVal) {
					$(df[i]).up("tr").show()
            		$(df[i]).enable()
            	} else {
            		$(df[i]).up("tr").hide()
            		$(df[i]).disable()
            	}
			} 
		}
	}
    $('{$element->getHtmlId()}').observe('change', syncDependFields)
    syncDependFields()
})
</script>
EOT;
        }
        return $html;
    }
}