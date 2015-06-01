<?php
class Treepodia_Video_Block_Configuration extends Mage_Core_Block_Template
{
	public function getVideoGeneralKindHtml() {
		$_html = '<select id="video_general_kind" name="video_general_kind" class="select"><option value="embedded"';
		if( Mage::getStoreConfig('video/configuration/general/kind') == '' || Mage::getStoreConfig('video/configuration/general/video_general_kind') == 'embedded' )
			$_html .= ' selected="selected"';
		$_html .= '>Embedded</option><option value="dialog"';
		if( Mage::getStoreConfig('video/configuration/general/kind') == 'dialog' )
			$_html .= ' selected="selected"';
		$_html .= '>Dialog</option></select>';
		return $_html;
	}
	
	public function getDialogStyleColorHtml() {
		$_html = '<input id="video_general_dialog_style_color" name="video_general_dialog_style_color" maxlength="6" size="6" value="'.Mage::getStoreConfig('video/configuration/general/dialog_style_color').'" class="input-text" type="text"/>';
		return $_html;
	}
	
	public function getDialogStyleIncludeBorderHtml() {
		$_html = '<input id="video_general_dialog_style_include_border" name="video_general_dialog_style_include_border" value="1" type="checkbox"';
		if( Mage::getStoreConfig('video/configuration/general/dialog_style_include_border') == '1' )
			$_html .= ' checked';
		$_html .= '/> Include border';
		return $_html;
	}
	
	public function getDialogButtonUrlHtml() {
		$_html = '<input id="video_general_dialog_button_url" name="video_general_dialog_button_url" value="'.Mage::getStoreConfig('video/configuration/general/dialog_button_url').'" class="input-text" type="text"/>';
		return $_html;
	}
	
