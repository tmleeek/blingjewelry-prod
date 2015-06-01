<?php   
class Bootstrap_Press_Block_Index extends Mage_Core_Block_Template{   

    public function __construct() {
        parent::__construct();
        //programmatically set template instead of xml
        //$this->setTemplate('bootstrap/ambassadors/city/view.phtml');
    }

   	public function _listPress()
	{
      	$pathInfo = $this->getRequest()->getOriginalPathInfo();
        // Extract the requested key (whatever)
        $pathArray = explode('/press/',$pathInfo);

        if(count($pathArray)>1){
       		$requestedKey = str_replace('/','',$pathArray[1]);
        }

    	$press = Mage::getModel('press/press')->getCollection()
				->setOrder('sort_order', 'ASC')
				->setOrder('date', 'DESC');
    	if(!empty($requestedKey)){
    		$press->addFieldToFilter('type',$requestedKey);
    	}
    	$press_html = '';
		foreach($press as $data)
		{
			switch($data->getData('type')){
			
				case 'url':
					$link = $data->getData('url');
					$type_class = ' link';
					$a_tag = '<a href="'.$link.'" target="_blank">';
				break;
				case 'video':
					$video = $data->getData('video');
					$type_class = ' video';
					$a_tag = '<a href="javascript:void(0);" class="video">';

				break;
				case 'image':
					$link = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $data->getData('image');
					$type_class = ' image';
					$a_tag = '<a href="'.$link.'" class="image">';
				break;
				case 'pdf':
					$link = $data->getData('pdf');
					$type_class = ' pdf';
				break;
			}

            $cat_class = ' ' . $data->getData('category');
			$th = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $data->getData('thumbnail');
			
			$resized = $this->helper('press/data')->resizeImg($data->getData('thumbnail'),300,360);
			
			$th_size = getimagesize($th);
			
			$productId = $data->getData('product_id');
			
			$_productBlock  = '';
			$_productLine   = '';
			$_override 		= '';
			$_circleLink 	= '';

			if(!empty($productId)){
				$_product 		= Mage::getModel("catalog/product")->load($productId);
				$_productImage 	= Mage::helper('catalog/image')->init($_product, 'image')->resize(100);

				$_override = $data->getData('circle');
				if(!empty($_override)){
					$_productImage = $this->helper('press/data')->resizeImg($data->getData('circle'),100,100);
				}

				$_productName 	= $_product->getName();
				$_productUrl 	= $_product->getProductUrl();
				
				$_circleLink 	= $data->getData('circle_link');
				if(!empty($_circleLink)){
					$_productUrl = $_circleLink;
				}
				
				$_productBlock 	= "<div class=\"press-product\"><a href=\"$_productUrl\"><img src=\"$_productImage\" alt=\"$_productName\"></a></div>";
				$_productLine   = "Featuring: <a href=\"$_productUrl\">$_productName</a>";
			}else{
				$_override = $data->getData('circle');
				if(!empty($_override)){
					$_productImage = $this->helper('press/data')->resizeImg($data->getData('circle'),100,100);
				}
				
				$_circleLink = $data->getData('circle_link');
				if(!empty($_circleLink)){
					$_productUrl = $_circleLink;
				}
				
				if(!empty($_override) && !empty($_circleLink)){
					$_productBlock 	= "<div class=\"press-product\"><a href=\"$_productUrl\"><img src=\"$_productImage\" alt=\"product link\"></a></div>";
					$_productLine   = '';			
				}
			}
			
			
			$date = $data->getData('date');
			$desc = $data->getData('description');
			$timestamp = strtotime($date);
			$date = date("F Y", $timestamp);
			
			$press_html .= '<li class="press-item col-sm-4 col-md-3 col-xs-6">';

			$press_html .= '<div class="image-wrap">';
			$press_html .= $_productBlock;	
			$press_html .= $a_tag;
			$press_html .= '<img src="' . $resized . '" alt="'.$data->getData('title').'" class="press-thumb">';
			$press_html .= '</a></div>';

			$press_html .= '<div class="title-wrap">';
			$press_html .= '<h3>' . $a_tag . $data->getData('title') . '</a></h3>';
			if(!empty($subtitle)){
				$press_html .= '<h4>' . $data->getData('subtitle') . '</h4>';
			}
			if(!empty($date)){
				$press_html .= '<div class="date">' . $date . '</div>';
			}
			if(isset($video)){
				$press_html .= '<div class="vid">' . $video . '</div>';
			}
			if(!empty($_productLine)){
				$press_html .= '<div class="featuring">' . $_productLine . '</div>';
			}
			if(!empty($desc)){
				$press_html .= '<div class="desc">' . $desc . '</div>';
			}

			$press_html .= '</div>';
			$press_html .= '</li>';
		}
		
		if(empty($press_html)){
			$press_html = 'No press';
		}
    	return $press_html;
	}

}