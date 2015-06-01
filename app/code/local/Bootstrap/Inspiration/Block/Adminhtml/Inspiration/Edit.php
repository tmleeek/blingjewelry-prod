<?php
class Bootstrap_Inspiration_Block_Adminhtml_Inspiration_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
   public function __construct()
   {
        parent::__construct();
        $this->_objectId = 'inspiration_id'; // or should be just id?
        //vwe assign the same blockGroup as the Grid Container
        $this->_blockGroup = 'inspiration';
        //and the same controller
        $this->_controller = 'adminhtml_inspiration';
        //define the label for the save and delete button
        $this->_updateButton('save', 'label','save');
        $this->_updateButton('delete', 'label', 'delete');
    }
       /* Here, we're looking if we have transmitted a form object,
          to update the good text in the header of the page (edit or add) */
    public function getHeaderText()
    {
        if( Mage::registry('inspiration_data')&&Mage::registry('inspiration_data')->getId())
         {
              return 'Edit Inspiration '.$this->htmlEscape(
              Mage::registry('inspiration_data')->getTitle()).'<br />';
         }
         else
         {
             return 'Add an Inspiration';
         }
    }
}