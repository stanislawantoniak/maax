<?php
session_start();
ini_set('memory_limit', '2040M');
/**
 * @category SolrBridge
 * @package Solrbridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 * BACKUP: To backup all solrbridge code please run php solrbridge.php -upgrade
 *
 */
require_once 'abstract.php';
/**
 * This class used for backup all solrbridge code
 * This is the alpha class, not yet using
 * @author haudanh
 *
 */
class Solrsearch_Shell_Upgrade
{
	public function backup()
	{
		$baseDir = '/'.trim(Mage::getBaseDir(), '/');
		$backUpPath = $baseDir.'/var/Magento_Solr_Bridge_Backup';
		if (file_exists($backUpPath)) {
			rmdir($backUpPath);
		}

		//check if the backup directory exist
		if (!file_exists($solrBridgePath)) {
			mkdir($backUpPath);
		}

		$backupPaths = array(
			$baseDir.'/app/code/community/WebMods' => $backUpPath.'/app/code/community/WebMods',
			$baseDir.'/js' => $backUpPath.'/js',
			$baseDir.'/app/design/frontend' => $backUpPath.'/app/design/frontend',
			$baseDir.'/app/design/adminhtml' => $backUpPath.'/app/design/adminhtml',
			$baseDir.'/skin/frontend' => $backUpPath.'/skin/frontend',
			$baseDir.'/shell' => $backUpPath.'/shell'
		);

		foreach ($backupPaths as $src => $dest) {
			self::rcopy($src, $dest);
		}
	}

	static public function rcopy($src, $dst)
	{
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					self::rcopy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					echo 'Copy '.$src . '/' . $file .' TO '.$dst . '/' . $file."\n";
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
}

class Solrsearch_Shell_Indexer extends Mage_Shell_Abstract{

	public $ultility = null;

    public $indexer = null;

    public function __construct() {
        parent::__construct();
        //Init Ultility Model
        $this->ultility = Mage::getModel('solrsearch/ultility');
        $this->indexer = Mage::getResourceModel('solrsearch/indexer');
    }
    /**
     * Get setting value
     * @param string $key
     */
    public function getSetting($key)
    {
    	return Mage::helper('solrsearch')->getSetting($key);
    }

    public function runSynchronize($solrCore, $totalMagentoProducts, $sleep = 0)
    {
    	if (!$sleep) {
    		sleep(30);
    	}else{
    		sleep($sleep);
    	}
    	Mage::getResourceModel('solrsearch/solr')->synchronizeData($solrCore);
    }

    public function runThread($threadId, $solrurl)
    {
    	$threadPath = $this->ultility->getThreadPath();
    	$threadFilePath = $threadPath.'/'.$threadId;

    	if ( file_exists($threadFilePath) ) {
    		$jsonDataBinary = file_get_contents($threadFilePath);

    		$postFields = array('stream.body'=>$jsonDataBinary);

    		$updateurl = $solrurl.'/update/json?wt=json';

    		Mage::getResourceModel('solrsearch/solr')->doRequest($updateurl, $postFields);
    	}
    	unlink($threadFilePath);
    	return true;
    }

    public function coreNotFoundMessage($solrcore)
    {
        return Mage::helper('solrsearch')->__('The solr core %s not found', $solrcore);
    }

