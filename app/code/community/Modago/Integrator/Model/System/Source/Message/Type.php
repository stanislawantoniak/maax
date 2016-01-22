<?php
/**
 * message types for message queue
 */
class Modago_Integrator_Model_System_Source_Message_Type {
    const MESSAGE_NEW_ORDER				= 'newOrder';
    const MESSAGE_CANCELLED_ORDER   	= 'cancelledOrder';
    const MESSAGE_PAYMENT_DATA_CHANGED  = 'paymentDataChanged';
    const MESSAGE_ITEMS_CHANGED			= 'itemsChanged';
    const MESSAGE_DELIVERY_DATA_CHANGED = 'deliveryDataChanged';
    const MESSAGE_INVOICE_ADDRESS_CHANGED = 'invoiceAddressChanged';
    const MESSAGE_STATUS_CHANGED		= 'statusChanged';
    
    const MESSAGE_RESERVATION_STATUS_PROBLEM = 'problem';
    const MESSAGE_RESERVATION_STATUS_OK		 = 'ok';
}
