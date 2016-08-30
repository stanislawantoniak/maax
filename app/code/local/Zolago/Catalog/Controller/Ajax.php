<?php

class Zolago_Catalog_Controller_Ajax extends ZolagoOs_OmniChannel_Controller_VendorAbstract {
    
    /**
     * Sets "flash message"
     * 
     * @param string $message
     */
    protected function _setFlashMessage($message) {
        $this->_getSession()->setZolagocatalogAjaxFlashMessage($message);
    }
    
    /**
     * Gets "flash message" and clears it
     * 
     * @return string
     */
    protected function _getFlashMessage() {
        $message = $this->_getSession()->getZolagocatalogAjaxFlashMessage();
        $this->_setFlashMessage(null);
        return $message;
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
        Mage::helper('zolagocatalog')->log(var_export($data, true));
    }
    
    /**
     * Logs AJAX exception and prepares proper JSON response with error message
     * 
     * @param Exception $e
     */
    protected function _processException($e) {
        if (get_class($e) === 'Exception') {
            Mage:logException($e);
            $message = $this->__('Technical error');
        } else {
            $message = $e->getMessage();
        }
        $this->_logError($this->getRequest(), $e->getMessage());
        $result = array(
            'status' => 0,
            'message'=>array('message' => $this->__($message)),
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
        $_helper = Mage::helper('zolagocatalog');
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
        return $this->_formatSuccessContentForResponse($content);
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
}