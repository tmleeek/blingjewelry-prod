<?php
class Treepodia_Video_VideositemapController extends Mage_Core_Controller_Front_Action
{
	public function indexAction ()
	{	
		if( !Mage::getStoreConfig('video/general/enabled') )
			return;
		$pageId = '';
		if( isset($_GET['pageId']) )
			$pageId = $_GET['pageId'];
		$url = "http://api.treepodia.com/sitemap/".Mage::getStoreConfig('video/general/store_uuid')."/sitemap.xml";
		if ( !empty($pageId) )
			$url = "http://api.treepodia.com/sitemap/".Mage::getStoreConfig('video/general/store_uuid')."/sitemap.xml?pageId=".$pageId;
		$this->getResponse()->setRedirect($url, $code = 301);
	}
}
?>