<?php

class ZolagoOs_OmniChannelVendorRatings_Adminhtml_ReviewController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = array('edit');

    public function preDispatch()
    {
        parent::preDispatch();
        Mage::helper('udratings')->useMyEt();
    }

    public function indexAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('All Vendor Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/udropship/review');

        $this->_addContent($this->getLayout()->createBlock('udratings/adminhtml_review_main'));

        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Pending Vendor Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            Mage::register('usePendingFilter', true);
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/review');

        Mage::register('usePendingFilter', true);
        $this->_addContent($this->getLayout()->createBlock('udratings/adminhtml_review_main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Edit Vendor Review'));

        $this->loadLayout();
        $this->_setActiveMenu('sales/udropship/review');

        $this->_addContent($this->getLayout()->createBlock('udratings/adminhtml_review_edit'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Customer Reviews'));

        $this->_title($this->__('New Review'));

        $this->loadLayout();
        $this->_setActiveMenu('sales/udropship/review');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_add'));
        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_product_grid'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = Mage::getModel('review/review')->load($reviewId);

            if (! $review->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    $votes = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId=>$optionId) {
                        if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            Mage::getModel('rating/rating')
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
                } catch (Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }

            return $this->getResponse()->setRedirect($this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/'));
        }
        $this->_redirectReferer();
    }

    public function deleteAction()
    {
        $reviewId = $this->getRequest()->getParam('id', false);

        try {
            Mage::getModel('review/review')->setId($reviewId)
                ->aggregate()
                ->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The review has been deleted'));
            if( $this->getRequest()->getParam('ret') == 'pending' ) {
                $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            }
            return;
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $reviewsIds = $this->getRequest()->getParam('udratings');
        if(!is_array($reviewsIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select review(s).'));
        } else {
            try {
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('review/review')->load($reviewId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($reviewsIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateStatusAction()
    {
        $reviewsIds = $this->getRequest()->getParam('udratings');
        if(!is_array($reviewsIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select review(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('review/review')->load($reviewId);
                    $model->setStatusId($status)
                        ->save()
                        ->aggregate();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($reviewsIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massVisibleInAction()
    {
        $reviewsIds = $this->getRequest()->getParam('udratings');
        if(!is_array($reviewsIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select review(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $stores = $this->getRequest()->getParam('stores');
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('review/review')->load($reviewId);
                    $model->setSelectStores($stores);
                    $model->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($reviewsIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('*/*/pending');
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('udratings/adminhtml_review_product_grid')->toHtml());
    }

    public function reviewGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('udratings/adminhtml_review_grid')->toHtml());
    }

    public function jsonProductInfoAction()
    {
        $response = new Varien_Object();
        $id = $this->getRequest()->getParam('id');
        if( intval($id) > 0 ) {
            $product = Mage::getModel('catalog/product')
                ->load($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(Mage::helper('catalog')->__('Unable to get the product ID.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function postAction()
    {
        $vendorId = $this->getRequest()->getParam('vendor_id', false);
        $shipmentId = $this->getRequest()->getParam('shipment_id', false);
        if ($data = $this->getRequest()->getPost()) {
            if(isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = Mage::getModel('review/review')->setData($data);

            try {
                $review->setEntityId(Mage::helper('udratings')->myEt()) // product
                    ->setEntityPkValue($vendorId)
                    ->setRelEntityPkValue($shipmentId)
                    ->setStoreId(Mage::app()->getDefaultStoreView()->getId())
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                    Mage::getModel('rating/rating')
                       ->setRatingId($ratingId)
                       ->setReviewId($review->getId())
                       ->addOptionVote($optionId, $vendorId);
                }

                $review->aggregate();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The review has been saved.'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                }

                return;
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }

    public function customerReviewsAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udratings/adminhtml_review_grid', 'admin.customer.reviews')
                ->setCustomerId($this->getRequest()->getParam('id'))
                ->setUisMassactionAvailable(false)
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    public function vendorReviewsAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udratings/adminhtml_review_grid', 'admin.vendor.reviews')
                ->setVendorId($this->getRequest()->getParam('id'))
                ->setUisMassactionAvailable(false)
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    public function ratingItemsAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('udratings/adminhtml_review_rating_detailed')->setIndependentMode()->toHtml());
    }

    public function ratingItemsNaAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('udratings/adminhtml_review_rating_detailedNa')->setIndependentMode()->toHtml());
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review/review_pending');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review/review_all');
                break;
        }
    }
}
