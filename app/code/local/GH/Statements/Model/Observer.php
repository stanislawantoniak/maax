<?php

class GH_Statements_Model_Observer
{

    /**
     * todo description here
     */
    public static function processStatements() {

        /* @var $transaction Varien_Db_Adapter_Interface */
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
	    $alreadyExists = array();

        try {
            $transaction->beginTransaction();

            /* Format our dates */
            /** @var Mage_Core_Model_Date $dateModel */
            $dateModel = Mage::getModel('core/date');
            $today     = $dateModel->date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));

            // Collection of active vendors who have statement calendar
            /* @var $collection Unirgy_Dropship_Model_Mysql4_Vendor_Collection */
            $VendorsSollection = Mage::getResourceModel('udropship/vendor_collection');
            $VendorsSollection->addStatusFilter(Unirgy_Dropship_Model_Source::VENDOR_STATUS_ACTIVE);
            $VendorsSollection->addFieldToFilter('statements_calendar', array('neq' => null));

            foreach($VendorsSollection as $vendor) {
                /** @var Zolago_Dropship_Model_Vendor $vendor */
                $calendarId = (int)$vendor->getStatementsCalendar();

                /* @var GH_Statements_Model_Resource_Calendar_Item_Collection $itemCollection */
                $itemCollection = Mage::getResourceModel('ghstatements/calendar_item_collection');
                $itemCollection->addFieldToFilter('calendar_id', $calendarId);
                $itemCollection->addFieldToFilter('event_date', array('eq' => $yesterday));

                if ($itemCollection->getFirstItem()->getId()) {
                    /** @var GH_Statements_Model_Calendar_Item $calendarItem */
                    $calendarItem = $itemCollection->getFirstItem();

	                try {
		                $statement = self::initStatement($vendor, $calendarItem);

		                $statementTotals = new stdClass();
		                $statementTotals->order = self::processStatementsOrders($statement, $vendor);
		                $statementTotals->rma = self::processStatementsRma();
		                $statementTotals->refund = self::processStatementsRefunds($statement);
		                $statementTotals->track = self::processStatementsTracks($statement);

		                self::populateStatement($statement, $statementTotals);
	                } catch(Mage_Core_Exception $e) {
                        Mage::log($e->getMessage(), null, 'ghstatements_cron_exception.log');
		                $alreadyExists[] = $e->getMessage();
	                }
                }
            }

