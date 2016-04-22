<?php

class ZolagoOs_OmniChannelMicrositePro_Helper_Protected
{
	protected $_registrationFields = NULL;

	protected function _initRegistrationFields() {
		ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_OmniChannelMicrositePro");
		if (null === $this->_registrationFields) {
			$hlp = Mage::helper("udropship");
			$sHlp = Mage::helper("udmspro");
			foreach (Mage::getConfig()->getNode("global/udropship/vendor/fields")->children() as $code => $node) {
				if ($node->is("disabled")) {
					continue;
				}

				$name = $node->name ? (string)$node->name : $code;
				$type = $node->reg_type ? (string)$node->reg_type : $node->type ? (string)$node->type : "text";
				if (in_array($type, array("multiselect", "checkboxes"))) {
					$name .= "[]";
				}

				$field = array("id" => $name, "type" => $type, "name" => $name, "label" => $hlp->__($node->reg_label ? (string)$node->reg_label : (string)$node->label), "note" => $hlp->__((string)$node->note), "field_config" => $node, "required" => (int)$node->reg_required, "class" => (string)$node->reg_class);
				if ($node->frontend_model || $node->reg_frontend_model) {
					$field["type"] = $code;
					$field["input_renderer"] = Mage::getConfig()->getBlockClassName((string)($node->reg_frontend_model ? $node->reg_frontend_model : $node->frontend_model));
				}

				switch ($type) {
					case "statement_po_type":
					case "payout_po_status_type":
					case "notify_lowstock":
					case "select":
					case "multiselect":
					case "checkboxes":
					case "radios":
						$srcModel = $node->reg_source_model ? (string)$node->reg_source_model : $node->source_model ? (string)$node->source_model : "udropship/source";
						if(!$srcModel) {
							continue;
						}
						$field["source_model"] = $srcModel;
						$field["source"] = $node->source ? (string)$node->source : $code;
						$source = Mage::getSingleton($srcModel);
						if (is_callable(array($source, "setPath"))) {
							$source->setPath($node->source ? (string)$node->source : $code);
						}

						if (in_array($type, array("multiselect", "checkboxes", "radios")) || !is_callable(array($source, "toOptionHash"))) {
							$field["values"] = $source->toOptionArray((int)$node->reg_use_selector);
						} else {
							$field["options"] = $source->toOptionHash((int)$node->reg_use_selector);
						}

						break;
					case "date":
					case "datetime":
						$field["image"] = $sHlp->getSkinUrl("images/grid-cal.gif");
						$field["input_format"] = Varien_Date::DATE_INTERNAL_FORMAT;
						$field["format"] = Varien_Date::DATE_INTERNAL_FORMAT;
				}
				$this->_registrationFields[$code] = $field;
			}
			if (empty($this->_registrationFields["comments"])) {
				$this->_registrationFields["comments"] = array("id" => "comments", "type" => "textarea", "name" => "comments", "label" => $hlp->__("Comments"));
			}
		}
		return $this;
	}

	public function getRegistrationField($code) {
		$this->_initRegistrationFields();
		return !empty($this->_registrationFields[$code]) ? $this->_registrationFields[$code] : false;
	}
}


