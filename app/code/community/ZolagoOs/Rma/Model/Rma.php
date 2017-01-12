<?php

class ZolagoOs_Rma_Model_Rma extends Mage_Sales_Model_Abstract
{
    const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/rma/template';
    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/rma/identity';
    const XML_PATH_EMAIL_COPY_TO                = 'sales_email/rma/copy_to';
    const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/rma/copy_method';
    const XML_PATH_EMAIL_ENABLED                = 'sales_email/rma/enabled';

    const XML_PATH_UPDATE_EMAIL_TEMPLATE        = 'sales_email/rma_comment/template';
    const XML_PATH_UPDATE_EMAIL_IDENTITY        = 'sales_email/rma_comment/identity';
    const XML_PATH_UPDATE_EMAIL_COPY_TO         = 'sales_email/rma_comment/copy_to';
    const XML_PATH_UPDATE_EMAIL_COPY_METHOD     = 'sales_email/rma_comment/copy_method';
    const XML_PATH_UPDATE_EMAIL_ENABLED         = 'sales_email/rma_comment/enabled';

    protected $_items;
    protected $_order;
    protected $_comments;
    
    protected $_eventPrefix = 'urma_rma';
    protected $_eventObject = 'rma';
    
    protected $_commentsChanged = false;

    protected function _construct()
    {
        $this->_init('urma/rma');
    }

