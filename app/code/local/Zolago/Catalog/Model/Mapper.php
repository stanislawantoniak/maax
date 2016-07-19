<?php

class Zolago_Catalog_Model_Mapper extends Mage_Core_Model_Abstract {

    /**
     * path to pictures
     */
    protected $_path;

    /**
     * vendor products collection
     */
    protected $_collection;

    protected $_pidList;

    protected function _construct() {
        $this->_init('zolagocatalog/mapper');
        parent::_construct();
    }
    public function setCollection($collection) {
        $this->_collection = $collection;
    }
    public function setPath($path) {
        $this->_path = $path;
    }
    public function _getFileList() {
        $list = array();
        $dir = dir($this->_path);
        while (false !== ($entry = $dir->read())) {
            if (!in_array($entry,array('.','..','.tmb','.quarantine'))) {
                /* pokombinujemy później
                $tmp = $entry;
                for ($a=strlen($entry)-1;$a>0;$a--) {
                    $pom = array();
                    $pom[$entry[$a]] = $tmp;
                    $tmp = $pom;
                }
                $list[$entry[0]] = $tmp;
                */
                $list[] = $entry;
            }
        }
        $dir->close();
        return $list;
    }
    public function mapByFile($importlist, $removeImages = false, $fullList = array()) {
        $hlp = Mage::helper('zolagocatalog');
        $response = array();
        $count = 0;
        $message = "";
        $storeid = 0;
        $pidList = array();

        $toDelete = array();
        foreach ($this->_collection as $item) {
            $skuv = $item->getData(Mage::getStoreConfig('zolagoos/vendor/vendor_sku_attribute'));
            $pid = $item->getData('entity_id');
            if (!$skuv) {
                continue;
            }
            $updateFlag = false;
            if (!empty($importlist[$skuv])) {
                // found file
                foreach ($importlist[$skuv] as $filename) {
                    $imagefile=$this->_copyImageFile($filename[1]);

                    if(!$imagefile){
                        $message[] = $hlp->__("File:")." <b>" . $filename[1] . "</b> " . $hlp->__("not found among uploaded");
                    } else
                    {
                        //add to gallery
                        if ($this->_addImageToGallery($pid,$storeid,$imagefile,trim($filename[2]),$filename[3])) {
                            // remove image from upload area
                            //$toDelete[] =  $this->_path.'/'.$filename[1];

                            $updateFlag = true;
                            $count ++;
                        } else {
                            $message[] = $hlp->__("An error occured while adding image")." <b>" . $filename[1] . "</b> ".$hlp->__("to gallery");
                        }
                    }
                }
            }
            if ($updateFlag) {
                $this->_changeCheckFlag($pid);
                $pidList[] = $pid;
            }
        }
        if ($pidList) {
            $this->_savePid($pidList);
        }

        $path = $this->_path;
        if ($removeImages) {
            if (!empty($fullList)) {
                foreach ($fullList as $fullListItem) {
                    if (!empty($fullListItem)) {
                        foreach ($fullListItem as $filename) {
                            if (isset($filename[1])) {
                                $toDelete[] = $path . '/' . $filename[1];
                            }
                        }
                    }
                }
            }

            if ($toDelete) {
                foreach ($toDelete as $file) {
                    @unlink($file);
                }
            }
        }
        $response['count'] = $count;
        $response['message'] = $message;
        $response['pid'] = $pidList;
        return $response;
    }
    public function getPidList() {
        return $this->_pidList;
    }
    protected function _savePid($pidList) {
        $this->_pidList = $pidList;
//        Mage::getSingleton('core/session')->setMappedEntities($pidList);
    }
    protected function _changeCheckFlag($pid) {
        $_product = Mage::getModel('catalog/product')->load($pid);
        $_product->setGalleryToCheck(1);
        $_product->getResource()->saveAttribute($_product, 'gallery_to_check');

    }
    public function mapByName($list) {
        $hlp = Mage::helper('zolagocatalog');
        $response = array();

        $count = 0;
        $message = "";

        $storeid = 0;
        $pidList = array();

        if ($this->_collection->getSize() ==0){
            $message[] = $hlp->__("Sorry, no matches found for the selected images");
        }
        foreach ($this->_collection as $item) {
            $updateFlag = false;
            $skuv = $item->getData(Mage::getStoreConfig('zolagoos/vendor/vendor_sku_attribute'));

            $pid = $item->getData('entity_id');
            $label = $item->getData('name');
            if (!$skuv) {
                continue;
            }

            foreach ($list as $file) {
                if (!strncmp(trim($skuv),trim($file),strlen($skuv))) {
                    $imagefile=$this->_copyImageFile($file);
                    if(!$imagefile)
                    {
                        $message[] = $hlp->__("File:")." <b>" . $file . "</b> ".$hlp->__("not found among uploaded");
                    } else {
                        //add to gallery

                        if ($this->_addImageToGallery($pid,$storeid,$imagefile,'',$label)) {
                            // remove image from upload area
                            @unlink($this->_path.'/'.$file);
                            $count ++;
                            $updateFlag = true;
                        } else {
                            $message[] = $hlp->__("An error occurred while adding image")." <b>" . $file . "</b> ".$hlp->__("to gallery");
                        }
                    }

                }
            }
            if ($updateFlag) {
                $this->_changeCheckFlag($pid);
                $pidList[] = $pid;
            }
        }
        if ($pidList) {
            $this->_savePid($pidList);
        }
        $response['count'] = $count;
        $response['message'] = $message;
        $response['pid'] = $pidList;

        return $response;

    }
    /**
     * imageInGallery
     * @param int $pid  : product id to test image existence in gallery
     * @param string $imgname : image file name (relative to /products/media in magento dir)
     * @return bool : if image is already present in gallery for a given product id
     */
    protected function _getImageId($pid,$attid,$imgname)
    {
        $resource = Mage::getSingleton('core/resource');
        $t=$resource->getTableName('catalog_product_entity_media_gallery');

        $sql="SELECT $t.value_id FROM $t ";
        $sql.=" WHERE value='%s' AND entity_id='%s' AND attribute_id='%s'";
        $sql = sprintf($sql,$imgname,$pid,$attid);
        $readConnection = $resource->getConnection('core_read');
        $result = $readConnection->fetchAll($sql);
        if (!empty($result[0])) {
            $imgid = $result[0]['value_id'];
        } else {
            $imgid = null;
        }
        $writeConnection = $resource->getConnection('core_write');

        if($imgid==null)
        {
            // insert image in media_gallery
            $sql="INSERT INTO $t
                 (attribute_id,entity_id,value)
                 VALUES
                 ('%s','%s','%s')";
            $sql = sprintf($sql,$attid,$pid,$imgname);

            $writeConnection->query($sql);
            $imgid = $writeConnection->lastInsertId();
        }
        else
        {
            $sql="UPDATE $t
                 SET value='%s'
                 WHERE value_id='%s'";
            $sql = sprintf($sql,$imgname,$imgid);
            $writeConnection->query($sql);
        }
        return $imgid;
    }

