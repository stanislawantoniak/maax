<?php

/**
 * Class Zolago_Dropship_Model_Vendor
 * @method string getVendorName()
 * @method string getStatementsCalendar()
 * @method string getUrlKey()
 *
 * @method string getStatus()
 *
 * @method string getRegulationAcceptDocumentDate()
 * @method string getRegulationAcceptDocumentData()
 * @method int getRegulationAccepted()
 * @method string getRegulationConfirmRequestSentDate()
 */
class Zolago_Dropship_Model_Vendor extends Unirgy_Dropship_Model_Vendor
{

    const VENDOR_TYPE_BRANDSHOP = 2;
    const VENDOR_TYPE_STANDARD = 1;

    /**
     * Overide fuction to add additional email address bases od vendor operators
     * @return array
     */
    public function getNewOrderCcEmails() {
        $po = $this->getData("po");
        /* @var $po Zolago_Po_Model_Po */
        $new = array();
        if($po && $po->getId()) {
            foreach($po->getAllowedOperators() as $operator) {
                $new[] = $operator->getEmail();
            }
        }
        $old = parent::getNewOrderCcEmails();
        return array_unique(array_merge(is_array($old) ? $old : array(), $new));
    }

    /**
     * @todo add params
     * @param array $params
     * @return string
     */
    public function getVendorUrl($params=array()) {
        return Mage::helper("zolagodropshipmicrosite")->getVendorUrl($this);
    }

    /**
     * @return bool
     */
    public function isBrandshop() {
        return $this->getVendorType()==Zolago_Dropship_Model_Source::VENDOR_TYPE_BRANDSHOP;
    }

    public function isStandard() {
        return $this->getVendorType()==Zolago_Dropship_Model_Source::VENDOR_TYPE_STANDARD;
    }

    /**
     * Sets vendor root category to registry and then return
     *
     * @param int|null $websiteId
     * @return Zolago_Dropship_Model_Vendor | Zolago_Catalog_Model_Category
     * @throws Mage_Core_Exception
     *
     * @deprecated deprecated, used only in very specific situation because sometimes return
     * Zolago_Dropship_Model_Vendor and sometimes Zolago_Catalog_Model_Category what it's wrong
     * use instead this:
     * $helperZDM = Mage::helper("zolagodropshipmicrosite");
     * $helperZDM->getVendorRootCategoryObject();
     * code using this functions use also registry('vendor_current_category')
     * it's deprecated too
     * use instead this:
     * registry('current_category')
     */
    public function rootCategory($websiteId = NULL) {

        if($category = Mage::registry('vendor_current_category')) {
            return $category;
        }

        $websiteId		= ($websiteId) ? $websiteId : Mage::app()->getWebsite()->getId();
        $rootCategoryId = Mage::helper('zolagodropshipmicrosite')
                          ->getVendorRootCategory($this, $websiteId);

        $category = Mage::getModel("catalog/category")->load($rootCategoryId);

        if(!$category->getId()) {
            $category->load(Mage::app()->getStore()->getRootCategoryId());
        }

        Mage::register('vendor_current_category', $category);

        return $category;
    }

    /**
     * @return array
     */
    public function getChildVendorIds() {
        if(!$this->hasData('child_vendor_ids')) {
            $this->setData('child_vendor_ids', $this->getResource()->getChildVendorIds($this));
        }
        return $this->getData('child_vendor_ids');
    }

    /**
     * @return array
     */
    public function getAllowedPos() {
        if(!$this->hasData("allowed_pos")) {
            $allowedPos = array();
            if($this->getId()) {
                $allowedPos = $this->getResource()->getAllowedPos($this);
            }
            $this->setData("allowed_pos", $allowedPos);
        }
        return $this->getData("allowed_pos");
    }

    /**
     * @return Zolago_Rma_Model_Resource_Rma_Reason_Collection
     */
    public function getRmaReasonVendorCollection() {
        //$vendor_id = $this->getVendorId();
        $collection = Mage::getResourceModel('zolagorma/rma_reason_vendor_collection');
        /* @var $collection Zolago_Rma_Model_Resource_Rma_Reason_Collection */
        if($this->getId()) {
            $collection->addFieldToFilter('vendor_id', $this->getId());
        } else {
            $collection->addFieldToFilter('vendor_id', -1);
        }
        return $collection;
    }

    public function getMaxShippingDays($storeId=null)
    {
        $maxShippingDays = $this->getData('max_shipping_days');
        if (is_null($maxShippingDays) || $maxShippingDays=="" || $maxShippingDays < 0) {
            $maxShippingDays = Mage::getStoreConfig('udropship/vendor/max_shipping_days', $storeId);
        }
        return (int)$maxShippingDays;
    }

    public function getMaxShippingTime($storeId=null)
    {
        $maxShippingTime = $this->getData('max_shipping_time');
        if (is_null($maxShippingTime) || $maxShippingTime=="" || $maxShippingTime==0) {
            $maxShippingTime = Mage::getStoreConfig('udropship/vendor/max_shipping_time', $storeId);
        }
        return $maxShippingTime;
    }

    protected function _beforeSave() {
        if($this->getData("max_shipping_days")=="" || $this->getData("max_shipping_days") < 0) {
            $this->setData("max_shipping_days", null);
        }

        if($this->getData("max_shipping_time")=="" || $this->getData("max_shipping_time")==0) {
            $this->setData("max_shipping_time", null);
        }
        else {
            if($this->getData('max_shipping_time') && is_array($this->getData('max_shipping_time'))) {
                $this->setData('max_shipping_time', implode(',', $this->getData('max_shipping_time')));
            }
        }

        return parent::_beforeSave();
    }

