<?php
/**
 * Class Modago_Integrator_ZintegrationController
 *
 *
 */
abstract class Modago_Integrator_Controller_Abstract extends
    Mage_Core_Controller_Front_Action {

    /**
     * check id and secret key
     *
     * @return bool
     */

    protected function _checkAuthorization() {
        $request = $this->getRequest();
        $helper = Mage::helper('modagointegrator');
        if (($request->getParam('id') == $helper->getExternalId())
                && ($request->getParam('key') == $helper->getSecret())) {
            return true;
        }
        die('Wrong id or secret key');
    }

    abstract public function indexAction();

    protected function _flushData($filename) {
        $this->addHttpHeaders($filename);
        readfile($filename);
        // change file name
        rename($filename,$filename.'.old');
    }
    /**
     * get file
     *
     * @param string $type
     * @param bool $generate
     */
    protected function _getFile($type,$generate = false) {
        set_time_limit(3600);
        try {
            $this->_checkAuthorization();
            $model = Mage::helper('modagointegrator')->createGenerator($type);
            if ($filename = $model->getFile()) {
                // print file data
                $this->_flushData($filename);
            } else {
                if ($generate) {
                    $model->generate();
                    $model->compress();
                    if ($filename = $model->getFile()) {
                        $this->_flushData($filename);
                    } else {
                        echo sprintf('Genration file error: %s',$type);
                    }
                } else {
                    echo sprintf('No file type: %s',$type);
                }
            }
            die();
        } catch (Exception $xt) {
            Mage::logException($xt);
            echo $xt->getMessage();
        }
    }
    /**
    * adding headers before download
    *
    * @param string $filename
    */
    public function addHttpHeaders($filename) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();


    }

}