    /**
     * adds an image to product image gallery only if not already exists
     * @param int $pid  : product id to test image existence in gallery
     * @param array $attrdesc : product attribute description
     * @param string $imgname : image file name (relative to /products/media in magento dir)
     */
    protected function _addImageToGallery($pid,$storeid,$imgname,$pos='',$imglabel='')
    {

        $resource = Mage::getSingleton('core/resource');

        $attribute_code = "media_gallery";
        $attribute_details = Mage::getSingleton("eav/config")->getAttribute('catalog_product',    $attribute_code);
        $gal_attinfo = $attribute_details->getData();

        $tg=$resource->getTableName('catalog_product_entity_media_gallery');
        $tgv=$resource->getTableName('catalog_product_entity_media_gallery_value');

        $vid=$this->_getImageId($pid,$gal_attinfo["attribute_id"],$imgname);


        if($vid!=null)
        {

            if ($pos === '') {
#get maximum current position in the product gallery
                $sql="SELECT MAX( position ) as maxpos
                     FROM $tgv AS emgv
                     JOIN $tg AS emg ON emg.value_id = emgv.value_id AND emg.entity_id = '%s'
                     WHERE emgv.store_id='%s'
                     GROUP BY emg.entity_id";
                $sql = sprintf($sql,$pid,$storeid);
                $readConnection = $resource->getConnection('core_read');
                $result = $readConnection->fetchAll($sql);
                $pos = (empty($result[0]))? 0:($result[0]['maxpos']+1);
            }
#insert new value (ingnore duplicates)

            $vinserts=array();
            $data=array();

            $sql="INSERT INTO $tgv
                 (value_id,store_id,position,disabled,label)
                 VALUES ('%d','%d','%d','%d','%s')
                 ON DUPLICATE KEY UPDATE label=VALUES(`label`),position=VALUES(`position`),disabled=1";
            $insert = sprintf($sql,$vid,$storeid,$pos,1,$imglabel);

            $writeConnection = $resource->getConnection('core_write');
            $writeConnection->query($insert);
        }
        return $vid;
    }


    protected function _getTargetName($fname)
    {
        $cname=basename($fname);
        $cname=strtolower(preg_replace("/%[0-9][0-9|A-F]/","_",rawurlencode($cname)));
        return $cname;
    }

    protected function _saveImage($imgfile,$target)
    {
        $parse = parse_url($imgfile);
        if (!isset($imgfile['scheme'])) {
            $imgfile = $this->_path.'/'.$imgfile;
            if (!file_exists($imgfile)) {
                return false;
            }
        }
        $result = copy($imgfile,$target);
        return $result;
    }

