<?php
class Bootstrap_Inspiration_Block_Adminhtml_Inspiration_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
		$fieldset = $form->addFieldset('inspiration_form',array('legend'=>'Information'));
        $fieldset->addField('title', 'text',
						array(
                          	'label' => 'Title',
                          	'class' => 'required-entry',
                          	'required' => true,
                        	'name' => 'title',
                    	));

        $fieldset->addField('sort_order', 'text',
						array(
                          	'label' => 'Sort Order',
                        	'name' => 'sort_order',
                    	));
        /*
        $fieldset->addField('date', 'date',
						array(
							'name'		=> 'date',
        					'label'     => 'Date',
        					'text'      => date('M/d/yyyy', $this->dateValue),
        					'format'    => 'M/d/yyyy',
        					'time'      => false,
        					'image'     => $this->getSkinUrl('images/grid-cal.gif'),
        					'required'  => true,
                    	));
                      */
        $fieldset->addField('description', 'editor',
                    	array(
                          	'label' => 'Description',
                          	'name' => 'description',
							'title' => 'Description',
							'style' => 'height:12em;',
							'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
          				));	
		$fieldset->addField('thumbnail', 'image', 
			array(
          		'label'     => 'Thumbnail',
          		'required'  => false,
          		'name'      => 'thumbnail',
			));
        $featured_cats = explode(',', Mage::getStoreConfig('bootstrapsettings/inspiration/featured_cats'));
        $values = array();
        foreach($featured_cats as $cat){
            array_push($values, array('value' => $cat, 'label' => ucfirst($cat)));
        }
		$fieldset->addField('category', 'select',
                    	array(
                        	'label' => 'Category',
                        	'class' => 'required-entry',
                        	'required' => true,
                        	'name' => 'category',
        					'values' => $values,
                 ));
		$type = $fieldset->addField('type', 'select',
                    	array(
                        	'label' => 'Type',
                        	'class' => 'required-entry',
                        	'required' => true,
                        	'name' => 'type',
        					'values' => array(
          						array(
            			  			'value' => 'url',
              						'label' => 'URL',
          						),
          						array(
            			  			'value' => 'image',
              						'label' => 'Image',
          						),
          						array(
            			  			'value' => 'pdf',
              						'label' => 'PDF',
          						),
          						array(
            			  			'value' => 'video',
              						'label' => 'Video',
          						),
          					),
                 ));
			
		$pdf = $fieldset->addField('pdf', 'file', 
			array(
          		'label'     => 'PDF',
          		'required'  => false,
          		'name'      => 'pdf',
			));
		$url = $fieldset->addField('url', 'text', 
			array(
          		'label'     => 'URL',
          		'required'  => false,
          		'name'      => 'url',
          		'after_element_html' => '<small>http://example.com</small>',
			));
		$video = $fieldset->addField('video', 'text', 
			array(
          		'label'     => 'Video',
          		'required'  => false,
          		'name'      => 'video',
			));
		$image = $fieldset->addField('image', 'image', 
			array(
          		'label'     => 'image',
          		'required'  => false,
          		'name'      => 'image',
			));
		
 if ( Mage::registry('inspiration_data') )
 {
    $form->setValues(Mage::registry('inspiration_data')->getData());
  }
  
  
  
  
    $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap($type->getHtmlId(), $type->getName())
            ->addFieldMap($pdf->getHtmlId(), $pdf->getName())
            ->addFieldMap($url->getHtmlId(), $url->getName())
            ->addFieldMap($video->getHtmlId(), $video->getName())
            ->addFieldMap($image->getHtmlId(), $image->getName())
            ->addFieldDependence(
                $pdf->getName(),
                $type->getName(),
                'pdf'
            )
            ->addFieldDependence(
                $url->getName(),
                $type->getName(),
                'url'
            )
            ->addFieldDependence(
                $video->getName(),
                $type->getName(),
                'video'
            )
            ->addFieldDependence(
                $image->getName(),
                $type->getName(),
                'image'
            )
        );  
  
  
  
  
  
  return parent::_prepareForm();
 }
}