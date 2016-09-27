<?php

class Snowdog_Freshmail_Model_Api_RequestManager
{
    /**
     * Logger instance
     *
     * @var Snowdog_Freshmail_Model_Log_Adapter
     */
    protected $_logger;

    /**
     * Init request manager
     */
    public function __construct()
    {
        $this->_logger = Mage::getModel(
            'snowfreshmail/log_adapter',
            'snowfreshmail.log'
        );
    }

    /**
     * @param int $time
     */
    public function clean($time)
    {
        /** @var Mage_Core_Model_Resource $resourceModel */
        $resourceModel = Mage::getSingleton('core/resource');
        $adapter = $resourceModel->getConnection('core_write');
        $adapter->delete(
            $resourceModel->getTableName('snowfreshmail/api_request'),
            $adapter->quoteInto('date_expires < ?', date('Y-m-d H:i:s', $time))
        );
    }

    /**
     * Run request action
     *
     * @param Snowdog_Freshmail_Model_Api_Request   $request
     * @param bool                                  $forceExpired
     */
    public function run(Snowdog_Freshmail_Model_Api_Request $request, $forceExpired = false)
    {
        if (!Mage::helper('snowfreshmail/api')->isConnected()) {
            return;
        }

        $action = str_replace(
            ' ',
            '',
            ucwords(str_replace('_', ' ', $request->getAction()))
        );
        $modelClass = 'snowfreshmail/api_action_' . $action;
        $action = Mage::getModel($modelClass);
        if ($action) {
            /** @var Snowdog_Freshmail_Model_Api_Action_Interface $action */
            try {
                if (!$forceExpired && $request->needToBeExpired()) {
                    $request->expiry();
                    return;
                }
                $action->execute($request);
                $request->setStatus(
                    Snowdog_Freshmail_Model_Api_Request::STATUS_SUCCESS
                );
                $request->setLastResponse(null);
            } catch (Exception $e) {
                $request->setStatus(
                    Snowdog_Freshmail_Model_Api_Request::STATUS_FAILED
                );
                $request->setLastResponse($e->getMessage());
                $this->_logger->log($e->getMessage());
            }
            $request->setProcessedAt(
                Mage::getSingleton('core/date')->gmtDate()
            );
            $request->save();
        } else {
            $this->_logger->log(
                sprintf(
                    'Request action model could not be found : %s',
                    $modelClass
                )
            );
        }
    }
}