    /*
     * Run script
     */
    public function run() {

    	//Clear logs table
    	$logTableName = $this->ultility->getLogTable();
    	$writeConnection = $this->ultility->getWriteConnection();
    	if (Mage::getResourceModel('solrsearch/solr')->isTableExists($logTableName)) {
    		$truncateSql = "TRUNCATE TABLE {$logTableName}";
    		$writeConnection->query($truncateSql);
    	}

    	//Show help information
        if ($this->getArg('info') || $this->getArg('help'))
        {
            $this->printInfo();
        }
        else if($this->getArg('upgrade')) {
        	$upgrade = new Solrsearch_Shell_Upgrade();
        	$upgrade->backup();
        }
        else if ($this->getArg('generatethumb'))
        {
        	//Get which solrcore tobe reindex/update
        	$solrcore = $this->getArg('generatethumb');
        	//Get available solr cores(english, german, ...)
        	$availableCores = array_keys($this->ultility->getAvailableCores());
        	if (in_array($solrcore, $availableCores))
        	{
        		//run indexing for core $solrcore
        		$this->generateThumbs($solrcore);
        	}
        }
        else if ($this->getArg('updateindex'))
        {
            //Get which solrcore tobe reindex/update
        	$solrcore = $this->getArg('updateindex');
            //Get available solr cores(english, german, ...)
            $availableCores = array_keys($this->ultility->getAvailableCores());
            if (in_array($solrcore, $availableCores))
            {
                //run indexing for core $solrcore
                $this->runUpdateindexByCore($solrcore);
            }
            else
            {
            	echo $this->coreNotFoundMessage($solrcore);
            }

        }
        else if($this->getArg('runindexbypage'))
        {
        	$solrcore = $this->getArg('runindexbypage');
        	if($this->getArg('page')){
        		$page = $this->getArg('page');
        		if (intval($page) > 0) {
        			$availableCores = array_keys($this->ultility->getAvailableCores());

        			if (in_array($solrcore, $availableCores))
        			{
        				//run indexing for core $solrcore
        				$this->runIndexingByCoreByPage($solrcore, $page);
        			}
        			else
        			{
        				echo $this->coreNotFoundMessage($solrcore);
        			}
        		}
        	}
        }
        else if ($this->getArg('reindex'))
        {
        	//Get which solrcore tobe reindex/update
        	$solrcore = $this->getArg('reindex');
        	//Get available solr cores(english, german, ...)
        	$availableCores = array_keys($this->ultility->getAvailableCores());
        	if (in_array($solrcore, $availableCores))
        	{
        		//run indexing for core $solrcore
        		$this->runReindexByCore($solrcore);
        	}
        	else
        	{
        		echo $this->coreNotFoundMessage($solrcore);
        	}

        }
        else if ($this->getArg('truncate'))
        {
            //Get which solrcore to be truncated
        	$solrcore = $this->getArg('truncate');
            $availableCores = array_keys($this->ultility->getAvailableCores());
            if (in_array($solrcore, $availableCores))
            {
                //run truncate index data for core $solrcore
                $this->truncateSolrCore($solrcore);
            }
            else
            {
                echo $this->coreNotFoundMessage($solrcore);
            }
        }
        else if($this->getArg('updatecategory')) { //Update category
        	$categoryid = $this->getArg('updatecategory');
        	if (
        	!empty($categoryid) &&
        	is_numeric($categoryid))
        	{
        		$this->updateCategoryByCore($categoryid);
        	}else{
        		throw new Exception('Category id or solr core not found or wrong solrcore');
        	}

        }
        else if ($this->getArg('updatesingle')) //Update single product
        {
        	$productid = $this->getArg('updatesingle');
        	if ( !empty($productid) )
        	{
        		$this->updateSingleProduct($productid);
        	}else{
        		throw new Exception('Product id or solr core not found or wrong solrcore');
        	}
        }
        else if ($this->getArg('updatemass')) //Mass update products
        {
        	$productids = $this->getArg('updatemass');
        	if ( !empty($productids) )
        	{
        		$productIdsArray = explode('_', $productids);
        		if (is_array($productIdsArray) && !empty($productIdsArray)) {
        			foreach ($productIdsArray as $productId){
        				if (!empty($productId) && is_numeric($productId)) {
        					$this->updateSingleProduct($productId);
        				}
        			}
        		}
        	}else{
        		throw new Exception('Product ids or solr core not found or wrong solrcore');
        	}
        }
        else if ($this->getArg('runthread'))
        {

        	$threadId = $this->getArg('runthread');
        	$solrurl = $this->getArg('solrurl');
        	$storeid = $this->getArg('storeid');
        	$this->runThread($threadId, $solrurl, $storeid);
        }
        else if ($this->getArg('synchronize'))
        {
        	$totalMagentoProducts = $this->getArg('totalmagentoproducts');
        	$this->runSynchronize($this->getArg('synchronize'), $totalMagentoProducts);
        }
        else if ($this->getArg('savesearchterm')) //Update single product
        {
            $searchText = $this->getArg('savesearchterm');
            $resultnum = $this->getArg('resultnum');
            $storeid = $this->getArg('storeid');
            try
            {
                $this->ultility->saveSearchTerm($searchText, $resultnum, $storeid);
            }
            catch (Execption $e)
            {
                $this->ultility->writeLog($e->getMessage(), $storeid, '', '', true);
            }
        }
        else
        {
            echo Mage::helper('solrsearch')->__('run indexing all cores'); // not yet implemented will do it in the furture
        }

        exit;
    }
	/**
	 * This function run to update solr document when magento product got updated
	 * @param int $productid
	 */
    public function updateSingleProduct($productid)
    {
    	$storeid = 0;
    	if (!is_numeric($productid))
    	{
    		$info = explode('_', $productid);
    		if(isset($info[0]) && $info[0] > 0 && isset($info[1]) && $info[1] > 0)
    		{
    			$productid = $info[0];
    			$storeid = $info[1];
    		}
    	}
    	Mage::getResourceModel('solrsearch/solr')->updateSingleProduct($productid, $storeid);
    }
	/**
	 * This function run to remove related documents when magento category got updated
	 * @param int $categoryid
	 */
    public function updateCategoryByCore($categoryid)
    {
    	Mage::getResourceModel('solrsearch/solr')->updateSingleCategory($categoryid);
    }
	/**
	 * This function used to regenerate thumbs without reindex solr index
	 * @param string $solrcore
	 */
    public function generateThumbs($solrcore)
    {
    	$request = array(
    		'solrcore' => $solrcore,
    		'starttime' => time(),
    	);

    	while (true)
    	{
    		try{
	    		$this->indexer->start($request);
	    		$this->indexer->generateThumbs();
	    		$response = $this->indexer->end();
	    		if (isset($response['message']) && !empty($response['message']))
	    		{
	    			$messages = @implode("\n", $response['message'])."\n";
	    			echo $messages;
	    			$storeid = isset($response['currentstoreid']) ? $response['currentstoreid'] : 0;
	    			$percent = isset($response['percent']) ? $response['percent'] : 0;
	    			$this->ultility->writeLog($messages, $storeid, $solrcore, $percent, true);
	    		}
	    		if (isset($response['status']) && $response['status'] == 'FINISH')
	    		{
	    			break;
	    		}
	    		if (isset($response['status']) && $response['status'] == 'ERROR')
	    		{
	    			break;
	    		}
	    		$request = $response;
    		}catch (Exception $e){
    		    echo $e->getMessage();
    			$this->ultility->writeLog($e->getMessage(), 0, $solrcore, 100, true);
    			break;
    		}
    	}
    	exit();
    }

