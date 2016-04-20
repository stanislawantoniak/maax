<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Helper_Form_PayoutPoStatusType extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $html .= '
<script type="text/javascript">
var switchPayoutPoStatusSelect = function() {
    var defStPoType = "'.(Mage::getStoreConfig('udropship/statement/statement_po_type')).'";
    var getStPoType = function(val) {
        return val == "999" ? defStPoType : val;
    }
    for (i=0; i<$("statement_po_type").options.length; i++) {
		var statusSel = $("payout_"+getStPoType($("statement_po_type").options[i].value)+"_status");
		if (statusSel) {
    		if (statusSel.id == "payout_"+getStPoType($("statement_po_type").value)+"_status" && $("payout_po_status_type").value == "payout") {
    			statusSel.up("tr").show();
    			statusSel.enable();
    		} else {
    			statusSel.up("tr").hide();
    			statusSel.disable();
    		}
		}
	}
}
document.observe("dom:loaded", function(){
    $("payout_po_status_type").observe("change", switchPayoutPoStatusSelect)
    $("statement_po_type").observe("change", switchPayoutPoStatusSelect)
    switchPayoutPoStatusSelect();
});
</script>        	
        ';
        return $html;
    }
}

