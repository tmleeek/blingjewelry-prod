<?php

/**
 * Class to add module installation information to base Magento info call
 */
class ElevenMain_Core_Model_Magento_Api extends Mage_Core_Model_Magento_Api {

	/**
     * Retrieve information about current Magento installation
     *
     * @return array
     */
	public function info()
	{
		// Get existing info
		$info = parent::info();
		$info['installed_modules'] = array();

		// Get the list of installed modules
		$config = Mage::getConfig();
		foreach($config->getNode('modules')->children() as $name => $item)
		{
			$info['installed_modules'][] = $name;
		}
		sort($info['installed_modules']);

		// Add a member to verify that this module is installed
		$info['11main_module_installed'] = TRUE;

		// Add a version number for feature tracking
		$info['version'] = '3.5.1';

		return $info;
	}

}
// End of Api.php