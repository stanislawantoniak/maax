<?php

class Zolago_Dropship_Helper_Data extends ZolagoOs_OmniChannel_Helper_Data
{
    const TRACK_SINGLE = 1;
    protected $trackingHelperPath = 'orbashipping/carrier_tracking';


    /**
     * @return array
     */
    public function getAllowedStores($vendor)
    {
        $allowed = array();
        $limitedWebsites = $vendor->getWebsitesAllowed();

        if (!is_array($limitedWebsites)) {
            $realWebsites = array();
        }
        elseif (!count($limitedWebsites)) {
            $realWebsites = array();
        }
        elseif (count($limitedWebsites) == 1 && $limitedWebsites[0] == "") {
            $realWebsites = array();
        }
        else {
            foreach ($limitedWebsites as $websiteId) {
                if ($websiteId) {
                    $realWebsites[] = $websiteId;
                }
            }
        }

        foreach (Mage::app()->getWebsites() as $website) {
            $websiteDefaultStoreId = Mage::app()
                                     ->getWebsite($website->getWebsiteId())
                                     ->getDefaultGroup()
                                     ->getDefaultStoreId();

            $websiteDefaultStore = Mage::getModel("core/store")->load($websiteDefaultStoreId);
            if ($realWebsites && in_array($website->getWebsiteId(), $realWebsites)) {
                $allowed[] = array("id" => $websiteDefaultStore->getId(), "name" => $website->getName());
            }
            elseif (!$realWebsites) {
                $allowed[] = array("id" => $websiteDefaultStore->getId(), "name" => $website->getName());
            }
        }
        return $allowed;
    }


    /**
     * @param string
     */
    public function setTrackingHelperPath($path)
    {
        $this->trackingHelperPath = $path;

    }

    /**
     * @param ZolagoOs_Rma_Model_Rma_Track | string $tracking
     */
    public function getTrackingStatusName($tracking)
    {
        if ($tracking instanceof ZolagoOs_Rma_Model_Rma_Track) {
            $tracking = $tracking->getUdropshipStatus();
        }
        switch ($tracking) {
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_CANCELED:
            return $this->__("Canceled");
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED:
            return $this->__("Delivered");
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING:
            return $this->__("Pending");
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_READY:
            return $this->__("Ready");
            break;
        case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED:
            return $this->__("Shipped");
            break;
        case Zolago_Dropship_Model_Source::TRACK_STATUS_UNDELIVERED:
            return $this->__("Undelivered");
            break;
        }
        return $tracking;
    }

