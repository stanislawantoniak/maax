<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Helper_Form_NotifyLowstock extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $html .= '
<script type="text/javascript">
var switchNotifyLowstockSelect = function() {
	if ($("notify_lowstock").value==1) {
		$("notify_lowstock_qty").up("tr").show()
		$("notify_lowstock_qty").enable()
	} else {
		$("notify_lowstock_qty").up("tr").hide()
		$("notify_lowstock_qty").disable()
	}
}
$("notify_lowstock").observe("change", switchNotifyLowstockSelect)
document.observe("dom:loaded", switchNotifyLowstockSelect)
</script>        	
        ';
        return $html;
    }
}

