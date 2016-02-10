<?php

class GH_Marketing_Dropship_MarketingController extends Zolago_Dropship_Controller_Vendor_Abstract {

	/**
	 * Sledzenie budżetu kosztów marketingu dla sprzedawcy
	 */
	public function budgetAction() {
		parent::_renderPage(null,'budget_marketing');
	}
}