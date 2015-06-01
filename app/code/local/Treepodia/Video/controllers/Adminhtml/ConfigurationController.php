<?php
class Treepodia_Video_Adminhtml_ConfigurationController extends Mage_Adminhtml_Controller_Action
{	
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
		
	public function saveAction() {
		$post = $this->getRequest()->getPost();
		if ( $post ) :
			try {
				$error = false;
				$msg = 'Unable to submit your request. Please, try again later.';
				if( !empty($post['video_playback_initial_volume']) ) :
					if( $post['video_playback_initial_volume'] > 100 ) :
						$error = true;
						$msg = 'Please set initial volumn number from 0 - 100';
					endif;
				endif;			
				if ($error)
                    throw new Exception();
				$config = new Mage_Core_Model_Config();
				/*===== General =====*/
				$config ->saveConfig('video/configuration/general/kind', $post['video_general_kind'], 'default', 0);
				$config ->saveConfig('video/configuration/general/dialog_style_color', $post['video_general_dialog_style_color'], 'default', 0);
				if( empty($post['video_general_dialog_style_include_border']) )
					$config ->saveConfig('video/configuration/general/dialog_style_include_border', '', 'default', 0);
				else
					$config ->saveConfig('video/configuration/general/dialog_style_include_border', $post['video_general_dialog_style_include_border'], 'default', 0);
				$config ->saveConfig('video/configuration/general/dialog_button_url', $post['video_general_dialog_button_url'], 'default', 0);
				/*===== Apperance =====*/				
				$config ->saveConfig('video/configuration/appearance/player_style', $post['video_appearance_player_style'], 'default', 0);
				$config ->saveConfig('video/configuration/appearance/player_skin', $post['video_appearance_player_skin'], 'default', 0);
				$config ->saveConfig('video/configuration/appearance/allow_full_screen', $post['video_apperance_allow_full_screen'], 'default', 0);
				$config ->saveConfig('video/configuration/appearance/show_play_button', $post['video_apperance_show_play_button'], 'default', 0);
				$config ->saveConfig('video/configuration/appearance/player_width', $post['video_appearance_player_width'], 'default', 0);
				$config ->saveConfig('video/configuration/appearance/player_height', $post['video_apperance_player_height'], 'default', 0);
				/* ===== Playback ===== */
				$config ->saveConfig('video/configuration/playback/auto_play', $post['video_playback_auto_play'], 'default', 0);
				$config ->saveConfig('video/configuration/playback/loop_play', $post['video_playback_loop_play'], 'default', 0);
				$config ->saveConfig('video/configuration/playback/play_on_click', $post['video_playback_play_on_click'], 'default', 0);
				$config ->saveConfig('video/configuration/playback/mute', $post['video_playback_mute'], 'default', 0);
				$config ->saveConfig('video/configuration/playback/initial_volume', $post['video_playback_initial_volume'], 'default', 0);
				/* ===== Social ===== */
				if( empty($post['video_social_share_items']) ) :
					$config ->saveConfig('video/configuration/social/share_items', '', 'default', 0);
				else :
					$config ->saveConfig('video/configuration/social/share_items', implode(',', $post['video_social_share_items']), 'default', 0);
				endif;
				/* ===== Advance ===== */			
				$config ->saveConfig('video/configuration/advance/atc_enable', $post['video_advance_atc_enable'], 'default', 0);
				$config ->saveConfig('video/configuration/advance/atc_label', $post['video_advance_atc_label'], 'default', 0);
				Mage::app()->getCache()->clean();
				Mage::getSingleton('adminhtml/session')->addSuccess('Save treepcodia configuration successful.');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($msg);
            }
		endif;
		$this->_redirect('*/*/');
	}
}
?>