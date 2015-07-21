<?php

class GH_Statements_Model_Observer
{

    public static function processStatements() {

        /* @var $transaction Varien_Db_Adapter_Interface */
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');

        try {
            $transaction->beginTransaction();

            self::initStatement();

            self::processStatementsOrders();
            self::processStatementsRma();
            self::processStatementsRefunds();
            self::processStatementsTracks();

            self::populateStatement();

            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollBack();
            Mage::logException($ex);
        }
    }

    /**
     * This create row for statement
     */
    private function initStatement() {

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