    /**
     * copy image file from source directory to
     * product media directory
     * @param $imgfile : name of image file name in source directory
     * @return : name of image file name relative to magento catalog media dir,including leading
     * directories made of first char & second char of image file name.
     */
    protected function _copyImageFile($imgfile)
    {
        $bimgfile=$this->_getTargetName($imgfile);
        //source file exists
        $i1=$bimgfile[0];
        $i2=$bimgfile[1];
        $l2d = getcwd()."/media/catalog/product/$i1/$i2";
        $te="$l2d/$bimgfile";
        $result="/$i1/$i2/$bimgfile";
        /* test for same image (disabled) */
        if(1 || !file_exists($te))
        {
            /* try to recursively create target dir */
            if(!file_exists("$l2d"))
            {
                $tst=mkdir($l2d,0755,true);
                if(!$tst)
                {
                    return false;
                }
            }

            if(!$this->_saveImage($imgfile,"$l2d/$bimgfile"))
            {
                return false;
            }
            else
            {
                @chmod("$l2d/$bimgfile",0644);
            }
        }
        /* return image file name relative to media dir (with leading / ) */
        return $result;
    }
    public function checkGallery($list) {
        $pidList = explode(',',$list);

        if ($pidList) {
            $productEntityId = Mage::getModel('catalog/product')->getResource()->getTypeId();

            $attributeImage = Mage::getModel('eav/entity_attribute')->loadByCode($productEntityId, 'image')->getAttributeId();
            $attributeSmallImage = Mage::getModel('eav/entity_attribute')->loadByCode($productEntityId, 'small_image')->getAttributeId();
            $attributeThumbnail = Mage::getModel('eav/entity_attribute')->loadByCode($productEntityId, 'thumbnail')->getAttributeId();


            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');

            $tg=$resource->getTableName('catalog_product_entity_media_gallery');
            $tgv=$resource->getTableName('catalog_product_entity_media_gallery_value');

            $sql1 = "
        SELECT
          gallery.`entity_id` AS product_id,
          gallery.`value` AS image_path,
          MIN(gallery_value.`position`) AS sort_order
        FROM
          {$tg} gallery
          JOIN {$tgv} AS gallery_value
            ON gallery.`value_id` = gallery_value.`value_id`
        WHERE gallery.`entity_id` IN (%s)
        GROUP BY gallery.`entity_id`
            ";

            $query1 = $writeConnection->query(sprintf($sql1,$list));

            $minPositions = $query1->fetchAll();

            $minPositionsData = array();
            if(!empty($minPositions)){
                foreach($minPositions as $minPosition){
                    $minPositionsData[$minPosition['product_id']] = $minPosition['sort_order'];
                }
            }

            $sendProductToIndexAndSolr = array();
            foreach ($pidList as $pid) {
                /**
                 * @todo full product load not required;
                 * use mass action save
                 */
                $_product = Mage::getModel('catalog/product')->load($pid);
                $_product->setGalleryToCheck(0);
                $_product->getResource()->saveAttribute($_product, 'gallery_to_check');

                if($_product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                    $sendProductToIndexAndSolr[] = $pid;
                }

                //Pierwsze wgrywane zdjęcie musi mieć ustawione dodatkowe parametry w galerii (base image, small image, thumbnail
                $minPosition = isset($minPositionsData[$pid]) ? (int)$minPositionsData[$pid] : 0;

                $sqlI = "INSERT IGNORE INTO `catalog_product_entity_varchar`   (entity_type_id, attribute_id,store_id,entity_id)
                VALUES ({$productEntityId},{$attributeImage},0,%s) , ({$productEntityId},{$attributeSmallImage},0,%s),({$productEntityId},{$attributeThumbnail},0,%s);";

                $writeConnection->query(sprintf($sqlI,$pid,$pid,$pid));

                $sql2 = "UPDATE catalog_product_entity_media_gallery AS mg,
                catalog_product_entity_media_gallery_value AS mgv,
                catalog_product_entity_varchar AS ev
                SET ev.value = mg.value
                WHERE  mg.value_id = mgv.value_id
                AND mg.entity_id = ev.entity_id
                  AND ev.attribute_id IN ({$attributeImage}, {$attributeSmallImage}, {$attributeThumbnail})
                  AND mgv.position = {$minPosition}
                  AND mg.entity_id=%s;
                ";

                $writeConnection->query(sprintf($sql2,$pid));
            }


            $sql = 'UPDATE '.$tgv.' as gv '.
                   ' INNER JOIN '.$tg.' as g ON g.value_id = gv.value_id '.
                   ' SET gv.disabled = 0 '.
                   ' WHERE g.entity_id in (%s)';
            $writeConnection->query(sprintf($sql,$list));



            $indexer = Mage::getResourceModel('catalog/product_indexer_eav_source');
            /* @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source */
            $indexer->reindexEntities($sendProductToIndexAndSolr);
            Mage::dispatchEvent(
                "catalog_converter_price_update_after",
                array(
                    "product_ids" => $sendProductToIndexAndSolr
                )
            );
        }

    }

}