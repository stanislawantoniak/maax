<?php

class ZolagoOs_OmniChannelVendorRatings_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if ($this->getRequest()->getActionName() != 'vote'
            && !Mage::getSingleton('customer/session')->authenticate($this)
        ) {
            if ($this->getRequest()->getActionName() == 'post' && $this->getRequest()->isPost()) {
                $this->_saveFormData();
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    protected function _saveFormData($data=null, $id=null)
    {
        Mage::helper('udratings')->saveFormData($data, $id);
    }

    protected function _fetchFormData($id=null)
    {
        return Mage::helper('udratings')->fetchFormData($id);
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('udratings/session');
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('udratings/customer');
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Vendor Pending Reviews'));
        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('udratings/session');
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('review/customer/pending');
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Vendor Reviews'));
        $this->renderLayout();
    }

    protected function _validatePost()
    {
        $id = $this->getRequest()->getParam('id');
        $relId = $this->getRequest()->getParam('rel_id');
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $shipment = Mage::getModel('sales/order_shipment')->load($relId);
        return !empty($id) && !empty($relId) && $shipment->getId()
               && $shipment->getUdropshipVendor()===$id && $shipment->getCustomerId()==$customerId;
    }

    public function postAction()
    {
        if (!$this->_validatePost()) {
            Mage::getSingleton('udratings/session')->addError($this->__('Review not allowed.'));
            $this->_redirect('*/*/pending');
            return $this;
        }
        if ($data = $this->_fetchFormData()) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (!empty($data)) {
            $session    = Mage::getSingleton('udratings/session');
            $review     = Mage::getModel('review/review')->setData($data);
            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId(Mage::helper('udratings')->myEt())
                        ->setEntityPkValue($this->getRequest()->getParam('id'))
                        ->setRelEntityPkValue($this->getRequest()->getParam('rel_id'))
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $this->getRequest()->getParam('id'));
                    }

                    $review->aggregate();
                    $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $this->_saveFormData($data);
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
            else {
                $this->_saveFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
        }

        $this->_redirect(
            count(Mage::helper('udratings')->getPendingCustomerReviewsCollection())>0
            ? '*/*/pending' : '*/*/index'
        );
    }
    public function voteAction() {
        $session = Mage::getSingleton('core/session');
        $customerSession = Mage::getSingleton('customer/session');

        try {
            $id = $this->getRequest()->getParam('id');
            $value = $this->getRequest()->getParam('value');

            $customerId = (int)Mage::getSingleton('customer/session')->getCustomerId();

            $votedUdreviews = $customerSession->getVotedUdreviews();

            if (!$votedUdreviews) {
                $votedUdreviews = Mage::getModel('core/cookie')->get('udratings_votes_' . $customerId);
            }

            $rHlp = Mage::getResourceSingleton('udropship/helper');

            if ($votedUdreviews && in_array($id, explode(',', $votedUdreviews))
            ) {
                return $this->returnResult(array(
                    'error' => true,
                    'message' => $this->__('You have already voted on this review!')
                ));
            } else {
                $reviewModel = Mage::getModel('review/review');
                $reviewData = $rHlp->loadDbColumns($reviewModel, $id, array('helpfulness_yes','helpfulness_no'));
                reset($reviewData);
                $reviewData = current($reviewData);
                if (!empty($reviewData)) {
                    if ($value) {
                        $reviewData['helpfulness_yes']++;
                    } else {
                        $reviewData['helpfulness_no']++;
                    }
                    $reviewData['helpfulness_pcnt'] = $reviewData['helpfulness_yes']/($reviewData['helpfulness_yes']+$reviewData['helpfulness_no'])*100;
                    $rHlp->updateModelData($reviewModel,$reviewData,$id);
                    $votedUdreviews = $votedUdreviews . ($votedUdreviews ? ',' : '') . $id;
                    $customerSession->setVotedUdreviews($votedUdreviews);
                    Mage::getModel('core/cookie')->set('udratings_votes_' . $customerId, $votedUdreviews, true);
                    $this->returnResult(array(
                        'message' => $this->__('Your voice has been accepted. Thank you!')
                    ));
                } else {
                    $this->returnResult(array(
                        'error' => true,
                        'message' => $this->__('Review was not found!')
                    ));
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->returnResult(array(
                'error' => true,
                'message' => $this->__('Unable to vote. Please, try again later.')
            ));
        }
    }
    public function returnResult($result)
    {
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
