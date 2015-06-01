<?php
class Bootstrap_Hero_Block_Adminhtml_Hero_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
   public function __construct()
   {
        parent::__construct();
        $this->_objectId = 'hero_id'; // or should be just id?
        //vwe assign the same blockGroup as the Grid Container
        $this->_blockGroup = 'hero';
        //and the same controller
        $this->_controller = 'adminhtml_hero';
        //define the label for the save and delete button
        $this->_updateButton('save', 'label','save');
        $this->_updateButton('delete', 'label', 'delete');
    }
       /* Here, we're looking if we have transmitted a form object,
          to update the good text in the header of the page (edit or add) */
    public function getHeaderText()
    {
        if( Mage::registry('hero_data')&&Mage::registry('hero_data')->getId())
         {
              return 'Edit Hero '.$this->htmlEscape(
              Mage::registry('hero_data')->getTitle()).'<br />';
         }
         else
         {
             return 'Add Hero Image';
         }
    }
}