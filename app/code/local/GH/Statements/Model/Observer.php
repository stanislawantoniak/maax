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


                    $statement = self::initStatement($vendor, $calendarItem);

                    self::processStatementsOrders($statement);
                    self::processStatementsRma();
                    self::processStatementsRefunds();
                    self::processStatementsTracks();

                    self::populateStatement();
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
            throw new Mage_Core_Exception(Mage::helper('ghstatements')->__('Statement already exist'));
        }

        /** @var GH_Statements_Model_Calendar $calendar */
        $calendar = Mage::getModel('ghstatements/calendar')->load($calendarItem->getCalendarId());

        /** @var GH_Statements_Model_Statement $statement */
        $statement = Mage::getModel('ghstatements/statement');
        $statement->setData(array(
            "vendor_id"         => (int)$vendor->getId(),
            "calendar_id"       => (int)$calendarItem->getCalendarId(),
            "calendar_item_id"  => (int)$calendarItem->getItemId(),
            "name"              => $vendor->getVendorName() . ' ' . date("Y-m-d", strtotime($calendarItem->getEventDate())) . ' (' . $calendar->getName()  . ')'
        ));
        $statement->save();

        return $statement;
    }

    /**
     * This populate statement with sums of ...
     */
    public static function populateStatement() {

    }

    /**
     * This process statements orders
     * @param GH_Statements_Model_Statement $statement
     */
    public static function processStatementsOrders($statement) {
        $statementId = (int)$statement->getId();
        // TODO
    }

    /**
     * This process statements refunds
     */
    public static function processStatementsRefunds() {

    }

    /**
     * This process statements tracks
     */
    public static function processStatementsTracks() {

    }

    /**
     * This process statements RMA
     */
    public static function processStatementsRma() {

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
        $collection->addFieldToFilter('calendar_item_id', $calendarItem->getItemId());

        return $collection->getFirstItem()->getId() ? true : false;
    }
}