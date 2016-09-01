<?php

/**
 * GH Common adminhtml controller
 */
class GH_Common_Adminhtml_RawsqlController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * query grid
     */

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    
    /**
     * prepare model and save it into registry
     *
     * @return GH_Common_Model_Sql
     */

    protected function _getModel() {
        if (!Mage::registry('ghcommon_sql')) {
            $id = $this->getRequest()->getParam('id',null);
            $model = Mage::getModel('ghcommon/sql');
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->_getSession()->addError(Mage::helper("ghcommon")->__("Invaild ghcommon_sql object Id"));
                    return $this->_redirectReferer();

                }
            } else {
                $model->setDefaults();
            }
            Mage::register('ghcommon_sql',$model);
        }
        return Mage::registry('ghcommon_sql');
    }
    
    /**
     * edit and add query
     */

    public function editAction() {
        $model = $this->_getModel();
        if ($values = $this->_getSession()->getData('rawsql_form_data', true)) {
            $model->addData($values);
        }
        $this->loadLayout();
        $this->renderLayout();

    }
    
    /**
     * save query params
     */

    protected function _saveModel() {
        $request = $this->getRequest();
        $data = $request->getPost();
        $model = $this->_getModel();
        $model->addData($data);
        return $model->save();
    }
    
    /**
     * action save query
     */

    public function saveAction() {
        $run = $this->getRequest()->getParam('launch_query');
        $model = $this->_saveModel();        
        if ($run) {
            $this->_forward('launch');
        } else {
            $this->_getSession()->addSuccess(Mage::helper('ghcommon')->__('Query saved.'));
            $this->_redirect('*/*/edit',array('id'=>$model->getId()));
        }

    }
    
    /**
     * delete query
     */

    public function deleteAction() {
        $model = $this->_getModel();
        if ($model->getId()) {
            $success = false;
            try {
                $name = $model->getQueryName();
                $model->delete();
                $success = true;
            } catch (Exception $ex) {
                Mage::logException($ex);
                $this->_getSession()->addError($ex->getMessage());
            }
            if ($success) {
                $this->_getSession()->addSuccess(Mage::helper('ghcommon')->__('Query &quot;%s&quot; was deleted.', $name));
            }

        }

        $this->_redirect('*/*/index');
    }
    
    /**
     * start query
     */

    public function launchAction() {
        $model = $this->_getModel();
        $result = array();
        try {
            $result = $model->launchQuery();
        } catch (Exception $ex) {
            Mage::logException($ex);
            $result[] = array('error' => $ex->getMessage());
        }
        Mage::register('ghcommon_query_result',$result);        
        $this->loadLayout();
        $this->renderLayout();
    }
    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/ghcommon_sql');
    }

    /**
     * download query in csv
     */
    public function downloadAction() {
        $model = $this->_getModel();
        $queryId = $model->getId();
        $queryData = $model->getData();
        $filename = str_replace(' ', '_', $queryData['query_name']);
        $filename = preg_replace('/[*\/,]/', '', $filename);
        $filename = $queryId."_".$filename;
        $result = $model->launchQuery();
        header('Content-Type: text/csv');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment;filename='.$filename.'.csv;');
        $f = fopen('php://output', 'w');
        if($result){
            $arrayToCsv = $this->_prepareCsvData($result);
            foreach($arrayToCsv as $row){
                fputcsv($f, $row, ',');
            }
        }else{
            fputcsv($f, array(Mage::helper('ghcommon')->__('No result')));
        }
        fclose($f);
    }

    /**
     * @param array $dataArray
     * @return array
     */
    protected function _prepareCsvData($dataArray){
        $arrayKeys = array(array_keys($dataArray[0]));
        $arrayToCsv = array_merge($arrayKeys, $dataArray);
        return $arrayToCsv;
    }
}
