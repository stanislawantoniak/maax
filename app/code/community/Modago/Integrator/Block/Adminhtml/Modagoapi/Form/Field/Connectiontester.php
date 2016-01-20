<?php

/**
 * Field with button for testing between Modago API
 *
 * Class Modago_Integrator_Block_Adminhtml_Modagoapi_Form_Field_Connectiontester
 */
class Modago_Integrator_Block_Adminhtml_Modagoapi_Form_Field_Connectiontester extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		$html = $this->getButtonHtml();
		$html .= $this->getJs();
		return $html;
	}

	public function render(Varien_Data_Form_Element_Abstract $element) {
		$element->setScope(null); // don't show scope ex: [GLOBAL]
		return parent::render($element);
	}

	/**
	 * Prepare button html
	 *
	 * @return string
	 */
	protected function getButtonHtml() {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');

		$html = '<div id="connectiontester">
					<button class="scalable" type="button" id="connectiontesterBtn">
						<span>' . $helper->__("Start test") . '</span>
					</button>
				</div>';
		return $html;
	}

	/**
	 * Prepare javascript with ajax witch test connection to Modago API
	 *
	 * @return string
	 */
	protected function getJs() {
		$testUrl = $this->getUrl('adminhtml/modagoapi_connection/test');
		$js = '<script type="text/javascript">';
		$js .= '
		var modagoApiConnectionTester = {
			testUrl: \'' . $testUrl . '\',
			init: function() {
				Event.observe(\'connectiontesterBtn\', \'click\', function () {
					modagoApiConnectionTester.test();
				});
			},
			test: function() {
				new Ajax.Request(this.testUrl, {
					onComplete: function(response) {
						alert(response.responseText);
					}
				});
			}
		};
		modagoApiConnectionTester.init();';
		$js .= '</script>';
		return $js;
	}
}
