<?php
/**
 * message types for message queue
 */
class GH_Api_Model_System_Source_Message_Type {
    const GH_API_MESSAGE_NEW_ORDER				= 'newOrder';
    const GH_API_MESSAGE_CANCELLED_ORDER   	= 'cancelledOrder';
    const GH_API_MESSAGE_PAYMENT_DATA_CHANGED  = 'paymentDataChanged';
    const GH_API_MESSAGE_ITEMS_CHANGED			= 'itemsChanged';
    const GH_API_MESSAGE_DELIVERY_DATA_CHANGED = 'deliveryDataChanged';
    const GH_API_MESSAGE_INVOICE_ADDRESS_CHANGED = 'invoiceAddressChanged';
    const GH_API_MESSAGE_STATUS_CHANGED		= 'statusChanged';
}
