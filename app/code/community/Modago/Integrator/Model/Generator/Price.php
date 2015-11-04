<?php

/**
 * generating price file
 */
class Modago_Integrator_Model_Generator_Price
    extends Modago_Integrator_Model_Generator
{
    protected $_getList;
    protected $_header;
    protected $_footer;

    protected function _construct()
    {
        $this->setFileNamePrefix('PRICES');
    }

    /**
     * prepare content
     *
     * @return array
     */
    protected function _prepareList()
    {
        if ($this->_getList) {
            return false;
        }

        $res = array();

        $res = Mage::getModel("modagointegrator/product_price")
            ->appendPricesForConfigurable($res);

        $res = Mage::getModel("modagointegrator/product_price")
            ->appendPricesForSimple($res);

        $this->_getList = true;
        return $res;
    }

    /**
     * prepare xml block
     *
     * @param array $item
     * @return string
     */
    protected function _prepareXmlBlock($item)
    {
        $price = number_format($item['price'], 2, ".", "");
        return '<product price="' . $price . '">' . $item['sku'] . '</product>';
    }

    /**
     * prepare header
     *
     * @return string
     */
    protected function _getHeader()
    {
        if (!$this->_header) {
            $this->_header = '<mall><merchant>' . $this->getExternalId() . '</merchant>';
        }
        return $this->_header;
    }

    /**
     * prepare footer
     *
     * @return string
     */
    protected function _getFooter()
    {
        if (!$this->_footer) {
            $this->_footer = "</mall>";
        }
        return $this->_footer;
    }

    /**
     * prepare section header
     *
     * @param $type
     * @return string
     */
    protected function _getSectionHeader($type)
    {
        return '<priceList priceId="' . $type . '">';
    }

    /**
     * prepare section footer
     *
     * @return string
     */
    protected function _getSectionFooter()
    {
        return "</priceList>";
    }

    /**
     * generation file
     *
     * @return bool
     */
    public function generate()
    {
        $this->_status = false;
        $this->_fileName = null;
        $helper = $this->getHelper();
        try {
            $helper->createFile($this->_getPath());
            $helper->addToFile($this->_getHeader());
            $list = $this->_prepareList();

            foreach ($list as $type => $items) {
                $helper->addToFile($this->_getSectionHeader($type));
                foreach ($items as $item) {
                    $block = $this->_prepareXmlBlock($item);
                    $helper->addToFile($block);
                }
                $helper->addToFile($this->_getSectionFooter());
            }

            $helper->addToFile($this->_getFooter());
            $helper->closeFile();
            $this->_status = true;
        } catch (Modago_Integrator_Exception $ex) {
            Mage::logException($ex);
            $helper->closeFile();
        }
        return $this->_status;
    }

}
