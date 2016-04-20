<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    public function getRate($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return 0;
        }

        //UDROPSHIP
        if ($request->getCountryId()=='US'
            && ($v = $request->getVendor()) && $v->getCountryId()=='US' && (
                !$v->getTaxRegions() ||
                $v->getTaxRegions() && !in_array($request->getRegionId(), (array)$v->getTaxRegions())
            )
        ) {
            return 0;
        }

        $cacheKey = "{$request->getProductClassId()}|{$request->getCustomerClassId()}|{$request->getCountryId()}|{$request->getRegionId()}|{$request->getPostcode()}";

        if (!isset($this->_rateCache[$cacheKey])) {
            $this->unsRateValue();
            $this->unsCalculationProcess();
            $this->unsEventModuleId();
            Mage::dispatchEvent('tax_rate_data_fetch', array('request'=>$this));
            if (!$this->hasRateValue()) {
                $this->setCalculationProcess($this->_getResource()->getCalculationProcess($request));
                $this->setRateValue($this->_getResource()->getRate($request));
            } else {
                $this->setCalculationProcess($this->_formCalculationProcess());
            }
            $this->_rateCache[$cacheKey] = $this->getRateValue();
            $this->_rateCalculationProcess[$cacheKey] = $this->getCalculationProcess();
        }
        return $this->_rateCache[$cacheKey];
    }
}