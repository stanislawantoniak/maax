<?php

class GH_Statements_Adminhtml_Vendor_StatementsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $statement = $this->_initModel($id);

        if(!$statement->getId()){
            $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Statement does not exists"));
            return $this->_redirect("*/*");
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return GH_Statements_Model_Statement
     */
    protected function _initModel($modelId) {
        if(Mage::registry('ghstatements_current_statement') instanceof GH_Statements_Model_Statement){
            return Mage::registry('ghstatements_current_statement');
        }

        $model = Mage::getModel("ghstatements/statement");
        /* @var $model GH_Statements_Model_Statement */
        if($modelId){
            $model->load($modelId);
        }

        Mage::register('ghstatements_current_statement', $model);
        return $model;
    }

    public function deleteAction()
    {
        $model = Mage::getModel("ghstatements/statement");
        $id = $this->getRequest()->getParam("id");

        try {
            $model->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('ghstatements')->__("Statement not found"));
            } elseif($model->getVendorInvoiceId()) {
	            throw new Mage_Core_Exception(Mage::helper('ghstatements')->__("Cannot delete statement because invoice for this statement has been already generated."));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('ghstatements')->__("Statement deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirect("*/*/edit",array('id' => $id));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('ghstatements')->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirect("*/*/edit",array('id' => $id));
        }

        return $this->_redirect("*/*/index");
    }


    /*
     * orderGrid ajax block response
     */
    public function orderGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_order',
                    'ghstatements.statement.order'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    /**
     * refundGrid ajax block response
     */
    public function refundGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_refunds',
                    'ghstatements.statement.refunds'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    /**
     * trackGrid ajax block response
     */
    public function trackGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_track',
                    'ghstatements.statement.track'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    /**
     * marketingGrid ajax block response
     */
    public function marketingGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_marketing',
                    'ghstatements.statement.marketing'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    /**
     * paymentGrid ajax block response
     */
    public function paymentGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_payment',
                    'ghstatements.statement.payment'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    /**
     * Manually trigger gh_statements cron with custom date (today)
     * @return Mage_Adminhtml_Controller_Action
     */
    public function generate_todayAction() {
        $forceCustomDate = $this->getRequest()->getParam('date');
        try {
            /** @var GH_Statements_Model_Observer $model */
            $model = Mage::getModel('ghstatements/observer');
            $model->processStatements(null,$forceCustomDate);
            return $this->_redirect("*/*/index");

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper("ghstatements")->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
    }

	public function massInvoiceAction() {
		if($this->getRequest()->isPost() && $this->_validateFormKey()) {
			$statementsIds = $this->getRequest()->getParam('vendor_statements');
			if(is_array($statementsIds) && count($statementsIds)) {
				$invoiceModels = array();
				$statementModels = array();
				$updatedStatements = 0;
				$skippedStatements = 0;
				$month = false;
				try {
					foreach ($statementsIds as $statementId) {
						/** @var Gh_Statements_Model_Statement $statementModel */
						$statementModel = Mage::getModel('ghstatements/statement');
						$statementModel->load($statementId);
						if ($statementModel->getId()) {
							if(!$statementModel->getVendorInvoiceId()) {
								$statementModels[] = $statementModel;
							} else {
								$skippedStatements++;
								continue;
							}

							$thisMonth = date('m',strtotime($statementModel->getEventDate()));
							if(!$month) {
								$month = $thisMonth;
							} elseif($month != $thisMonth) {
								Mage::throwException(Mage::helper("ghstatements")->__("All selected statements have to be from the same month!"));
							}

							$vid = $statementModel->getVendorId();
							if(!isset($invoiceModels[$vid])) {
								$invoiceModels[$vid] = Mage::getModel('zolagopayment/vendor_invoice')
									->setData('vendor_id',$vid);
							}

							//costs adding start
							$commissionBrutto = floatval($invoiceModels[$vid]->getData('commission_brutto'));
							$transportBrutto = floatval($invoiceModels[$vid]->getData('transport_brutto'));
							$marketingBrutto = floatval($invoiceModels[$vid]->getData('marketing_brutto'));
							//$otherBrutto = floatval($invoiceModels[$vid]->getData('other_brutto')); //will be used somewhere in the future ;)

							$invoiceModels[$vid]
								->setData(
									'commission_brutto',
									$commissionBrutto + floatval($statementModel->getData('total_commission'))
								)
								->setData(
									'transport_brutto',
									$transportBrutto + floatval($statementModel->getData('tracking_charge_total')) + floatval($statementModel->getData('delivery_correction'))
								)
								->setData(
									'marketing_brutto',
									$marketingBrutto + floatval($statementModel->getData('marketing_value')) + floatval($statementModel->getData('marketing_correction'))
								)

								/*->setData(
									'other_brutto',
									$otherBrutto + //whatever
								)*/;
							//costs adding end

							//dates calculation start
							$time = strtotime($statementModel->getEventDate());
							$dateToInsert = date("Y-m-t", $time);
							if(!$invoiceModels[$vid]->getData('date') || strtotime($invoiceModels[$vid]->getData('date')) < strtotime($dateToInsert)) {
								$invoiceModels[$vid]->setData('date',$dateToInsert)->setData('sale_date',$dateToInsert);
							}
							//dates calculation end

						} else {
							$this->_getSession()->addError(Mage::helper("ghstatements")->__("Some error occurred!"));
							return $this->_redirectReferer();
						}
					}
					$invoiceIds = array();
					if (count($invoiceModels)) {
						foreach ($invoiceModels as $invoice) {
						    if ($invoice->checkNotEmpty()) {
    							$invoice->save();
	    						$invoiceIds[$invoice->getData('vendor_id')] = $invoice->getId();
                            }
						}
						foreach($statementModels as $statement) {
							if(isset($invoiceIds[$statement->getVendorId()])) {							    
								$statement->setVendorInvoiceId($invoiceIds[$statement->getVendorId()]);
								$statement->save();
								$updatedStatements++;
							} else {
								$skippedStatements++;
							}
						}
						if($updatedStatements) {
							$this->_getSession()->addSuccess(Mage::helper("ghstatements")->__("Invoices have been generated for %s statements",$updatedStatements));
						}
						if($skippedStatements) {
							$this->_getSession()->addNotice(Mage::helper("ghstatements")->__("%s statements have been skipped during invoice generation",$updatedStatements));
						}
					}
				} catch (Mage_Core_Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				} catch (Exception $e) {
					$this->_getSession()->addError(Mage::helper("ghstatements")->__("Some error occurred!"));
					Mage::logException($e);
				}
			}
		}
		return $this->_redirectReferer();
	}

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/ghstatements_vendor');
    }
}
