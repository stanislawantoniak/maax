<?php

class ZolagoOs_OmniChannelVendorRatings_Adminhtml_RatingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initEnityId();
        $this->loadLayout();

        $this->_setActiveMenu('sales/udropship/review');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Ratings'), Mage::helper('adminhtml')->__('Manage Ratings'));
        $this->_addContent($this->getLayout()->createBlock('udratings/adminhtml_rating_rating'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_initEnityId();
        $this->loadLayout();

        $ratingModel = Mage::getModel('rating/rating');
        if ($this->getRequest()->getParam('id')) {
            $ratingModel->load($this->getRequest()->getParam('id'));
        }

        $this->_title($ratingModel->getId() ? $ratingModel->getRatingCode() : $this->__('New Rating'));

        $this->_setActiveMenu('catalog/ratings');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Ratings'), Mage::helper('adminhtml')->__('Manage Ratings'));

        $this->_addContent($this->getLayout()->createBlock('udratings/adminhtml_rating_edit'))
            ->_addLeft($this->getLayout()->createBlock('udratings/adminhtml_rating_edit_tabs'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $this->_initEnityId();

        if ( $this->getRequest()->getPost() ) {
            try {
                $ratingModel = Mage::getModel('rating/rating');

                $stores = $this->getRequest()->getParam('stores');
                $stores[] = 0;
                if (!($rNew = !$this->getRequest()->getParam('id'))) {
                    $ratingModel->load($this->getRequest()->getParam('id'));
                    if (!$ratingModel->getId()) {
                        Mage::throwException($this->__('Rating with %s id not found', $this->getRequest()->getParam('id')));
                    }
                } else {
                    $ratingModel->setIsAggregate($this->getRequest()->getParam('is_aggregate'));
                }
                $ratingModel->setRatingCode($this->getRequest()->getParam('rating_code'))
                      ->setRatingCodes($this->getRequest()->getParam('rating_codes'))
                      ->setStores($stores)
                      ->setEntityId(Mage::registry('entityId'))
                      ->save();

                $options = $this->getRequest()->getParam('option_title');

                if( is_array($options) ) {
                    $i = $ratingModel->getIsAggregate() && !$rNew ? 1 : 0;
                    $iStart = $ratingModel->getIsAggregate() ? 1 : 0;
                    foreach( $options as $key => $optionCode ) {
                        $optionModel = Mage::getModel('rating/rating_option');
                        $roNew = true;
                        if( !preg_match("/^add_([0-9]*?)$/", $key) ) {
                            $roNew = false;
                            $optionModel->setId($key);
                        }

                        if (!$roNew || $i>=$iStart) {
                            $optionModel->setCode($optionCode)
                                ->setValue($i)
                                ->setRatingId($ratingModel->getId())
                                ->setPosition($i)
                                ->save();
                        }
                        $i++;
                        if (!$ratingModel->getIsAggregate() && $i>1) {
                            break;
                        }
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The rating has been saved.'));
                Mage::getSingleton('adminhtml/session')->setRatingData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setRatingData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('rating/rating');
                /* @var $model Mage_Rating_Model_Rating */
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The rating has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _initEnityId()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Manage Ratings'));

        Mage::register('entityId', Mage::getModel('rating/rating_entity')->getIdByCode('udropship_vendor'));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review/rating');
    }

}