            $transaction->commit();
        } catch (Mage_Core_Exception $ex){
            // For example when 'Statement already exist'
            $transaction->rollBack();
        } catch (Exception $ex) {
            $transaction->rollBack();
            Mage::logException($ex);
        }
    }

    /**
     * This create row for statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param GH_Statements_Model_Calendar_Item $calendarItem
     * @throws Exception
     * @throws Mage_Core_Exception
     * @return GH_Statements_Model_Statement
     */
    public static function initStatement($vendor, $calendarItem) {

        if (self::isStatementAlready($vendor, $calendarItem)) {
            throw new Mage_Core_Exception(Mage::helper('ghstatements')->__('Statement for date %s and vendor %s already exist',$calendarItem->getEventDate(),$vendor->getVendorName()));
        }

        /** @var GH_Statements_Model_Calendar $calendar */
        $calendar = Mage::getModel('ghstatements/calendar')->load($calendarItem->getCalendarId());

        /** @var GH_Statements_Model_Statement $statement */
        $statement = Mage::getModel('ghstatements/statement');
        $statement->setData(array(
            "vendor_id"         => (int)$vendor->getId(),
            "calendar_id"       => (int)$calendarItem->getCalendarId(),
            "event_date"        => $calendarItem->getEventDate(),
            "name"              => $vendor->getVendorName() . ' ' . date("Y-m-d", strtotime($calendarItem->getEventDate())) . ' (' . $calendar->getName()  . ')'
        ));
        $statement->save();

        return $statement;
    }

    /**
     *
     * This populate statement with sums of totals
     *
     * @param GH_Statements_Model_Statement $statement
     * @param $statementTotals
     */
    public static function populateStatement(GH_Statements_Model_Statement $statement, $statementTotals)
    {
        $data = array(
            //order
            "order_commission_value" => $statementTotals->order->commissionAmount,
            "order_value" => $statementTotals->order->amount,

            //rma
            "rma_commission_value" => $statementTotals->rma->commissionAmount,
            "rma_value" => $statementTotals->rma->amount,

            //refund
            "refund_value" => $statementTotals->refund->amount,

            //track
            "tracking_charge_subtotal" => $statementTotals->track->netto,
            "tracking_charge_total" => $statementTotals->track->brutto,
        );

        $statement->addData($data);
        $statement->save();
    }

    /**
     * This process statements orders
     * @param GH_Statements_Model_Statement $statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @return stdClass
     * @throws Exception
     */
    public static function processStatementsOrders(&$statement, $vendor) {
        $orderStatementTotals = new stdClass();

        /* @var Zolago_Po_Model_Resource_Po_Collection $collection */
        $collection = Mage::getResourceModel('zolagopo/po_collection');
        $collection->addVendorFilter($vendor);
        $collection->addFieldToFilter('main_table.statement_id', array('null' => true));
        $collection->addFieldToFilter('main_table.udropship_status', array('in' => array(
            Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED,    // Wysłano
            Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED,  // Dostarczono
            Zolago_Po_Model_Source::UDPO_STATUS_RETURNED    // Zwrocono
        )));

        $commissionAmount = 0;
        $amount = 0;

        foreach ($collection as $po) {
            /** @var Zolago_Po_Model_Po $po */

            // Shipping and track
            $currentShipping = $po->getLastNotCanceledShipment();

	        if($currentShipping) {
		        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
		        $track = $currentShipping->getTracksCollection()->getFirstItem();
		        $shippingCost = $currentShipping->getShippingAmountIncl();

		        // Data to save
		        $data = array();
		        $data['statement_id'] = $statement->getId();
		        $data['po_id'] = $po->getId();
		        $data['po_increment_id'] = $po->getIncrementId(); // Nr zamówienia
		        $data['payment_channel_owner'] = $po->getPaymentChannelOwner(); // System płatności (galeria | partner)
		        $data['shipped_date'] = $track->getShippedDate(); // Data wysyłki
		        $data['carrier'] = $track->getTitle(); // Kurier
		        $data['gallery_shipping_source'] = $track->getGalleryShippingSource(); // Kontrakt kurierski
		        $data['payment_method'] = ucfirst(str_replace('_', ' ', $po->ghapiPaymentMethod())); // Metoda płatności

		        /** @var Zolago_Po_Model_Resource_Po_Item_Collection $itemsColl */
		        $itemsColl = $po->getItemsCollection();

		        foreach ($itemsColl as $item) {
			        /** @var Zolago_Po_Model_Po_Item $item */
			        if ($item->getParentItemId()) {
				        continue; // Skip simple from configurable
			        }
			        $data['po_item_id'] = $item->getId();
			        $data['sku'] = $item->getFinalSku();// SKU
			        $data['qty'] = $item->getQty();
			        $data['price'] = $item->getPriceInclTax() * $item->getQty(); // Sprzedaż przed zniżką (zł)
			        $data['discount_amount'] = $item->getDiscountAmount(); // Zniżka (zł)
			        $data['commission_percent'] = $item->getCommissionPercent(); // Stawka prowizji Modago
			        $data['final_price'] = $item->getFinalItemPrice() * $item->getQty(); // Sprzedaż w zł

			        $data['gallery_discount_value'] = 0;
			        foreach ($item->getDiscountInfo() as $relation) {
				        /** @var Zolago_SalesRule_Model_Relation $relation */
				        if ($relation->getPayer() == Zolago_SalesRule_Model_Rule_Payer::PAYER_GALLERY) {
					        $data['gallery_discount_value'] += floatval($relation->getDiscountAmount()); // Zniżka finansowana przez Modago
				        }
			        }

			        $data['shipping_cost'] = 0;
			        if ($shippingCost) { // Shipping cost for first item only
				        $data['shipping_cost'] = $shippingCost; // Transport
				        $shippingCost = 0;
			        }
			        // (( <Sprzedaż przed zniżką> - <zniżka> + <Zniżka finansowana przez Modago>) * <Stawka prowizji Modago> ) * <podatek>
			        $data['commission_value'] =
				        (($data['price'] - $data['discount_amount'] + $data['gallery_discount_value'])
					        * (floatval($data['commission_percent']) / 100)) * self::getTax(); // Prowizja Modago
                    $data['commission_value'] = round($data['commission_value'], 2, PHP_ROUND_HALF_UP);

                    if ($po->getPaymentChannelOwner() == Zolago_Payment_Model_Source_Channel_Owner::OWNER_MALL) {
                        // <Sprzedaż w zł> + <Transport> - <Prowizja Modago> + <Zniżka finansowana przez Modago>
                        $data['value'] = $data['final_price'] + $data['shipping_cost'] - $data['commission_value'] + $data['gallery_discount_value']; // Do wypłaty
                    } else {
                        // - <prowizja modago> + <Zniżka finansowana przez Modago>
                        $data['value'] = $data['gallery_discount_value'] - $data['commission_value']; // Do wypłaty
                    }
                    $data['value'] = round($data['value'], 2, PHP_ROUND_HALF_UP);

			        $commissionAmount += $data['commission_value'];
			        $amount += $data['value'];

			        // Save
			        $statementOrder = Mage::getModel('ghstatements/order');
			        $statementOrder->setData($data);
			        $statementOrder->save();
		        }

		        // For each PO save info about statement was processed
		        $po->setData('statement_id', $statement->getId());
		        $po->save();
	        }
        }
        $orderStatementTotals->commissionAmount = $commissionAmount;
        $orderStatementTotals->amount = $amount;

        return $orderStatementTotals;
    }

	/**
	 * @param GH_Statements_Model_Statement $statement
	 * @return GH_Statements_Model_Statement
	 */
    public static function processStatementsRefunds(&$statement) {
        $refundStatementTotals = new stdClass();

	    /** @var GH_Statements_Model_Refund $refundsStatements */
	    $refundsStatements = Mage::getModel('ghstatements/refund');

	    $dateModel = Mage::getModel('core/date');
	    $today     = $dateModel->date('Y-m-d');
	    $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));






	    $yesterday = $today; //todo: remove







	    $collection = $refundsStatements->getCollection();
	    $collection
		    ->addFieldToFilter('statement_id',array('null' => true))
		    ->addFieldToFilter('date',array('lteq' => $yesterday))
		    ->addFieldToFilter('vendor_id',$statement->getVendorId());

	    $refundValue = 0;
	    $refundIdsToUpdate = array();
	    if($collection->getSize()) {
		    foreach($collection as $refundStatement) {
			    /** @var GH_Statements_Model_Refund $refundStatement */
			    $refundValue += $refundStatement->getValue();
			    $refundIdsToUpdate[] = $refundStatement->getId();
		    }
	    }

	    if(count($refundIdsToUpdate)) {
		    /** @var GH_Statements_Model_Resource_Refund $refundStatementsResource */
		    $refundStatementsResource = $refundsStatements->getResource();
		    $refundStatementsResource->assignToStatement($statement->getId(), $refundIdsToUpdate);
	    }

	    $statement->setRefundValue($refundValue);
        $refundStatementTotals->amount = $refundValue;

	    return $refundStatementTotals;
    }

    /**
     * This process statements tracks
     */
    public static function processStatementsTracks(&$statement) {
        $trackStatementTotals = new stdClass();

        $nettoTotal = 0;
        $bruttoTotal =0;

	    $dateModel = Mage::getModel('core/date');
	    $today     = $dateModel->date('Y-m-d');
	    $yesterday = date('Y-m-d', strtotime('yesterday',strtotime($today)));





	    $yesterday = $today;//todo: remove





	    $trackStatements = array();
	    $tax = self::getTax();

	    /** @var Mage_Sales_Model_Order_Shipment $ordersShipments */
		$ordersShipments = Mage::getModel('sales/order_shipment');

	    //orders tracking start

	    //load orders shipments
	    $orderShipmentsCollection = $ordersShipments->getCollection();
	    $orderShipmentsCollection
		    ->addFieldToFilter('statement_id',array('null' => true))
		    ->addFieldToFilter('udropship_vendor',$statement->getVendorId())
		    ->addFieldToFilter('udropship_status',array('neq'=>Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED));

	    //prepare arrays to load orders shipments trackings
	    $ordersShipmentsIds = array();
	    $ordersShipmentsObjects = array();
	    if($orderShipmentsCollection->getSize()) {
		    foreach($orderShipmentsCollection as $orderShipment) {
			    /** @var Mage_Sales_Model_Order_Shipment $orderShipment */
			    $ordersShipmentsIds[] = $orderShipment->getId();
			    $ordersShipmentsObjects[$orderShipment->getId()] = $orderShipment;
		    }
	    }

	    if(count($ordersShipmentsIds)) {

			//load orders shipments tracks based on shimpents ids gathered in previous loop
		    /** @var Mage_Sales_Model_Order_Shipment_Track $ordersTracks */
		    $ordersTracks = Mage::getModel('sales/order_shipment_track');

		    $ordersTracksCollection = $ordersTracks->getCollection();
		    $ordersTracksCollection
			    ->addFieldToFilter('statement_id', array('null' => true))
			    ->addFieldToFilter('gallery_shipping_source', 1)
			    ->addFieldToFilter('shipped_date', array('notnull' => true))
			    ->addFieldToFilter('shipped_date', array('lteq' => $yesterday))
			    ->addFieldToFilter('parent_id',array('in'=>$ordersShipmentsIds))
		        ->addFieldToFilter('udropship_status',array('in'=>array(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED,Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED)));

		    //not all shipments selected in previous loop will be adequate to update so we have to collect shipments ids once again based on loaded trackings
		    $ordersShipmentsIdsToUpdate = array();
		    if($ordersTracksCollection->getSize()) {
			    foreach($ordersTracksCollection as $orderTrack) {
				    /** @var Mage_Sales_Model_Order_Shipment_Track $orderTrack */
				    $shipmentId = $orderTrack->getParentId();
				    $shipment = $ordersShipmentsObjects[$shipmentId];
				    $ordersShipmentsIdsToUpdate[] = $shipmentId;

				    $chargeTotal = $orderTrack->getChargeTotal() * $tax;
                    $chargeTotal = round($chargeTotal, 2, PHP_ROUND_HALF_UP);

				    //prepare array to insert into gh_statements_track
				    $trackStatements[] = array(
					    'statement_id'      => $statement->getId(),
					    'po_id'             => $shipment->getUdpoId(),
					    'po_increment_id'   => $shipment->getUdpoIncrementId(),
					    'rma_id'            => null,
					    'rma_increment_id'  => null,
					    'shipped_date'      => $orderTrack->getShippedDate(),
					    'track_number'      => $orderTrack->getTrackNumber(),
					    'charge_shipment'   => $orderTrack->getChargeShipment(),
					    'charge_fuel'       => $orderTrack->getChargeFuel(),
					    'charge_insurance'  => $orderTrack->getChargeInsurance(),
					    'charge_cod'        => $orderTrack->getChargeCod(),
					    'charge_subtotal'   => $orderTrack->getChargeTotal(),
					    'charge_total'      => $chargeTotal,
				    );

				    $nettoTotal += $orderTrack->getChargeTotal();
				    $bruttoTotal += $chargeTotal;
			    }
		    }
		    //orders tracking end

		    //rmas tracking start
		    /** @var Zolago_Rma_Model_Rma_Track $rmasTracks */
		    $rmasTracks = Mage::getModel('urma/rma_track');

		    $rmasTracksCollection = $rmasTracks->getCollection();
		    $rmasTracksCollection
			    ->addFieldToFilter('main_table.statement_id', array('null' => true))
			    ->addFieldToFilter('main_table.gallery_shipping_source', 1)
			    ->addFieldToFilter('main_table.udropship_status',array('in'=>array(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED,Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED)))
			    ->getSelect()
			        ->join(
				        'urma_rma',
				        'main_table.parent_id = urma_rma.entity_id',
				        array('udropship_vendor')
			        )
			        ->where('urma_rma.udropship_vendor = '.$statement->getVendorId());

		    $rmasTracksToUpdate = array();
			if($rmasTracksCollection->getSize()) {
				foreach($rmasTracksCollection as $rmaTrack) {
					/** @var Zolago_Rma_Model_Rma_Track $rmaTrack */

					/** @var Zolago_Rma_Model_Rma $rma */
					$rma = Mage::getModel("zolagorma/rma")->load($rmaTrack->getParentId());

					if($rma && $rma->getId()) {
						$rmasTracksToUpdate[] = $rmaTrack->getId();

						$chargeTotal = $rmaTrack->getChargeTotal() * $tax;
                        $chargeTotal = round($chargeTotal, 2, PHP_ROUND_HALF_UP);

						$shippedDate = date('Y-m-d',strtotime($rmaTrack->getUpdatedAt()));

						/** @var Zolago_Po_Model_Po $po */
						$po = $rma->getPo();

						//prepare array to insert into gh_statements_track
						$trackStatements[] = array(
							'statement_id'      => $statement->getId(),
							'po_id'             => $po->getId(),
							'po_increment_id'   => $po->getIncrementId(),
							'rma_id'            => $rma->getId(),
							'rma_increment_id'  => $rma->getIncrementId(),
							'shipped_date'      => $shippedDate,
							'track_number'      => $rmaTrack->getTrackNumber(),
							'charge_shipment'   => $rmaTrack->getChargeShipment(),
							'charge_fuel'       => $rmaTrack->getChargeFuel(),
							'charge_insurance'  => $rmaTrack->getChargeInsurance(),
							'charge_cod'        => $rmaTrack->getChargeCod(),
							'charge_subtotal'   => $rmaTrack->getChargeTotal(),
							'charge_total'      => $chargeTotal,
						);

						$nettoTotal += $rmaTrack->getChargeTotal();
						$bruttoTotal += $chargeTotal;
					}
				}
			}
		    //rmas tracking end

		    if(count($trackStatements)) {
			    /** @var GH_Statements_Model_Track $trackStatement */
			    $trackStatement = Mage::getModel("ghstatements/track");

			    /** @var GH_Statements_Model_Resource_Track $trackStatementResource */
			    $trackStatementResource = $trackStatement->getResource();
			    $trackStatementResource->appendTracks($trackStatements);
		    }

		    if(count($ordersShipmentsIdsToUpdate)) {
			    foreach($ordersShipmentsIdsToUpdate as $shipmentId) {
				    /** @var Mage_Sales_Model_Order_Shipment $currentShipment */
				    $currentShipment = $ordersShipmentsObjects[$shipmentId];
				    $currentShipment->setStatementId($statement->getId());
				    $currentShipment->save();
			    }
		    }

		    $trackStatementTotals->netto = $nettoTotal;
		    $trackStatementTotals->brutto = $bruttoTotal;

		    return $trackStatementTotals;
	    }
    }

    /**
     * This process statements RMA
     */
    public static function processStatementsRma() {
        $rmaStatementTotals = new stdClass();

        $commissionAmount = 0;
        $amount = 0;

        $rmaStatementTotals->commissionAmount = $commissionAmount;
        $rmaStatementTotals->amount = $amount;

        return $rmaStatementTotals;
    }

    /**
     * This check if statement for vendor and event date is in table
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param GH_Statements_Model_Calendar_Item $calendarItem
     * @return bool
     */
    public static function isStatementAlready($vendor, $calendarItem) {

        /* @var $collection GH_Statements_Model_Resource_Statement_Collection */
        $collection = Mage::getResourceModel('ghstatements/statement_collection');
        $collection->addFieldToFilter('vendor_id', $vendor->getId());
        $collection->addFieldToFilter('event_date', date("Y-m-d", strtotime($calendarItem->getEventDate())));

        return $collection->getFirstItem()->getId() ? true : false;
    }

    /**
     * This return tax for statement
     *
     * @return float
     */
    public static function getTax() {
        return floatval(str_replace(',','.', Mage::getStoreConfig('ghstatements/general/tax_for_commission')));
    }
}