<?php
class Bootstrap_Hero_Adminhtml_HeroController extends Mage_Adminhtml_Controller_Action{

	public function indexAction()
	{
		$this->_initAction();
		$this->loadLayout();
		$this->renderLayout();
		//echo '<pre>';
		//var_dump(Mage::getSingleton('core/layout')->getUpdate()->getHandles());
		//echo '</pre>';
		//exit("bailing early at ".__LINE__." in ".__FILE__);
	}  
     
    public function newAction()
    {  
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }  
     
    public function editAction()
    {  
		$id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('hero/hero')->load($id);
        if ($model->getId() || $id == 0)
        {
			Mage::register('hero_data', $model);
            $this->loadLayout();
        	$this->_setActiveMenu('cms/hero');
            $this->_addBreadcrumb('Hero Manager', 'Hero Manager');
            $this->_addBreadcrumb('Hero Description', 'Hero Description');
            $this->getLayout()->getBlock('head')
					->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()
					->createBlock('hero/adminhtml_hero_edit'))
					->_addLeft($this->getLayout()
					->createBlock('hero/adminhtml_hero_edit_tabs')
			);
			$this->renderLayout();
		}else{
			Mage::getSingleton('adminhtml/session')
				->addError('Hero does not exist');
				$this->_redirect('*/*/');
		}         
    }
         
    public function saveAction()
    {

		if ($this->getRequest()->getPost())
        {
        	try {
				$postData = $this->getRequest()->getPost();

				// save image
				if(isset($_FILES['image']['name']) and (file_exists($_FILES['image']['tmp_name']))) {
					$postData['image'] = $this->_uploadImage('image');
				}else{					
					if(isset($postData['image']['delete']) && $postData['image']['delete'] == 1){
						$path = Mage::getBaseDir("media") . DS . $postData['image']['value'];
						unlink($path);
						$postData['image'] = '';
					}else{
						unset($postData['image']);
					}
				}
				// save retina image
				if(isset($_FILES['retina_image']['name']) and (file_exists($_FILES['retina_image']['tmp_name']))) {
					$postData['retina_image'] = $this->_uploadImage('retina_image');
				}else{					
					if(isset($postData['retina_image']['delete']) && $postData['retina_image']['delete'] == 1){
						$path = Mage::getBaseDir("media") . DS . $postData['retina_image']['value'];
						unlink($path);
						$postData['retina_image'] = '';
					}else{
						unset($postData['retina_image']);
					}
				}

                // save mobile image
				if(isset($_FILES['mobile_image']['name']) and (file_exists($_FILES['mobile_image']['tmp_name']))) {
					$postData['mobile_image'] = $this->_uploadImage('mobile_image');
				}else{					
					if(isset($postData['mobile_image']['delete']) && $postData['mobile_image']['delete'] == 1){
						$path = Mage::getBaseDir("media") . DS . $postData['mobile_image']['value'];
						unlink($path);
						$postData['mobile_image'] = '';
					}else{
						unset($postData['mobile_image']);
					}
				}
			
				// save to db
                $model = Mage::getModel('hero/hero');
               	if( $this->getRequest()->getParam('id') <= 0 )             	
                  	$model->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
                  	$model->addData($postData)->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
        			Mage::getSingleton('adminhtml/session')->addSuccess('Successfully saved');
                 	Mage::getSingleton('adminhtml/session')->setHeroData(false);
                 	$this->_redirect('*/*/');
                	return;
          	} catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->settestData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit',array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    protected function _uploadImage($name){

  		try {
    		$uploader = new Varien_File_Uploader($name);
    		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
    		$uploader->setAllowRenameFiles(false);
 
    		// setAllowRenameFiles(true) -> move your file in a folder the magento way
    		// setAllowRenameFiles(true) -> move your file directly in the $path folder
    		$uploader->setFilesDispersion(false);
    		$path = Mage::getBaseDir('media') . DS .'hero' . DS;        
    		$uploader->save($path, $_FILES[$name]['name']);
			//$data[$name] = $_FILES[$name]['name'];
    		$save_path = 'hero' . DS . $_FILES[$name]['name'];
    		return $save_path;
  		}catch(Exception $e) {
 			return false;
 		}
    }
    
    
	public function deleteAction()
    {
    	if($this->getRequest()->getParam('id') > 0)
        {
        	try
            {
        		$model = Mage::getModel('hero/hero');
				$model->setId($this->getRequest()->getParam('id'))->delete();
				
				//$path = Mage::getBaseDir("media") . DS . $filename_from _db;
				//unlink($path);				
				
				
				Mage::getSingleton('adminhtml/session')->addSuccess('Successfully deleted');
				$this->_redirect('*/*/');
			}
			catch (Exception $e)
            {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
     
    public function messageAction()
    {
        $data = Mage::getModel('hero/hero')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }
     
    /**
     * Initialize action
     *
     * Here, we set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('cms/hero')->_addBreadcrumb('Hero Manager','Hero Manager');
		return $this;
	}
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/hero');
    }

}
