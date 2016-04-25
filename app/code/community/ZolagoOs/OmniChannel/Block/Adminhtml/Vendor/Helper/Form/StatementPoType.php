<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Helper_Form_StatementPoType extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        $defPoType = (string)Mage::getStoreConfig('udropship/statement/statement_po_type');
        $html .= '
<script type="text/javascript">
var switchStatementPoStatusSelect = function() {
	for (i=0; i<$("statement_po_type").options.length; i++) {
	    var poTypeValue = $("statement_po_type").value;
        if (poTypeValue == "999") {
            poTypeValue = "'.$defPoType.'";
        }
		var statusSel = $("statement_"+$("statement_po_type").options[i].value+"_status")
		if (statusSel) {
    		if (statusSel.id == "statement_"+poTypeValue+"_status") {
    			statusSel.up("tr").show()
    			statusSel.enable()
    		} else {
    			statusSel.up("tr").hide()
    			statusSel.disable()
    		}
		}
	}
}
$("statement_po_type").observe("change", switchStatementPoStatusSelect)
document.observe("dom:loaded", switchStatementPoStatusSelect)
</script>        	
        ';
        return $html;
    }
}

