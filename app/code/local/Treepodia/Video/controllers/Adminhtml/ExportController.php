<?php
class Treepodia_Video_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function exportAction()
	{	
		$_magentoPath = Mage::getBaseDir();	
		$_magentoUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$_imagePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product/';
		$_xmlFolder = 'export';
		$_xmlPath = $_magentoPath . '/' . $_xmlFolder;
		$_fileName = 'treepodia_products.xml';
		$xmlFile = $_xmlPath . "/" . $_fileName;
		$xmlFileUrl = $_magentoUrl . $_xmlFolder . '/' . $_fileName;
		if( !is_dir($_xmlPath) )
			mkdir($_xmlPath);	
		$_productCollection = Mage::getModel('catalog/product')->getCollection();
		$_productCollection->addAttributeToSelect('*');
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
		$doc->save( $xmlFile );
		Mage::app()->getCache()->clean();
		Mage::getSingleton('adminhtml/session')->addSuccess('Exporting products to Treepodia accepted XML successful. You can view the file <a href="'.$xmlFileUrl . '" target="_blank">here</a> ( to save this file right click the link and select "save linked file as").');
		$this->_redirect('*/*/');
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