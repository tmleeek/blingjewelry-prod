<?php
ini_set('memory_limit','1024M');
set_time_limit(7200);
ini_set('max_execution_time',7200);
ini_set('display_errors', 1);
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
//include_once("createZip.php");
class Celebros_Salesperson_Model_ObserverLarge extends Celebros_Salesperson_Model_Observer
{
	protected $_limit = 50000;
	protected static $_profilingResults;
	protected $bExportProductLink = true;
	protected $_product_entity_type_id = null;
	protected $_category_entity_type_id = null;

	function __construct() {

		$this->_fStore_id = 2;
		$this->export_config($this->_fStore_id);
		$this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->_product_entity_type_id = $this->get_product_entity_type_id();
		$this->_category_entity_type_id = $this->get_category_entity_type_id();
	}
	
	public function export_celebros() {
		self::startProfiling(__FUNCTION__);
		$this->export_tables();
		$this->export_products();
		$this->zipLargeFiles();
		$this->ftpfile();
		
		self::stopProfiling(__FUNCTION__);
		
		$html = self::getProfilingResultsString();
		$this->log_profiling_results($html);
		echo $html;
	}
	
	protected function log_profiling_results($html) {
		$fh = $this->create_file("profiling_results.log", "html");
		$this->write_to_file($html, $fh);
	}
	
