<?php
class Bootstrap_Press_Adminhtml_PressController extends Mage_Adminhtml_Controller_Action{

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
        $model = Mage::getModel('press/press')->load($id);
        if ($model->getId() || $id == 0)
        {
			Mage::register('press_data', $model);
            $this->loadLayout();
        	$this->_setActiveMenu('cms/press');
            $this->_addBreadcrumb('Press Manager', 'Press Manager');
            $this->_addBreadcrumb('Press Description', 'Press Description');
            $this->getLayout()->getBlock('head')
					->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()
					->createBlock('press/adminhtml_press_edit'))
					->_addLeft($this->getLayout()
					->createBlock('press/adminhtml_press_edit_tabs')
			);
			$this->renderLayout();
		}else{
			Mage::getSingleton('adminhtml/session')
				->addError('Press does not exist');
				$this->_redirect('*/*/');
		}         
    }
         
    public function saveAction()
    {

		if ($this->getRequest()->getPost())
        {
        	try {
				$postData = $this->getRequest()->getPost();
	
				// save circle
				if(isset($_FILES['circle']['name']) and (file_exists($_FILES['circle']['tmp_name']))) {
					$postData['circle'] = $this->_uploadImage('circle');
				}else{					
					if(isset($postData['circle']['delete']) && $postData['circle']['delete'] == 1){
						$path = Mage::getBaseDir("media") . DS . $postData['circle']['value'];
						unlink($path);
						$postData['circle'] = '';
					}else{
						unset($postData['circle']);
					}
				}

				// save thumbnail
				if(isset($_FILES['thumbnail']['name']) and (file_exists($_FILES['thumbnail']['tmp_name']))) {
					$postData['thumbnail'] = $this->_uploadImage('thumbnail');
				}else{					
					if(isset($postData['thumbnail']['delete']) && $postData['thumbnail']['delete'] == 1){
						$path = Mage::getBaseDir("media") . DS . $postData['thumbnail']['value'];
						unlink($path);
						$postData['thumbnail'] = '';
					}else{
						unset($postData['thumbnail']);
					}
				}

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
				
				// save pdf
				if(isset($_FILES['pdf']['name']) and (file_exists($_FILES['pdf']['tmp_name']))) {
					$postData['pdf'] = $this->_uploadPdf('pdf');
				}else{					
					if(isset($postData['pdf']['delete']) && $postData['pdf']['delete'] == 1){
						$path = Mage::getBaseDir("media") . DS . $postData['pdf']['value'];
						unlink($path);
						$postData['pdf'] = '';
					}else{
						unset($postData['pdf']);
					}
				}
				
				$postData['date'] = strtotime($postData['date']); 
				//$p_date_post = date(’Y-m-d H:i:s’, $date_post);
			
				// save to db
                $model = Mage::getModel('press/press');
               	if( $this->getRequest()->getParam('id') <= 0 )             	
                  	$model->setCreatedTime(Mage::getSingleton('core/date')->gmtDate());
                  	$model->addData($postData)->setUpdateTime(Mage::getSingleton('core/date')->gmtDate())
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();
        			Mage::getSingleton('adminhtml/session')->addSuccess('Successfully saved');
                 	Mage::getSingleton('adminhtml/session')->setPressData(false);
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
    		$path = Mage::getBaseDir('media') . DS .'press' . DS;        
    		$uploader->save($path, $_FILES[$name]['name']);
			//$data[$name] = $_FILES[$name]['name'];
    		$save_path = 'press' . DS . $_FILES[$name]['name'];
    		return $save_path;
  		}catch(Exception $e) {
 			return false;
 		}
    }
    
    protected function _uploadPdf($name){

  		try {
    		$uploader = new Varien_File_Uploader($name);
    		$uploader->setAllowedExtensions(array('pdf')); // or pdf or anything
    		$uploader->setAllowRenameFiles(false);
 
    		// setAllowRenameFiles(true) -> move your file in a folder the magento way
    		// setAllowRenameFiles(true) -> move your file directly in the $path folder
    		$uploader->setFilesDispersion(false);
    		$path = Mage::getBaseDir('media') . DS .'press' . DS;        
    		$uploader->save($path, $_FILES[$name]['name']);
			//$data[$name] = $_FILES[$name]['name'];
    		$save_path = 'press' . DS . $_FILES[$name]['name'];
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
        		$model = Mage::getModel('press/press');
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
        $data = Mage::getModel('press/press')->load($this->getRequest()->getParam('id'));
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
        $this->loadLayout()->_setActiveMenu('cms/press')->_addBreadcrumb('Press Manager','Press Manager');
		return $this;
	}
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/press');
    }

}
