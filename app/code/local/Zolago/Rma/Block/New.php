<?php
class Zolago_Rma_Block_New extends Mage_Core_Block_Template
{
    protected $_returnRenderer;
    public function getPo() {
        return Mage::registry('current_po');
    }
    public function getItemList() {
        $po = $this->getPo();
        if (!$po) {
            return array();            
        }
        $items = $po->getItemsCollection();
        $out = array();
        $parents = array();
        foreach ($items as $item) {
            if ($parentId = $item->getParentItemId()) {
                $parents[$parentId]  = $parentId;            
            }
        }
        foreach ($items as $item) {
                for ($a = 0; $a<$item->getQty();$a++) {            
                    $entity_id = $item->getEntityId();
                    if (!isset($parents[$item->getOrderItemId()])) {
                        $out[$entity_id][$a] = array (
                            'entityId' => $entity_id,
                            'name' => $item->getName(),
                        );
                    }
                }
        }
        return $out;
    }
    public function getReturnRenderer() {
        if (is_null($this->_returnRenderer)) {
            $helper = Mage::helper('urma');        
            $list = $helper->getItemConditionTitles();        
            $out = '';
            foreach ($list as $key=>$item) {
                $out .= '<option value="'.$key.'">'.Mage::helper('zolagorma')->__($item).'</option>';
            }
            $this->_returnRenderer = $out;
        }
        return $this->_returnRenderer;
    }
}
