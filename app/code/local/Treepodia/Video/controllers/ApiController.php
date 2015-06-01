<?php
class Treepodia_Video_ApiController extends Mage_Core_Controller_Front_Action
{
	public function pingAction ()
	{
		echo 'OK';
	}
	
	public function get_data_feedAction ()
	{
		try {
			$pageId = 0;
			$prodNum = 0;
			if( isset($_GET['pageId']) )
				$pageId = $_GET['pageId'];
			if( isset($_GET['prodNum']) )
				$prodNum = $_GET['prodNum'];
			
			set_time_limit(0);
			ini_set('memory_limit', '800M');
			$_imagePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product/';
			$_productCollection = "";

			if ( !empty($pageId) && !empty($prodNum) )
				$_productCollection = Mage::getModel('catalog/product')->getCollection()
													->addAttributeToFilter('status', array('eq' => 1))
													->addAttributeToFilter('sku', array('neq' => 'NULL'))
													->addAttributeToFilter('visibility', 4)
													->addAttributeToSelect('*')
													->setPage($pageId, $prodNum);
			elseif ( !empty($pageId) )
				$_productCollection = Mage::getModel('catalog/product')->getCollection()
													->addAttributeToFilter('status', array('eq' => 1))
													->addAttributeToFilter('sku', array('neq' => 'NULL'))
													->addAttributeToFilter('visibility', 4)
													->addAttributeToSelect('*')
													->setPage($pageId, 1500);
			elseif ( !empty($prodNum) )
				$_productCollection = Mage::getModel('catalog/product')->getCollection()
													->addAttributeToFilter('status', array('eq' => 1))
													->addAttributeToFilter('sku', array('neq' => 'NULL'))
													->addAttributeToFilter('visibility', 4)
													->addAttributeToSelect('*')
													->setPage(1, $prodNum);
			else
				$_productCollection = Mage::getModel('catalog/product')->getCollection()
													->addAttributeToFilter('status', array('eq' => 1))
													->addAttributeToFilter('sku', array('neq' => 'NULL'))
													->addAttributeToFilter('visibility', 4)
													->addAttributeToSelect('*');
													
			$doc = new DomDocument('1.0', 'UTF-8');
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;	
			$root = $doc->createElement('products');
			$root = $doc->appendChild($root);
			$_taxHelper = Mage::helper('tax');
			$_tagModel = Mage::getModel('tag/tag');
			$storeId = $this->getStoreId();
			$today = date('Y-m-d 00:00:00');
			foreach ( $_productCollection as $_product ) :
				$product_id = $_product->getId();
				$full_product = Mage::getModel('catalog/product')->load($product_id);
				$v['sku'] = $_product->getSku();
				$v['name'] = $_product->getName();
				$price = $_taxHelper->getPrice($_product, $_product->getPrice());
				$v['price'] = $price;
				$salePrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice());
				if( $price != $salePrice )
					$v['sale-price'] = $salePrice;
				else
					unset($v['sale-price']);
				$v['page-url'] = $_product->getProductUrl();
				$galleryData = $full_product->getMediaGallery();
				foreach ( $galleryData ['images'] as $image ) :			
					$v['image'][] = $_imagePath . trim($image['file'], '/');
				endforeach;
				$v['description'] = $_product->getShortDescription();
				$category_name = '';
				$categoryIds = $_product->getCategoryIds();
				if(is_array($categoryIds)) :
					$category_names = array();
					foreach($categoryIds as $categoryId) :
						$category = Mage::getModel('catalog/category')->load($categoryId);
						$category_names[] = $category->getName();
					 endforeach;
					 $category_name = implode(' / ', $category_names);
					 unset($category_names);
				else :
					$category = Mage::getModel('catalog/category')->load($categoryId);
					$category_name = $category->getName();
				endif;
				if( empty($category_name) )
					unset($v['category']);
				else
					$v['category'] = $category_name;
				$rating = $this->getRating($product_id, $storeId);
				if( $rating > 0 )
					$v['rating'] = $rating;
				else
					unset($v['rating']);
				if( $_product->getManufacturer() > 0 )
					$v['Brand'] = $_product->getAttributeText('manufacturer');
				else
					unset($v['Brand']);
				$weight = $_product->getWeight();
				if( empty($weight) )
					unset($v['weight']);
				else
					$v['weight'] = $_product->getWeight();
				if( $_product->getData('news_from_date') || $_product->getData('news_to_date') ) :
					if( $_product->getData('news_to_date') ) :
						if( $today > $_product->getData('news_to_date') )
							$v['new-arrivals'] = 'No';
						else
							$v['new-arrivals'] = 'Yes';
					else :
						if( $today < $_product->getData('news_from_date') )
							$v['new-arrivals'] = 'No';
						else
							$v['new-arrivals'] = 'Yes';
					endif;
				else :
					unset($v['new-arrivals']);
				endif;
				$tag_name = '';
				$_collection = $_tagModel->getResourceCollection()
					->addPopularity()
					->addStatusFilter($_tagModel->getApprovedStatus())
					->addProductFilter($product_id)
					->setFlag('relation', true)
					->addStoreFilter($storeId)
					->setActiveFilter()
					->load();
				$_items = $_collection->getItems();
				if( count($_items) > 0 ) :
					foreach($_items as $_item) :
						$_tags[] = $_item->getName();
					endforeach;
					$tag_name = implode(',', $_tags);
					unset($_tags);
				endif;
				if( empty($tag_name) )
					unset($v['tag']);
				else
					$v['tag'] = $tag_name;
				$occ = $doc->createElement('product');
				$occ = $root->appendChild($occ);
				foreach ( $v as $fieldName => $fieldValue ) :
					if ( is_array($fieldValue) ) {
						foreach( $fieldValue as $subField ) :
							$child = $doc->createElement($fieldName);
							$child = $occ->appendChild($child);
							$value = $doc->createTextNode($subField);
							$value = $child->appendChild($value);
						endforeach;
					} else {
						$child = $doc->createElement($fieldName);
						$child = $occ->appendChild($child);
						$value = $doc->createTextNode($fieldValue);
						$value = $child->appendChild($value);
					}
				endforeach;
				unset($v['image']);
			endforeach;
			echo $doc->saveXML();
		} catch(Exception $e) {
			echo $e;
		}
	}
	
	private function getStoreId() {
		$storeId = 0;
		$allStores = Mage::app()->getStores();
		foreach ($allStores as $_eachStoreId => $val) :
			if( Mage::app()->getStore($_eachStoreId)->getIsActive() && Mage::app()->getStore($_eachStoreId)->getCode() == 'default' )
				$storeId = Mage::app()->getStore($_eachStoreId)->getId();
		endforeach;
		return $storeId;
	}
	
	private function getRating($product_id, $storeId) {
		$rating = 0;
		$review_summary = Mage::getModel('review/review_summary')->load($product_id);
		$entityId = $review_summary->getEntityPkValue();
		$reviewsCount = Mage::getModel('review/review')
			->getTotalReviews($entityId, true);
		if( $reviewsCount > 0 ) :
			$ratingCollection = Mage::getModel('rating/rating')
				->getResourceCollection()
				->addEntityFilter('product') # TOFIX
				->setPositionOrder()
				->setStoreFilter($storeId)
				->addRatingPerStoreName($storeId)
				->load();
			if ($entityId)
				$ratingCollection->addEntitySummaryToItem($entityId, $storeId);
			if(!empty($ratingCollection) && $ratingCollection->getSize()) :
				foreach ($ratingCollection as $_rating) :
					if($_rating->getSummary()) :
						$rating += ceil($_rating->getSummary());
					endif;
				endforeach;
				if( $rating > 0 ) :
					$rating = ceil($rating/$ratingCollection->getSize());
					$rating = ceil( $rating / 20 );
				endif;
			endif;
		endif;
		return $rating;
	}
}
?>