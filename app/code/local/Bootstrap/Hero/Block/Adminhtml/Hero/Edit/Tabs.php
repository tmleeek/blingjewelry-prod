<?php
  class Bootstrap_Hero_Block_Adminhtml_Hero_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
  {
     public function __construct()
     {
          parent::__construct();
          $this->setId('hero_tabs');
          $this->setDestElementId('edit_form');
          $this->setTitle('Hero Item');
      }
	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array(
                   'label' => 'Information',
                   'title' => 'Information',
                   'content' => $this->getLayout()
     				->createBlock('hero/adminhtml_hero_edit_tab_form')->toHtml()
		));
		/*
		$this->addTab('form_section', array(
                   'label' => 'Images',
                   'title' => 'Images',
                   'content' => $this->getLayout()
     				->createBlock('hero/adminhtml_hero_edit_tab_form')->toHtml()
		));
		*/
		return parent::_beforeToHtml();
	}
}