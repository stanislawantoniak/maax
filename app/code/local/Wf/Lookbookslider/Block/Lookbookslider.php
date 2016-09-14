<?php

/**
 * Class Wf_Lookbookslider_Block_Lookbookslider
 */
class Wf_Lookbookslider_Block_Lookbookslider extends Altima_Lookbookslider_Block_Lookbookslider {

    /**
     * Change SKU to product information (link data) into Json array
     *
     * @param json array $array
     * @return array
     */
    public function getHotspotsWithProductDetails($slide){
        $helper = Mage::helper('lookbookslider');
        $hotspots = $slide->getHotspots();
        if ($hotspots=='') return '';
        $decoded_array = json_decode($hotspots,true);
        $img_width = $slide->getWidth();
        $hotspot_icon  = $helper->getHotspotIcon();
        $hotspot_icon_path  = $helper->getHotspotIconPath();
        $icon_dimensions = $helper->getImageDimensions($hotspot_icon_path);
        $_coreHelper = Mage::helper('core');

        foreach($decoded_array as $key => $value){

            $product_details = null;
            if ($decoded_array[$key]['sku']!='') {
                $product_details = Mage::getModel('catalog/product')->loadByAttribute('sku',$decoded_array[$key]['sku']);
                if($product_details){
                    $product_details_full = Mage::getModel('catalog/product')->load($product_details->getId());
                }else{
                    $decoded_array[$key]['text']=$this->__("Product with SKU %s doesn't exist",$decoded_array[$key]['sku']);
                    $product_details_full = NULL;
                }
            }

            $html_content = '';
            if (!isset($icon_dimensions['error'])) {
                $html_content .= '<img class="hotspot-icon" src="'.$hotspot_icon.'" alt="" style="
                        left:'. (round($value['width']/2)-round($icon_dimensions['width']/2)) .'px; 
                        top:'. (round($value['height']/2)-round($icon_dimensions['height']/2)) .'px;
                        "/>';
                //$html_content .= '<div class="hotspot-icon">'.$i.'</div>';
                $decoded_array[$key]['icon_width'] = $icon_dimensions['width'];
                $decoded_array[$key]['icon_height'] = $icon_dimensions['height'];
            }
            $html_content .=  '<div class="product-info" class="col-sm-12" style="';
            $html_content .=  'left:'.round($value['width']/2).'px;';
            $html_content .=  'top:'.round($value['height']/2).'px;';

            if ($product_details) {
                $_p_name = $product_details->getName();
                if ($helper->canShowProductDescr()) {
                    $_p_shrt_desc = Mage::helper('core/string')->truncate($product_details_full->getShortDescription());
                    $_p_shrt_image = Mage::helper('catalog/image')->init($product_details_full, 'image')->resize(50,50);
                }

                //$html_content .=  'width: '. strlen($_p_name)*8 .'px;';
            }
            else
            {
                $html_content .=  'width: '. strlen($decoded_array[$key]['text'])*8 .'px;';
            }
            $html_content .=  '"><div class="pro-detail-div">';


            if ($product_details) {
                $_p_price = $_coreHelper->currency($product_details->getFinalPrice(),true,false);
                /** check if product is in stock */
                /** $stockItem = $product_details->getStockItem();
                if($stockItem->getIsInStock())
                 */
                $html_content .=  '<div class="left-detail">';
                if($product_details->isAvailable())
                {
                    if ($helper->getUseFullProdUrl()) {
                        $_p_url = $helper::getFullProductUrl($product_details);
                        //$_p_url = $helper->getFullProductUrl($product_details);
                    }
                    else {
                        $_p_url = $product_details->getProductUrl();
                    }

                    $html_content .= '<h2><a href=\''.$_p_url.'\' target="_blank">'.$_p_name.'</a></h2>';
                }
                else
                {
                    $html_content .= '<h2>'.$_p_name.'</h2>';
                    $html_content .= '<div class="out-of-stock"><span>'. $helper->__('Out of stock') .'</span></div>';
                }
                if ($helper->canShowProductDescr()) {
                    $html_content .= '<div class="desc"><img src="'.$_p_shrt_image.'" alt="product image"/>'.$_p_shrt_desc.'</div>';
                }

                if($product_details->getFinalPrice()){
                    if ($product_details->getPrice()>$product_details->getFinalPrice()){
                        $regular_price = $_coreHelper->currency($product_details->getPrice(),true,false);
                        $_p_price = '<div class="old-price">'.$regular_price.'</div>'.$_p_price;
                    }
                    $html_content .= '<div class="price">'.$_p_price.'</div>';
                }
                if ($helper->canShowAddToCart()) {
                    $html_content .= $this->getAddToCartHtml($product_details_full);
                }
                $html_content .= '<div class="row"><div class="col-sm-12"><a href="'.$_p_url.'" class="lookbook_see_product button button-fourth large link">SEE PRODUCT</a></div></div>';
                $html_content .= '</div>';

            }
            else
            {
                //$html_content .= '<div>Product with SKU "'.$decoded_array[$key]['text'].'" doesn\'t exists.</div>';
                $html_content .= '<div><a href=\''.$decoded_array[$key]['href'].'\'>'.$decoded_array[$key]['text'].'</a></div>';
            }
            $html_content .= '</div></div>';

            $decoded_array[$key]['text'] = $html_content;
        }
        $result = $decoded_array;
        return $result;
    }

}