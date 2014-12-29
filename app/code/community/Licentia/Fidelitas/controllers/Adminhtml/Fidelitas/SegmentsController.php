<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Adminhtml_Fidelitas_SegmentsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('fidelitas/segments');

        $auth = Mage::getModel('fidelitas/egoi')->validateEgoiEnvironment();
        if (!$auth) {
            $this->_redirect('adminhtml/fidelitas_account/new');
        }

        return $this;
    }

    public function indexAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Segments'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_segments'));
        $this->renderLayout();
    }

    public function recordsgridAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/segments');

        $model->load($id);
        if (!$model->getId()) {
            throw new Exception($this->__('This segment no longer exists.'));
        }

        Mage::register('current_segment', $model);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction() {
        $this->_title($this->__('E-Goi'))->_title($this->__('Segments'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('fidelitas/segments');

        if ($id) {
            $model->load($id);
            if (!$model->getSegmentId()) {
                $this->_getSession()->addError($this->__('This segment no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Segment'));

        // set entered data if was error when we do save
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('current_segment_rule', $model);

        $this->_initAction()->getLayout()->getBlock('fidelitas_segments_edit')
                ->setData('action', $this->getUrl('*/promo_catalog/save'));

        $this->_addBreadcrumb($id ? $this->__('Edit Segment') : $this->__('New Segment'))
                ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {

        if ($this->getRequest()->getPost()) {

            $data = $this->getRequest()->getPost();

            try {

                $model = Mage::getModel('fidelitas/segments');
                $data = $this->_filterDates($data, array('deploy_at'));

                if ($id = $this->getRequest()->getParam('id')) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException($this->__('Wrong Segment specified.'));
                    }
                }

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $model->loadPost($data);
                $this->_getSession()->setFormData($model->getData());

                $model->setData('controller', true);
                $model->save();

                $this->_getSession()->addSuccess($this->__('The segment has been saved.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while saving the segment data. Please review the log and try again.'));
                Mage::logException($e);
                $this->_getSession()->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction() {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];


        $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('fidelitas/segments'))
                ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function deleteAction() {

        if ($this->getRequest()->getParam('id')) {

            $id = $this->getRequest()->getParam('id');

            try {
                $model = Mage::getModel('fidelitas/segments');
                $model->setId($id)->delete();

                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Segment was successfully deleted'));

                $this->_redirect('*/*/index');
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/index');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function recordsAction() {

        $this->_title($this->__('E-Goi'))->_title($this->__('Segments'))->_title($this->__('Records'));
        $id = $this->getRequest()->getParam('id');

        $model = Mage::getModel('fidelitas/segments');

        $model->load($id);
        if (!$model->getId()) {
            $this->_getSession()->addError($this->__('This segment no longer exists.'));
            $this->_redirect('*/*');
            return;
        }
        Mage::register('current_segment', $model, true);

        $this->_title($model->getName());

        if ($this->getRequest()->getParam('refresh') == 1) {

            $now = Mage::app()->getLocale()->date()->addHour(1)->get(Licentia_Fidelitas_Model_Campaigns::MYSQL_DATETIME);

            if ($model->getData('run') != '0' && $now <= $model->getData('run') && $model->getData('run') !== null) {
                $this->_getSession()->addError($this->__('Another update is already being processed. Please wait until it finishes.'));
                $this->_redirect('*/*/*', array('id' => $id));
                return;
            }

            Mage::getModel('fidelitas/segments_list')->loadList($id);
            $this->_getSession()->addSuccess($this->__('Segment successfully refreshed'));
            $this->_redirect('*/*/*', array('id' => $id));
            return;
        }

        if ($this->getRequest()->getParam('refresh') == 2) {
            $admin = Mage::getSingleton('admin/session')->getUser()->getId();
            $model->setData('build', 1)->setData('notify_user', $admin)->save();

            $cron = Mage::getModel('cron/schedule')->getCollection()
                    ->addFieldToFilter('job_code', 'fidelitas_build_segments_user')
                    ->addFieldToFilter('status', 'pending');

            if ($cron->getSize() > 0) {
                $this->_getSession()->addError($this->__('Please wait until previous cron ends'));
            } else {
                $cron = Mage::getModel('cron/schedule');
                $data['status'] = 'pending';
                $data['job_code'] = 'fidelitas_build_segments_user';
                $data['scheduled_at'] = now();
                $data['created_at'] = now();
                $cron->setData($data)->save();
                $this->_getSession()->addSuccess($this->__('Segment will be built next time the cron runs'));
            }

            $this->_redirect('*/*/*', array('id' => $id));
            return;
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('fidelitas/adminhtml_segments_records'));
        $this->renderLayout();
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'segments.csv';
        $content = $this->getLayout()->createBlock('fidelitas/adminhtml_segments_records_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction() {
        $fileName = 'segments.xml';
        $content = $this->getLayout()->createBlock('fidelitas/adminhtml_segments_records_grid')
                ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