    public function loadByIncrementId($incrementId)
    {
        $ids = $this->getCollection()
            ->addAttributeToFilter('increment_id', $incrementId)
            ->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }

    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }

    public function getProtectCode()
    {
        return (string)$this->getOrder()->getProtectCode();
    }

    public function getOrder()
    {
        if (!$this->_order instanceof Mage_Sales_Model_Order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * return address for pickup rma 
     * @var return Mage_Customer_Model_Address
     */
   public function getCourierAddress() {
       if ($customerAddressId = $this->getData('customer_address_id')) {
            $shippingAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($shippingAddress->getId()) {
                return $shippingAddress;
            }
       }
       return self::getShippingAddress();
   }

    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    public function register()
    {
        if ($this->getId()) {
            Mage::throwException(
                Mage::helper('sales')->__('Cannot register existing rma')
            );
        }

        $totalQty = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQty()>0) {
                $item->register();
                if (!$item->getOrderItem()->isDummy(true)) {
                    $totalQty+= $item->getQty();
                }
            }
            else {
                $item->isDeleted(true);
            }
        }
        $this->setTotalQty($totalQty);

        return $this;
    }
    
    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = Mage::getResourceModel('urma/rma_item_collection')
                ->setRmaFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setRma($this);
                }
            }
        }
        return $this->_items;
    }

    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId()==$itemId) {
                return $item;
            }
        }
        return false;
    }

    public function addItem(ZolagoOs_Rma_Model_Rma_Item $item)
    {
        $item->setRma($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }
    public function createComment($comment,$notify=false,$visibleOnFront=false,$notifyVendor=false,$visibleToVendor=true) {
            $comment = Mage::getModel('urma/rma_comment')
                ->setComment($comment)
                ->setIsCustomerNotified($notify)
                ->setIsVisibleOnFront($visibleOnFront)
                ->setIsVendorNotified($notifyVendor)
                ->setIsVisibleToVendor($visibleToVendor);
            return $comment;
    }
    public function addComment($comment, $notify=false, $visibleOnFront=false, $notifyVendor=false, $visibleToVendor=true)
    {
        $this->_commentsChanged = true;
        if (!($comment instanceof ZolagoOs_Rma_Model_Rma_Comment)) {
            $comment = $this->createComment($comment,$notify,$ficibleOnFront,$notifyVendor,$visibleToVendor);
        }
        $comment->setRma($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$comment->getId()) {
            $this->getCommentsCollection()->addItem($comment);
        }
        return $this;
    }

    public function saveComments()
    {
        if ($this->_commentsChanged) {
            $this->getCommentsCollection()->save();
        }
        return $this;
    }

    protected $_vendorComments;
    public function getVendorCommentsCollection($reload=false)
    {
        if (is_null($this->_vendorComments) || $reload) {
            $this->_vendorComments = Mage::getResourceModel('urma/rma_comment_collection')
                ->setRmaFilter($this->getId())
                ->addFieldToFilter('is_visible_to_vendor',1)
                ->setCreatedAtOrder();

	        $this->_vendorComments->getSelect()->where("comment IS NOT NULL");


            $this->_vendorComments->load();

            if ($this->getId()) {
                foreach ($this->_vendorComments as $comment) {
                    $comment->setRma($this);
                }
            }
        }
        return $this->_vendorComments;
    }

    public function getCommentsCollection($reload=false)
    {
        if (is_null($this->_comments) || $reload) {
            $this->_comments = Mage::getResourceModel('urma/rma_comment_collection')
                ->setRmaFilter($this->getId())
                ->setCreatedAtOrder();

            $this->_comments->load();

            if ($this->getId()) {
                foreach ($this->_comments as $comment) {
                    $comment->setRma($this);
                }
            }
        }
        return $this->_comments;
    }
    
    protected function _beforeSave()
    {
        if ((!$this->getId() || null !== $this->_items) && !count($this->getAllItems())) {
            Mage::throwException(
                Mage::helper('sales')->__('Cannot create an empty rma.')
            );
        }

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
            $this->setShippingAddressId($this->getOrder()->getShippingAddress()->getId());
        }

        return parent::_beforeSave();
    }

    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    protected $_tracks;
    public function getTracksCollection()
    {
        if (empty($this->_tracks)) {
            $this->_tracks = Mage::getResourceModel('urma/rma_track_collection')
                ->setRmaFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_tracks as $track) {
                    $track->setRma($this);
                }
            }
        }
        return $this->_tracks;
    }

    public function getAllTracks()
    {
        $tracks = array();
        foreach ($this->getTracksCollection() as $track) {
            if (!$track->isDeleted()) {
                $tracks[] =  $track;
            }
        }
        return $tracks;
    }

    public function getTrackById($trackId)
    {
        foreach ($this->getTracksCollection() as $track) {
            if ($track->getId()==$trackId) {
                return $track;
            }
        }
        return false;
    }

    public function addTrack(ZolagoOs_Rma_Model_Rma_Track $track)
    {
        $track->setRma($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$track->getId()) {
            $this->getTracksCollection()->addItem($track);
        }
        return $this;
    }
    
    protected function _afterSave()
    {
        if (null !== $this->_items) {
            foreach ($this->_items as $item) {
                $item->save();
            }
        }

        if (null !== $this->_comments) {
            foreach($this->_comments as $comment) {
                if (!$comment->getRmaStatus()) {
                    $comment->setRmaStatus($this->getRmaStatus());
                }
                $comment->save();
            }
        }

        if (null !== $this->_tracks) {
            foreach($this->_tracks as $track) {
                $track->save();
            }
        }

        return parent::_afterSave();
    }
    
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    public function sendUpdateEmail($notifyCustomer = true, Zolago_Rma_Model_Rma_Comment $comment = null)
    {
        if (!Mage::helper('sales')->canSendShipmentCommentEmail($this->getOrder()->getStore()->getId())) {
            return $this;
        }

        $hlp = Mage::helper('udropship');

        $order  = $this->getOrder();
	    $store = Mage::app()->getStore();

        $copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $this->getStoreId());

        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        $paymentBlock   = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($order->getStore()->getId());

        $mailTemplate = Mage::getModel('zolagocommon/core_email_template');

        $template = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $order->getStoreId());
        if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }

        $data = array();
        if ($notifyCustomer) {
            $sendTo[] = array(
                'name'  => $customerName,
                'email' => $order->getCustomerEmail()
            );
            if ($copyTo && $copyMethod == 'bcc') {
                foreach ($copyTo as $email) {
                    $data['_BCC'][] = $email;
                }
            }

        }

        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        if ($this->hasPrintableTracks()) {
            try {
                $lblBatch = Mage::getModel('udropship/label_batch')
                    ->setForcedFilename('rma_label_'.$this->getIncrementId())
                    ->setVendor($this->getVendor())
                    ->renderRmas(array($this));
                $labelModel = Mage::helper('udropship')->getLabelTypeInstance($lblBatch->getLabelType());
                $labelModel->setBatch($lblBatch);
                $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($lblBatch);
            } catch (Exception $e) {}
        }

        $hlp->setDesignStore($this->getOrder()->getStore());

        $dateRmaCreate = $this->getCteateAt();
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($dateRmaCreate));
        $dateRmaCreateFormated = date('d.m.Y H:i', $dateTimestamp);

	    if($comment !== null) {
		    /** @var $_commentHelper Zolago_Rma_Helper_Data */
		    $_commentHelper = Mage::helper("zolagorma");
		    $comment = $_commentHelper->formatComment($comment);
	    } else {
		    $comment = '';
	    }

        $data = array_merge($data, array(
            'rma_creation_date'     => $dateRmaCreateFormated,
            'order'                 => $order,
            'rma'                   => $this,
            'rma_status'            => Mage::helper("zolagorma")->__($this->getStatusCustomerNotes() ? $this->getStatusCustomerNotes() : $this->getRmaStatusName()),
            'po'                    => $this->getPo(),
            'vendor'                => $this->getVendor(),
            'store_name'            => $store->getFrontendName(),
            'comment'               => $comment,
            'billing'               => $order->getBillingAddress(),
            'payment_html'          => $paymentBlock->toHtml(),
            'show_order_info'       =>!Mage::getStoreConfigFlag('urma/general/customer_hide_order_info'),
            'show_receiver'         => $this->isReceiverVisible(),
            'show_notes'            =>$this->getStatusCustomerNotes()||($this->isAllowedResolutionNotes()&&$this->getResolutionNotes()),
            'show_both_notes'       =>$this->getStatusCustomerNotes()&&($this->isAllowedResolutionNotes()&&$this->getResolutionNotes()),
            'customer_notes'        =>$this->getStatusCustomerNotes(),
            'show_resolution_notes' =>$this->isAllowedResolutionNotes()&&$this->getResolutionNotes(),
            'resolution_notes'      =>$this->getResolutionNotes(),
	        'use_attachments'       => true
        ));

	    /** @var Zolago_Common_Helper_Data $mailer */
	    $mailer = Mage::helper('zolagocommon');
	    if(isset($sendTo)) {
		    foreach ($sendTo as $recipient) {
			    $mailer->sendEmailTemplate(
				    $recipient['email'],
				    $recipient['name'],
				    $template,
				    $data,
				    $order->getStoreId(),
				    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $order->getStoreId())
			    );
		    }
	    }

        $hlp->setDesignStore();

        Mage::helper('udropship')->processQueue();

        return $this;
    }

    public function sendEmail($notifyCustomer=true, Zolago_Rma_Model_Rma_Comment $comment = null)
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
	    
	    $store = Mage::app()->getStore();
        $order  = $this->getOrder();
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStoreId());

        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }

        $currentDesign = Mage::getDesign()->setAllGetOld(array(
            'package' => Mage::getStoreConfig('design/package/name', $this->getStoreId()),
            'store' => $this->getStoreId()
        ));

        $paymentBlock   = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($order->getStore()->getId());


	    /** @var Zolago_Common_Model_Core_Email_Template $mailTemplate */
        $mailTemplate = Mage::getModel('zolagocommon/core_email_template');

        $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $order->getStoreId());
        if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }

        if ($notifyCustomer) {
            $sendTo[] = array(
                'name'  => $customerName,
                'email' => $order->getCustomerEmail()
            );
            if ($copyTo && $copyMethod == 'bcc') {
                foreach ($copyTo as $email) {
                    $mailTemplate->addBcc($email);
                }
            }

        }

        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        $data = array();
        if ($this->hasPrintableTracks()) {
            try {
                $lblBatch = Mage::getModel('udropship/label_batch')
                    ->setForcedFilename('rma_label_'.$this->getIncrementId())
                    ->setVendor($this->getVendor())
                    ->renderRmas(array($this));
                $labelModel = Mage::helper('udropship')->getLabelTypeInstance($lblBatch->getLabelType());
                $labelModel->setBatch($lblBatch);
                $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($lblBatch);
            } catch (Exception $e) {}
        }
        $dateRmaCreate = $this->getCteateAt();
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($dateRmaCreate));
        $dateRmaCreateFormated = date('d.m.Y H:i', $dateTimestamp);

        //courier
        $shippingAddress = $this->getCourierAddress();
        $shippingAddressStreet = Mage::helper('core')->escapeHtml(is_array($shippingAddress->getStreet(-1)) ? explode(" ", $shippingAddress->getStreet(-1)) : $shippingAddress->getStreet(-1));
        $weekdays = Mage::app()->getLocale()->getOptionWeekdays();
        $carrierDate = $this->getCarrierDate();
        $showCourier = false;
        if(!empty($carrierDate)){
            $showCourier = true;
            $courierDateF = Mage::helper("core")->formatDate($carrierDate, "short");
        } else {
	        $courierDateF = false;
        }

	    if($comment !== null) {
		    /** @var $_commentHelper Zolago_Rma_Helper_Data */
		    $_commentHelper = Mage::helper("zolagorma");
		    $comment = $_commentHelper->formatComment($comment);
	    } else {
		    $comment = '';
	    }

        $data = array_merge($data, array(
            'rma_creation_date'         => $dateRmaCreateFormated,
            'order'                     => $order,
            'rma'                       => $this,
            'rma_status_pending'        => ($this->getRmaStatus() == Zolago_Rma_Model_Rma_Status::STATUS_PENDING) ? true : false,
            'rma_status_pending_pickup' => ($this->getRmaStatus() == Zolago_Rma_Model_Rma_Status::STATUS_PENDING_PICKUP) ? true : false,
            'po'                        => $this->getPo(),
            'vendor'                    => $this->getVendor(),
            'show_comment'              => !empty($comment) ? true : false,
            'comment'                   => $comment,
            'show_courier'              => $showCourier,
            'courier_week_day'          => $weekdays[date('w',strtotime($carrierDate))]['label'],
            'courier_date'              => $courierDateF,
            'courier_shipping'          => $shippingAddress,
            'courier_shipping_street'   => $shippingAddressStreet,
            'courier_pdf_url'           => Mage::getUrl('sales/rma/pdf', array('id' => $this->getId())),
            'billing'                   => $order->getBillingAddress(),
            'payment_html'              => $paymentBlock->toHtml(),
            'show_order_info'           => !Mage::getStoreConfigFlag('urma/general/customer_new_hide_order_info'),
            'show_receiver'             => $this->isReceiverVisible(),
            'show_notes'                =>$this->getStatusCustomerNotes()||($this->isAllowedResolutionNotes()&&$this->getResolutionNotes()),
            'show_both_notes'           =>$this->getStatusCustomerNotes()&&($this->isAllowedResolutionNotes()&&$this->getResolutionNotes()),
            'customer_notes'            =>$this->getStatusCustomerNotes(),
            'show_resolution_notes'     =>$this->isAllowedResolutionNotes()&&$this->getResolutionNotes(),
            'store_name'                => $store->getFrontendName(),
            'resolution_notes'          =>$this->getResolutionNotes(),
            'rma_url'                   => Mage::getUrl('sales/rma/view', array('id' => $this->getId())),
	        'use_attachments'           => true
        ));


	    /** @var Zolago_Common_Helper_Data $mailer */
	    $mailer = Mage::helper('zolagocommon');
	    if(isset($sendTo)) {
		    foreach ($sendTo as $recipient) {
			    $mailer->sendEmailTemplate(
				    $recipient['email'],
				    $recipient['name'],
				    $template,
				    $data,
				    $order->getStoreId(),
				    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $order->getStoreId())
			    );
		    }
	    }

        $translate->setTranslateInline(true);

        Mage::getDesign()->setAllGetOld($currentDesign);

        Mage::helper('udropship')->processQueue();

        return $this;
    }

    protected function _getEmails($configPath)
    {
        $data = Mage::getStoreConfig($configPath, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    public function getRmaStatusName()
    {
        return Mage::helper('urma')->getRmaStatusName($this->getRmaStatus());
    }
    public function getRmaReasonName()
    {
        return Mage::helper('urma')->getReasonTitle($this->getRmaReason());
    }
    public function getStatusLabel()
    {
        return Mage::helper('urma')->__($this->getRmaStatus());
    }
    public function getStatusCustomerNotes()
    {
        return Mage::helper('urma')->getStatusCustomerNotes($this->getRmaStatus());
    }
    public function isAllowedResolutionNotes()
    {
        $allowed = Mage::helper('urma')->getAllowedResolutionNotesStatuses();
        return array_key_exists($this->getRmaStatus(), $allowed);
    }
    public function isReceiverVisible()
    {
        $allowed = Mage::helper('urma')->getReceiverVisibleStatuses();
        return array_key_exists($this->getRmaStatus(), $allowed);
    }

    public function getVendorName()
    {
        return $this->getVendor()->getVendorName();
    }

    public function getVendor()
    {
        return Mage::helper('udropship')->getVendor($this->getUdropshipVendor());
    }

    public function getRemainingWeight()
    {
        $weight = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy(true)) continue;
            $weight += $item->getWeight()*$item->getQty();
        }
        return $weight;
    }

    public function getRemainingValue()
    {
        $value = 0;
        foreach ($this->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy(true)) continue;
            $value += $item->getPrice()*$item->getQty();
        }
        return $value;
    }

    public function hasPrintableTracks()
    {
        $has = false;
        foreach ($this->getAllTracks() as $track) {
            if ($track->getLabelImage()) {
                $has = true;
                break;
            }
        }
        return $has;
    }

}
