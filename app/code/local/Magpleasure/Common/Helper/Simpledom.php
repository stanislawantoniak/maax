<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Helper_Simpledom extends Mage_Core_Helper_Abstract
{
    public function file_get_html() {
        $dom = new Magpleasure_Common_Helper_Simpledom_Dom;
        $args = func_get_args();
        $dom->load(call_user_func_array('file_get_contents', $args), true);
        return $dom;
    }

    // get html dom form string
    public function str_get_html($str, $lowercase=true) {
        $dom = new Magpleasure_Common_Helper_Simpledom_Dom;
        $dom->load($str, $lowercase);
        return $dom;
    }

    // dump html dom tree
    public function dump_html_tree($node, $show_attr=true, $deep=0) {
        $lead = str_repeat('    ', $deep);
        echo $lead.$node->tag;
        if ($show_attr && count($node->attr)>0) {
            echo '(';
            foreach($node->attr as $k=>$v)
                echo "[$k]=>\"".$node->$k.'", ';
            echo ')';
        }
        echo "\n";

        foreach($node->nodes as $c){
            $this->dump_html_tree($c, $show_attr, $deep+1);
        }
    }

    // get dom form file (deprecated)
    public function file_get_dom() {
        $dom = new Magpleasure_Common_Helper_Simpledom_Dom;
        $args = func_get_args();
        $dom->load(call_user_func_array('file_get_contents', $args), true);
        return $dom;
    }

    // get dom form string (deprecated)
    public function str_get_dom($str, $lowercase=true) {
        $dom = new Magpleasure_Common_Helper_Simpledom_Dom;
        $dom->load($str, $lowercase);
        return $dom;
    }

}