    /**
     * Run data indexing by solrcore
     * @param string $coreName
     */
    public function runUpdateindexByCore( $solrcore = 'english') {

        $this->totalSolrDocuments = (int) Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);

        $request = array(
        		'solrcore' => $solrcore,
        		'action' => ($this->totalSolrDocuments > 0) ? 'UPDATEINDEX' : 'REINDEX',
        		'starttime' => time(),
        );

        while (true) {
			try{
				$this->indexer = Mage::getResourceModel('solrsearch/indexer');

	        	$this->indexer->start($request);

	        	$this->indexer->execute();

	        	$this->indexer->checkIndexStatus();

	        	$response = $this->indexer->end();

	        	if (isset($response['message']) && !empty($response['message']))
	        	{
	        		$messages = @implode("\n", $response['message'])."\n";
	    			echo $messages;
	    			$storeid = isset($response['currentstoreid']) ? $response['currentstoreid'] : 0;
	    			$percent = isset($response['percent']) ? $response['percent'] : 0;
	    			$this->ultility->writeLog($messages, $storeid, $solrcore, $percent, true);
	        	}

	        	if (isset($response['status']) && $response['status'] == 'FINISH')
	        	{
	        		break;
	        	}
	        	if (isset($response['status']) && $response['status'] == 'ERROR')
	        	{
	        		break;
	        	}
	        	$request = $response;
	        	unset($this->indexer);
			}catch (Exception $e){
			    echo $e->getMessage();
				$this->ultility->writeLog($e->getMessage(), 0, $solrcore, 100, true);
				break;
			}
        }
        exit();
    }

