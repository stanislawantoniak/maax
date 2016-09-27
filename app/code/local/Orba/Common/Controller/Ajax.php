<?php

class Orba_Common_Controller_Ajax extends Mage_Core_Controller_Front_Action {
    
    /**
     * Sets "flash message"
     * 
     * @param string $message
     */
    protected function _setFlashMessage($message) {
        $this->_getSession()->setOrbacommonAjaxFlashMessage($message);
    }
    
    /**
     * Gets "flash message" and clears it
     * 
     * @return string
     */
    protected function _getFlashMessage() {
        $message = $this->_getSession()->getOrbacommonAjaxFlashMessage();
        $this->_setFlashMessage(null);
        return $message;
    }

	/**
	 * Sets "flash details"
	 *
	 * @param array $details
	 * @return $this
	 */
	protected function _setFlashDetails($details = array()) {
		$this->_getSession()->setOrbacommonAjaxFlashDetails($details);
		return $this;
	}

	/**
	 * Gets "flash details" and clears it
	 *
	 * @return string|array
	 */
	protected function _getFlashDetails() {
		$details = $this->_getSession()->getOrbacommonAjaxFlashDetails();
		$this->_setFlashDetails(null);
		return $details;
	}

	/**
	 * Escape quotes in java scripts
	 *
	 * @param mixed $data
	 * @param string $quote
	 * @return mixed
	 */
	public function jsQuoteEscape($data, $quote = '\'')
	{
		return Mage::helper('core')->jsQuoteEscape($data, $quote);
	}
    
    /**
     * Gets session instance
     * 
     * @return Mage_Core_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('core/session');
    }
    
    /**
     * Logs AJAX errors
     * 
     * @param Mage_Core_Controller_Request_Http $request
     * @param string $message
     */
    protected function _logError($request, $message) {
        $data = array(
            'request' => array(
                'module' => $request->getModuleName(),
                'controller' => $request->getControllerName(),
                'action' => $request->getActionName(),
                'params' => $request->getParams()
            ),
            'message' => $message
        );
        Mage::helper('orbacommon')->log(var_export($data, true), Orba_Common_Helper_Data::LOG_SCOPE_AJAX);
    }
    
    /**
     * Logs AJAX exception and prepares proper JSON response with error message
     * 
     * @param Exception $e
	 * @param array $details
     */
    protected function _processException($e, $details = array()) {
        if (get_class($e) === 'Exception') {
            Mage::logException($e);
            $message = $this->__('Technical error');
        } else {
            $message = $e->getMessage();
        }
        $this->_logError($this->getRequest(), $e->getMessage());
        $result = array(
            'status' => false,
            'message' => $this->__($message),
			'details' => $details
        );
        $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type', 'application/json')
                ->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    /**
     * Prepares JSON response with results
     * 
     * @param array $result
     * @param int $expires In seconds
     * @param int $lastModified Timestamp
     */
    protected function _setSuccessResponse($result, $expires = null, $lastModified = null) {
        $_helper = Mage::helper('orbacommon');
        $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-type', 'application/json', true);
                //->setHeader('Set-Cookie', '', true);
        if ($expires) {
            $this->getResponse()
                    ->setHeader('Cache-Control', 'public, cache, must-revalidate, post-check='.$expires.', pre-check='.$expires.', max-age='.$expires, true)
                    ->setHeader('Pragma', 'cache', true)
                    ->setHeader('Expires', $_helper->timestampToGmtDate(time() + $expires), true);
        } else {
            $this->getResponse()
                    ->setHeader('Pragma', 'no-cache', true)
                    ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Expires', $_helper->timestampToGmtDate(0), true);
        }
        if ($lastModified) {
            $this->getResponse()->setHeader('Last-Modified', $_helper->timestampToGmtDate($lastModified), true);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    /**
     * Generates basic success response
     * 
     * @param string $message
     * @return array
     */
    protected function _generateBasicSuccessResponse($message) {
        $content = array(
            'message' => $this->__($message)
        );
		$result = $this->_formatSuccessContentForResponse($content);
		if ($details = $this->_getFlashDetails()) {
			$result['details'] = $details;
		}
        return $result;
    }
    
    /**
     * Formats success content for response
     * 
     * @param array $content
     * @param bool|int $status
     * @return array
     */
    protected function _formatSuccessContentForResponse($content, $status = true) {
        return array(
            'status' => $status,
            'content' => $content
        );
    }
    
    /**
     * Gets product id from request. The method checks "product_id" and "sku" params.
     * 
     * @return int
     */
    protected function _getProductIdFromRequest() {
        $request = $this->getRequest();
        $productId = $request->getParam('product_id', null);
        if (!$productId) {
            $sku = $request->getParam('sku', null);
            if ($sku) {
                $productId = Mage::getSingleton('catalog/product')->getIdBySku($sku);
            }
        }
        return $productId;
    }

}