	public function getPlayerStyleHtml() {
		$_html = '<table cellspacing="0" style="text-align:center;">'.
                    	'<tr>'.
                        	'<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/fulldisplay.png') . '" width="180" height="155" /></td>'.
                            '<td><img src="'. Mage::getDesign()->getSkinUrl('treepodia/images/chromeless.png') . '" width="180" height="137" /></td>'.
                        '</tr>'.
                        '<tr>'.
                        	'<td><input type="radio" name="video_appearance_player_style" value="full"';	
		if( Mage::getStoreConfig('video/configuration/appearance/player_style') == '' || Mage::getStoreConfig('video/configuration/appearance/player_style') == 'full' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Full Display</td>'.
                            '<td><input type="radio" name="video_appearance_player_style" value="chromeless"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_style') == 'chromeless' )					
			$_html .= ' checked="checked"';
		 $_html .= ' /> Chromeless Display</td>'.
                        '</tr>'.
                    '</table>';
		return $_html;	
	}
	
	public function getPlayerSkinHtml() {
		$_html = '<table cellspacing="0" style="text-align:center;">'.
                    	'<tr>'.
                        	'<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-default.png') . '" width="180" height="155" /></td>'.
                            '<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-compact.png') . '" width="180" height="151" /></td>'.
                            '<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-blue.png') . '" width="180" height="153" /></td>'.
                            '<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-skyblue.png') . '" width="180" height="152" /></td>'.
                        '</tr>'.
                        '<tr>'.
                        	'<td><input type="radio" name="video_appearance_player_skin" value="default"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == '' || Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'default' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Default</td>'.
                            '<td><input type="radio" name="video_appearance_player_skin" value="compact".';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'compact' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Compact</td>'.
                            '<td><input type="radio" name="video_appearance_player_skin" value="Blue"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'Blue' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Blue</td>'.
                        	'<td><input type="radio" name="video_appearance_player_skin" value="SkyBlue"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'SkyBlue' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Sky Blue</td>'.
                        '</tr>'.
                        '<tr><td>&nbsp;</td></tr>'.
                        '<tr>'.
                        	'<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-purple.png') . '" width="180" height="152" /></td>'.
                            '<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-blackgreen.png') . '" width="180" height="151" /></td>'.
                            '<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-blackblue.png') . '" width="180" height="151" /></td>'.
                        	'<td><img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/player-blackred.png') . '" width="180" height="151" /></td>'.
                        '</tr>'.
                        '<tr>'.
                        	'<td><input type="radio" name="video_appearance_player_skin" value="Purple"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'Purple' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Purple</td>'.
                            '<td><input type="radio" name="video_appearance_player_skin" value="BlackGreen"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'BlackGreen' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Black Green</td>'.
                            '<td><input type="radio" name="video_appearance_player_skin" value="BlackBlue"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'BlackBlue' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Black Blue</td>'.
                            '<td><input type="radio" name="video_appearance_player_skin" value="BlackRed"';
		if( Mage::getStoreConfig('video/configuration/appearance/player_skin') == 'BlackRed' )
			$_html .= ' checked="checked"';
		$_html .= ' /> Black Red</td>'.
                        '</tr>'.
                    '</table>';
		return $_html;	
	}
	
	public function getAllowFullScreenHtml() {
		$_html = '<select id="video_apperance_allow_full_screen" name="video_apperance_allow_full_screen" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/appearance/allow_full_screen') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Yes</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/appearance/allow_full_screen') == '' || Mage::getStoreConfig('video/configuration/appearance/allow_full_screen') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>No</option>'.
                    '</select>';
		return $_html;	
	}
	
	public function getShowPlayButtonHtml() {
		$_html = '<select id="video_apperance_show_play_button" name="video_apperance_show_play_button" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/appearance/show_play_button') == '' || Mage::getStoreConfig('video/configuration/appearance/show_play_button') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Yes</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/appearance/show_play_button') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>No</option>'.
                    '</select>';
		return $_html;	
	}
	
	public function getPlayerWidthHtml() {
		$_html = '<input id="video_appearance_player_width" name="video_appearance_player_width" value="'.Mage::getStoreConfig('video/configuration/appearance/player_width').'" class="validate-digits validate-greater-than-zero input-text" type="text"/>';
		return $_html;
	}
	
	public function getPlayerHeightHtml() {
		$_html = '<input id="video_apperance_player_height" name="video_apperance_player_height" value="'.Mage::getStoreConfig('video/configuration/appearance/player_height').'" class="validate-digits input-text" type="text"/>';
		return $_html;
	}
	
	public function getAutoPlayHtml() {
		$_html = '<select id="video_playback_auto_play" name="video_playback_auto_play" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/playback/auto_play') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Yes</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/playback/auto_play') == '' || Mage::getStoreConfig('video/configuration/playback/auto_play') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>No</option>'.
                    '</select>';
		return $_html;	
	}
	
	public function getLoopPlayHtml() {
		$_html = '<select id="video_playback_loop_play" name="video_playback_loop_play" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/playback/loop_play') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Yes</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/playback/loop_play') == '' || Mage::getStoreConfig('video/configuration/playback/loop_play') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>No</option>'.
                    '</select>';
		return $_html;	
	}
	
	public function getPlayOnClickHtml() {
		$_html = '<select id="video_playback_play_on_click" name="video_playback_play_on_click" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/playback/play_on_click') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Yes</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/playback/play_on_click') == '' || Mage::getStoreConfig('video/configuration/playback/play_on_click') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>No</option>'.
                    '</select>';
		return $_html;	
	}
	
	public function getMuteHtml() {
		$_html = '<select id="video_playback_mute" name="video_playback_mute" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/playback/mute') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Yes</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/playback/mute') == '' || Mage::getStoreConfig('video/configuration/playback/mute') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>No</option>'.
                    '</select>';
		return $_html;
	}
	
	public function getInitialVolumeHtml() {
		$_html = '<input id="video_playback_initial_volume" name="video_playback_initial_volume" value="'.Mage::getStoreConfig('video/configuration/playback/initial_volume').'" class="validate-digits input-text" type="text"/>';
		return $_html;
	}
	
	public function getShareItemHtml() {
		$shareItems = explode(',', Mage::getStoreConfig('video/configuration/social/share_items'));
		$_html = '<ul>'.
                    	'<li><input type="checkbox" name="video_social_share_items[]" value="facebook"';
		if( in_array('facebook', $shareItems) )
			$_html .= ' checked="checked"';
		$_html .= ' /> <img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/share_facebook.jpg') . '" style="vertical-align:middle;" /></li>'.
                        '<li><input type="checkbox" name="video_social_share_items[]" value="linkedin"';
		if( in_array('linkedin', $shareItems) )
			$_html .= ' checked="checked"';
		$_html .= ' /> <img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/share_linkedin.jpg') . '" style="vertical-align:middle;" /></li>'.
                        '<li><input type="checkbox" name="video_social_share_items[]" value="twitter"';
		if( in_array('twitter', $shareItems) )
			$_html .= ' checked="checked"';
		$_html .= ' /> <img src="' . Mage::getDesign()->getSkinUrl('treepodia/images/share_twitter.jpg') . '" style="vertical-align:middle;" /></li>'.
                    '</ul>';
		return $_html;	
	}
	
	public function getATCButtonEnableHtml() {
		$_html = '<select id="video_advance_atc_enable" name="video_advance_atc_enable" class="select">'.
                        '<option value="1"';
		if( Mage::getStoreConfig('video/configuration/advance/atc_enable') == '1' )
			$_html .= ' selected="selected"';
		$_html .= '>Enable</option>'.
                        '<option value="0"';
		if( Mage::getStoreConfig('video/configuration/advance/atc_enable') == '' || Mage::getStoreConfig('video/configuration/advance/atc_enable') == '0' )
			$_html .= ' selected="selected"';
		$_html .= '>Disable</option>'.
                    '</select>';
		return $_html;
	}
	
	public function getATCLabelHtml() {
		$_html = '<input id="video_advance_atc_label" name="video_advance_atc_label" value="'.Mage::getStoreConfig('video/configuration/advance/atc_label').'" class="input-text" type="text"/>';
		return $_html;
	}
}
?>