window.pulsestorm_tinymce_loaded = true;
if(window.tinyMceWysiwygSetup)
{
    tinyMceWysiwygSetup.prototype.originalGetSettings = tinyMceWysiwygSetup.prototype.getSettings;
    tinyMceWysiwygSetup.prototype.getSettings = function(mode)
    {
        var settings = this.originalGetSettings(mode);
        //add any extra settings you'd like below
	settings.plugins = settings.plugins + ',template';

	settings.template_templates = [
        {title: "Hero",      src: "/js/tiny_mce_templates/tpl-hero.htm",      description: "Simple Template"  },
        {title: "Carousel",      src: "/js/tiny_mce_templates/tpl-carousel.htm",      description: "Simple Template"  },
        {title: "MegaMenu",      src: "/js/tiny_mce_templates/tpl-megamenu.htm",      description: "Simple Template"  },
        {title: "Side Menu",      src: "/js/tiny_mce_templates/tpl-sidemenu.htm",      description: "Simple Template"  },
        {title: "Landing Page - Category",      src: "/js/tiny_mce_templates/tpl-landing-category.htm",      description: "Simple Template"  },
        {title: "Landing Page -  Moments",      src: "/js/tiny_mce_templates/tpl-landing-moments.htm",      description: "Simple Template"  },
        {title: "XML Layout Updates",      src: "/js/tiny_mce_templates/tpl-xml.htm",      description: "Simple Template"  },
	    {title: "2 column grid",		src: "/js/tiny_mce_templates/grid-cols2.htm",		description: "Simple Template"	},
	    {title: "3 column grid",		src: "/js/tiny_mce_templates/grid-cols3.htm",		description: "Simple Template"	},
	    {title: "4 column grid",		src: "/js/tiny_mce_templates/grid-cols4.htm",		description: "Simple Template"	},
        {title: "4 column grid w/ colspan",        src: "/js/tiny_mce_templates/grid-cols4-center.htm",       description: "Simple Template"  },
	    {title: "5 column grid",		src: "/js/tiny_mce_templates/grid-cols5.htm",		description: "Simple Template"	},
        {title: "Simple grid",      src: "/js/tiny_mce_templates/grid-simple.htm",      description: "Simple Template"  }
	];
	settings.theme_advanced_buttons5 = "template";

        //makes "placeholder" a valid element for inputs
        //settings.extended_valid_elements = 'input[placeholder|accept|alt|checked|disabled|maxlength|name|readonly|size|src|type|value]';
        return settings;
    };
}
