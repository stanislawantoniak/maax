<?php
class Zolago_Po_Model_Source extends ZolagoOs_OmniChannelPo_Model_Source
{
    const UDPO_STATUS_PAYMENT = 12;
    const UDPO_STATUS_TO_PICK = 13;
	
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udpo');

        $options = array();

        switch ($this->getPath()) {

        case 'udropship/stockpo/generate_on_po_status':
    	case 'udropship/batch/export_on_po_status':
        case 'udropship/purchase_order/default_po_status':
        case 'udropship/purchase_order/default_virtual_po_status':
        case 'udropship/vendor/restrict_udpo_status':
        case 'udropship/pocombine/notify_on_status':
        case 'udropship/pocombine/after_notify_status':
        case 'udropship/statement/statement_po_status':
        case 'statement_po_status':
        case 'po_statuses':
        case 'notify_by_udpo_status':
        case 'initial_po_status':
        case 'initial_virtual_po_status':
        case 'vendor_po_grid_status_filter':
            $options = array(
                self::UDPO_STATUS_PENDING    => $hlp->__('Pending collection'),
                self::UDPO_STATUS_PAYMENT    => $hlp->__('Pending payment'),
                self::UDPO_STATUS_TO_PICK    => $hlp->__('Ready to pick up'),
                self::UDPO_STATUS_EXPORTED   => $hlp->__('Collecting and packing'),
                self::UDPO_STATUS_ACK        => $hlp->__('Pending acceptance'),
                self::UDPO_STATUS_BACKORDER  => $hlp->__('Pending stock check'),
                self::UDPO_STATUS_ONHOLD     => $hlp->__('Problem'),
                self::UDPO_STATUS_READY      => $hlp->__('Packed and label printed'),
                self::UDPO_STATUS_PARTIAL    => $hlp->__('Partially Shipped'), // unuse
                self::UDPO_STATUS_SHIPPED    => $hlp->__('Shipped'),
                self::UDPO_STATUS_DELIVERED  => $hlp->__('Delivered'),
                self::UDPO_STATUS_CANCELED   => $hlp->__('Canceled'),
                self::UDPO_STATUS_RETURNED   => $hlp->__('Returned'),
            );
            if (Mage::helper('udropship')->isModuleActive('ustockpo')) {
                $options[self::UDPO_STATUS_STOCKPO_READY]   = $hlp->__('Ready for stock PO');
                $options[self::UDPO_STATUS_STOCKPO_EXPORTED]   = $hlp->__('Exported stock PO');
                $options[self::UDPO_STATUS_STOCKPO_RECEIVED]   = $hlp->__('Received stock PO');
            }
            if (in_array($this->getPath(), array('initial_po_status','statement_po_status','initial_virtual_po_status'))) {
                $options = array('999' => $hlp->__('* Default (global setting)')) + $options;
            }
            break;
            
        case 'udropship/purchase_order/po_increment_type':
        case 'po_increment_types':
            $options = array(
                self::UDPO_INCREMENT_NATIVE      => $hlp->__('Magento Native'),
                self::UDPO_INCREMENT_ORDER_BASED => $hlp->__('Order Based'),
            );
            break;

        case 'udropship/purchase_order/autoinvoice_shipment':
            $options = array(
                ZolagoOs_OmniChannelPo_Model_Source::AUTOINVOICE_SHIPMENT_NO => $hlp->__('No'),
                ZolagoOs_OmniChannelPo_Model_Source::AUTOINVOICE_SHIPMENT_YES => $hlp->__('Yes'),
                ZolagoOs_OmniChannelPo_Model_Source::AUTOINVOICE_SHIPMENT_ORDER => $hlp->__('Trigger whole order invoice'),
            );
            break;
        
        case 'udropship/purchase_order/shipment_increment_type':
        case 'shipment_increment_types':
            $options = array(
                self::SHIPMENT_INCREMENT_NATIVE      => $hlp->__('Magento Native'),
                self::SHIPMENT_INCREMENT_ORDER_BASED => $hlp->__('Order Based'),
                self::SHIPMENT_INCREMENT_PO_BASED    => $hlp->__('PO Based'),
            );
            break;

        case 'vendor_po_grid_sortby':
            $options = array(
                'order_increment_id' => $hlp->__('Order ID'),
                'increment_id' => $hlp->__('PO ID'),
                'order_date' => $hlp->__('Order Date'),
                'po_date' => $hlp->__('PO Date'),
                'shipping_method' => $hlp->__('Delivery Method'),
                'udropship_status' => $hlp->__('PO Status'),
            );
            break;

        case 'new_order_notifications':
            $options = array(
                '' => $hlp->__('* No notification'),
                '1' => $hlp->__('* Email notification'),
                '-1' => $hlp->__('* Email notification By Status'),
            );
            $config = Mage::getConfig()->getNode('global/udropship/notification_methods');
            foreach ($config->children() as $code=>$node) {
                if (!$node->label) {
                    continue;
                }
                $options[$code] = $hlp->__((string)$node->label);
            }
            asort($options);
            break;
            case 'payment_method':
                $options = [];
                foreach (Mage::helper('payment')->getStoreMethods() as $method) {
                    if ($method->getConfigData('visible')
                        //&& $method->canUseCheckout()
                    ) {
                        switch ($method->getCode()) {
                            case Zolago_Payment_Model_Cc::PAYMENT_METHOD_CODE:
                            case Zolago_Payment_Model_Gateway::PAYMENT_METHOD_CODE:
                            $paymentCode = Zolago_Dotpay_Model_Client::PAYMENT_METHOD;
                            continue 1;
                            default:
                                $paymentCode = $method->getCode();

                        }

                        $options[$paymentCode] = Mage::getStoreConfig('payment/'.$paymentCode.'/title');

                    }
                }
             break;

        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__('* Please select')) + $options;
        }

        return $options;
    }

}
