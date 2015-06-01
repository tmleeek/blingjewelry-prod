<?php
class Loginradius_Sociallogin_Model_Source_SharingVerticalAlignment
{
    public function toOptionArray()
    {
        $result = array();
        $result[] = array('value' => 'top_left', 'label'=>'Top Left');
        $result[] = array('value' => 'top_right', 'label'=>'Top Right');
        $result[] = array('value' => 'bottom_left', 'label'=>'Bottom Left');
        $result[] = array('value' => 'bottom_right', 'label'=>'Bottom Right');
        return $result;  
    }     
}