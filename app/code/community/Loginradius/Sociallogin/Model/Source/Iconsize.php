<?php
class Loginradius_Sociallogin_Model_Source_Iconsize
{
    public function toOptionArray()
    {
        $result = array();
        $result[] = array('value' => 'medium', 'label'=>'Medium<br/>');
        $result[] = array('value' => 'small', 'label'=>'Small<br/>');
        return $result;
    }     
}