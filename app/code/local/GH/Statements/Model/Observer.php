<?php

class GH_Statements_Model_Observer
{

    /**
     * todo description here
     */
    public static function processStatements() {

        /* @var $transaction Varien_Db_Adapter_Interface */
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');

        try {
            $transaction->beginTransaction();

            /* Format our dates */
            /** @var Mage_Core_Model_Date $dateModel */
            $dateModel = Mage::getModel('core/date');
            $today     = $dateModel->date('Y-m-d H:i:s');
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


                    self::initStatement($vendor, $calendarItem);

                    self::processStatementsOrders();
                    self::processStatementsRma();
                    self::processStatementsRefunds();
                    self::processStatementsTracks();

                    self::populateStatement();
                }
            }

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            Mage::logException($ex);
        }
    }

    /**
     * This create row for statement
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param GH_Statements_Model_Calendar_Item $calendarItem
     */
    private function initStatement($vendor, $calendarItem) {

        /** @var GH_Statements_Model_Calendar $calendar */
        $calendar = Mage::getModel('ghstatements/calendar')->load($calendarItem->getItemId());

        /** @var GH_Statements_Model_Statement $statement */
        $statement = Mage::getModel('ghstatements/statement');
        $statement->setData(array(
            "vendor_id"         => (int)$vendor->getId(),
            "calendar_id"       => (int)$calendarItem->getCalendarId(),
            "calendar_item_id"  => (int)$calendarItem->getItemId(),
            "name"              => $vendor->getVendorName() . ' ' . $calendar->getName() . '( ' . $calendarItem->getEventDate() . ' )'
        ));
        $statement->save();
    }

    /**
     * This populate statement with sums of ...
     */
    private function populateStatement() {

    }

    /**
     * This process statements orders
     */
    private function processStatementsOrders() {

    }

    /**
     * This process statements refunds
     */
    private function processStatementsRefunds() {

    }

    /**
     * This process statements tracks
     */
    private function processStatementsTracks() {

    }

    /**
     * This process statements RMA
     */
    private function processStatementsRma() {

    }
}