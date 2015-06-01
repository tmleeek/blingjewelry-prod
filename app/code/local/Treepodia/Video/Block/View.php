<?php
class Treepodia_Video_Block_View extends Mage_Core_Block_Template
{
	protected function _prepareLayout()
	{
		$this->getLayout()->getBlock('head')->addCss('treepodia/css/style.css');
		$block = $this->getLayout()->createBlock( 'Treepodia_Video_Block_DVS', 'video_dvs', array('template' => 'treepodia/video/dvs.phtml') );
		$this->getLayout()->getBlock('head')->append($block);
		return parent::_prepareLayout();
	}
	
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
	
	public function renderVideo() {
		$_html = '';
		$width = 640;
		$height = 400;
        $_product = $this->_getData('product');
        if (!$_product)
            $_product = Mage::registry('product');

		if( Mage::getStoreConfig('video/configuration/appearance/player_width') != '' ) :
			$width = Mage::getStoreConfig('video/configuration/appearance/player_width');
		endif;
		if( Mage::getStoreConfig('video/configuration/appearance/player_height') != '' ) :
			$height = Mage::getStoreConfig('video/configuration/appearance/player_height');
		endif;

		if( Mage::getStoreConfig('video/configuration/general/kind') == 'embedded' )
			$_html = '<div id="trpdVideoDiv" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">
			<meta itemprop="name" content="'.$_product->getName().'" />
			<meta itemprop="description" content="'.$_product->getMetaDescription().'" />
			<meta itemprop="thumbnailUrl" content="'.$_product->getImageUrl().'" />
			<meta itemprop="contentURL" content="http://api.treepodia.com/video/get/'.Mage::getStoreConfig('video/general/store_uuid').'/'.$_product->getSku().'" />
			<meta itemprop="embedURL" content="http://api.treepodia.com/video/treepodia_player.swf?video=http://api.treepodia.com/video/get/'.Mage::getStoreConfig('video/general/store_uuid').'/'.$_product->getSku().'" />
			<div id="video-location"></div>
			<noscript>
			<object type="application/x-shockwave-flash" data="http://api.treepodia.com/video/compact.swf" width="'.$width.'px" height="'.$height.'px"  title="product video player" rel="media:video">
			<param name="movie" value="http://api.treepodia.com/video/compact.swf" />
			<param name="allowFullScreen" value="true" />
			<param name="allowScriptAccess" value="always" />
			<param name="bgcolor" value="#ffffff" />
			<param name="flashvars" value="player_api_base=http://api.treepodia.com/video/overlay/&amp;player_chromeless=false&amp;player_skin_on_top=false&amp;bgcolor=0xffffff&amp;video_auto_play=false&amp;video_play_on_click=true&amp;player_allow_full_screen=true&amp;player_callback=_trpd_vid_cbk_0&amp;player_show_logo=true&amp;audio_init_mute=false&amp;video_url=http://api.treepodia.com/video/get/'.Mage::getStoreConfig('video/general/store_uuid').'/'.$_product->getSku().'" />
			</object>
			</noscript>
</div>';
		if( Mage::getStoreConfig('video/configuration/general/kind') == 'dialog' ) :
			$_html .= '<script type="text/javascript">' .
							'document.write(unescape("%3Cscript src=\'" + document.location.protocol + "//dxa05szpct2ws.cloudfront.net/utils/trpdDialog/video-dialog.min.js\' type=\'text/javascript\'%3E%3C/script%3E"));'.
							'</script>';
		endif;	
		$_html .= '<script type="text/javascript">document.write(unescape("%3Cscript src=\'" + document.location.protocol + "//dxa05szpct2ws.cloudfront.net/TreepodiaAsyncLoader.js\' type=\'text/javascript\'%3E%3C/script%3E"));</script>';
		$_html .= '<script type="text/javascript">' .
					'var video; var product;' .
					'function initTreepodia() { product = Treepodia.getProduct("'.Mage::getStoreConfig('video/general/store_uuid').'", "'.$_product->getSku().'"); product.requestVideo(handleVideo); }'.
					'function handleVideo(vid) { video = vid;'.
						'if(vid.hasVideos()) {'.
						'video.setPlayer("'.Mage::getStoreConfig('video/configuration/appearance/player_skin').'");';
		if( Mage::getStoreConfig('video/configuration/appearance/video_appearance_player_style') == 'chromeless' )
			$_html .= 'video.setChromeless("chromeless");';
		if( Mage::getStoreConfig('video/configuration/appearance/allow_full_screen') )
			$_html .= 'video.setAllowFullScreen(true);';
		if( !Mage::getStoreConfig('video/configuration/appearance/show_play_button') )
			$_html .= 'video.setShowCenterPlay(false);';
		if( Mage::getStoreConfig('video/configuration/appearance/player_width') != '' )
			$_html .= 'video.setWidth('.Mage::getStoreConfig('video/configuration/appearance/player_width').');';
		if( Mage::getStoreConfig('video/configuration/appearance/player_height') != '' )
			$_html .= 'video.setHeight('.Mage::getStoreConfig('video/configuration/appearance/player_height').');';
		if( Mage::getStoreConfig('video/configuration/playback/auto_play') )
			$_html .= 'video.setAutoplay(true);';
		if( Mage::getStoreConfig('video/configuration/playback/loop_play') )
			$_html .= 'video.setLoop(true);';
		if( Mage::getStoreConfig('video/configuration/playback/play_on_click') )
			$_html .= 'video.setPlayOnClick(true);';
		if( Mage::getStoreConfig('video/configuration/playback/mute') )
			$_html .= 'video.setMute(true);';
		if( Mage::getStoreConfig('video/configuration/appearance/initial_volume') != '' )
			$_html .= 'video.setInitialVolume('.Mage::getStoreConfig('video/configuration/appearance/initial_volume').');';
		if( Mage::getStoreConfig('video/configuration/social/share_items') != '' ) :
			$shareItems = explode(',', Mage::getStoreConfig('video/configuration/social/share_items'));
			foreach($shareItems as $item)
				$_html .= 'video.addShareItem("'.$item.'");';
		endif;
		if( Mage::getStoreConfig('video/configuration/advance/atc_enable') )
			$_html .= 'video.addATCJavaScriptAction("'.Mage::getStoreConfig('video/configuration/advance/atc_label').'", "productAddToCartForm.submit(this)");';
		
		if( Mage::getStoreConfig('video/configuration/general/kind') == 'dialog' )
			$_html .= 'document.getElementById("video-btn").style.display = "inline";';
		else
			$_html .= 'video.show("video-location");';
		$_html .= '}}'.
				'</script>';
		$_html .= '';
		echo $_html;
	}
}
?>