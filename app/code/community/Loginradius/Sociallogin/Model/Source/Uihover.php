<?php
class Loginradius_Sociallogin_Model_Source_Uihover
{
    public function toOptionArray()
	{
        $result = array();
        $result[] = array('value' => 'same', 'label'=>'Redirect to same page where the user logged in<br/>');
        $result[] = array('value' => 'account', 'label'=>'Redirect to Account page<br/>');
        $result[] = array('value' => 'index', 'label'=>'Redirect to Home page<br/>');
        $result[] = array('value' => 'custom', 'label'=>'Redirect to following url' );
        return $result;  
    }     
}