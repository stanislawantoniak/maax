<?php
class Zolago_DropshipPo_Helper_Data extends Unirgy_DropshipPo_Helper_Data
{
	public function sendNewPoNotificationEmail($po, $comment = '')
	{
		$order = $po->getOrder();
		$store = $order->getStore();

		$vendor = $po->getVendor();

		$hlp = Mage::helper('udropship');
		$udpoHlp = Mage::helper('udpo');
		$data = array();

		if (!$po->getResendNotificationFlag()
			&& ($store->getConfig('udropship/vendor/attach_packingslip') && $vendor->getAttachPackingslip()
				|| $store->getConfig('udropship/vendor/attach_shippinglabel') && $vendor->getAttachShippinglabel() && $vendor->getLabelType())
		) {
			$udpoHlp->createReturnAllShipments = true;
			if ($shipments = $udpoHlp->createShipmentFromPo($po, array(), true, true, true)) {
				foreach ($shipments as $shipment) {
					$shipment->setNewShipmentFlag(true);
					$shipment->setDeleteOnFailedLabelRequestFlag(true);
				}
			}
			$udpoHlp->createReturnAllShipments = false;
		}

		if ($po->getResendNotificationFlag()) {
			foreach ($po->getShipmentsCollection() as $_shipment) {
				if ($_shipment->getUdropshipStatus() != Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED) {
					$shipments[] = $_shipment;
					break;
				}
			}
		}

		$adminTheme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));

		if ($store->getConfig('udropship/purchase_order/attach_po_pdf') && $vendor->getAttachPoPdf()) {
			Mage::getDesign()->setArea('adminhtml')
				->setPackageName(!empty($adminTheme[0]) ? $adminTheme[0] : 'default')
				->setTheme(!empty($adminTheme[1]) ? $adminTheme[1] : 'default');

			$orderShippingAmount = $order->getShippingAmount();
			$order->setShippingAmount($po->getShippingAmount());

			$pdf = Mage::helper('udpo')->getVendorPoMultiPdf(array($po));

			$order->setShippingAmount($orderShippingAmount);

			$data['_ATTACHMENTS'][] = array(
				'content' => $pdf->render(),
				'filename' => 'purchase_order-' . $po->getIncrementId() . '-' . $vendor->getId() . '.pdf',
				'type' => 'application/x-pdf',
			);
		}

		if ($store->getConfig('udropship/vendor/attach_packingslip') && $vendor->getAttachPackingslip() && !empty($shipments)) {
			Mage::getDesign()->setArea('adminhtml')
				->setPackageName(!empty($adminTheme[0]) ? $adminTheme[0] : 'default')
				->setTheme(!empty($adminTheme[1]) ? $adminTheme[1] : 'default');

			foreach ($shipments as $shipment) {
				$orderShippingAmount = $order->getShippingAmount();
				$order->setShippingAmount($shipment->getShippingAmount());

				$pdf = Mage::helper('udropship')->getVendorShipmentsPdf(array($shipment));

				$order->setShippingAmount($orderShippingAmount);
				$shipment->setDeleteOnFailedLabelRequestFlag(false);

				$data['_ATTACHMENTS'][] = array(
					'content' => $pdf->render(),
					'filename' => 'packingslip-' . $po->getIncrementId() . '-' . $vendor->getId() . '.pdf',
					'type' => 'application/x-pdf',
				);
			}
		}

		if ($store->getConfig('udropship/vendor/attach_shippinglabel') && $vendor->getAttachShippinglabel()
			&& $vendor->getLabelType() && !empty($shipments)
		) {
			foreach ($shipments as $shipment) {
				try {
					$hlp->unassignVendorSkus($shipment);
					$hlp->unassignVendorSkus($po);
					foreach ($shipment->getAllItems() as $sItem) {
						$firstOrderItem = $sItem->getOrderItem();
						break;
					}
					if (!isset($firstOrderItem) || !$firstOrderItem->getUdpompsManual()) {
						if (!$po->getResendNotificationFlag()) {
							$batch = Mage::getModel('udropship/label_batch')->setVendor($vendor)->processShipments(array($shipment));
							if ($batch->getErrors()) {
								if (Mage::app()->getRequest()->getRouteName() == 'udropship') {
									Mage::throwException($batch->getErrorMessages());
								} else {
									Mage::helper('udropship/error')->sendLabelRequestFailedNotification($shipment, $batch->getErrorMessages());
								}
							} else {
								if ($batch->getShipmentCnt() > 1) {
									$labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
									$data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
								} else {
									$labelModel = $hlp->getLabelTypeInstance($batch->getLabelType());
									foreach ($shipment->getAllTracks() as $track) {
										$data['_ATTACHMENTS'][] = $labelModel->renderTrackContent($track);
									}
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
								if (count($tracks) > 1) {
									$labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
									$data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
								} else {
									reset($tracks);
									$labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
									$data['_ATTACHMENTS'][] = $labelModel->renderTrackContent(current($tracks));
								}
							}
						}
					}
				} catch (Exception $e) {
					// ignore if failed
				}
			}
		}

		if (!empty($shipments)) {
			foreach ($shipments as $shipment) {
				if ($shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
					$shipment->setNoInvoiceFlag(false);
					$hlp->unassignVendorSkus($shipment);
					$hlp->unassignVendorSkus($po);
					$udpoHlp->invoiceShipment($shipment);
				}
			}
		}

		$hlp->setDesignStore($store);
		$shippingAddress = $order->getShippingAddress();
		if (!$shippingAddress) {
			$shippingAddress = $order->getBillingAddress();
		}
		$hlp->assignVendorSkus($po);
		$data += array(
			'po' => $po,
			'order' => $order,
			'vendor' => $vendor,
			'comment' => $comment,
			'store_name' => $store->getName(),
			'vendor_name' => $vendor->getVendorName(),
			'po_id' => $po->getIncrementId(),
			'order_id' => $order->getIncrementId(),
			'customer_info' => Mage::helper('udropship')->formatCustomerAddress($shippingAddress, 'html', $vendor),
			'shipping_method' => $po->getUdropshipMethodDescription() ? $po->getUdropshipMethodDescription() : $vendor->getShippingMethodName($order->getShippingMethod(), true),
			'po_url' => Mage::getUrl('udpo/vendor/', array('_query' => 'filter_po_id_from=' . $po->getIncrementId() . '&filter_po_id_to=' . $po->getIncrementId())),
			'po_pdf_url' => Mage::getUrl('udpo/vendor/udpoPdf', array('udpo_id' => $po->getId())),
			'use_attachements' => true
		);

		$template = $vendor->getEmailTemplate();
		if (!$template) {
			$template = $store->getConfig('udropship/purchase_order/new_po_vendor_email_template');
		}
		$identity = $store->getConfig('udropship/vendor/vendor_email_identity');

		$data['_BCC'] = $vendor->getNewOrderCcEmails();
		if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
			$email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
		} else {
			$email = $vendor->getEmail();
		}

		/** @var Zolago_Common_Helper_Data $mailer */
		$mailer = Mage::helper("zolagocommon");
		$mailer->sendEmailTemplate(
			$email,
			$vendor->getVendorName(),
			$template,
			$data,
			$store->getId(),
			$identity
		);

		$hlp->unassignVendorSkus($po);

		$hlp->setDesignStore();
	}
}