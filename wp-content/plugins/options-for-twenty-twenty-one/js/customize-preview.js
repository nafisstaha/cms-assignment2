(function($) {

	wp.customize('body_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('body').css('font-size', '1.25rem');
		    } else {
                $('body').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('hide_site_header', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-header').css('display', 'none');
		    } else {
                $('.site-header').css('display', 'block');
		    }
        });
	});

	wp.customize('header_min_height', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.home #masthead').css('min-height', '0');
		    } else {
                $('.home #masthead').css('min-height', newval + 'vh');
		    }
        });
	});

	wp.customize('logo_border_bottom', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-header > .site-logo').css('border-bottom-width', '1px');
		    } else {
                $('.site-header > .site-logo').css('border-bottom-width', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('logo_align', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-header > .site-logo').css('text-align', 'center');
                $('.site-header > .site-branding > .site-logo').css('text-align', 'left');
		    } else {
                $('.site-header > .site-logo, .site-header > .site-branding > .site-logo').css('text-align', newval);
		    }
		});
	});

	wp.customize('hide_site_title', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-title').css('display', 'none');
		    } else {
                $('.site-title').css('display', 'block');
		    }
        });
	});

	wp.customize('site_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-title').css('font-size', '1.5rem');
		    } else {
                $('.site-title').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('site_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-title, .site-title a').css('font-weight', 'normal');
		    } else {
                $('.site-title, .site-title a').css('font-weight', newval);
		    }
        });
	});

	wp.customize('site_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-title, .site-footer > .site-info .site-name').css('text-transform', 'uppercase');
		    } else {
                $('.site-title, .site-footer > .site-info .site-name').css('text-transform', newval);
		    }
		});
	});

	wp.customize('remove_site_title_underline', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-title a').css('text-decoration', 'none');
		    } else {
                $('.site-title a').css('text-decoration', 'underline');
		    }
        });
	});

	wp.customize('hide_site_description', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-description').css('display', 'none');
		    } else {
                $('.site-description').css('display', 'block');
		    }
        });
	});

	wp.customize('site_description_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-description').css('font-size', '1rem');
		    } else {
                $('.site-description').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('site_description_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-description').css('font-weight', 'normal');
		    } else {
                $('.site-description').css('font-weight', newval);
		    }
        });
	});

	wp.customize('header_border_bottom_width', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-header').css('border-bottom-width', '0');
		    } else {
                $('.site-header').css('border-bottom-width', newval + 'px');
		    }
        });
	});

	wp.customize('header_border_bottom_style', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-header').css('border-bottom-style', 'solid');
		    } else {
                $('.site-header').css('border-bottom-style', newval);
		    }
        });
	});

	wp.customize('navigation_border_width', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container').css('border-width', '0');
		    } else {
                $('.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container').css('border-width', newval + 'px');
		    }
        });
	});

	wp.customize('navigation_border_style', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container').css('border-style', 'solid');
		    } else {
                $('.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container').css('border-style', newval);
		    }
        });
	});

	wp.customize('nav_burger_background_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.menu-button-container #primary-mobile-menu').css('background-color', 'transparent');
		    } else {
                $('.menu-button-container #primary-mobile-menu').css('background-color', newval);
		    }
		});
	});

	wp.customize('nav_link_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.primary-navigation .primary-menu-container > ul > .menu-item').css('text-transform', 'none');
		    } else {
                $('.primary-navigation .primary-menu-container > ul > .menu-item').css('text-transform', newval);
		    }
		});
	});

	wp.customize('nav_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.primary-navigation a').css('font-weight', 'normal');
		    } else {
                $('.primary-navigation a').css('font-weight', newval);
		    }
        });
	});

	wp.customize('content_padding_top', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-main').css('padding-top', '30px');
		    } else {
                $('.site-main').css('padding-top', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('hide_page_headers', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.page .entry-header, .single .entry-header').css('display', 'none');
		    } else {
                $('.page .entry-header, .single .entry-header').css('display', 'block');
		    }
        });
	});

	wp.customize('hide_featured_images', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.page .entry-header .post-thumbnail, .single .entry-header .post-thumbnail, .page-header .archive-thumbnail').css('display', 'none');
		    } else {
                $('.page .entry-header .post-thumbnail, .single .entry-header .post-thumbnail, .page-header .archive-thumbnail').css('display', 'block');
		    }
        });
	});

	wp.customize('title_background_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.singular .entry-header').css('background-color', 'transparent');
                $('.singular .entry-header').css('padding-left', '0');
                $('.singular .entry-header').css('padding-top', '0');
                $('.singular .entry-header').css('padding-right', '0');
		    } else {
                $('.singular .entry-header').css('background-color', newval);
                $('.singular .entry-header').css('padding-left', '30px');
                $('.singular .entry-header').css('padding-top', '30px');
                $('.singular .entry-header').css('padding-right', '30px');
		    }
		});
	});

	wp.customize('page_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.singular .entry-title, .blog .page-title, .error404 .page-title').css('font-size', '4rem');
		    } else {
                $('.singular .entry-title, .blog .page-title, .error404 .page-title').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('page_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.entry-title, h1.entry-title, .page-title, h1.page-title').css('font-weight', '300');
		    } else {
                $('.entry-title, h1.entry-title, .page-title, h1.page-title').css('font-weight', newval);
		    }
        });
	});

	wp.customize('page_title_padding_bottom', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.singular .entry-header, .page-header').css('padding-bottom', '60px');
		    } else {
                $('.singular .entry-header, .page-header').css('padding-bottom', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('title_border_bottom', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.singular .entry-header, .page-header').css('border-bottom-width', '3px');
		    } else {
                $('.singular .entry-header, .page-header').css('border-bottom-width', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('post_footer_border_bottom', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-main > article > .entry-footer').css('border-bottom-width', '1px');
		    } else {
                $('.site-main > article > .entry-footer').css('border-bottom-width', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('post_footer_border_top', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.single .site-main > article > .entry-footer').css('border-top-width', '3px');
		    } else {
                $('.single .site-main > article > .entry-footer').css('border-top-width', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('title_margin_bottom', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.singular .entry-header, .page-header').css('margin-bottom', '90px');
		    } else {
                $('.singular .entry-header, .page-header').css('margin-bottom', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('hide_archive_titles', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.archive .page-header').css('display', 'none');
		    } else {
                $('.archive .page-header').css('display', 'block');
		    }
        });
	});

	wp.customize('archive_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.archive .page-title, .search .page-title').css('font-size', '4rem');
		    } else {
                $('.archive .page-title, .search .page-title').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('page_title_letter_spacing', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.entry-title, .page-title').css('letter-spacing', 'normal');
		    } else {
                $('.entry-title, .page-title').css('letter-spacing', (newval / 100) + 'em');
		    }
        });
	});

	wp.customize('hr_width', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('hr, hr.wp-block-separator').css('border-bottom-width', '1px');
		    } else {
                $('hr, hr.wp-block-separator').css('border-bottom-width', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('hide_taxonomies', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.post-taxonomies').css('display', 'none');
		    } else {
                $('.post-taxonomies').css('display', 'block');
		    }
        });
	});

	wp.customize('hide_tags', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.archive .entry-footer .tags-links, .single .site-main>article>.entry-footer .tags-links').css('display', 'none');
		    } else {
                $('.archive .entry-footer .tags-links, .single .site-main>article>.entry-footer .tags-links').css('display', 'block');
		    }
        });
	});

	wp.customize('hide_cat', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.archive .entry-footer .cat-links, .single .site-main>article>.entry-footer .cat-links').css('display', 'none');
		    } else {
                $('.archive .entry-footer .cat-links, .single .site-main>article>.entry-footer .cat-links').css('display', 'block');
		    }
        });
	});

	wp.customize('hide_date', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.posted-on').css('display', 'none');
		    } else {
                $('.posted-on').css('display', 'block');
		    }
        });
	});

	wp.customize('hide_post_navigation', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.post-navigation').css('display', 'none');
		    } else {
                $('.post-navigation').css('display', 'block');
		    }
        });
	});

	wp.customize('footer_widget_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.widget-area').css('font-size', '1rem');
		    } else {
                $('.widget-area').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('footer_nav_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.footer-navigation').css('font-size', '1rem');
		    } else {
                $('.footer-navigation').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('footer_nav_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.footer-navigation-wrapper').css('font-weight', '400');
		    } else {
                $('.footer-navigation-wrapper').css('font-weight', newval);
		    }
        });
	});

	wp.customize('hide_site_info', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-footer>.site-info').css('display', 'none');
		    } else {
                $('.site-footer>.site-info').css('display', 'flex');
		    }
        });
	});

	wp.customize('footer_site_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-footer > .site-info .site-name').css('font-size', '1.5rem');
		    } else {
                $('.site-footer > .site-info .site-name').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

	wp.customize('footer_border_top', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-footer>.site-info').css('border-top-width', '3px');
		    } else {
                $('.site-footer>.site-info').css('border-top-width', (newval - 1) + 'px');
		    }
        });
	});

	wp.customize('footer_site_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-footer > .site-info .site-name').css('text-transform', 'uppercase');
		    } else {
                $('.site-footer > .site-info .site-name').css('text-transform', newval);
		    }
		});
	});

	wp.customize('powered_by_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-footer > .site-info').css('font-size', '1.125rem');
		    } else {
                $('.site-footer > .site-info').css('font-size', (newval / 1000) + 'rem');
		    }
        });
	});

})(jQuery);
