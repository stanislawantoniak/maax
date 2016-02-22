<?php
class Zolago_DropshipMicrositePro_Model_TemplateFilter extends Unirgy_DropshipMicrositePro_Model_TemplateFilter
{

    public function skinDirective($construction) {
        $params = $this->_getIncludeParameters($construction[2]);
        $return = parent::skinDirective($construction);
        if (!empty($params['_no_protocol'])) {
            $return = str_replace(array('http://','https://'),array('//','//'),$return);
        }
        return $return;
    }
}