    public function waiting($params)
    {
    	$solrcore = $params['solrcore'];
    	$totalSolrDocuments = (int) Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);
    	if ($totalSolrDocuments < $params['totalmagentoproducts']) {
    	    while (true)
    	    {
    	    	sleep(1);
    	    	echo '.';
    	    	$totalSolrDocuments = (int) Mage::helper('solrsearch')->getTotalDocumentsByCore($solrcore);
    	    	if ($totalSolrDocuments >= (int) $params['totalmagentoproducts']) {
    	    		echo '.'."\n";
    	    		echo Mage::helper('solrsearch')->__('Indexed %s products into Solr core (%s) successfully', $params['totalmagentoproducts'], $solrcore)."\n";
    	    		break;
    	    	}
    	    }
    	}
    	exit();
    }

    public function runIndexingByCoreByPage($solrcore = 'english', $page = 0)
    {
    	if ($page > 0) {
    		$request = array(
    				'solrcore' => $solrcore,
    				'action' => 'REINDEX',
    				'starttime' => time(),
    				'page' => $page,
    		);

    		$indexer = Mage::getResourceModel('solrsearch/indexer');

    		$indexer->start($request);

    		$indexer->execute();

    		$indexer->checkIndexStatus();

    		$response = $indexer->end();
    	}
    }


    /**
     * Run data indexing by solrcore
     * @param string $coreName
     */
    public function runReindexByCore( $solrcore = 'english') {
		//Empty index
    	$request = array(
        		'solrcore' => $solrcore,
        		'action' => 'TRUNCATE',
        		'starttime' => time(),
        );
    	$this->indexer->start($request);

    	$this->indexer->truncateIndex();

    	$response = $this->indexer->end();

    	if (isset($response['message']) && !empty($response['message']))
    	{
    		$messages = @implode("\n", $response['message']);
    		echo $messages ."\n";
    	}

    	$progress = 0;

    	//Start reindexing the whole index
    	$request = array(
    			'solrcore' => $solrcore,
    			'action' => 'REINDEX',
    			'starttime' => time(),
    			'count' => 0,
    	);

    	while (true) {
    		try{
    			$this->indexer = Mage::getResourceModel('solrsearch/indexer');

    			$this->indexer->start($request);

    			$this->indexer->execute();

    			$this->indexer->checkIndexStatus();

    			$response = $this->indexer->end();

    			if (isset($response['message']) && !empty($response['message']))
    			{
    				$messages = @implode("\n", $response['message'])."\n";
    				echo $messages;
    				$storeid = isset($response['currentstoreid']) ? $response['currentstoreid'] : 0;
    				$percent = isset($response['percent']) ? $response['percent'] : 0;
    				$this->ultility->writeLog($messages, $storeid, $solrcore, $percent, true);
    			}

    			if (isset($response['status']) && $response['status'] == 'FINISH')
    			{
    				Mage::getResourceModel('solrsearch/solr')->synchronizeData($solrcore);
    				break;
    			}
    			if (isset($response['status']) && $response['status'] == 'ERROR')
    			{
    				break;
    			}

    			if (isset($response['status']) && $response['status'] == 'WAITING')
    			{
    				$this->waiting($response);
    				break;
    			}

    			$request = $response;
    			$request['action'] = 'REINDEX';

    			/*
    			$this->ultility
    			->getThreadManager()
    			->addThread(array('runindexbypage' => $solrcore, 'page' => ($response['page'])))
    			->run();
    			*/

    			//$request['page'] = ($request['page'] + 1);
    			unset($this->indexer);
    		}catch (Exception $e){
    		    echo $e->getMessage();
    			$this->ultility->writeLog($e->getMessage(), 0, $solrcore, 100, true);
    			break;
    		}
    	}
    	exit();
    }

    /**
     * Truncate Solr Data Index
     * @param string $solrcore
     */
    public function truncateSolrCore($solrcore = 'english')
    {
    	//get solr core
    	$request = array(
        		'solrcore' => $solrcore,
        		'action' => 'TRUNCATE',
        		'starttime' => time(),
        );
    	$this->indexer->start($request);

    	$this->indexer->truncateIndex();

    	$response = $this->indexer->end();

    	if (isset($response['message']) && !empty($response['message']))
    	{
    		$messages = @implode("\n", $response['message']);
    		echo $messages ."\n";
    	}
    	exit();
    }

    public function printInfo() {
        $cores = $this->ultility->getAvailableCores();
        foreach ($cores as $key=>$core) {
        	if ( isset($core['stores']) && strlen(trim($core['stores'], ',')) > 0 )
        	{
        		echo sprintf('%-30s', $key);
        		echo sprintf('%-30s', 'active');
        		echo $core['label'] . "\n";
        	}else{
        		echo sprintf('%-30s', $key);
        		echo sprintf('%-30s', 'inactive');
        		echo $core['label'] . "\n";
        	}
        }
    }
}

$shell = new Solrsearch_Shell_Indexer();
$shell->run();