<?php   
class Bootstrap_Inspiration_Block_Index extends Mage_Core_Block_Template{   

    public function __construct() {
        parent::__construct();
        //programmatically set template instead of xml
        //$this->setTemplate('bootstrap/ambassadors/city/view.phtml');
    }

   	public function _listInspiration()
	{
      	$pathInfo = $this->getRequest()->getOriginalPathInfo();
        // Extract the requested key (whatever)
        $pathArray = explode('/inspiration/',$pathInfo);

        if(count($pathArray)>1){
       		$requestedKey = str_replace('/','',$pathArray[1]);
        }

    	$inspiration = Mage::getModel('inspiration/inspiration')->getCollection()
				->setOrder('sort_order', 'ASC')
				->setOrder('timestamp', 'DESC');
    	if(!empty($requestedKey)){
    		$inspiration->addFieldToFilter('type',$requestedKey);
    	}
    	$inspiration_html = '';
		foreach($inspiration as $data)
		{
			switch($data->getData('type')){
			
				case 'url':
					$link = $data->getData('url');
					$type_class = ' link';
					$a_tag = '<a href="'.$link.'">';
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
			
			//$resized = $this->helper('inspiration/data')->resizeImg($data->getData('thumbnail'),300,360);
			
			//$th_size = getimagesize($th);
			
			//$date = $data->getData('date');
			//$desc = $data->getData('description');
			//$timestamp = strtotime($date);
			//$date = date("F Y", $timestamp);
			
			$inspiration_html .= '<li class="inspiration-item">';

			$inspiration_html .= '<div class="image-wrap">';
			$inspiration_html .= $a_tag;
			$inspiration_html .= '<img src="' . $th . '" alt="'.$data->getData('title').'" class="inspiration-thumb">';
			$inspiration_html .= '</a></div>';
/*
			$inspiration_html .= '<div class="title-wrap">';
			$inspiration_html .= '<h3>' . $a_tag . $data->getData('title') . '</a></h3>';
			if(!empty($date)){
				$inspiration_html .= '<div class="date">' . $date . '</div>';
			}
			if(isset($video)){
				$inspiration_html .= '<div class="vid">' . $video . '</div>';
			}
			if(!empty($desc)){
				$inspiration_html .= '<div class="desc">' . $desc . '</div>';
			}

			$inspiration_html .= '</div>';
			*/
			$inspiration_html .= '</li>';
		}
		
		if(empty($inspiration_html)){
			$inspiration_html = 'No inspiration';
		}
    	return $inspiration_html;
	}

}