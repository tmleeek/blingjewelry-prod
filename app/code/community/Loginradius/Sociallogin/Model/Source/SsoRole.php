<?php
class Loginradius_Sociallogin_Model_Source_SsoRole
{
    public function toOptionArray()
    {
        $result = array();
        $result[] = array('value' => 'master', 'label'=>'Master<br/>');
        $result[] = array('value' => 'slave', 'label'=>'Slave<br/>');
        return $result;  
    }     
}