<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 *
 */

class Celebros_Salesperson_Model_SalespersonAnalyticsApi extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('salesperson/salespersonAnalyticsApi');
    }

	/**
	 * Retrieve salesperson session
	 *
	 * @return Mage_Catalog_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('salesperson/session');
	}

	public function getAnlxSearchResultFunction(){

		if (Mage::getStoreConfig('salesperson/anlx_settings/dc') == ""||
			Mage::getStoreConfig('salesperson/anlx_settings/cid') == "")
				return "";

		/*$observer = array('ssid' => Mage::Helper('salesperson')->getSalespersonApi()->results->SearchInformation->SessionId, 'logHandle' => Mage::Helper('salesperson')->getSalespersonApi()->results->GetLogHandle());
		Mage::getModel('salesperson/observer')->sendResultAnlxInfo($observer);*/

		$dca = Mage::getStoreConfig('salesperson/anlx_settings/dc');
		$cid = Mage::getStoreConfig('salesperson/anlx_settings/cid');
		$results = Mage::Helper('salesperson')->getSalespersonApi()->results;
		$sessionId = $this->_getSession()->getSessionId();

		return Mage::getModel('salesperson/api_anlx_analyticsFunctions', array("G_DATA_COLLECTOR_ADDRESS" => $dca, "G_CUSTOMER_ID" => $cid, "G_CUSTOMER_NAME" => $cid, "G_PUBLIC_KEY" => "" ) )
			->Celebros_Analytics_SearchResults(
			$results->SearchInformation->SessionId,//sSearchHandle
	    	$results->GetLogHandle(),//sLogHandle
	    	$sessionId,//sUserID
	    	'1',//sGroupID
	    	$sessionId,//sWebSessionID
	    	isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'',//sReferrer
	    	(bool)Mage::getStoreConfig('salesperson/anlx_settings/protocol_connection'),//bIsSSL
    		true//bFromQwiser
    		);
	}
	
	protected function makeWebRequest($url) {
	
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);						
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT,1);
		$curlResult = curl_exec($curl);
		$curlError = curl_error($curl);
		$curlInfo = curl_getinfo($curl);
		curl_close($curl);
		if(!empty($curlError)) {
			Mage::throwException('Celebros Analytics: ' . $curlError .' Request Url: ' . $uri);
		}	
	}
}