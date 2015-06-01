<?php   
class Bootstrap_Press_Block_Menu extends Mage_Core_Block_Template{   

    public function __construct() {
        parent::__construct();
        //programmatically set template instead of xml
        //$this->setTemplate('bootstrap/ambassadors/city/view.phtml');
    }
    
   	public function _listMenu()
	{
	
		$menu = Mage::getModel('press/press')->getCollection()
				->setOrder('category','ASC')
				->addFieldToSelect('category')
				->distinct(true);
			
      	$pathInfo = $this->getRequest()->getOriginalPathInfo();
        // Extract the requested key (whatever)
        $pathArray = explode('/press/',$pathInfo);
        if(count($pathArray)>1){
       		$requestedKey = str_replace('/','',$pathArray[1]);
        }

		$html = '';
		foreach($menu as $data)
		{
			$active = '';
			if(isset($requestedKey)){
			if($data->getData('category') == $requestedKey){
				$active = ' class="current"';
			}
			}		

			$url = 'press/'. $data->getData('category');
			//$html .= '<li'.$active.'><a href="'. Mage::getUrl($url) .'">' . $data->getData('category') . '</a></li>';
			$html .= '<li'.$active.'><a href="#" data-filter=".'.$data->getData('category').'">' . $data->getData('category') . '</a></li>';
		}
		$html .= '<li><a href="#" data-filter="*">View All</a></li>';
    	return $html;
	}

}