    /**
     * @param Mage_Core_Model_Store|int|null $store
     * @return Mage_Catalog_Model_Entity_Attribute
     */
    public function getVendorSkuAttribute($store = null)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = $store->getId();
        }
        $attrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute', $store);
        if (!empty($attrCode)) {
            $attr = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
            if ($attr->getId()) {
                return $attr;
            }
        }
        return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, "sku");
    }

    public function getAllowedCarriers()
    {
        $allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
        return $allowCarriers;
    }

    //{{{
    /**
     *
     * @param Zolago_Pos_Model_Pos $pos
     * @return
     */
    public function getAllowedCarriersForPos($pos)
    {
        $out = array();
        if ($pos->getUseDhl()) {
            $out[Orba_Shipping_Model_Carrier_Dhl::CODE] = Orba_Shipping_Model_Carrier_Dhl::CODE;
        }
        if ($pos->getUseOrbaups()) {
            $out[Orba_Shipping_Model_Carrier_Ups::CODE] = Orba_Shipping_Model_Carrier_Ups::CODE;
        }
        return $out;
    }
    //}}}
    //{{{
    /**
     *
     * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
     * @param bool $rmaMode
     * @return array
     */
    public function getAllowedCarriersForVendor($vendor, $rmaMode = false)
    {
        $out = array();
        if ($vendor->getUseDhl()) {
            $out[Orba_Shipping_Model_Carrier_Dhl::CODE] = Orba_Shipping_Model_Carrier_Dhl::CODE;
        }
        if ($vendor->getDhlRma() && $rmaMode) {
            $out[Orba_Shipping_Model_Carrier_Dhl::CODE] = Orba_Shipping_Model_Carrier_Dhl::CODE;
        }
        if ($vendor->getUseOrbaups()) {
            $out[Orba_Shipping_Model_Carrier_Ups::CODE] = Orba_Shipping_Model_Carrier_Ups::CODE;
        }
        if ($vendor->getOrbaupsRma() && $rmaMode) {
            $out[Orba_Shipping_Model_Carrier_Ups::CODE] = Orba_Shipping_Model_Carrier_Ups::CODE;
        }
        return array_unique($out);
    }

    //}}}
    public function isUdpoMpsAvailable($carrierCode, $vendor = null)
    {
        $allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
        if (in_array($carrierCode, $allowCarriers)) {
            return true;
        }
        return parent::isUdpoMpsAvailable($carrierCode, $vendor);
    }

    /**
     * Poll carriers tracking API
     *
     * @param mixed $tracks
     */
    public function collectTracking($tracks)
    {
        $time = Mage::getSingleton('core/date')->timestamp();
        $requests = array();
        foreach ($tracks as $track) {
            $cCode = $track->getCarrierCode();
            if (!$cCode) {
                continue;
            }
            $vId = $track->getShipment()->getUdropshipVendor();
            $v = Mage::helper('udropship')->getVendor($vId);
            $allowCarriers = Mage::helper('orbashipping/carrier_tracking')->getTrackingCarriersList();
            if (!in_array($cCode, $allowCarriers)) {
                if (!$v->getTrackApi($cCode) || !$v->getId()) {
                    continue;
                }
            }

            if (!$vId) {
                continue;
            }

            $requests[$cCode][$vId][$track->getNumber()][] = $track;
        }
        foreach ($requests as $cCode => $vendors) {
            foreach ($vendors as $vId => $trackIds) {
                $_track = null;
                foreach ($trackIds as $_trackId => $_tracks) {
                    foreach ($_tracks as $_track) break 2;
                }
                try {
                    if ($_track) Mage::helper('udropship/label')->beforeShipmentLabel($v, $_track);
                    $helper = Mage::helper($this->trackingHelperPath);
                    $trackingManager = Mage::helper('orbashipping')->getShippingHelper($cCode);
                    if ($trackingManager) {
                        $helper->setHelper($trackingManager);
                        $result = $helper->collectTracking($trackIds);
                    } else {
                        $result = $v->getTrackApi($cCode)->collectTracking($v, array_keys($trackIds));
                    }
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                } catch (Exception $e) {
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                    $this->_processPollTrackingFailed($trackIds, $e);
                    continue;
                }

                if (!is_array($result)) continue;

                $processTracks = array();
                foreach ($result as $trackId => $status) {
                    foreach ($trackIds[$trackId] as $track) {

                        if (in_array($status, array(ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING, ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_READY, ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_SHIPPED))) {
                            $repeatIn = Mage::getStoreConfig('udropship/customer/repeat_poll_tracking', $track->getShipment()->getOrder()->getStoreId());
                            if ($repeatIn <= 0) {
                                $repeatIn = 12;
                            }
                            $repeatIn = $repeatIn * 60 * 60;
                            $track->setNextCheck(date('Y-m-d H:i:s', $time + $repeatIn))->save();
                            if ($status == ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_PENDING) continue;
                        }
                        $track->setUdropshipStatus($status);

                        if ($track->dataHasChangedFor('udropship_status')) {
                            switch ($status) {
                            case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_READY:
                                Mage::helper('udropship')->addShipmentComment(
                                    $track->getShipment(),
                                    $this->__('Tracking ID %s was picked up from %s', $trackId, $v->getVendorName())
                                );
                                $track->getShipment()->save();
                                break;

                            case ZolagoOs_OmniChannel_Model_Source::TRACK_STATUS_DELIVERED:
                                Mage::helper('udropship')->addShipmentComment(
                                    $track->getShipment(),
                                    $this->__('Tracking ID %s was delivered to customer', $trackId)
                                );
                                $track->setShippedDate();
                                $track->save();
                                $track->getShipment()->save();
                                break;
                            }
                            if (empty($processTracks[$track->getParentId()])) {
                                $processTracks[$track->getParentId()] = array();
                            }
                            $processTracks[$track->getParentId()][] = $track;
                        }
                    }
                }

                foreach ($processTracks as $_pTracks) {
                    try {
                        $this->processTrackStatus($_pTracks, true);
                    } catch
                        (Exception $e) {
                        $this->_processPollTrackingFailed($_pTracks, $e);
                        continue;
                    }
                }
            }
        }
    }


    public function getProductStatusForVendor(ZolagoOs_OmniChannel_Model_Vendor $vendor)
    {
        $status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;

        if ($vendor->getReviewStatus()) {
            $status = (int)$vendor->getReviewStatus();
        }

        return $status;
    }

    /**
     * @param Zolago_Dropship_Model_Vendor $vendor
     * @param int $width
     * @param int $height
     * @return null|string
     */
    public function getVendorLogoResizedUrl($vendor, $width, $height)
    {
        if (!$vendor->getLogo()) {
            return null;
        }
        return Mage::helper('udropship')->getResizedVendorLogoUrl($vendor, $width, $height);
    }


    public function sendPasswordResetEmail($email)
    {
        $vendor = Mage::getModel('udropship/vendor')->load($email, 'email');
        if (!$vendor->getId()) {
            return $this;
        }
        $vendor->setRandomHash(sha1(rand()))->save();

        $store = Mage::app()->getStore();
        $this->setDesignStore($store);

        /** @var Zolago_Common_Helper_Data $mailer */
        $mailer = Mage::helper('zolagocommon');
        $mailer->sendEmailTemplate(
            $email,
            $email,
            $store->getConfig('udropship/vendor/vendor_password_template'),
            array(
                'store_name' => $store->getName(),
                'vendor_name' => $vendor->getVendorName(),
                'use_attachments' => true,
                'url' => Mage::getUrl('udropship/vendor/password', array(
                                          'confirm' => $vendor->getRandomHash(),
                                      )
                                     )
            ),
            $store->getId(),
            $store->getConfig('udropship/vendor/vendor_email_identity')
        );

        $this->setDesignStore();

        return $this;
    }

    public function sendShipmentCommentNotificationEmail($shipment, $comment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();

        $vendor = $this->getVendor($shipment->getUdropshipVendor());

        $hlp = Mage::helper('udropship');
        $data = array();

        $hlp->setDesignStore($store);

        $data += array(
                     'shipment'        => $shipment,
                     'order'           => $order,
                     'vendor'          => $vendor,
                     'comment'         => $comment,
                     'store_name'      => $store->getName(),
                     'vendor_name'     => $vendor->getVendorName(),
                     'shipment_id'     => $shipment->getIncrementId(),
                     'shipment_status' => $this->getShipmentStatusName($shipment),
                     'order_id'        => $order->getIncrementId(),
                     'shipment_url'    => Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$order->getIncrementId().'&filter_order_id_to='.$order->getIncrementId())),
                     'packingslip_url' => Mage::getUrl('udropship/vendor/pdf', array('shipment_id'=>$shipment->getId())),
                     'use_attachments' => true
                 );

        if ($this->isUdpoActive() && ($po = Mage::helper('udpo')->getShipmentPo($shipment))) {
            $data['po']     = $po;
            $data['po_id']  = $po->getIncrementId();
            $data['po_url'] = Mage::getUrl('udpo/vendor/', array('_query'=>'filter_po_id_from='.$po->getIncrementId().'&filter_po_id_to='.$po->getIncrementId()));
        }

        $template = $store->getConfig('udropship/vendor/shipment_comment_vendor_email_template');
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        $data['_BCC'] = $vendor->getNewOrderCcEmails();
        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }

        /** @var Zolago_Common_Helper_Data $mailer */
        $mailer = Mage::helper('zolagocommon');
        $mailer->sendEmailTemplate(
            $email,
            $vendor->getVendorName(),
            $template,
            $data,
            $store->getId(),
            $identity
        );

        $hlp->setDesignStore();
    }

    /**
     * Send vendor comment to store owner
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $comment
     */
    public function sendVendorComment($shipment, $comment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();
        $to = $store->getConfig('udropship/admin/vendor_comments_receiver');
        $subject = $store->getConfig('udropship/admin/vendor_comments_subject');
        $template = $store->getConfig('udropship/admin/vendor_comments_template');
        $vendor = $this->getVendor($shipment->getUdropshipVendor());
        $ahlp = Mage::getModel('adminhtml/url');

        if ($subject && $template && $vendor->getId()) {
            $toEmail = $store->getConfig('trans_email/ident_'.$to.'/email');
            $toName = $store->getConfig('trans_email/ident_'.$to.'/name');
            $data = array(
                'vendor_name'   => $vendor->getVendorName(),
                'order_id'      => $order->getIncrementId(),
                'shipment_id'   => $shipment->getIncrementId(),
                'vendor_url'    => $ahlp->getUrl('udropship/adminhtml_vendor/edit', array(
                        'id'        => $vendor->getId()
                                                 )),
                'order_url'     => $ahlp->getUrl('adminhtml/sales_order/view', array(
                        'order_id'  => $order->getId()
                                                 )),
                'shipment_url'  => $ahlp->getUrl('adminhtml/sales_order_shipment/view', array(
                        'shipment_id'=> $shipment->getId(),
                        'order_id'  => $order->getId(),
                                                 )),
                'comment'      => $comment,
                'use_attachments'=>true
            );
            if ($this->isUdpoActive() && ($po = Mage::helper('udpo')->getShipmentPo($shipment))) {
                $data['po_id'] = $po->getIncrementId();
                $data['po_url'] = $ahlp->getUrl('udpoadmin/order_po/view', array(
                                                    'udpo_id'  => $po->getId(),
                                                    'order_id' => $order->getId(),
                                                ));
            }

            if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
                $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
            } else {
                $email = $vendor->getEmail();
            }

            /** @var Zolago_Common_Helper_Data $mailer */
            $mailer = Mage::helper('zolagocommon');
            $mailer->sendEmailTemplate(
                $toEmail,
                $toName,
                $template,
                $data,
                $store->getId(),
                $email
            );
        }

        Mage::helper('udropship')->addShipmentComment(
            $shipment,
            $this->__($vendor->getVendorName().': '.$comment)
        );
        $shipment->getCommentsCollection()->save();

        return $this;
    }


    /**
     * Check if vendor is LOCAL VENDOR
     * (LOCAL VENDOR = System > Config > Drop Shipping > Vendor Options > Local Vendor)
     * @param null $vendorId
     * @return bool
     */
    public function isLocalVendor($vendorId = NULL) {
        if(is_null($vendorId)) {
            $vendorId = Mage::getSingleton('udropship/session')->getVendorId();
        }
        return (bool)($vendorId == $this->getLocalVendorId());
    }


    /**
     * Get VENDOR ROOT CATEGORY for current website
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function getCurrentVendorRootCategory()
    {
        /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
        $vendor = Mage::helper("umicrosite")->getCurrentVendor();
        $vendorRootCategoryId = false; //VENDOR ROOT CATEGORY for current website

        if (!$vendor) {
            return $vendorRootCategoryId;
        }
        $website = Mage::app()->getWebsite()->getId(); //Current website
        $vendorRootCategory = $vendor->getRootCategory(); //array of root categories (key is website_id)
        $vendorRootCategoryId = isset($vendorRootCategory[$website]) ? $vendorRootCategory[$website] : false;


        return $vendorRootCategoryId;
    }

    public function addAdminhtmlVersion($module='ZolagoOs_OmniChannel')
    {
        $layout = Mage::app()->getLayout();
        $version = (string)Mage::getConfig()->getNode("modules/{$module}/version");

        $layout->getBlock('before_body_end')->append($layout->createBlock('core/text')->setText('
                <script type="text/javascript">var legality = $$(".legality")[0]; legality == undefined ? "" : legality.insert({after:"'.$module.' ver. '.$version.', "});</script>
                '));

        return $this;
    }
}