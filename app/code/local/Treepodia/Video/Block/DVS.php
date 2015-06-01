<?php
class Treepodia_Video_Block_DVS extends Mage_Core_Block_Template
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
		if (!$this->getTemplate() || !Mage::getStoreConfig('video/general/enabled')) {
			return '';
		}
		$html = $this->renderView();
		return $html;
    }
	
	public function renderDVS() {
		$_html = '';
		if (Mage::registry('current_product')) :
			$_product = $this->_getData('product');
			if (!$_product)
				$_product = Mage::registry('product');	
			$width = 640;
			$height = 400;
			if( Mage::getStoreConfig('video/configuration/appearance/player_width') != '' )
				$width = Mage::getStoreConfig('video/configuration/appearance/player_width');
			if( Mage::getStoreConfig('video/configuration/appearance/player_height') != '' )
				$height = Mage::getStoreConfig('video/configuration/appearance/player_height');
			$_html = '<meta property="og:video" content="http://api.treepodia.com/video/compact.swf?player_api_base=http://api.treepodia.com/video/overlay/&amp;player_chromeless=true&amp;player_skin_on_top=false&amp;bgcolor=0xffffff&amp;video_auto_play=true&amp;video_play_on_click=true&amp;player_allow_full_screen=true&amp;player_callback=_trpd_vid_cbk_0&amp;player_show_logo=true&amp;audio_init_mute=false&amp;video_url=http://api.treepodia.com/video/get/'.Mage::getStoreConfig('video/general/store_uuid').'/'.$_product->getSku().'" />
<meta property="og:video:secure_url" content="https://api.treepodia.com/video/compact.swf?player_api_base=https://api.treepodia.com/video/overlay/&amp;player_chromeless=true&amp;player_skin_on_top=false&amp;bgcolor=0xffffff&amp;video_auto_play=true&amp;video_play_on_click=true&amp;player_allow_full_screen=true&amp;player_callback=_trpd_vid_cbk_0&amp;player_show_logo=true&amp;audio_init_mute=false&amp;video_url=https://api.treepodia.com/video/get/'.Mage::getStoreConfig('video/general/store_uuid').'/'.$_product->getSku().'" />
<meta property="og:video:type" content="application/mp4" />
<meta property="og:video:width" content="'.$width.'" />
<meta property="og:video:height" content="'.$height.'" />
<link rel="video_src" href="http://api.treepodia.com/video/compact.swf?player_api_base=http://api.treepodia.com/video/overlay/&amp;player_chromeless=true&amp;player_skin_on_top=false&amp;bgcolor=0xffffff&amp;video_auto_play=true&amp;video_play_on_click=true&amp;player_allow_full_screen=true&amp;player_callback=_trpd_vid_cbk_0&amp;player_show_logo=true&amp;audio_init_mute=false&amp;video_url=http://api.treepodia.com/video/get/'.Mage::getStoreConfig('video/general/store_uuid').'/'.$_product->getSku().'" />
<meta name="medium" content="video">
<meta name="video_width" content="'.$width.'">
<meta name="video_height" content="'.$height.'">
<meta name="video_type" content="video/mp4" />';
		endif;
		echo $_html;
	}
}
?>