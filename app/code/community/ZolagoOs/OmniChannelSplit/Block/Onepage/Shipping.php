<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    static $_shippingOverridden = false;

    protected function _toHtml()
    {
        if (!Mage::helper('udsplit')->isActive()) {
            return parent::_toHtml();
        }

        $block = $this->getLayout()->createBlock('udsplit/onepage_review')
            ->setTemplate('checkout/onepage/review/info.phtml')
            ->setShowDropdowns(true);
        Mage::helper('udropship')->applyItemRenderers('checkout_onepage_review', $block);

        $html = $block->toHtml();
        if (!self::$_shippingOverridden) {
            self::$_shippingOverridden = true;

            $html = str_replace('id="checkout-review-submit"', 'id="udsplit-checkout-review-submit"', $html);

            $js = <<<EOT
<script type="text/javascript">
if (typeof ShippingMethod != "undefined") {
    ShippingMethod.prototype.validate = function() {
        var methods = $$('.shipment-methods');
        if (methods.length==0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no shipping methods available for it. Please make neccessary changes in your shipping address.'));
            return false;
        }
        for (var i=0; i<methods.length; i++) {
            if (methods[i].options.length==0) {
                alert(Translator.translate('Your order can not be completed at this time as some of the shipping methods are not available. Please make neccessary changes in your shipping address.'));
                return false;
            }
        }
        return true;
    }
}
$('udsplit-checkout-review-submit') && Element.remove('udsplit-checkout-review-submit')
</script>
EOT;
            $html = $js.$html;

            if (Mage::getStoreConfigFlag('carriers/udsplit/sm_hide_amounts')) {

                $qtyLbl = Mage::helper('checkout')->__('Qty');
                $html .= <<<EOT
<script type="text/javascript">
var uds_sm_hide_amounts_idx = 0
$$('#checkout-review-table thead tr th').each(function(el, idx){
    if (el.innerHTML.strip() == '$qtyLbl') uds_sm_hide_amounts_qty_idx = idx
    if (idx>0 && el.innerHTML.strip() != '$qtyLbl') el.remove()
})
$$('#checkout-review-table colgroup col').each(function(el, idx){
    if (idx>0 && idx != uds_sm_hide_amounts_qty_idx) el.remove()
})
$$('#checkout-review-table tbody tr').each(function(trEl, idx){
    trEl.childElements().each(function(tdEl, idx){
        if (idx>0 && idx != uds_sm_hide_amounts_qty_idx) {
            tdEl.remove()
        } else if (idx == uds_sm_hide_amounts_qty_idx) {
            tdEl.colspan=1
            tdEl.addClassName('last')
        }
        var uvSub = tdEl.select('.udsplit-vendor-subtotal')
        if (uvSub && uvSub.length>0) {
            uvSub[0].remove()
        }
    })
})
</script>
EOT;
            }

        }

        return $html;
    }
}