    public function getFormatedAddress($type='text')
    {
        switch ($type) {
        case 'text':
            return $this->getStreet(-1)."\n".$this->getCity().', '.$this->getRegionCode().' '.$this->getZip();
        }
        $format = Mage::getSingleton('customer/address_config')->getFormatByCode($type);
        if (!$format) {
            return null;
        }
        $renderer = $format->getRenderer();
        //die(var_dump($renderer));
        if (!$renderer) {
            return null;
        }
        $address = $this->getAddressObj();
        $address->unsVendorAttn();
        $address->unsFirstname();
        $address->unsLastname();
        $address->setCompany($this->getCompanyName());

        return $renderer->render($address);
    }

    public function getVendorLogoUrl() {
        return Mage::getBaseUrl(Mage_core_model_store::URL_TYPE_MEDIA) . $this->getData('logo');
    }
    public function getRmaAddress() {
        $data = $this->getData();
        $address = array (
                       'name' 		=> (empty($data['company_name']))? $data['vendor_name']:$data['company_name'],
                       'city' 		=> $data['city'],
                       'postcode' 	=> $data['zip'],
                       'street' 	=> $data['street'],
                       'personName' => $data['vendor_attn'],
                       'phone' 	=> $data['rma_executive_telephone_mobile'],
                       'email' 	=> $data['rma_executive_email'],
                       'country'	=> $data['country_id'],
                   );
        return $address;
    }
    public function sendOrderNotificationEmail($shipment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();

        $hlp = Mage::helper('udropship');
        $data = array();

        $adminTheme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));
        if ($store->getConfig('udropship/vendor/attach_packingslip') && $this->getAttachPackingslip()) {
            Mage::getDesign()->setArea('adminhtml')
            ->setPackageName(!empty($adminTheme[0]) ? $adminTheme[0] : 'default')
            ->setTheme(!empty($adminTheme[1]) ? $adminTheme[1] : 'default');

            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($shipment->getShippingAmount());

            $pdf = Mage::helper('udropship')->getVendorShipmentsPdf(array($shipment));

            $order->setShippingAmount($orderShippingAmount);

            $data['_ATTACHMENTS'][] = array(
                                          'content'=>$pdf->render(),
                                          'filename'=>'packingslip-'.$order->getIncrementId().'-'.$this->getId().'.pdf',
                                          'type'=>'application/x-pdf',
                                      );
        }

        if ($store->getConfig('udropship/vendor/attach_shippinglabel') && $this->getAttachShippinglabel() && $this->getLabelType()) {
            try {
                if (!$shipment->getResendNotificationFlag()) {
                    $hlp->unassignVendorSkus($shipment);
                    $batch = Mage::getModel('udropship/label_batch')->setVendor($this)->processShipments(array($shipment));
                    if ($batch->getErrors()) {
                        if (Mage::app()->getRequest()->getRouteName()=='udropship') {
                            Mage::throwException($batch->getErrorMessages());
                        } else {
                            Mage::helper('udropship/error')->sendLabelRequestFailedNotification($shipment, $batch->getErrorMessages());
                        }
                    } else {
                        $labelModel = $hlp->getLabelTypeInstance($batch->getLabelType());
                        foreach ($shipment->getAllTracks() as $track) {
                            $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent($track);
                        }
                    }
                } else {
                    $batchIds = array();
                    foreach ($shipment->getAllTracks() as $track) {
                        $batchIds[$track->getBatchId()][] = $track;
                    }
                    foreach ($batchIds as $batchId => $tracks) {
                        $batch = Mage::getModel('udropship/label_batch')->load($batchId);
                        if (!$batch->getId()) continue;
                        if (count($tracks)>1) {
                            $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                            $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
                        } else {
                            reset($tracks);
                            $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
                            $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent(current($tracks));
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore if failed
            }
        }

        $hlp->setDesignStore($store);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }
        $hlp->assignVendorSkus($shipment);
        $data += array(
                     'shipment'        => $shipment,
                     'order'           => $order,
                     'vendor'          => $this,
                     'store_name'      => $store->getName(),
                     'vendor_name'     => $this->getVendorName(),
                     'order_id'        => $order->getIncrementId(),
                     'customer_info'   => Mage::helper('udropship')->formatCustomerAddress($shippingAddress, 'html', $this),
                     'shipping_method' => $shipment->getUdropshipMethodDescription() ? $shipment->getUdropshipMethodDescription() : $this->getShippingMethodName($order->getShippingMethod(), true),
                     'shipment_url'    => Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$order->getIncrementId().'&filter_order_id_to='.$order->getIncrementId())),
                     'packingslip_url' => Mage::getUrl('udropship/vendor/pdf', array('shipment_id'=>$shipment->getId())),
                     'use_attachments' => true
                 );

        $template = $this->getEmailTemplate();
        if (!$template) {
            $template = $store->getConfig('udropship/vendor/vendor_email_template');
        }
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        $data['_BCC'] = $this->getNewOrderCcEmails();

        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $this->getData($emailField) ? $this->getData($emailField) : $this->getEmail();
        } else {
            $email = $this->getEmail();
        }

        /* @var $helper Zolago_Common_Helper_Data */
        $helper = Mage::helper("zolagocommon");
        $helper->sendEmailTemplate(
            $email,
            $this->getVendorName(),
            $template,
            $data,
            true,
            $identity
        );

        $hlp->unassignVendorSkus($shipment);

        $hlp->setDesignStore();
    }
    
    /**
     * list of vendors which can add product 
     *
     * @param 
     * @return 
     */

    public function getCanAddProduct() {
        $model = Mage::getModel('zolagodropship/vendor_brandshop');
        $collection = $model->getCollection();
        $collection->setCanAddFilter($this->getId());
        return $collection;    
    }
}
