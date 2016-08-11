<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Adminhtml_RegistrationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $hlp = Mage::helper('umicrosite');

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb($hlp->__('Vendor Registrations'), $hlp->__('Vendor Registrations'));
        $this->_addContent($this->getLayout()->createBlock('umicrosite/adminhtml_registration'));

        $this->renderLayout();
    }
	
	/**
	 * Acl check for this controller
	 *
	 * @return bool
	 */
	protected function _isAllowed() {
		return
			Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor_registration') ||
			Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_registration');
	}

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('umicrosite/adminhtml_registration_grid')->toHtml()
        );
    }
    /**
     * Export subscribers grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'registrations.csv';
        $content    = $this->getLayout()->createBlock('umicrosite/adminhtml_registration_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export subscribers grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'registrations.xml';
        $content    = $this->getLayout()->createBlock('umicrosite/adminhtml_registration_grid')
            ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $certIds = $this->getRequest()->getParam('vendor');
        if (!is_array($certIds)) {
            $this->_getSession()->addError($this->__('Please select registration(s)'));
        }
        else {
            try {
                $cert = Mage::getSingleton('umicrosite/registration');
                foreach ($certIds as $certId) {
                    $cert->setId($certId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($certIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('reg_id');
        $reg = Mage::getModel('umicrosite/registration')->load($id);
        if (!$reg) {
            return;
        }
        Mage::register('vendor_data', $reg->toVendor());

        $this->_forward('edit', 'adminhtml_vendor', 'zolagoosadmin');
        return;
        $this->loadLayout();

        $this->_setActiveMenu('sales/udropship');
        $this->_addBreadcrumb(Mage::helper('udropship')->__('Vendor Registrations'), Mage::helper('udropship')->__('Vendor Registrations'));

        $this->_addContent($this->getLayout()->createBlock('udropship/adminhtml_vendor_edit'))
            ->_addLeft($this->getLayout()->createBlock('udropship/adminhtml_vendor_edit_tabs'));

        $this->renderLayout();
    }
}
