<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrositePro_Block_Vendor_Register_Form_DependSelect extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $fc = (array)$this->getData('field_config');
        if (isset($fc['depend_fields']) && ($dependFields = (array)$fc['depend_fields'])) {
            foreach ($dependFields as &$dv) {
                $dv = explode(',', $dv);
            }
            $dfJson = Zend_Json::encode($dependFields);
            $html .=<<<EOT
<script type="text/javascript">
document.observe("dom:loaded", function() {
	var df = \$H($dfJson)
	var syncDependFields = function() {
		df.each(function(pair){
			if ($(pair.key) && (trElem = $(pair.key+'-container'))) {
				if (\$A(pair.value).indexOf($('{$this->getHtmlId()}').value) != -1) {
					trElem.show()
            		trElem.select('select').invoke('enable')
            		trElem.select('input').invoke('enable')
            		trElem.select('textarea').invoke('enable')
            	} else {
            		trElem.hide()
            		trElem.select('select').invoke('disable')
            		trElem.select('input').invoke('disable')
            		trElem.select('textarea').invoke('disable')
            	}
			}
		})
	}
    $('{$this->getHtmlId()}').observe('change', syncDependFields)
    syncDependFields()
})
</script>
EOT;
        }
        return $html;
    }
}

