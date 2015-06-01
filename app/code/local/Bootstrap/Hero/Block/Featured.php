<?php   
class Bootstrap_Hero_Block_Featured extends Mage_Core_Block_Template{   

    public function __construct() {
        parent::__construct();
        //programmatically set template instead of xml
        //$this->setTemplate('bootstrap/ambassadors/city/view.phtml');
    }

   	public function _listHero()
	{

    	$hero = Mage::getModel('hero/hero')->getCollection()
    			//->addAttributeToFilter('image', 'notnull')
				->setOrder('sort_order', 'ASC');
    	$hero_html = '';
		foreach($hero as $data)
		{
			$img 	= $data->getData('image');
			$th 	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $img;

			$bg 	= $data->getData('retina_image');
			$bgs 	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $bg;

            $mobile 	= $data->getData('mobile_image');
			$mobiles 	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $mobile;

			$id 	= $data->getData('hero_id');
			$name 	= $data->getData('name');
			$link 	= $data->getData('link');

			if(!empty($img)){
				$hero_html .= '<div class="slide">';
				if(!empty($link)){		
					$hero_html .= "<a href=\"$link\">";
				}
			
				$hero_html .='<img src="'.$th.'" alt="'.$name.'">';
				
				if(!empty($link)){		
					$hero_html .= "</a>";
				}
				$hero_html .= '</div>';
			}
		}
		
		if(empty($hero_html)){
			$hero_html = 'No hero';
		}
    	return $hero_html;
	}

}