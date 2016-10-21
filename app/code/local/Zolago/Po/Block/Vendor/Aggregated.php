<?php

class Zolago_Po_Block_Vendor_Aggregated extends Mage_Core_Block_Template
{

    protected function _beforeToHtml() {
        $this->getGrid();
        return parent::_beforeToHtml();
    }

    public function getGridJsObjectName() {
        return $this->getGrid()->getJsObjectName();
    }

    protected function _prepareLayout()
    {
        //fix for horizontal scroll for grid
        $this->getLayout()
        ->getBlock('root')
        ->addBodyClass('grid-hscroll-fix')
        ->addBodyClass('grid-hscroll-700w');
        return parent::_prepareLayout();
    }

    /**
     * @return Zolago_Po_Block_Vendor_Po_Grid
     */
    public function getGrid() {
        if(!$this->getData("grid")) {
            $design = Mage::getDesign();
            $design->setArea("adminhtml");
            $block = $this->getLayout()->
                     createBlock("zolagopo/vendor_aggregated_grid");
            $block->setParentBlock($this);
            $this->setGridHtml($block->toHtml());
            $this->setData("grid", $block);
            $design->setArea("frontend");
        }
        return $this->getData("grid");
    }

    /**
     * post offices list from api
     */

    protected function _getPostOfficeList() {
        // cache
        $lambda = function ($param) {
            $out = array();
            try {
                $manager = Mage::helper('orbashipping')->getShippingManager(Orba_Shipping_Model_Post::CODE);
                if ($manager->isActive()) {
                    $client = $manager->getClient();
                    $list = $client->getPostOfficeList();
                    foreach ($list as $item) {
                        $out[$item->urzadNadania] = sprintf('%s [%s]',$item->opis,$item->nazwaWydruk);
                    }
                }
            } catch (Orba_Shipping_Model_Post_Client_Exception_NoPostOffices $xt) {
                $out[0] = sprintf('-- %s --',$xt->getMessage());
            } catch (Exception $xt) {
                Mage::logException($xt);
                Mage::getSingleton('core/session')->addError(Mage::helper('udpo')->__('There was a technical error. Please contact shop Administrator.'));
            }
            return serialize($out);
        };
        $cacheKey = 'post_office_list';
        return unserialize(Mage::helper('zolagocommon')->getCache($cacheKey,self::CACHE_GROUP,$lambda,null));
    }

}
