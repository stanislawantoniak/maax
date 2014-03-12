<?php
/**
 * @category SolrBridge
 * @package SolrBridge_Solrsearch
 * @author	Hau Danh
 * @copyright	Copyright (c) 2011-2014 Solr Bridge (http://www.solrbridge.com)
 *
 */
class SolrBridge_Solrsearch_Model_Resource_Thread_Process extends SolrBridge_Solrsearch_Model_Resource_Thread_Abstract {
	public function startThread($command, array $options = null) {
		//$process = popen ( $command, 'r' );
		//stream_set_blocking ( $process, false );
		$process = exec ( $command );
		return $process;
	}
	public function closeThread($thread) {
		pclose ( $thread );
	}
	public function prepareThreadCommand($params, $options) {
		$scriptPath = isset ( $options ['scriptPath'] ) ? $options ['scriptPath'] : null;
		$process = ! empty ( $options ['process'] ) ? $options ['process'] : 'php';
		
		if (! $scriptPath || ! file_exists ( $scriptPath )) {
			throw new \Exception ( $scriptPath . ' does not exists.' );
		}
		
		//$args = str_replace ( '&', '\\&', http_build_query ( ( array ) $params ) );
		
		$argsString = '';
		
		foreach ($params as $k=>$v)
		{
			$argsString .= ' -'.$k.' '.$v;
		}
		$logPath = dirname($scriptPath);
		//$logPath = '/'.trim($logPath, '/').'/../var/log/solrbridge-single-update-log-'.time().'.log';
		$logPath = '/dev/null';
		return "{$process} {$scriptPath} {$argsString} > {$logPath} &";
	}
}