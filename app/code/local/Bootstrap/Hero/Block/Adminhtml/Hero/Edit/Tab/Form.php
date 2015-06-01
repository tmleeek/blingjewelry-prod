<?php
class Bootstrap_Hero_Block_Adminhtml_Hero_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareLayout()
	{
        $return = parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
        return $return;
    }
        
        
   protected function _prepareForm()
   {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('hero_form',array('legend'=>'Information'));
        $fieldset->addField('name', 'text',
						array(
                          	'label' => 'Name',
                          	'class' => 'required-entry',
                          	'required' => true,
                        	'name' => 'name',
                    	));
        $fieldset->addField('link', 'text',
						array(
                          	'label' => 'Link',
                          	'required' => false,
                        	'name' => 'link',
                    	));
        $fieldset->addField('description', 'editor',
                    	array(
                          	'label' => 'Description',
                          	'required' => false,
                          	'name' => 'description',
							'title' => 'Description',
							'style' => 'height:12em;',
							'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
          				));	
		$fieldset->addField('image', 'image', 
			array(
          		'label'     => 'Image',
                'class' => 'required-entry',
          		'required'  => true,
          		'name'      => 'image',
			));
		$fieldset->addField('retina_image', 'image', 
			array(
          		'label'     => 'Retina Image',
          		'required'  => false,
          		'name'      => 'retina_image',
			));
        $fieldset->addField('mobile_image', 'image', 
			array(
          		'label'     => 'Mobile Image',
          		'required'  => false,
          		'name'      => 'mobile_image',
			));
        $fieldset->addField('sort_order', 'text',
						array(
                          	'label' => 'Sort Order',
                        	'name' => 'sort_order',
                    	));
		$fieldset->addField('active', 'select',
                    	array(
                        	'label' => 'Enabled?',
                        	'class' => 'required-entry',
                        	'required' => true,
                        	'name' => 'active',
        					'values' => array(
                					array(
                    					'value'     => 0,
                    					'label'     => 'Disabled',
                					),
                					array(
                    					'value'     => 1,
                    					'label'     => 'Enabled',
                					), 
        					),
                 		));
		
 if ( Mage::registry('hero_data') )
 {
    $form->setValues(Mage::registry('hero_data')->getData());
  }
    
  
  return parent::_prepareForm();
 }
}