	protected function get_status_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_product_entity_type_id} AND attribute_code='status'";
		return $this->_read->fetchOne($sql);
	}
		
	protected function get_product_entity_type_id() {
		$table = $this->getTableName("eav_entity_type");
		$sql = "SELECT entity_type_id
		FROM {$table}
		WHERE entity_type_code='catalog_product'";
		return $this->_read->fetchOne($sql);
	}	
	
	protected function get_category_entity_type_id() {
		$table = $this->getTableName("eav_entity_type");
		$sql = "SELECT entity_type_id
		FROM {$table}
		WHERE entity_type_code='catalog_category'";
		return $this->_read->fetchOne($sql);
	}	
	
	protected function get_visibility_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_product_entity_type_id} AND attribute_code='visibility'";
		return $this->_read->fetchOne($sql);
	}
	
	protected function get_category_name_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_category_entity_type_id} AND attribute_code='name'";
		return $this->_read->fetchOne($sql);
	}

	protected function get_category_is_active_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_category_entity_type_id} AND attribute_code='is_active'";
		return $this->_read->fetchOne($sql);
	}
	
	protected function export_tables() {
		self::startProfiling(__FUNCTION__);
		
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id, attribute_code, backend_type, frontend_input
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}";

		$this->export_table($sql, "attributes_lookup");

		$table = $this->getTableName("catalog_product_entity");
		$sql = "SELECT entity_id, type_id, sku
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}";
		$this->export_table($sql, $table);
		
		$status_attribute_id = $this->get_status_attribute_id();
		$table = $this->getTableName("catalog_product_entity_int");
		$sql = "SELECT DISTINCT entity_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0
				AND attribute_id = {$status_attribute_id}
				AND value = 2";
		$this->export_table($sql, "disabled_products");

		$visibility_attribute_id = $this->get_visibility_attribute_id();
		$table = $this->getTableName("catalog_product_entity_int");
		$sql = "SELECT DISTINCT entity_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0
				AND attribute_id = $visibility_attribute_id
				AND value = 1";
		$this->export_table($sql, "not_visible_individually_products");		
		
		$table = $this->getTableName("catalog_product_entity_varchar");
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);
		
		$table = $this->getTableName("catalog_product_entity_int");
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);

		$table = $this->getTableName("catalog_product_entity_text");
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);

		$table = $this->getTableName("catalog_product_entity_decimal");
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);

		$table = $this->getTableName("catalog_product_entity_datetime");
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);

		$table = $this->getTableName("eav_attribute_option_value");
		$sql = "SELECT option_id, value
				FROM {$table}
				WHERE store_id = 0";
		$this->export_table($sql, $table);
		
		$table = $this->getTableName("eav_attribute_option");
		$sql = "SELECT option_id, attribute_id
				FROM {$table}";
		$this->export_table($sql, $table);
		
		$table = $this->getTableName("catalog_category_product");
		$sql = "SELECT category_id, product_id
				FROM {$table}";
		$this->export_table($sql, $table);
		
		$table = $this->getTableName("catalog_category_entity");
		$sql = "SELECT entity_id, parent_id, path
				FROM {$table}";
		$this->export_table($sql, $table);

		$name_attribute_id = $this->get_category_name_attribute_id();
		$table = $this->getTableName("catalog_category_entity_varchar");
		$sql = "SELECT entity_id, value
				FROM {$table}
				WHERE attribute_id = {$name_attribute_id}
				AND store_id =0";
		$this->export_table($sql, "category_lookup");
		
		$is_active_attribute_id = $this->get_category_is_active_attribute_id();
		$table = $this->getTableName("catalog_category_entity_int");
		$sql = "SELECT entity_id
				FROM {$table}
				WHERE `attribute_id` = {$is_active_attribute_id}
				AND value = 0
				AND entity_type_id ={$this->_category_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, "disabled_categories");

		$table = $this->getTableName("catalog_product_super_link");
		$sql = "SELECT product_id, parent_id
				FROM {$table}";
		$this->export_table($sql, $table);

		$table = $this->getTableName("catalog_product_super_attribute");
		$sql = "SELECT product_id, attribute_id
				FROM {$table}";
		$this->export_table($sql, $table);		

		self::stopProfiling(__FUNCTION__);
	}
	
	protected function export_table($sql, $filename) {
		self::startProfiling(__FUNCTION__. "({$filename})");
		
		$fh = $this->create_file($filename);
		
		$header = "";
		$str = "";
		$i = 0;
		$startFrom = $i * $this->_limit;
		$stm = $this->_read->query($sql . " LIMIT {$startFrom}, {$this->_limit}");
		
		while($row = $stm->fetch()) {
			//Build header
			if(empty($header)) {
				$header = "^" . implode("^	^",array_keys($row)) . "^" . "\r\n";
				$this->write_to_file($header, $fh);
			}
			do {
				$str .= "^" . implode("^	^",$row) . "^" . "\r\n";
			} while($row = $stm->fetch());
			$this->write_to_file($str , $fh);
			$str = "";
			$i++;
			$startFrom = $i * $this->_limit;

			$stm = $this->_read->query($sql . " LIMIT {$startFrom}, {$this->_limit}");
		}
		fclose($fh);
		self::stopProfiling(__FUNCTION__. "({$filename})");
	}
	
	protected function create_file($name, $ext = "txt") {
		self::startProfiling(__FUNCTION__);
		if (!is_dir($this->_fPath)) $dir=@mkdir($this->_fPath,0777,true);
		$filePath = $this->_fPath . DIRECTORY_SEPARATOR . $name . "." . $ext;
		if (file_exists($filePath)) unlink($filePath);
		$fh = fopen($filePath, 'ab');
		self::stopProfiling(__FUNCTION__);
		return $fh;
	}
	
	protected function write_to_file($str, $fh){
		self::startProfiling(__FUNCTION__);
		fwrite($fh, $str);
		self::stopProfiling(__FUNCTION__);
	}
	
	public function zipLargeFiles() {
		self::startProfiling(__FUNCTION__);
		
		$out = false;
		$zipPath = $this->_fPath . DIRECTORY_SEPARATOR . $this->_fileNameZip;
		
		$dh=opendir($this->_fPath);
		$filesToZip = array(); 
		while(($item=readdir($dh)) !== false && !is_null($item)){
			$filePath = $this->_fPath . DIRECTORY_SEPARATOR . $item;
			$ext = pathinfo($filePath, PATHINFO_EXTENSION);
			if(is_file($filePath) && ($ext == "txt" || $ext == "log")) {
				$filesToZip[] = $filePath;
			}
		}
		
		for($i=0; $i < count($filesToZip); $i++) {
			$filePath = $filesToZip[$i];
			$out = $this->zipLargeFile($filePath, $zipPath);
		}

		self::stopProfiling(__FUNCTION__);
		return $out ? $zipPath : false;
	}
	
	public function zipLargeFile($filePath, $zipPath)
	{
		self::startProfiling(__FUNCTION__);
		
		$out = false;
	
		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::CREATE) == true) {
			$fileName = basename($filePath);
			$out = $zip->addFile($filePath, basename($filePath));
			if(!$out) throw new  Exception("Could not add file '{$fileName}' to_zip_file"); 
			$zip->close();
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if($ext != "log") unlink($filePath);
		}
		else
		{
			throw new  Exception("Could not create zip file");
		}
		
		self::stopProfiling(__FUNCTION__);
		return $out;
	}
	
	protected function export_products() {
		self::startProfiling(__FUNCTION__);
		$this->_flushRecordsCount = 5000;
		$i = 0;
		$str = "";
		$filename = "product";
				
		$fh = $this->create_file($filename);
		
		$fields = array("id", "price", "image_link", "thumbnail", "type_id", "product_sku");		
		$attributes = array("price", "image", "thumbnail", "type");
		
		if($this->bExportProductLink) $fields[] = "link";
		if($this->bExportProductLink) $attributes[] = "url_key";
		
		if(count($fields)- 2 != count($attributes)) throw new Exception('Fields (without id):' . implode("^	^",$fields) . ' are not equal to ' . implode("^	^",$fields));
		
		$header = "^" . implode("^	^",$fields) . "^" . "\r\n";
		$this->write_to_file($header, $fh);
		
		
		//Begin the output
		$sql='select min(cpe.entity_id ) as min, max( cpe.entity_id ) as max
		from '.$this->getTableName("catalog_product_entity").' cpe';
		$result=$this->_read->fetchAll($sql);
		
		$min=$result[0]["min"];
		if(!is_null($min))
		{
			$count=$result[0]["max"]-$min+1;
			$count=$count/$this->_flushRecordsCount;
			if(!is_int($count))
				$count=ceil($count);
		
			//select the product with the attributes
			//-----------------------------
			while($count>0)
			{
				$max=$min + $this->_flushRecordsCount-1;
				$select='$products_collection=Mage::getModel("catalog/product")->setStoreId($this->_fStore_id)->getCollection()->addStoreFilter($this->_fStore_id)';
				foreach($attributes as $value)
					$select.='->addAttributeToSelect("'.$value.'")';
				$select.='->addAttributeToFilter("status",array("neq"=>2))
						->addAttributeToFilter("visibility",array("neq"=>1))
						->addFieldToFilter("entity_id",array("from"=>'.$min.',"to"=>'.$max.'));';
				
				self::startProfiling("select product range");
				eval($select);
				self::stopProfiling("select product range");
				
				$min=$max+1;
				$count--;
				//print the content
				$num+=count($products_collection);
				foreach($products_collection as $product)
				{
					//$_product->getCategoryCollection();
					//$categs = $catCollection->exportToArray();
					//foreach($categs as $cat){
						//$categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
					//}
					
					self::startProfiling("id");
					$values["id"] = $product->getentity_id();
					self::stopProfiling("id");
	
					self::startProfiling("price");
					$values["price"] = $this->getCalculatedPrice($product);
					self::stopProfiling("price");
	
					self::startProfiling("image_link");
					$values["image_link"] = $product->getMediaConfig()->getMediaUrl($product->getData("image")); 
					self::stopProfiling("image_link");
	
					self::startProfiling("thumbnail");
					$values["thumbnail"] = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(66);
					self::stopProfiling("thumbnail");
					
					self::startProfiling("type_id");
					$values["type_id"] = $product->gettype_id();
					self::stopProfiling("type_id");
					
					self::startProfiling("product_sku");
					$values["product_sku"] = $product->getSku();
					self::stopProfiling("product_sku");
					
					if($this->bExportProductLink) {
						self::startProfiling("link");
						$values["link"] = Mage::helper('catalog/product')->getProductUrl($product);
						self::stopProfiling("link");
					}
	
					$str .= "^" . implode("^	^",$values) . "^" . "\r\n";
				}
		
				//Flushing and cleaning
				unset($products_collection);
				$this->write_to_file($str , $fh);
				$str = "";
				//die("check the files");
			}
		}		
		
		fclose($fh);
		
		self::stopProfiling(__FUNCTION__);
	}
	
	protected static function startProfiling($key) {
		
		if(!isset(self::$_profilingResults[$key])) {
			$profile = new stdClass();
			$profile->average =0 ;
			$profile->count = 0;
			$profile->max = 0;
			self::$_profilingResults[$key] = $profile;
		}
		
		$profile = self::$_profilingResults[$key];
		if(isset($profile->start) && $profile->start > $profile->end) throw new Exception("The start of profiling timer '{$key}' is called before the stop of it was called");
		
		$profile->start = (float) array_sum(explode(' ',microtime()));
	}
	
	protected static function stopProfiling($key) {
		if(!isset(self::$_profilingResults[$key])) throw new Exception("The stop of profiling timer '{$key}' was called while the start was never declared");
	
		$profile = self::$_profilingResults[$key];
		if($profile->start == -1) throw new Exception("The start time of '{$key}' profiling is -1");
		
		$profile->end = (float) array_sum(explode(' ',microtime()));
		$duration = $profile->end - $profile->start;
		if($profile->max < $duration) $profile->max = $duration;
		
		$profile->average = ($profile->average * $profile->count + $duration)/($profile->count +1);
		$profile->count++;
	}
	
	protected static function getProfilingResultsString() {
		$html = "";
		if(count(self::$_profilingResults)) {
			$html.= "In sec:";
			$html.=  '<table border="1">';
			$html.=  "<tr><th>Timer</th><th>Total</th><th>Average</th><th>Count</th><th>Peak</th></tr>";
			foreach(self::$_profilingResults as $key =>$profile) {
				$total = $profile->average * $profile->count;
				$html.=  "<tr><td>{$key}</td><td>{$total}</td><td>{$profile->average}</td><td>{$profile->count}</td><td>{$profile->max}</td></tr>";
			}
			$html.=  "</table>";
		}
		
		$html.= 'PHP Memory peak usage: ' . memory_get_peak_usage();
		
		return $html;
	}

}