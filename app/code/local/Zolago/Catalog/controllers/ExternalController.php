 <?php
/**
 * converter emulator
 */

class Zolago_Catalog_ExternalController extends Mage_Core_Controller_Front_Action
{
    protected function _getKey() {        
        $key = $this->getRequest()->getParam('key');
        $val = '';
        // validation
        preg_match('/^\"[0-9]+\:.*\"$/',$key,$val);
        if (empty($val)) {
            Mage::throwException('Wrong key format');
        }
        $val = substr($val[0],1,-1);        
        $data = explode(':',$val);
        if (empty($data[0]) 
            || empty($data[1])) {
            Mage::throwException('No valid data');    
        }
        return $data;
        
    }
    public function stockAction() {
        $key = $this->_getKey();
        $collection = Mage::getModel('zolagocatalog/external_stock')->getCollection();
        $collection->addFieldToFilter('vendor_id',$key[0]);
        $collection->addFieldToFilter('external_sku',$key[1]);
        $count = $collection->count();
        $out = array (
            'total_rows' => $count,
            'rows' => array(),
        );
        foreach ($collection as $item) {	
            $out['rows'][] = array (
                'id' => sprintf('STOCK:%s:%s:%s',$item->getVendorId(),$item->getExternalSku(),$item->getExternalStockId()),
                'key' => sprintf('%d:%s',$item->getVendorId(),$item->getExternalSku()),
                'value' => array (
                    'pos' => $item->getExternalStockId(),
                    'stock' => $item->getQty(),
                ),
            );
        }
        echo Zend_Json::encode($out);
        exit;
    }


}