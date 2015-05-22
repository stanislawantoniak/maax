<?php

/**
 * GH Urlrewrites adminhtml controller
 */
class GH_Rewrite_Adminhtml_GhrewriteController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction() {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->_title($rewriteHelper->__('GH URL Rewrite Management'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/rewrite');
        $this->_addContent($this->getLayout()->createBlock('ghrewrite/adminhtml_ghrewrite'));
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ghrewrite/adminhtml_ghrewrite_grid')->toHtml()
        );
    }

    public function loadcsvAction() {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $this->_title($rewriteHelper->__('GH URL Rewrite Management'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog/rewrite');
        $this->_addContent($this->getLayout()->createBlock('ghrewrite/adminhtml_ghrewrite_csv'));
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $ids = $this->getRequest()->getParam('url_id');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {

                foreach ($ids as $id) {
                    $row = Mage::getModel('ghrewrite/url')->load($id);
                    $row->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massGenerateAction() {
        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $ids = $this->getRequest()->getParam('url_id');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {

                foreach ($ids as $id) {
                    $row = Mage::getModel('ghrewrite/url')->load($id);
                    //TODO refs#1134
                    Mage::getSingleton('adminhtml/session')->addSuccess('[dev] url_id: '.$id);// remove this line
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $rewriteHelper->__(
                        'Total of %d record(s) were successfully generated', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
