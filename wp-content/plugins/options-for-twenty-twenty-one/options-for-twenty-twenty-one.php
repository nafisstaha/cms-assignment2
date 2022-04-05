<?php
/*
 * Plugin Name: Options for Twenty Twenty-One
 * Version: 1.6.4
 * Plugin URI: https://webd.uk/product/options-for-twenty-twenty-one-upgrade/
 * Description: Adds powerful customizer options to modify all aspects of the default Wordpress theme Twenty Twenty-One
 * Author: Webd Ltd
 * Author URI: https://webd.uk
 * Text Domain: options-for-twenty-twenty-one
 */



if (!defined('ABSPATH')) {
    exit('This isn\'t the page you\'re looking for. Move along, move along.');
}



if (!class_exists('options_for_twenty_twenty_one_class')) {

	class options_for_twenty_twenty_one_class {

        public static $version = '1.6.4';

		function __construct() {

            if (version_compare(get_bloginfo('version'), '5.8', '>=') && get_theme_mod('enable_template_editor')) {

                add_theme_support('block-templates');

            }

            add_action('customize_register', array($this, 'oftto_customize_register'), 999);
            add_action('widgets_init', array($this, 'oftto_widgets_init'), 11);
            add_action('customize_preview_init', array($this, 'oftto_enqueue_customize_preview_js'));

            if (is_admin()) {

                add_action('after_setup_theme', array($this, 'oftto_editor_styles'), 11);
                add_filter('pre_http_request', array($this, 'oftto_pre_http_request'), 10, 3);
                add_action('customize_controls_enqueue_scripts', array($this, 'oftto_enqueue_customizer_css'));
                add_action('customize_controls_enqueue_scripts', array($this, 'oftto_enqueue_customize_controls_js'));

                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'oftto_add_plugin_action_links'));
                add_action('admin_notices', 'ofttoCommon::admin_notices');
                add_action('wp_ajax_dismiss_oftto_notice_handler', 'ofttoCommon::ajax_notice_handler');
                add_action('customize_controls_enqueue_scripts', 'ofttoCommon::enqueue_customize_controls_js');

            } else {

                add_action('wp_head', array($this, 'oftto_frontend_styles'));
                add_action('wp_footer', array($this, 'oftto_frontend_javascript'));

            }

            add_action('customize_register', 'webd_customize_register');

		}

		function oftto_add_plugin_action_links($links) {

			$settings_links = ofttoCommon::plugin_action_links(admin_url('customize.php'));

			return array_merge($settings_links, $links);

		}

        function oftto_customize_register($wp_customize) {

            $section_description = ofttoCommon::control_section_description();
            $upgrade_nag = ofttoCommon::control_setting_upgrade_nag();



            $wp_customize->add_section('oftto_general', array(
                'title'     => __('General Options', 'options-for-twenty-twenty-one'),
                'description'  => __('Use these options to customise the overall site design.', 'options-for-twenty-twenty-one') . ' ' . $section_description,
                'priority'     => 0
            ));

if (version_compare(get_bloginfo('version'), '5.8', '>=')) {

            $wp_customize->add_setting('enable_template_editor', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('enable_template_editor', array(
                'label'         => __('Enable Template Editor', 'options-for-twenty-twenty-one'),
                'description'   => __('Enable the full site editing tool Template Editor introduced in Wortdpress v5.8.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'enable_template_editor',
                'type'          => 'checkbox'
            ));

}

            $wp_customize->add_setting('hide_print_urls', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_print_urls', array(
                'label'         => __('Hide URLs When Printing', 'options-for-twenty-twenty-one'),
                'description'   => __('Prevent URLs from showing when printing a page.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'hide_print_urls',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('body_font_size', array(
                'default'           => 1250,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('body_font_size', array(
                'label'         => __('Body Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of regular text.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'body_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 625,
                    'max'   => 2500,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('remove_link_underlines', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('remove_link_underlines', array(
                'label'         => __('Remove Link Underlines', 'options-for-twenty-twenty-one'),
                'description'   => __('Remove the underlines shown under links throughout the site.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'remove_link_underlines',
                'type'          => 'checkbox'
            ));

            $sidebar_locations = array(
                'all' => __('Everywhere', 'options-for-twenty-twenty-one'),
                'front_page' => __('Front Page', 'options-for-twenty-twenty-one'),
                'home' => __('Main Blog Page', 'options-for-twenty-twenty-one'),
                'page' => __('Single Pages', 'options-for-twenty-twenty-one'),
                'single' => __('Single Posts', 'options-for-twenty-twenty-one'),
                'archive' => __('Archive Pages', 'options-for-twenty-twenty-one')
            );

            foreach(get_post_types(array('public' => 'true')) as $post_type) {

                $post_type_object = get_post_type_object($post_type);
                $sidebar_locations['post_type/' . $post_type] = __('Post Type: ', 'options-for-twenty-twenty-one') . $post_type_object->label;

                foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {

                    if ($taxonomy->name !== 'post_format') {

                        $sidebar_locations['taxonomy/' . $post_type . '/' . $taxonomy->name] = $post_type_object->label . __(' Taxonomy: ', 'options-for-twenty-twenty-one') . $taxonomy->label;

                    }

                }

            }

            $wp_customize->add_setting('inject_sidebar', array(
                'default'       => array(),
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_multiple_options'
            ));
            $wp_customize->add_control(new webd_Customize_Control_Checkbox_Multiple($wp_customize, 'inject_sidebar', array(
                'label'         => __('Inject Sidebar', 'options-for-twenty-twenty-one'),
                'description'   => __('Inject a sidebar into the theme by choosing where you want it shown. Remember, you\'ll need to add widgets to the sidebar for it to show!', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'inject_sidebar',
                'choices'       => $sidebar_locations
            )));

            $wp_customize->add_setting('aligndefault_max_width', array(
                'default'           => 610,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('aligndefault_max_width', array(
                'label'         => __('Align Default Max Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the maximum width of align default content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'aligndefault_max_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 382,
                    'max'   => 2160,
                    'step'  => 2
                )
            ));

            $wp_customize->add_setting('no_aligndefault_max_width', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('no_aligndefault_max_width', array(
                'label'         => __('No Max Width on Align Default', 'options-for-twenty-twenty-one'),
                'description'   => __('Remove the align default max width restriction completely..', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'no_aligndefault_max_width',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('alignwide_max_width', array(
                'default'           => 1240,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('alignwide_max_width', array(
                'label'         => __('Align Wide Max Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the maximum width of align wide content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'alignwide_max_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 622,
                    'max'   => 2160,
                    'step'  => 2
                )
            ));

            $wp_customize->add_setting('no_alignwide_max_width', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('no_alignwide_max_width', array(
                'label'         => __('No Max Width on Align Wide', 'options-for-twenty-twenty-one'),
                'description'   => __('Remove the align wide max width restriction completely.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'no_alignwide_max_width',
                'type'          => 'checkbox'
            ));

if (class_exists('woocommerce')) {

            $wp_customize->add_setting('woocommerce_max_width', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('woocommerce_max_width', array(
                'label'         => __('WooCommerce Max Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Set WooCommerce content width to match the theme.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'woocommerce_max_width',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None (Default)', 'options-for-twenty-twenty-one'),
                    'wide' => __('Align Wide', 'options-for-twenty-twenty-one'),
                    'default' => __('Align Default', 'options-for-twenty-twenty-one')
                )
            ));

}

            $taxonomies = array(
                'blog' => 'Posts / Blog Page',
                'search' => 'Search Page'
            );

            foreach(get_taxonomies(array('public' => 'true'), 'objects') as $taxonomy) {

                $taxonomies[$taxonomy->name] = __('Taxonomy: ', 'options-for-twenty-twenty-one') . ': ' . $taxonomy->label;

            }

            unset($taxonomies['post_format']);
            $row_taxonomies = get_theme_mod('archive_row_template');

            if ($row_taxonomies && is_array($row_taxonomies)) {

                foreach ($row_taxonomies as $row_taxonomy) {

                    if (isset($taxonomies[$row_taxonomy])) { unset($taxonomies[$row_taxonomy]); }

                }

            }

            $column_taxonomies = get_theme_mod('archive_column_template');

            if ($column_taxonomies && is_array($column_taxonomies)) {

                foreach ($column_taxonomies as $column_taxonomy) {

                    if (isset($taxonomies[$column_taxonomy])) { unset($taxonomies[$column_taxonomy]); }

                }

            }

            $card_taxonomies = get_theme_mod('archive_card_template');

            if ($card_taxonomies && is_array($card_taxonomies)) {

                foreach ($card_taxonomies as $card_taxonomy) {

                    if (isset($taxonomies[$card_taxonomy])) { unset($taxonomies[$card_taxonomy]); }

                }

            }

            $wp_customize->add_setting('archive_grid_template', array(
                'default'       => array(),
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_multiple_options'
            ));
            $wp_customize->add_control(new webd_Customize_Control_Checkbox_Multiple($wp_customize, 'archive_grid_template', array(
                'label'         => __('Archive Grid Template', 'options-for-twenty-twenty-one'),
                'description'   => __('Show posts in a grid format on taxonomy pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_general',
                'settings'      => 'archive_grid_template',
                'choices'       => $taxonomies
            )));



            $wp_customize->add_section('oftto_header', array(
                'title'     => __('Header Options', 'options-for-twenty-twenty-one'),
                'description'  => __('Use these options to customise the header.', 'options-for-twenty-twenty-one') . ' ' . $section_description,
                'priority'     => 0
            ));



            $wp_customize->add_setting('hide_site_header', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_site_header', array(
                'label'         => __('Hide Site Header', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the site\'s header including site title, description, logo and navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'hide_site_header',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('wide_site_header', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('wide_site_header', array(
                'label'         => __('Wide Site Header', 'options-for-twenty-twenty-one'),
                'description'   => __('Full width site header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'wide_site_header',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('absolute_site_header', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('absolute_site_header', array(
                'label'         => __('Show Content Behind Header', 'options-for-twenty-twenty-one'),
                'description'   => __('This option will allow site content (like a cover block) to appear behind the site header. Don\'t forget to reduce the "Content Padding Top" in "Content Options". Note: This will disable other options like header background and height options.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'absolute_site_header',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('header_background_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_background_color', array(
                'label'         => __('Header Background Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color behind the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
            	'settings'      => 'header_background_color'
            )));

            $wp_customize->add_setting('header_background_image', array(
                'default'           => false,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'header_background_image', array(
                'mime_type'     => 'image',
                'label'         => __('Header Background Image', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose an image to use in the header background.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'header_background_image'
            )));

            $wp_customize->add_setting('fix_header_background_image', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('fix_header_background_image', array(
                'label'         => __('Fix Header Background Image', 'options-for-twenty-twenty-one'),
                'description'   => __('This will create a parallax effect but you will need to specify an image large enough to cover the browser window.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'fix_header_background_image',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('header_min_height', array(
                'default'           => 0,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('header_min_height', array(
                'label'         => __('Homepage Header Min Height', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose the minimum height of the site header on the home page.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'header_min_height',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 100,
                    'step'  => 5
                )
            ));

            $wp_customize->add_setting('site_wide_header_height', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('site_wide_header_height', array(
                'label'         => __('Site Wide Header Height', 'options-for-twenty-twenty-one'),
                'description'   => __('Set the min height of the header on all pages, not just the home page.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_wide_header_height',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('header_padding_top', array(
                'default'           => 73,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('header_padding_top', array(
                'label'         => __('Header Padding Top', 'options-for-twenty-twenty-one'),
                'description'   => __('Reduce the padding above the header on larger screens (smaller screens require the padding to give height to the header).', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'header_padding_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 73,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('header_padding_bottom', array(
                'default'           => 91,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('header_padding_bottom', array(
                'label'         => __('Header Padding Bottom', 'options-for-twenty-twenty-one'),
                'description'   => __('Reduce the padding below the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'header_padding_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 91,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('logo_prevent_focus_border', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('logo_prevent_focus_border', array(
                'label'         => __('Prevent Logo Border', 'options-for-twenty-twenty-one'),
                'description'   => __('Prevent the dotted border when clicking the logo in Chrome and Firefox.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'logo_prevent_focus_border',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('logo_below_nav', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('logo_below_nav', array(
                'label'         => __('Logo Below Nav', 'options-for-twenty-twenty-one'),
                'description'   => __('Show the logo below the primary navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'logo_below_nav',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('logo_size', array(
                'default'           => 100,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('logo_size', array(
                'label'         => __('Logo Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the maximum width of the site logo in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'logo_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 20,
                    'max'   => 1240,
                    'step'  => 10
                )
            ));

            $wp_customize->add_setting('logo_border_bottom', array(
                'default'           => 2,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('logo_border_bottom', array(
                'label'         => __('Logo Border Bottom', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border below the logo in the header which is visible when both logo and site title are shown.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'logo_border_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 11,
                    'step'  => 1
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('logo_align', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('logo_align', array(
                'label'         => __('Logo Align', 'options-for-twenty-twenty-one'),
                'description'   => __('Align the logo in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'logo_align',
                'type'          => 'select',
                'choices'       => array(
                    'left' => __('Left', 'options-for-twenty-twenty-one'),
                    '' => __('Center', 'options-for-twenty-twenty-one'),
                    'right' => __('Right', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('hide_site_title', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_site_title', array(
                'label'         => __('Hide Site Title', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the site title in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'hide_site_title',
                'type'          => 'checkbox',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('full_width_site_branding', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('full_width_site_branding', array(
                'label'         => __('Full Width Site Branding', 'options-for-twenty-twenty-one'),
                'description'   => __('Force the site branding to be full width on smaller screens, larger screens or both.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'full_width_site_branding',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Off', 'options-for-twenty-twenty-one'),
                    'mobile' => __('Mobile', 'options-for-twenty-twenty-one'),
                    'desktop' => __('Desktop', 'options-for-twenty-twenty-one'),
                    'both' => __('Both', 'options-for-twenty-twenty-one')
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('site_title_align', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('site_title_align', array(
                'label'         => __('Site Title Align', 'options-for-twenty-twenty-one'),
                'description'   => __('Align the site title in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_title_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-twenty-one'),
                    'left' => __('Left', 'options-for-twenty-twenty-one'),
                    'center' => __('Center', 'options-for-twenty-twenty-one'),
                    'right' => __('Right', 'options-for-twenty-twenty-one')
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('site_title_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'site_title_color', array(
                'label'         => __('Site Title Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the site title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
            	'settings'      => 'site_title_color',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            )));

            $wp_customize->add_setting('site_title_font_size', array(
                'default'           => 1500,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('site_title_font_size', array(
                'label'         => __('Site Title Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the site title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 750,
                    'max'   => 3000,
                    'step'  => 25
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('site_title_font_weight', array(
                'default'           => 400,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('site_title_font_weight', array(
                'label'         => __('Site Title Font Weight', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font weight of the site title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_title_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                )
            ));

            $wp_customize->add_setting('site_title_text_transform', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('site_title_text_transform', array(
                'label'         => __('Site Title Font Case', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font case of the site title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_title_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    'none' => __('None', 'options-for-twenty-twenty-one'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-twenty-one'),
                    '' => __('Uppercase', 'options-for-twenty-twenty-one'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('remove_site_title_underline', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('remove_site_title_underline', array(
                'label'         => __('Remove Site Title Underline', 'options-for-twenty-twenty-one'),
                'description'   => __('Remove the underline below the site title in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'remove_site_title_underline',
                'type'          => 'checkbox',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('hide_site_description', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_site_description', array(
                'label'         => __('Hide Site Description', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the site description in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'hide_site_description',
                'type'          => 'checkbox',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('site_description_align', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('site_description_align', array(
                'label'         => __('Site Description Align', 'options-for-twenty-twenty-one'),
                'description'   => __('Align the site description in the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_description_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-twenty-one'),
                    'left' => __('Left', 'options-for-twenty-twenty-one'),
                    'center' => __('Center', 'options-for-twenty-twenty-one'),
                    'right' => __('Right', 'options-for-twenty-twenty-one')
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('site_description_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'site_description_color', array(
                'label'         => __('Site Description Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the site description.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
            	'settings'      => 'site_description_color',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            )));

            $wp_customize->add_setting('site_description_font_size', array(
                'default'           => 1125,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('site_description_font_size', array(
                'label'         => __('Site Description Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the site description.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_description_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 560,
                    'max'   => 2250,
                    'step'  => 25
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('site_description_font_weight', array(
                'default'           => 400,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('site_description_font_weight', array(
                'label'         => __('Site Description Font Weight', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font weight of the site description.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'site_description_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                )
            ));

            $wp_customize->add_setting('header_border_bottom_width', array(
                'default'           => 0,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('header_border_bottom_width', array(
                'label'         => __('Header Border Bottom Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border below the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'header_border_bottom_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 10,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('header_border_bottom_style', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('header_border_bottom_style', array(
                'label'         => __('Header Border Bottom Style', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose the style of the border at the bottom of the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
                'settings'      => 'header_border_bottom_style',
                'type'          => 'select',
                'choices'       => array(
                    'dotted' => __('Dotted', 'options-for-twenty-twenty-one'),
                    'dashed' => __('Dashed', 'options-for-twenty-twenty-one'),
                    '' => __('Solid (Default)', 'options-for-twenty-twenty-one'),
                    'double' => __('Double', 'options-for-twenty-twenty-one'),
                    'groove' => __('3D Groove', 'options-for-twenty-twenty-one'),
                    'ridge' => __('3D Ridge', 'options-for-twenty-twenty-one'),
                    'inset' => __('3D Inset', 'options-for-twenty-twenty-one'),
                    'outset' => __('3D Outset', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('header_border_bottom_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_border_bottom_color', array(
                'label'         => __('Header Border Bottom Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose the color of the border at the bottom of the header.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_header',
            	'settings'      => 'header_border_bottom_color'
            )));



            $wp_customize->add_section('oftto_navigation', array(
                'title'     => __('Nav Options', 'options-for-twenty-twenty-one'),
                'description'  => __('Use these options to customise the navigation.', 'options-for-twenty-twenty-one') . ' ' . $section_description,
                'priority'     => 0
            ));



            $wp_customize->add_setting('move_nav_below_header', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('move_nav_below_header', array(
                'label'         => __('Move Nav Below Header', 'options-for-twenty-twenty-one'),
                'description'   => __('Move the primary navigation out of the header area on larger screens. Note: This option is not compatible with the Sticky Hamburger options.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'move_nav_below_header',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('mobile_nav_on_desktop', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('mobile_nav_on_desktop', array(
                'label'         => __('Mobile Nav on Desktop', 'options-for-twenty-twenty-one'),
                'description'   => __('Show the mobile navigation hamburger menu on all screen sizes.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'mobile_nav_on_desktop',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_nav_button_border', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_nav_button_border', array(
                'label'         => __('Hide Nav Button Border', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the dotted border on the mobile nav button.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'hide_nav_button_border',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('mobile_nav_align', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('mobile_nav_align', array(
                'label'         => __('Mobile Nav Align', 'options-for-twenty-twenty-one'),
                'description'   => __('Align the navigation on small screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'mobile_nav_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-twenty-one'),
                    'left' => __('Left', 'options-for-twenty-twenty-one'),
                    'center' => __('Center', 'options-for-twenty-twenty-one'),
                    'right' => __('Right', 'options-for-twenty-twenty-one')
                ),
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_are_title_and_tagline_displayed'
            ));

            $wp_customize->add_setting('desktop_nav_align', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('desktop_nav_align', array(
                'label'         => __('Desktop Nav Align', 'options-for-twenty-twenty-one'),
                'description'   => __('Align the navigation on larger screens. Not for use with hamburger nav on desktop.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'desktop_nav_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-twenty-one'),
                    'left' => __('Left', 'options-for-twenty-twenty-one'),
                    'center' => __('Center', 'options-for-twenty-twenty-one'),
                    'right' => __('Right', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('navigation_border_width', array(
                'default'           => 0,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('navigation_border_width', array(
                'label'         => __('Nav Border Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border around the primary navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'navigation_border_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 10,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('navigation_border_style', array(
                'default'       => 'solid',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('navigation_border_style', array(
                'label'         => __('Nav Border Style', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose the style of the border around the primary navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'navigation_border_style',
                'type'          => 'select',
                'choices'       => array(
                    'dotted' => __('Dotted', 'options-for-twenty-twenty-one'),
                    'dashed' => __('Dashed', 'options-for-twenty-twenty-one'),
                    'solid' => __('Solid (Default)', 'options-for-twenty-twenty-one'),
                    'double' => __('Double', 'options-for-twenty-twenty-one'),
                    'groove' => __('3D Groove', 'options-for-twenty-twenty-one'),
                    'ridge' => __('3D Ridge', 'options-for-twenty-twenty-one'),
                    'inset' => __('3D Inset', 'options-for-twenty-twenty-one'),
                    'outset' => __('3D Outset', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('navigation_border_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'navigation_border_color', array(
                'label'         => __('Nav Border Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose the color of the border around the primary navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'navigation_border_color'
            )));

            $wp_customize->add_setting('nav_background_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_background_color', array(
                'label'         => __('Nav Background Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the background color of the primary navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_background_color'
            )));

            $wp_customize->add_setting('desktop_nav_padding', array(
                'default'           => 0,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('desktop_nav_padding', array(
                'label'         => __('Desktop Nav Padding', 'options-for-twenty-twenty-one'),
                'description'   => __('Increase the padding above and below menu items in the primary navigation on larger screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'desktop_nav_padding',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 20,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('nav_burger_background_color', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_burger_background_color', array(
                'label'         => __('Nav Burger Background Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the background color of the hamburger icon on smaller screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_burger_background_color'
            )));

            $wp_customize->add_setting('hide_mobile_menu_text', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_mobile_menu_text', array(
                'label'         => __('Hide Mobile Menu Text', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the word "Menu" next to the hamburger menu on smaller screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'hide_mobile_menu_text',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('nav_burger_icon_size', array(
                'default'           => 24,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('nav_burger_icon_size', array(
                'label'         => __('Nav Burger Icon Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Increase the size of the hamburger icon on the mobile navigation button on larger screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_burger_icon_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 24,
                    'max'   => 96,
                    'step'  => 4
                )
            ));

            $wp_customize->add_setting('nav_burger_icon_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_burger_icon_color', array(
                'label'         => __('Nav Burger Icon Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the text and hamburger icon on the mobile navigation button.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_burger_icon_color'
            )));

            $wp_customize->add_setting('show_social_icons', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('show_social_icons', array(
                'label'         => __('Show Social Icons', 'options-for-twenty-twenty-one'),
                'description'   => __('Show the SVG social icons used in the footer in the primary nav.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'show_social_icons',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('nav_prevent_item_background', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('nav_prevent_item_background', array(
                'label'         => __('Prevent Background', 'options-for-twenty-twenty-one'),
                'description'   => __('Prevent the background when clicking links in Chrome and Firefox.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_prevent_item_background',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('nav_link_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_color', array(
                'label'         => __('Nav Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the navigation links.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_link_color'
            )));

            $wp_customize->add_setting('current_link_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'current_link_color', array(
                'label'         => __('Current Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the current page navigation links.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'current_link_color'
            )));

            $wp_customize->add_setting('nav_link_hover_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_hover_color', array(
                'label'         => __('Nav Link Hover Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the navigation hover links.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_link_hover_color'
            )));

            $wp_customize->add_setting('nav_mobile_link_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_mobile_link_color', array(
                'label'         => __('Nav Mobile Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the mobile navigation links.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_mobile_link_color'
            )));

            $wp_customize->add_setting('nav_mobile_link_hover_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_mobile_link_hover_color', array(
                'label'         => __('Nav Mobile Link Hover Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the mobile navigation hover links.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_mobile_link_hover_color'
            )));

            $wp_customize->add_setting('nav_link_text_transform', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('nav_link_text_transform', array(
                'label'         => __('Nav Link Font Case', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font case of the navigation menu items.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_link_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-twenty-one'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-twenty-one'),
                    'uppercase' => __('Uppercase', 'options-for-twenty-twenty-one'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('nav_desktop_font_size', array(
                'default'           => 1250,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('nav_desktop_font_size', array(
                'label'         => __('Nav Desktop Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the navigation on larger screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_desktop_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 625,
                    'max'   => 2500,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('nav_font_weight', array(
                'default'           => 400,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('nav_font_weight', array(
                'label'         => __('Nav Font Weight', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font weight of the navigation.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                )
            ));

            $wp_customize->add_setting('nav_desktop_item_padding', array(
                'default'           => 76,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('nav_desktop_item_padding', array(
                'label'         => __('Nav Desktop Item Padding', 'options-for-twenty-twenty-one'),
                'description'   => __('Adjust the horizontal padding of the navigation items on larger screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_desktop_item_padding',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 151,
                    'step'  => 1
                )
            ));





            $wp_customize->add_setting('nav_submenu_border_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_submenu_border_color', array(
                'label'         => __('Nav Sub-Menu Border Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the sub-menu border color.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_submenu_border_color'
            )));





            $wp_customize->add_setting('hide_submenu_caret', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_submenu_caret', array(
                'label'         => __('Hide Sub-Menu Carets', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the notch / tail from sub-menus in the primary navigation .', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'hide_submenu_caret',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('nav_submenu_background_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_submenu_background_color', array(
                'label'         => __('Nav Sub-Menu Background Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the sub-menu background color on larger screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_submenu_background_color'
            )));

            $wp_customize->add_setting('nav_submenu_font_size', array(
                'default'           => 1000,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('nav_submenu_font_size', array(
                'label'         => __('Nav Sub-Menu Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size in the navigation sub-menus.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_submenu_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 500,
                    'max'   => 2000,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('nav_submenu_link_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_submenu_link_color', array(
                'label'         => __('Nav Sub-Menu Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the sub-menu navigation links on larger screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
            	'settings'      => 'nav_submenu_link_color'
            )));

            $wp_customize->add_setting('nav_submenu_padding', array(
                'default'           => 14,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('nav_submenu_padding', array(
                'label'         => __('Nav Sub-Menu Padding', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the padding in the navigation sub-menus.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_navigation',
                'settings'      => 'nav_submenu_padding',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 27,
                    'step'  => 1
                )
            ));


            $wp_customize->add_section('oftto_content', array(
                'title'     => __('Content Options', 'options-for-twenty-twenty-one'),
                'description'  => __('Use these options to customise the content.', 'options-for-twenty-twenty-one') . ' ' . $section_description,
                'priority'     => 0
            ));



            $wp_customize->add_setting('content_padding_top', array(
                'default'           => 31,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('content_padding_top', array(
                'label'         => __('Content Padding Top', 'options-for-twenty-twenty-one'),
                'description'   => __('Reduce the padding above the content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'content_padding_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 31,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('content_link_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'content_link_color', array(
                'label'         => __('Content Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of links in the content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
            	'settings'      => 'content_link_color'
            )));

            $wp_customize->add_setting('content_link_hover_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'content_link_hover_color', array(
                'label'         => __('Content Link Hover Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of hover links in the content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
            	'settings'      => 'content_link_hover_color'
            )));

            $wp_customize->add_setting('inject_breadcrumbs', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('inject_breadcrumbs', array(
                'label'         => __('Inject Breadcrumbs', 'options-for-twenty-twenty-one'),
                'description'   => sprintf(wp_kses(__('Inject <a href="%s">Yoast SEO</a> or <a href="%s">Breadcrumb NavXT</a> breadcrumbs above page content.', 'options-for-twenty-twenty-one'), array('a' => array('href' => array()))), esc_url(admin_url('plugin-install.php?s=wordpress-seo&tab=search&type=term')), esc_url(admin_url('plugin-install.php?s=breadcrumb-navxt&tab=search&type=term'))),
                'section'       => 'oftto_content',
                'settings'      => 'inject_breadcrumbs',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_page_headers', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_page_headers', array(
                'label'         => __('Hide Post / Page Header', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the header on single posts and pages which includes the title and featured image.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_page_headers',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_page_titles', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_page_titles', array(
                'label'         => __('Hide Titles', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the titles on single posts and pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_page_titles',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_featured_images', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_featured_images', array(
                'label'         => __('Hide Featured Images', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the featured images on single posts and pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_featured_images',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('title_background_color', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'title_background_color', array(
                'label'         => __('Title Background Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Set the background color of the page / post title. Don\'t forget to reduce "Title Margin Bottom" and "Content Margin Top" if you want to remove the gap between the title and the content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
            	'settings'      => 'title_background_color'
            )));

            $wp_customize->add_setting('page_title_font_size', array(
                'default'           => 4000,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('page_title_font_size', array(
                'label'         => __('Page Title Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of page and post titles.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'page_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 2000,
                    'max'   => 8000,
                    'step'  => 50
                )
            ));

            $wp_customize->add_setting('page_title_font_weight', array(
                'default'           => 300,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('page_title_font_weight', array(
                'label'         => __('Title Font Weight', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font weight of the title font.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'page_title_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                )
            ));

            $wp_customize->add_setting('page_title_padding_bottom', array(
                'default'           => 61,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('page_title_padding_bottom', array(
                'label'         => __('Page Title Padding Bottom', 'options-for-twenty-twenty-one'),
                'description'   => __('Reduce the padding below the title on single posts and pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'page_title_padding_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 61,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('title_border_bottom', array(
                'default'           => 4,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('title_border_bottom', array(
                'label'         => __('Post Title Border Bottom', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border below the page / post title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'title_border_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 11,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('title_margin_bottom', array(
                'default'           => 91,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('title_margin_bottom', array(
                'label'         => __('Title Margin Bottom', 'options-for-twenty-twenty-one'),
                'description'   => __('Reduce the margin below the page / post title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'title_margin_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 91,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('hide_archive_titles', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_archive_titles', array(
                'label'         => __('Hide Archive Titles', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the titles on category and tag pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_archive_titles',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('archive_title_font_size', array(
                'default'           => 4000,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('archive_title_font_size', array(
                'label'         => __('Archive Title Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of tag and category archive titles.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'archive_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 2000,
                    'max'   => 8000,
                    'step'  => 50
                )
            ));

            $wp_customize->add_setting('archive_post_title_font_size', array(
                'default'           => 2500,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('archive_post_title_font_size', array(
                'label'         => __('Archive Post Title Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of post titles on blog and archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'archive_post_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1250,
                    'max'   => 5000,
                    'step'  => 50
                )
            ));

            $wp_customize->add_setting('page_title_letter_spacing', array(
                'default'           => 0,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('page_title_letter_spacing', array(
                'label'         => __('Page Title Letter Spacing', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the letter spacing of page titles.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'page_title_letter_spacing',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 20,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('content_margin_top', array(
                'default'           => 31,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('content_margin_top', array(
                'label'         => __('Content Margin Top', 'options-for-twenty-twenty-one'),
                'description'   => __('Adjust the margin inbetween the content and the title.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'content_margin_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 31,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('archive_post_title_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'archive_post_title_color', array(
                'label'         => __('Archive Post Title Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of the post titles on archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
            	'settings'      => 'archive_post_title_color'
            )));

            $wp_customize->add_setting('hr_width', array(
                'default'           => 2,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('hr_width', array(
                'label'         => __('Separator Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the separator block and &lt;hr&gt; tags.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hr_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 11,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('post_footer_border_bottom', array(
                'default'           => 2,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('post_footer_border_bottom', array(
                'label'         => __('Post Footer Border Bottom', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border below the post footer on archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'post_footer_border_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 11,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('post_footer_border_top', array(
                'default'           => 4,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('post_footer_border_top', array(
                'label'         => __('Post Footer Border Top', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border above the post footer on single post pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'post_footer_border_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 11,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('hide_date', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_date', array(
                'label'         => __('Hide Date', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide the date from posts and archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_date',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('move_date', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('move_date', array(
                'label'         => __('Move Date', 'options-for-twenty-twenty-one'),
                'description'   => __('Move the "Published" date below the post title on single posts and the blog page.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'move_date',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('remove_author', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('remove_author', array(
                'label'         => __('Remove Author', 'options-for-twenty-twenty-one'),
                'description'   => __('Prevents Twenty Twenty-One from revealing the author of a post.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'remove_author',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_taxonomies', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_taxonomies', array(
                'label'         => __('Hide Taxonomies', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide element that contains tags and categories from posts and archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_taxonomies',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_tags', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_tags', array(
                'label'         => __('Hide Tags', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide tag links from posts and archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_tags',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_cat', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_cat', array(
                'label'         => __('Hide Categories', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide category links from posts and archive pages.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_cat',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('comments_titles_font_size', array(
                'default'           => 3000,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('comments_titles_font_size', array(
                'label'         => __('Comments Titles Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of comments\' titles.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'comments_titles_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1500,
                    'max'   => 6000,
                    'step'  => 50
                )
            ));

            $wp_customize->add_setting('hide_post_navigation', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_post_navigation', array(
                'label'         => __('Hide Post Navigation', 'options-for-twenty-twenty-one'),
                'description'   => __('Hide previous and next post links on single posts.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_content',
                'settings'      => 'hide_post_navigation',
                'type'          => 'checkbox'
            ));



            $wp_customize->add_section('oftto_footer', array(
                'title'     => __('Footer Options', 'options-for-twenty-twenty-one'),
                'description'  => __('Use these options to customise the footer.', 'options-for-twenty-twenty-one') . ' ' . $section_description,
                'priority'     => 0
            ));




            $wp_customize->add_setting('footer_background_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_background_color', array(
                'label'         => __('Footer Background Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color behind the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
            	'settings'      => 'footer_background_color'
            )));

            $wp_customize->add_setting('footer_background_image', array(
                'default'           => false,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'footer_background_image', array(
                'mime_type'     => 'image',
                'label'         => __('Footer Background Image', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose an image to use in the footer background.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_background_image'
            )));

            $wp_customize->add_setting('expand_footer_background', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('expand_footer_background', array(
                'label'         => __('Expand Footer Background', 'options-for-twenty-twenty-one'),
                'description'   => __('Expand footer background behind footer widget area.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'expand_footer_background',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('footer_margin_top', array(
                'default'           => 181,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_margin_top', array(
                'label'         => __('Footer Margin Top', 'options-for-twenty-twenty-one'),
                'description'   => __('Reduce the margin above the footer widgets.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_margin_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 181,
                    'step'  => 5
                )
            ));

            $wp_customize->add_setting('footer_border_top', array(
                'default'           => 4,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_border_top', array(
                'label'         => __('Footer Border Top', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the width of the border above the site info in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_border_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 11,
                    'step'  => 1
                )
            ));

            $wp_customize->add_setting('mobile_widget_columns', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('mobile_widget_columns', array(
                'label'         => __('Mobile Widget Columns', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose how many columns of widgets are shown on small screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'mobile_widget_columns',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('1 (Default)', 'options-for-twenty-twenty-one'),
                    '2' => __('2', 'options-for-twenty-twenty-one'),
                    '3' => __('3', 'options-for-twenty-twenty-one'),
                    '4' => __('4', 'options-for-twenty-twenty-one'),
                    '5' => __('5', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('tablet_widget_columns', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('tablet_widget_columns', array(
                'label'         => __('Tablet Widget Columns', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose how many columns of widgets are shown on medium screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'tablet_widget_columns',
                'type'          => 'select',
                'choices'       => array(
                    '1' => __('1', 'options-for-twenty-twenty-one'),
                    '' => __('2 (Default)', 'options-for-twenty-twenty-one'),
                    '3' => __('3', 'options-for-twenty-twenty-one'),
                    '4' => __('4', 'options-for-twenty-twenty-one'),
                    '5' => __('5', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('desktop_widget_columns', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('desktop_widget_columns', array(
                'label'         => __('Desktop Widget Columns', 'options-for-twenty-twenty-one'),
                'description'   => __('Choose how many columns of widgets are shown on large screens.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'desktop_widget_columns',
                'type'          => 'select',
                'choices'       => array(
                    '1' => __('1', 'options-for-twenty-twenty-one'),
                    '2' => __('2', 'options-for-twenty-twenty-one'),
                    '' => __('3 (Default)', 'options-for-twenty-twenty-one'),
                    '4' => __('4', 'options-for-twenty-twenty-one'),
                    '5' => __('5', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('footer_max_width', array(
                'default'           => 1240,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_max_width', array(
                'label'         => __('Footer Max Width', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the maximum width of the footer content.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_max_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 622,
                    'max'   => 2160,
                    'step'  => 2
                )
            ));

            $wp_customize->add_setting('footer_widget_font_size', array(
                'default'           => 1125,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_widget_font_size', array(
                'label'         => __('Widget Area Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the widget area.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_widget_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 560,
                    'max'   => 2250,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('footer_text_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_text_color', array(
                'label'         => __('Footer Text Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the color of text in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
            	'settings'      => 'footer_text_color'
            )));

            $wp_customize->add_setting('__footer__color_link', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, '__footer__color_link', array(
                'label'         => __('Footer Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the footer link color.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
            	'settings'      => '__footer__color_link'
            )));

            $wp_customize->add_setting('footer_nav_font_size', array(
                'default'           => 1000,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_nav_font_size', array(
                'label'         => __('Footer Nav Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the secondary (social) nav in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_nav_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 500,
                    'max'   => 2000,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('footer_nav_font_weight', array(
                'default'           => 400,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_nav_font_weight', array(
                'label'         => __('Footer Nav Font Weight', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font weight of the links in the secondary menu.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_nav_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                )
            ));

            $wp_customize->add_setting('remove_social_icons', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('remove_social_icons', array(
                'label'         => __('Remove Social Icons', 'options-for-twenty-twenty-one'),
                'description'   => __('Remove the SVG social icons used in the footer secondary nav.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'remove_social_icons',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('social_icon_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'social_icon_color', array(
                'label'         => __('Social Icon Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the default color of the social icons.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
            	'settings'      => 'social_icon_color'
            )));

            $wp_customize->add_setting('replace_site_name_with_footer_menu', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('replace_site_name_with_footer_menu', array(
                'label'         => __('Replace Site Name', 'options-for-twenty-twenty-one'),
                'description'   => __('Replaces the site name (site logo / title) in the footer with the secondary (social) menu.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'replace_site_name_with_footer_menu',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_site_info', array(
                'default'       => false,
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_site_info', array(
                'label'         => __('Hide Site Info', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the site logo or title, "Proudly powered by WordPress." and the border above it in the website footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'hide_site_info',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('center_site_name_powered_by', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('center_site_name_powered_by', array(
                'label'         => __('Center Site Name or Powered By ', 'options-for-twenty-twenty-one'),
                'description'   => __('Centers the site name or "Powered by" text if one has been hidden.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'center_site_name_powered_by',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('hide_site_name', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('hide_site_name', array(
                'label'         => __('Hide Site Name', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the site logo or title in the website footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'hide_site_name',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('footer_logo_size', array(
                'default'           => 100,
                'transport'         => 'refresh',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_logo_size', array(
                'label'         => __('Logo Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the maximum width of the site logo in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_logo_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 20,
                    'max'   => 1240,
                    'step'  => 10
                )
            ));

            $wp_customize->add_setting('footer_site_title_font_size', array(
                'default'           => 1500,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('footer_site_title_font_size', array(
                'label'         => __('Footer Site Title Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the site title in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_site_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 750,
                    'max'   => 3000,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('footer_site_title_text_transform', array(
                'default'       => '',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'ofttoCommon::sanitize_options'
            ));
            $wp_customize->add_control('footer_site_title_text_transform', array(
                'label'         => __('Footer Site Title Font Case', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font case of the site title in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'footer_site_title_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    'none' => __('None', 'options-for-twenty-twenty-one'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-twenty-one'),
                    '' => __('Uppercase', 'options-for-twenty-twenty-one'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-twenty-one')
                )
            ));

            $wp_customize->add_setting('remove_powered_by_wordpress', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('remove_powered_by_wordpress', array(
                'label'         => __('Hide Powered by WordPress', 'options-for-twenty-twenty-one'),
                'description'   => __('Hides the "Powered by WordPress" text displayed in the website footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'remove_powered_by_wordpress',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('powered_by_font_size', array(
                'default'           => 1125,
                'transport'         => 'postMessage',
                'sanitize_callback' => 'absint'
            ));
            $wp_customize->add_control('powered_by_font_size', array(
                'label'         => __('"Powered by" Font Size', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the font size of the "Powered by WordPress" text in the footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'powered_by_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 750,
                    'max'   => 3000,
                    'step'  => 25
                )
            ));

            $wp_customize->add_setting('remove_footer_padding', array(
                'default'       => false,
                'transport'     => 'refresh',
                'sanitize_callback' => 'ofttoCommon::sanitize_boolean'
            ));
            $wp_customize->add_control('remove_footer_padding', array(
                'label'         => __('Remove Footer Padding', 'options-for-twenty-twenty-one'),
                'description'   => __('Removes all the padding and margins in the site footer.', 'options-for-twenty-twenty-one'),
                'section'       => 'oftto_footer',
                'settings'      => 'remove_footer_padding',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('__global__color_primary', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, '__global__color_primary', array(
                'label'         => __('Primary Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the primary color.', 'options-for-twenty-twenty-one'),
                'section'       => 'colors',
            	'settings'      => '__global__color_primary'
            )));

            $wp_customize->add_setting('__wp__style__color__link', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, '__wp__style__color__link', array(
                'label'         => __('Primary Link Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the primary link color.', 'options-for-twenty-twenty-one'),
                'section'       => 'colors',
            	'settings'      => '__wp__style__color__link'
            )));

            $wp_customize->add_setting('dark_mode_title_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'dark_mode_title_color', array(
                'label'         => __('Dark Mode Title Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the title color when dark mode is enabled.', 'options-for-twenty-twenty-one'),
                'section'       => 'colors',
            	'settings'      => 'dark_mode_title_color',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_is_dark_mode_enabled'
            )));

            $wp_customize->add_setting('dark_mode_description_color', array(
                'default'       => '',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'dark_mode_description_color', array(
                'label'         => __('Dark Mode Description Color', 'options-for-twenty-twenty-one'),
                'description'   => __('Change the description color when dark mode is enabled.', 'options-for-twenty-twenty-one'),
                'section'       => 'colors',
            	'settings'      => 'dark_mode_description_color',
                'active_callback' => 'options_for_twenty_twenty_one_class::oftto_is_dark_mode_enabled'
            )));



            $control_label = __('Featured Background Image', 'options-for-twenty-twenty-one');
            $control_description = __('Use the featured image as the background image (where applicable).', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'featured_background_image', 'oftto_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Left Sidebar', 'options-for-twenty-twenty-one');
            $control_description = __('Align the sidebar to the4 left on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'left_sidebar', 'oftto_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sidebar Width', 'options-for-twenty-twenty-one');
            $control_description = __('Set the width of the injected sidebar.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'sidebar_width', 'oftto_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sidebar Min Width', 'options-for-twenty-twenty-one');
            $control_description = __('Set the minimum width of the injected sidebar. This will prevent the sidebar from being too narrow on smaller screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'sidebar_min_width', 'oftto_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Archive Row Format', 'options-for-twenty-twenty-one');
            $control_description = __('Show posts in a row format on taxonomy pages.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'archive_row_template', 'oftto_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Archive Column Format', 'options-for-twenty-twenty-one');
            $control_description = __('Show posts in a column format on taxonomy pages.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'archive_column_template', 'oftto_general', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sticky Mobile', 'options-for-twenty-twenty-one');
            $control_description = __('Fix the header, navigation bar or floating hamburger to the top of the screen on small screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'sticky_mobile', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sticky Desktop', 'options-for-twenty-twenty-one');
            $control_description = __('Fix the header, navigation bar or floating hamburger to the top of the screen on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'sticky_desktop', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Sticky Dropshadow', 'options-for-twenty-twenty-one');
            $control_description = __('Add a dropshadow to the sticky headers and menus.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'sticky_menu_dropshadow', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Animate Dropshadow', 'options-for-twenty-twenty-one');
            $control_description = __('Give a 3D animation to the dropshadow on sticky headers and menus.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'sticky_menu_shadow_animate', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Featured Header Image', 'options-for-twenty-twenty-one');
            $control_description = __('Use the featured image as the header image (where applicable).', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'featured_header_image', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Header Gradient Height', 'options-for-twenty-twenty-one');
            $control_description = __('Change the height of the header gradient.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'header_gradient_height', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Header Gradient Color', 'options-for-twenty-twenty-one');
            $control_description = __('Change the color header gradient.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'header_gradient_color', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Header Gradient Opacity', 'options-for-twenty-twenty-one');
            $control_description = __('Change the opacity of the header gradient.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'header_gradient_opacity', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Header Widget Area Position', 'options-for-twenty-twenty-one');
            $control_description = __('Place the Header Widget Area at the top or the bottom of the header.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'header_widget_area', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Inline Logo', 'options-for-twenty-twenty-one');
            $control_description = __('Move logo inline with site title and description.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'inline_logo', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Text Shadow Width', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the width of the shadow on text in the header.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'header_text_shadow_width', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Text Shadow Color', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the color of the shadow on text in the header.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'header_text_shadow_color', 'oftto_header', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Desktop Nav on Mobile', 'options-for-twenty-twenty-one');
            $control_description = __('Show the desktop navigation on smaller screen sizes.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'desktop_nav_on_mobile', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Mobile Nav Breakpoint', 'options-for-twenty-twenty-one');
            $control_description = __('Choose when to show the mobile navigation hamburger menu on larger screen sizes.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'mobile_nav_breakpoint', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Mobile Nav Flyout', 'options-for-twenty-twenty-one');
            $control_description = __('Show the hamburger navigation as a flyout on larger screens if enabled with "Sticky Desktop", "Mobile Nav on Desktop" or "Mobile Nav Breakpoint" options.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'mobile_nav_flyout', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Border Width', 'options-for-twenty-twenty-one');
            $control_description = __('Add a border to the primary navigation menu items on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'nav_border_width', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Border Color', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the border color on the primary navigation menu items on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'nav_border_color', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Border Style', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the border style on the primary navigation menu items on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'nav_border_style', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Background Color', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the background color on the primary navigation menu items on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'nav_item_background_color', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Border Radius', 'options-for-twenty-twenty-one');
            $control_description = __('Add border radius to the primary navigation menu items on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'nav_item_border_radius', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Menu Item Margin', 'options-for-twenty-twenty-one');
            $control_description = __('Increase the margin on the primary navigation menu items on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'nav_item_margin', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Hide Sub-Menu Toggle', 'options-for-twenty-twenty-one');
            $control_description = __('Hides the switch that opens and closes sub-menus on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'hide_submenu_toggle', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Second-Level Sub-Menus', 'options-for-twenty-twenty-one');
            $control_description = __('Enables second-level, flyout sub-menus on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'second_level_submenus', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Second-Level Sub-Menu Background Color', 'options-for-twenty-twenty-one');
            $control_description = __('Change the color of the second-level sub-menu backgrounds on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'second_submenu_background_color', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Mobile Sub-Menus', 'options-for-twenty-twenty-one');
            $control_description = __('Enables sub-menus for smaller screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'mobile_submenus', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Primary Nav Search', 'options-for-twenty-twenty-one');
            $control_description = __('Add a search facility to the primary navigation.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'primary_nav_search', 'oftto_navigation', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Content Background Color', 'options-for-twenty-twenty-one');
            $control_description = __('Wrap the page / post content so it can have a different background color.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'content_background_color', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Remove Archive Title Prefix', 'options-for-twenty-twenty-one');
            $control_description = __('Remove the word "Tag" or "Category" in archive titles.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'remove_archive_title_prefix', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Parallax Cover Block', 'options-for-twenty-twenty-one');
            $control_description = __('Turn on a parallax effect for the cover block.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'parallax_cover_block', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Archive Featured Images', 'options-for-twenty-twenty-one');
            $control_description = __('Add featured image functionality to category and tag pages.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'archive_featured_images', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Box Shape', 'options-for-twenty-twenty-one');
            $control_description = __('Adjust the aspect ratio of the posts in the grid template.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_box_shape', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Columns (Small Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Select the number of columns per row in the grid template on small screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_columns_small', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Columns (Medium Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Select the number of columns per row in the grid template on medium screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_columns_medium', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Columns (Large Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Select the number of columns per row in the grid template on large screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_columns_large', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Border Width', 'options-for-twenty-twenty-one');
            $control_description = __('Adjust the width of the border around posts in the grid template that don\'t have a featured image.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_border_width', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Border Color', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the color of the border around posts in the grid template that don\'t have a featured image.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_border_color', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Border Style', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the style of the border around posts in the grid template that don\'t have a featured image.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_border_style', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Font Size (Small Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Adjust the title font size in the grid template on small screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_font_size_small', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Font Size (Medium Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Adjust the title font size in the grid template on medium screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_font_size_medium', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Font Size (Large Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Adjust the title font size in the grid template on large screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_font_size_large', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Title Shadow Width', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the width of the shadow on the title in the grid template.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_title_shadow_width', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Title Shadow Color', 'options-for-twenty-twenty-one');
            $control_description = __('Choose the color of the shadow on the title in the grid template.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_title_shadow_color', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Title Font Case', 'options-for-twenty-twenty-one');
            $control_description = __('Change the font case of the grid title.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_title_text_transform', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Grid Hover Animation', 'options-for-twenty-twenty-one');
            $control_description = __('Show an animated hover effect on grid items.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_hover_animation', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Show Post Dates in Grid', 'options-for-twenty-twenty-one');
            $control_description = __('Show published dates on the grid template.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'grid_post_dates', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Column Template Columns (Small Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Select the number of columns per row in the column template on small screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'column_columns_small', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Column Template Columns (Medium Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Select the number of columns per row in the column template on medium screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'column_columns_medium', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Column Template Columns (Large Screen)', 'options-for-twenty-twenty-one');
            $control_description = __('Select the number of columns per row in the column template on large screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'column_columns_large', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Show Post Dates in Column Template', 'options-for-twenty-twenty-one');
            $control_description = __('Show published dates on the column template.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'column_post_dates', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Remove "Published" Text', 'options-for-twenty-twenty-one');
            $control_description = __('Remove the word "Published" before the date on posts and words "Published in" on attachment pages.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'remove_published_text', 'oftto_content', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Widget Area Background Color', 'options-for-twenty-twenty-one');
            $control_description = __('Change the color behind the footer widget area.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'footer_widget_area_background_color', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Social Links Icon Size', 'options-for-twenty-twenty-one');
            $control_description = __('Increase the size of the social link icons on larger screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'social_links_icon_size', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Fix Social Links for Desktop', 'options-for-twenty-twenty-one');
            $control_description = __('Fix the social links to the left or right for large screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'fix_social_links', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Fix Social Links for Mobile', 'options-for-twenty-twenty-one');
            $control_description = __('Fix the social links to the left or right for small screens.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'fix_social_links_mobile', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Add Icons and Colors to Social Links Menu', 'options-for-twenty-twenty-one');
            $control_description = __('Adds Linkedin, Telegram, WhatsApp and Xing social icons and changes the color of the social icons to their relevant corporate colors.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'colored_social_links_menu', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Replace Site Title / Logo', 'options-for-twenty-twenty-one');
            $control_description = __('Provide alternate text / HTML to replace site title / logo.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'replace_site_title_logo', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);
           
            $control_label = __('Replace "Powered by" Text', 'options-for-twenty-twenty-one');
            $control_description = __('Provide alternate text to replace "Proudly powered by Wordpress".', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'replace_powered_by_wordpress', 'oftto_footer', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Dark Mode On by Default', 'options-for-twenty-twenty-one');
            $control_description = __('Enable Dark Mode when a user first visits the site.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'dark_mode_on', 'colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Dark Mode Background Image', 'options-for-twenty-twenty-one');
            $control_description = __('Choose an alternative background image to use for Dark Mode.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'dark_mode_background_image', 'colors', $control_label, $control_description . ' ' . $upgrade_nag);

            $control_label = __('Dark Mode Logo', 'options-for-twenty-twenty-one');
            $control_description = __('Choose an alternative logo to use for Dark Mode.', 'options-for-twenty-twenty-one');
            ofttoCommon::add_hidden_control($wp_customize, 'dark_mode_logo', 'colors', $control_label, $control_description . ' ' . $upgrade_nag);

        }

        function oftto_frontend_styles() {

            $mod = absint((get_theme_mod('mobile_nav_breakpoint')));
            $mobile_breakpoint = ($mod ? $mod + 481 : 481);

?>
<!--Customizer CSS-->
<style type="text/css">
#masthead-wrapper, .site-header {
    border-bottom-style: solid;
    border-bottom-width: 0;
}
<?php

            if (get_theme_mod('hide_print_urls')) {

?>
@media print {
	a:after {
		content: "" !important;
	}
}
<?php

            }

            ofttoCommon::generate_css('body', 'font-size', 'body_font_size', '', 'rem', absint(get_theme_mod('body_font_size')) / 1000);
            ofttoCommon::generate_css('a, .primary-navigation .current-menu-item > a:first-child, .primary-navigation .current_page_item > a:first-child, .primary-navigation a:hover, .primary-navigation .current-menu-item > a:first-child:hover, .primary-navigation .current_page_item > a:first-child:hover, .widget-area a', 'text-decoration', 'remove_link_underlines', '', '', 'none');
            ofttoCommon::generate_css(':root', '--global--color-primary', '__global__color_primary');
            ofttoCommon::generate_css(':root', '--global--color-secondary', '__global__color_primary');
            ofttoCommon::generate_css(':root', '--button--color-background', '__global__color_primary');
            ofttoCommon::generate_css(':root', '--button--color-text-hover', '__global__color_primary');
            ofttoCommon::generate_css('a', 'color', '__wp__style__color__link');
            ofttoCommon::generate_css(':root', '--wp--style--color--link', '__wp__style__color__link');

            if (get_theme_mod('inject_sidebar') || (is_singular() && get_post_meta(get_the_ID(), 'oftto_show_sidebar', true) == '1')) {

?>
#content-wrapper>#sidebar {
    display: block;
    margin-top: 0;
}
@media (min-width: 482px) {
    #content-wrapper {
        display: flex;
    }
    #content-wrapper>#content {
        width: 67%;
    }
    #content-wrapper>#sidebar {
        width: 33%;
        padding: 0 0 0 30px;
    }
}

<?php

            }

            $mod = absint(get_theme_mod('aligndefault_max_width'));

            if (get_theme_mod('no_aligndefault_max_width')) {

?>
@media only screen and (min-width: 482px) {
	:root {
		--responsive--aligndefault-width: calc(100vw - 4 * var(--global--spacing-horizontal));
	}
}
@media only screen and (min-width: 822px) {
	:root {
		--responsive--aligndefault-width: calc(100vw - 8 * var(--global--spacing-horizontal));
	}
}
@media only screen and (min-width: 822px) {
	.post-thumbnail,
	.entry-content .wp-audio-shortcode,
	.entry-content > *:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	*[class*=inner-container] > *:not(.entry-content):not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	.default-max-width,
	.wp-block-search,
	hr.wp-block-separator:not(.is-style-dots):not(.alignwide),
	.entry-content > .alignleft,
	.entry-content > .alignright,
	.author-bio,
	.search-form {
		max-width: calc(100vw - 200px);
	}
	.author-bio.show-avatars .author-bio-content {
		max-width: calc(100vw - 290px);
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		margin-right: 100px;
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		max-width: calc(50% - 100px);
	}
}
<?php

            } elseif ($mod) {

?>
@media only screen and (min-width: 482px) {
	:root {
		--responsive--aligndefault-width: min(calc(100vw - 4 * var(--global--spacing-horizontal)), <?php echo $mod; ?>px);
	}
}
@media only screen and (min-width: 822px) {
	:root {
		--responsive--aligndefault-width: min(calc(100vw - 8 * var(--global--spacing-horizontal)), <?php echo $mod; ?>px);
	}
}
@media only screen and (min-width: 482px) and (max-width: 821px) {
	.post-thumbnail,
	.entry-content .wp-audio-shortcode,
	.entry-content > *:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	*[class*=inner-container] > *:not(.entry-content):not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	.default-max-width,
	.wp-block-search,
	hr.wp-block-separator:not(.is-style-dots):not(.alignwide),
	.entry-content > .alignleft,
	.entry-content > .alignright,
	.author-bio,
	.search-form {
		max-width: calc(100vw - 100px);
	}
	.author-bio.show-avatars .author-bio-content {
		max-width: calc(100vw - 190px);
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		margin-right: 50px;
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		max-width: calc(50% - 50px);
	}
}<?php if (($mod + 200) > 822) { ?>
@media only screen and (min-width: 822px) and (max-width: <?php echo $mod + 200; ?>px) {
	.post-thumbnail,
	.entry-content .wp-audio-shortcode,
	.entry-content > *:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	*[class*=inner-container] > *:not(.entry-content):not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	.default-max-width,
	.wp-block-search,
	hr.wp-block-separator:not(.is-style-dots):not(.alignwide),
	.entry-content > .alignleft,
	.entry-content > .alignright,
	.author-bio,
	.search-form {
		max-width: calc(100vw - 200px);
	}
	.author-bio.show-avatars .author-bio-content {
		max-width: calc(100vw - 290px);
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		margin-right: 100px;
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		max-width: calc(50% - 100px);
	}
}<?php } ?>
@media only screen and (min-width: <?php echo $mod + 201; ?>px) {
	.post-thumbnail,
	.entry-content .wp-audio-shortcode,
	.entry-content > *:not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	*[class*=inner-container] > *:not(.entry-content):not(.alignwide):not(.alignfull):not(.alignleft):not(.alignright):not(.wp-block-separator):not(.woocommerce),
	.default-max-width,
	.wp-block-search,
	hr.wp-block-separator:not(.is-style-dots):not(.alignwide),
	.entry-content > .alignleft,
	.entry-content > .alignright,
	.author-bio,
	.search-form {
		max-width: <?php echo $mod; ?>px;
	}
	.author-bio.show-avatars .author-bio-content {
		max-width: <?php echo $mod -90; ?>px;
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		margin-right: calc(0.5 * (100vw - <?php echo $mod; ?>px));
	}
	.entry-content > .alignleft,
	.entry-content > .alignright {
		max-width: calc(0.5 * <?php echo $mod; ?>px);
	}
}
<?php

            }

            $mod = absint(get_theme_mod('alignwide_max_width'));

            if (get_theme_mod('no_alignwide_max_width')) {

?>
@media only screen and (min-width: 822px) {
	:root {
		--responsive--alignwide-width: calc(100vw - 8 * var(--global--spacing-horizontal));
	}
}
@media only screen and (min-width: 822px) {
    .widget-area,
    .pagination,
    .comments-pagination,
    .post-navigation,
    .site-footer,
    .site-header,
    .alignwide,
    .wide-max-width,
    .wp-block-pullquote.alignwide > p,
    .wp-block-pullquote.alignwide blockquote,
    hr.wp-block-separator:not(.is-style-dots).alignwide {
    	max-width: calc(100vw - 200px);
    }
    .entry-header .post-thumbnail,
    .singular .post-thumbnail,
    .alignfull [class*=inner-container] > .alignwide,
    .alignwide [class*=inner-container] > .alignwide,
    .entry-header .post-thumbnail,
    .singular .post-thumbnail,
    .alignfull [class*=inner-container] > .alignwide,
    .alignwide [class*=inner-container] > .alignwide {
    	width: calc(100vw - 200px);
    }
}
<?php

            } elseif ($mod) {

?>
@media only screen and (min-width: 822px) {
	:root {
		--responsive--alignwide-width: min(calc(100vw - 8 * var(--global--spacing-horizontal)), <?php echo $mod; ?>px);
	}
}
@media only screen and (min-width: 822px) and (max-width: <?php echo $mod + 200; ?>px) {
    .widget-area,
    .pagination,
    .comments-pagination,
    .post-navigation,
    .site-footer,
    .site-header,
    .alignwide,
    .wide-max-width,
    .wp-block-pullquote.alignwide > p,
    .wp-block-pullquote.alignwide blockquote,
    hr.wp-block-separator:not(.is-style-dots).alignwide {
    	max-width: calc(100vw - 200px);
    }
    .entry-header .post-thumbnail,
    .singular .post-thumbnail,
    .alignfull [class*=inner-container] > .alignwide,
    .alignwide [class*=inner-container] > .alignwide,
    .entry-header .post-thumbnail,
    .singular .post-thumbnail,
    .alignfull [class*=inner-container] > .alignwide,
    .alignwide [class*=inner-container] > .alignwide {
    	width: calc(100vw - 200px);
    }
}
@media only screen and (min-width: <?php echo $mod + 201; ?>px) {
    .widget-area,
    .pagination,
    .comments-pagination,
    .post-navigation,
    .site-footer,
    .site-header,
    .alignwide,
    .wide-max-width,
    .wp-block-pullquote.alignwide > p,
    .wp-block-pullquote.alignwide blockquote,
    hr.wp-block-separator:not(.is-style-dots).alignwide {
    	max-width: <?php echo $mod; ?>px;
    }
    .entry-header .post-thumbnail,
    .singular .post-thumbnail,
    .alignfull [class*=inner-container] > .alignwide,
    .alignwide [class*=inner-container] > .alignwide,
    .entry-header .post-thumbnail,
    .singular .post-thumbnail,
    .alignfull [class*=inner-container] > .alignwide,
    .alignwide [class*=inner-container] > .alignwide {
    	width: <?php echo $mod; ?>px;
    }
}
<?php

            }

            if (class_exists('woocommerce')) {

                $mod = get_theme_mod('woocommerce_max_width');

                if ($mod) {

?>
.woocommerce .content-area {
    max-width: var(--responsive--align<?php echo $mod; ?>-width);
    margin-left: auto;
    margin-right: auto;
    padding: 0;
}
.woocommerce .content-area .site-main {
    margin: 0;
}
<?php

                }

            }

            $mod = get_theme_mod('archive_grid_template');

            if ($mod && is_array($mod)) {

                foreach ($mod as $taxonomy) {

                    switch ($taxonomy) {

                        case 'blog':

                            break;

                        case 'search':

                            break;

                        case 'category':

                            break;

                        case 'post_tag':

                            $taxonomy = 'tag';
                            break;

                        default:

                            $taxonomy = 'tax-' . $taxonomy;
                            break;

                    }

                    $alignwide_max_width = absint(get_theme_mod('alignwide_max_width'));

                    if (!$alignwide_max_width) { $alignwide_max_width = 1240; }

?>
@media (min-width: <?php echo $alignwide_max_width; ?>px) {
    .<?php echo $taxonomy; ?> .page-header {
        max-width: none;
    }
}
.<?php echo $taxonomy; ?> .search-result-count {
    max-width: none;
}
.<?php echo $taxonomy; ?> .site-main {
	display: flex;
	flex-wrap: wrap;
	padding-left: 0;
	padding-right: 0;
	width: 100%;
}
@media (min-width: 482px) {
	.<?php echo $taxonomy; ?> .site-main {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
.<?php echo $taxonomy; ?> .site-main>* {
	padding: 1rem;
	width: 100%;
}
.<?php echo $taxonomy; ?> .site-main>article {
	margin: 0;
}
@media (min-width: 482px) {
	.<?php echo $taxonomy; ?> .site-main>article {
        width: 50%;
    }
}
@media (min-width: 822px) {
	.<?php echo $taxonomy; ?> .site-main>article {
        width: 25%;
    }
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header {
	width: 100%;
	position: relative;
	margin: 0;
}
.<?php echo $taxonomy; ?> .site-main>article:not(.has-post-thumbnail)>.entry-header {
	border: 1px solid #28303d;
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header::after {
	display: block;
	content: '';
	padding-bottom: 65%;
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.entry-title {
	position: absolute;
	z-index: 2;
	height: 100%;
	width: 100%;
	text-align: center;
}
@media (max-width: 482px) {
	.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.entry-title {
        font-size: 8vw;
    }
}
@media (min-width: 482px) and (max-width: 821px) {
	.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.entry-title {
        font-size: 4vw;
    }
}
@media (min-width: 822px) {
	.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.entry-title {
        font-size: 2vw;
    }
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.entry-title>a {
	display: flex;
    justify-content: center;
	padding: 0 1rem;
    align-items: center;
    height: 100%;
	text-decoration: none;
	text-transform: uppercase;
	font-weight: 900;
}
.<?php echo $taxonomy; ?> .site-main>article.has-post-thumbnail>.entry-header>.entry-title>a {
	text-shadow: -1px -1px 0 var(--global--color-background), 1px -1px 0 var(--global--color-background), -1px 1px 0 var(--global--color-background), 1px 1px 0 var(--global--color-background);
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.post-thumbnail {
	margin: 0;
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.post-thumbnail img {
	position: absolute;
	width: 100% !important;
	height: 100% !important;
	max-width: none !important;
	object-fit: cover;
	margin: 0;
	z-index: 1;
}
.<?php echo $taxonomy; ?> .site-main>article>.entry-header>.post-thumbnail figcaption,
.<?php echo $taxonomy; ?> .site-main>article>.entry-footer,
.<?php echo $taxonomy; ?> .site-main>article>.entry-content {
	display: none;
}
.<?php echo $taxonomy; ?> .pagination {
padding: 0;
max-width: none;
border: none;
}
.<?php echo $taxonomy; ?> .pagination .nav-links {
padding: 1rem;
border-top: 3px solid var(--global--color-border);
max-width: var(--responsive--alignwide-width);
margin: var(--global--spacing-vertical) auto;
}
<?php

                }

            }

            $mod = absint(get_theme_mod('header_min_height'));

            if ($mod) {

?>
<?php echo (get_theme_mod('site_wide_header_height') ? '' : '.home '); ?>#masthead {
	min-height: <?php echo $mod; ?>vh;
}
<?php echo (get_theme_mod('site_wide_header_height') ? '' : '.home'); ?>.admin-bar #masthead {
	min-height: calc(<?php echo $mod; ?>vh - 32px);
}
@media screen and (max-width: 782px) {
    <?php echo (get_theme_mod('site_wide_header_height') ? '' : '.home'); ?>.admin-bar #masthead {
    	min-height: calc(<?php echo $mod; ?>vh - 46px);
    }
}
<?php

            }

            ofttoCommon::generate_css('.site-header', 'display', 'hide_site_header', '', '', 'none');

            if (get_theme_mod('wide_site_header')) {

?>
@media only screen and (min-width: 482px) {
.site-header {
max-width: calc(100vw -  38px);
}
}
<?php

            }

            if ((get_theme_mod('absolute_site_header') && !((is_single() || is_page()) && get_post_meta(get_the_ID(), 'oftto_absolute_site_header', true) == '1')) || (!get_theme_mod('absolute_site_header') && (is_single() || is_page()) && get_post_meta(get_the_ID(), 'oftto_absolute_site_header', true) == '1')) {

?>
.site-header {
	position: absolute;
	top: 0;
	right: 0;
	left: 0;
	z-index: 2
}
.site-main {
	padding-top: 0;
}
.site-main > article > * {
	margin-top: 0;
}
<?php

            }

            $header_color = get_theme_mod('header_background_color');
            $header_image = absint(get_theme_mod('header_background_image'));

            if ($header_image || $header_color) {
?>
#masthead-wrapper {<?php if ($header_color) { ?>
    background-color: <?php echo $header_color; ?>;<?php } if ($header_image) { ?>
    background-image: url("<?php echo (wp_get_attachment_image_src($header_image, 'full') ? wp_get_attachment_image_src($header_image, 'full')[0] : ''); ?>");<?php } ?>
    background-size: cover;
    background-repeat: no-repeat;<?php if (get_theme_mod('fix_header_background_image')) { ?>
    background-attachment: fixed;<?php } ?>
    background-position: center;
}
@supports (-webkit-touch-callout: none) {
    #masthead-wrapper {
        background-attachment: scroll;
    }
}
<?php
            }

            $mod = absint(get_theme_mod('header_padding_top'));

            if ($mod) {

                $mod = $mod - 1;

?>
.site-header {
    padding-top: <?php echo ($this->oftto_are_title_and_tagline_displayed() ? round($mod * 22.5 / 72, 1) . 'px' : '74px'); ?>;
}
@media only screen and (min-width: 482px) {
    .site-header {
        padding-top: <?php echo round($mod * 40 / 72, 1); ?>px;
    }
}
@media only screen and (min-width: 822px) {
    .site-header {
        padding-top: <?php echo $mod; ?>px;
    }
}
<?php
            }

            $mod = absint(get_theme_mod('header_padding_bottom'));

            if ($mod) {

                $mod = $mod - 1;

?>
.site-header {
    padding-bottom: <?php echo round($mod * 60 / 90, 1); ?>px;
}
@media only screen and (min-width: 482px) {
    .site-header {
        padding-bottom: <?php echo round($mod * 40 / 90, 1); ?>px;
    }
}
@media only screen and (min-width: 822px) {
    .site-header {
        padding-bottom: <?php echo $mod; ?>px;
    }
}
<?php
            }

            ofttoCommon::generate_css('.site a:focus:not(.wp-block-button__link):not(.wp-block-file__button) img', 'outline', 'logo_prevent_focus_border', '', '', 'none');

            $mod = absint(get_theme_mod('logo_below_nav'));

            if ($mod) {

?>
.site-header > .site-logo {
	order: 1;
}
.site-header.has-logo:not(.has-title-and-tagline) > #site-navigation {
	width: 100%;
}
.site-header.has-logo:not(.has-title-and-tagline) > .site-branding {
	width: 100%;
	text-align: center;
	order: 1;
}
<?php

            }

            $mod = absint(get_theme_mod('logo_size'));

            if ($mod) {

?>
.site-header .site-logo .custom-logo {
    max-width: <?php echo $mod; ?>px;
    max-height: none;
    width: 100%;
}
@media only screen and (max-width: 481px) {
    .site-header.has-logo:not(.has-title-and-tagline).has-menu .site-logo img {
        width: auto;
    }
}
<?php

            }

            ofttoCommon::generate_css('.site-header > .site-logo', 'border-bottom-width', 'logo_border_bottom', '', 'px', absint(get_theme_mod('logo_border_bottom')) - 1);
            ofttoCommon::generate_css('.site-header > .site-logo, .site-header > .site-branding > .site-logo', 'text-align', 'logo_align');
            ofttoCommon::generate_css('.site-title', 'display', 'hide_site_title', '', '', 'none');

            $mod = get_theme_mod('full_width_site_branding');

            if ('mobile' === $mod) {

?>
@media screen and (max-width: <?php echo $mobile_breakpoint; ?>px) {
    .site-branding, .site-header:not(.has-logo).has-title-and-tagline .site-branding {
        width: 100%;
        max-width: none;
    }
}
<?php

            } elseif ('desktop' === $mod) {

?>
@media screen and (min-width: <?php echo $mobile_breakpoint + 1; ?>px) {
    .site-branding, .site-header:not(.has-logo).has-title-and-tagline .site-branding {
        width: 100%;
        max-width: none;
    }
}
<?php

            } elseif ('both' === $mod) {

?>
.site-branding, .site-header:not(.has-logo).has-title-and-tagline .site-branding {
    width: 100%;
    max-width: none;
}
<?php

            }

            ofttoCommon::generate_css('.site-branding .site-title', 'text-align', 'site_title_align');
            ofttoCommon::generate_css('.site-title', 'color', 'site_title_color');

            $mod = absint(get_theme_mod('site_title_font_size'));

            if ($mod) {

?>
.site-title {
    font-size: <?php echo $mod / 1000; ?>rem;
}
<?php

            }

            ofttoCommon::generate_css('.site-title, .site-title a', 'font-weight', 'site_title_font_weight');
            ofttoCommon::generate_css('.site-title, .site-footer > .site-info .site-name', 'text-transform', 'site_title_text_transform');
            ofttoCommon::generate_css('.site-title a', 'text-decoration', 'remove_site_title_underline', '', '', 'none');
            ofttoCommon::generate_css('.site-description', 'display', 'hide_site_description', '', '', 'none');
            ofttoCommon::generate_css('.site-branding .site-description', 'text-align', 'site_description_align');
            ofttoCommon::generate_css('.site-description', 'color', 'site_description_color');
            ofttoCommon::generate_css('.site-description', 'font-size', 'site_description_font_size', '', 'rem', absint(get_theme_mod('site_description_font_size')) / 1000);
            ofttoCommon::generate_css('.site-description', 'font-weight', 'site_description_font_weight');

            $mod = (
                absint(get_theme_mod('header_background_image')) || 
                get_theme_mod('header_background_color') ||
                get_theme_mod('sticky_mobile') === 'masthead-wrapper' ||
                get_theme_mod('sticky_desktop') === 'masthead-wrapper'
            ) ? '#masthead-wrapper' : '.site-header';

            ofttoCommon::generate_css($mod, 'border-bottom-width', 'header_border_bottom_width', '', 'px');
            ofttoCommon::generate_css($mod, 'border-bottom-style', 'header_border_bottom_style');
            ofttoCommon::generate_css($mod, 'border-bottom-color', 'header_border_bottom_color');

            if (get_theme_mod('move_nav_below_header')) {

?>
#site-navigation.primary-navigation {
    max-width: var(--responsive--alignwide-width);
    margin-right: auto;
}
<?php

            }

            if (get_theme_mod('mobile_nav_on_desktop')) {

                if (!self::oftto_are_title_and_tagline_displayed()) {

?>
@media only screen and (min-width: 482px) {
    .site-header {
        position: relative;
    }
    .primary-navigation {
        position: absolute;
        right: 0;
    }
}
<?php

                }

?>
.primary-navigation-open .primary-navigation {
    z-index: 2;
}
.primary-navigation-open .primary-navigation > .primary-menu-container {
    height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    border: 2px solid transparent;
}
.primary-navigation > div > .menu-wrapper {
    padding-bottom: 100px;
    padding-left: 0;
}
.primary-navigation-open .primary-navigation {
    width: 100%;
    position: fixed;
}
.menu-button-container {
    display: flex;
}
.primary-navigation > .primary-menu-container {
    visibility: hidden;
    opacity: 0;
    position: fixed;
    padding-top: 71px;
    padding-left: 20px;
    padding-right: 20px;
    padding-bottom: 25px;
    background-color: var(--global--color-background);
    transition: all 0.15s ease-in-out;
    transform: translateX(0) translateY(0);
}
.primary-navigation > div > .menu-wrapper li {
    display: block;
    position: relative;
    width: 100%;
}
body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .menu-button-container #primary-mobile-menu {
	padding-left: calc(var(--global--spacing-horizontal) * 0.6 - 4.5px);
	padding-right: calc(var(--global--spacing-horizontal) * 0.6 - 4.5px);
	margin-right: calc(0px - var(--global--spacing-horizontal) * 0.6);
}
.has-logo.has-title-and-tagline .primary-navigation > .primary-menu-container {
    position: fixed;
    transform: translateY(0) translateX(100%);
}
body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .menu-button-container {
    position: relative;
    padding-top: 0;
    margin-top: calc(0px - var(--button--padding-vertical)) + (0.25 * var(--global--spacing-unit))));
}
body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .primary-navigation {
    position: relative;
    top: 0;
}
.primary-navigation-open .has-logo.has-title-and-tagline .primary-navigation > .primary-menu-container {
    transform: translateX(0) translateY(0);
}
.admin-bar .primary-navigation, .admin-bar .primary-navigation > .primary-menu-container {
	top:32px;
}
.admin-bar .primary-navigation > .primary-menu-container {
    top: 0;
}
@media screen and (max-width: 782px) {
    .admin-bar .primary-navigation {
    	top: 46px;
    }
}
.primary-navigation-open .menu-button-container {
    width: auto;
}
<?php

            }

            if (get_theme_mod('hide_nav_button_border')) {

?>
.menu-button-container #primary-mobile-menu:focus, .primary-navigation > div > .menu-wrapper .sub-menu-toggle:focus {
    outline-color: transparent;
}
<?php

            }

            $mod = get_theme_mod('mobile_nav_align');

            if ($mod) {
?>
@media only screen and (max-width: <?php echo $mobile_breakpoint; ?>px) {
<?php

                if ($mod === 'left') {

?>
    .primary-navigation {
        margin-left: 0;
        margin-right: auto;
    }
    body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .menu-button-container #primary-mobile-menu {
        margin-left: -10.5px;
    }
<?php

                } elseif ($mod === 'center') {

?>
    .primary-navigation {
        margin-left: auto;
        margin-right: auto;
    }
    body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .menu-button-container #primary-mobile-menu {
        margin-right: 0;
    }
<?php

                } elseif ($mod === 'right') {

?>
    .primary-navigation {
        margin-left: auto;
        margin-right: 0;
    }
<?php

                }

?>
}
<?php
            }

            $mod = get_theme_mod('desktop_nav_align');

            if ($mod) {
?>
@media only screen and (min-width: <?php echo $mobile_breakpoint + 1; ?>px) {
<?php

                if ($mod === 'left') {

?>
    .primary-navigation {
        margin-left: 0;
        margin-right: auto;
    }
    body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .menu-button-container #primary-mobile-menu {
        margin-left: -10.5px;
    }
<?php

                } elseif ($mod === 'center') {

?>
    .primary-navigation {
        margin-left: auto;
        margin-right: auto;
    }
    body:not(.primary-navigation-open) .site-header.has-logo.has-title-and-tagline .menu-button-container #primary-mobile-menu {
        margin-right: 0;
    }
    .primary-navigation > div > .menu-wrapper {
        justify-content: center;
    }
<?php

                } elseif ($mod === 'right') {

?>
    .primary-navigation {
        margin-left: auto;
        margin-right: 0;
    }
    .primary-navigation > div > .menu-wrapper {
        justify-content: flex-end;
    }
<?php

                }

?>
}
<?php
            }

            $mod = absint(get_theme_mod('navigation_border_width'));

?>
.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container {
    border-width: <?php echo $mod; ?>px;
}
<?php

            ofttoCommon::generate_css('.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container', 'border-style', 'navigation_border_style');
            ofttoCommon::generate_css('.primary-navigation > .primary-menu-container, .primary-navigation-open .primary-navigation > .primary-menu-container', 'border-color', 'navigation_border_color');
            ofttoCommon::generate_css('.primary-navigation > .primary-menu-container', 'background-color', 'nav_background_color');
            ofttoCommon::generate_css('.primary-navigation-open .menu-button-container', 'background-color', 'nav_background_color', '', '', 'transparent');

            $mod = absint(get_theme_mod('desktop_nav_padding'));

            if ($mod) {

?>
@media only screen and (min-width: <?php echo $mobile_breakpoint + 1; ?>px) {
    .primary-navigation {
        padding: <?php echo $mod; ?>px 0;
    }
}
<?php

            }

            ofttoCommon::generate_css('.menu-button-container #primary-mobile-menu', 'background-color', 'nav_burger_background_color');

            if (get_theme_mod('hide_mobile_menu_text')) {
?>
.menu-button-container .button.button .dropdown-icon {
	text-indent: -99999px;
	white-space: nowrap;
	overflow: hidden;
}
<?php
            }

            $mod = absint(get_theme_mod('nav_burger_icon_size'));

            if ($mod) {
?>
@media only screen and (min-width: 482px) {
.menu-button-container .button.button .dropdown-icon.open .svg-icon {
width: <?php echo (($mod - 24) / 2) + 24; ?>px;
height: <?php echo (($mod - 24) / 2) + 24; ?>px;
}
}
@media only screen and (min-width: 822px) {
.menu-button-container .button.button .dropdown-icon.open .svg-icon {
width: <?php echo $mod; ?>px;
height: <?php echo $mod; ?>px;
}
}
<?php
            }

            if (get_theme_mod('show_social_icons')) {

                add_filter('walker_nav_menu_start_el', array($this, 'oftto_walker_nav_menu_start_el'), 10, 4);

            }

            ofttoCommon::generate_css('.menu-button-container #primary-mobile-menu', 'color', 'nav_burger_icon_color');
            ofttoCommon::generate_css('.site a:focus:not(.wp-block-button__link):not(.wp-block-file__button), .has-background-white .site a:focus:not(.wp-block-button__link):not(.wp-block-file__button), .is-dark-theme .site a:focus:not(.wp-block-button__link):not(.wp-block-file__button)', 'background-color', 'nav_prevent_item_background', '', '', 'transparent');
            ofttoCommon::generate_css('.primary-navigation a:link, .primary-navigation a:visited, .primary-navigation > div > .menu-wrapper .sub-menu-toggle .icon-plus svg, .primary-navigation > div > .menu-wrapper .sub-menu-toggle .icon-minus svg', 'color', 'nav_link_color');
            ofttoCommon::generate_css('.primary-navigation .current-menu-item > a, .primary-navigation .current_page_item > a', 'color', 'current_link_color');
            ofttoCommon::generate_css('.primary-navigation #menu-item-search.menu-item>.svg-icon', 'fill', 'nav_link_color');
            ofttoCommon::generate_css('.primary-navigation a:hover, .primary-navigation .sub-menu .menu-item > a:hover', 'color', 'nav_link_hover_color');
            ofttoCommon::generate_css('.primary-navigation #menu-item-search.menu-item>.svg-icon:hover', 'fill', 'nav_link_hover_color');

            $mod = get_theme_mod('nav_mobile_link_color');

            if ($mod) {
?>
@media only screen and (max-width: 481px) {
.primary-navigation a:link, .primary-navigation a:visited {
color: <?php echo $mod; ?>;
}
}
<?php
            }

            $mod = get_theme_mod('nav_mobile_link_hover_color');

            if ($mod) {
?>
@media only screen and (max-width: 481px) {
.primary-navigation a:hover {
color: <?php echo $mod; ?>;
}
}
<?php
            }

            ofttoCommon::generate_css('.primary-navigation .primary-menu-container > ul > .menu-item', 'text-transform', 'nav_link_text_transform');

            $size = absint(get_theme_mod('nav_desktop_font_size'));

            if ($size) {

?>
@media only screen and (min-width: <?php echo $mobile_breakpoint + 1; ?>px) {
    .primary-navigation .primary-menu-container > ul > .menu-item > a {
    	padding: <?php echo $size / 1000 * 0.75; ?>rem;
    	font-size: <?php echo $size / 1000; ?>rem;
    	line-height: <?php echo $size / 1000; ?>rem;
    }
    .primary-navigation .primary-menu-container > ul > #menu-item-search > .svg-icon {
    	width: <?php echo $size / 1000; ?>rem;
    	height: <?php echo $size / 1000; ?>rem;
    }
	.primary-navigation > div > .menu-wrapper .sub-menu-toggle {
	    height: <?php echo $size / 1000 * 2.5; ?>rem;
    }
    .primary-navigation > div > .menu-wrapper .sub-menu-toggle .icon-plus svg, .primary-navigation > div > .menu-wrapper .sub-menu-toggle .icon-minus svg {
    	height: <?php echo $size / 1000; ?>rem;
    	width: <?php echo $size / 1000; ?>rem;
    	margin-top: 0;
    	margin-right: <?php echo $size / 1000 * 0.15; ?>rem;
    }
    .primary-navigation > div > .menu-wrapper .sub-menu-toggle {
    	width: <?php echo $size / 1000 * 2; ?>rem;
    }
}
<?php

            }

            $mod = absint(get_theme_mod('nav_desktop_item_padding'));

            if ($mod) {

                $size = ($size ? $size : 1250) / 1000;
                $mod = ($mod - 1) / 100;

?>
@media only screen and (min-width: <?php echo $mobile_breakpoint + 1; ?>px) {
    .primary-navigation .primary-menu-container > ul > .menu-item > a {
    	padding-left: <?php echo round($size * $mod, 2); ?>rem;
    	padding-right: <?php echo round($size * $mod, 2); ?>rem;
    }
}
<?php

            }

            ofttoCommon::generate_css('.primary-navigation a', 'font-weight', 'nav_font_weight');

            $mod = get_theme_mod('nav_submenu_border_color');

            if ($mod) {
?>
.primary-navigation .sub-menu {
    border-color: <?= $mod; ?>;
}
@media only screen and (min-width: 482px) {
	.primary-navigation > div > .menu-wrapper > li > .sub-menu:before,
	.primary-navigation > div > .menu-wrapper > li > .sub-menu:after {
		border-color: <?= $mod; ?> transparent;
	}
}
<?php
            }

            ofttoCommon::generate_css('.primary-navigation > div > .menu-wrapper > li > .sub-menu:before, .primary-navigation > div > .menu-wrapper > li > .sub-menu:after', 'display', 'hide_submenu_caret', '', '', 'none');

            $mod = get_theme_mod('nav_submenu_background_color');

            if ($mod) {
?>
@media only screen and (min-width: 482px) {
    .primary-navigation > div > .menu-wrapper > li > .sub-menu:after {
        border-bottom-color: <?php echo $mod; ?>;
    }
    .primary-navigation > div > .menu-wrapper > li > .sub-menu, .primary-navigation > div > .menu-wrapper > li > .sub-menu li {
        background-color: <?php echo $mod; ?>;
    }
}
<?php
            }

            $mod = get_theme_mod('nav_submenu_link_color');

            if ($mod) {
?>
@media only screen and (min-width: <?php echo $mobile_breakpoint + 1; ?>px) {
    .primary-navigation .sub-menu .menu-item > a:link, .primary-navigation .sub-menu .menu-item > a:visited {
        color: <?php echo $mod; ?>;
    }
}
<?php
            }

            $mod = absint(get_theme_mod('nav_submenu_font_size'));

            if ($mod) {
?>
.primary-navigation .sub-menu .menu-item > a {
    font-size: <?php echo $mod / 1000 * 1.125; ?>rem;
}
@media only screen and (min-width: 482px) {
    .primary-navigation .sub-menu .menu-item > a {
        font-size: <?php echo $mod / 1000; ?>rem;
    }
}
<?php
            }

            ofttoCommon::generate_css('.primary-navigation .sub-menu .menu-item > a', 'padding', 'nav_submenu_padding', '', '', absint(((absint(get_theme_mod('nav_submenu_padding')) -1) * 1.25)) . 'px ' . (absint(get_theme_mod('nav_submenu_padding')) -1) . 'px');

            ofttoCommon::generate_css('.site-main', 'padding-top', 'content_padding_top', '', '', (absint(get_theme_mod('content_padding_top')) -1) . 'px ');
            ofttoCommon::generate_css('.site-main a', 'color', 'content_link_color');
            ofttoCommon::generate_css('.site-main a:hover', 'color', 'content_link_hover_color');
            ofttoCommon::generate_css('.page .entry-header, .single .entry-header', 'display', 'hide_page_headers', '', '', 'none');
            ofttoCommon::generate_css('.blog .page-header .page-title, .page .entry-header .entry-title, .single .entry-header .entry-title', 'display', 'hide_page_titles', '', '', 'none');
            ofttoCommon::generate_css('.page .entry-header .post-thumbnail, .single .entry-header .post-thumbnail, .page-header .archive-thumbnail', 'display', 'hide_featured_images', '', '', 'none');
            ofttoCommon::generate_css('.singular .entry-header', 'background-color', 'title_background_color');
            ofttoCommon::generate_css('.singular .entry-header', 'padding-left', 'title_background_color', '', '', '30px');
            ofttoCommon::generate_css('.singular .entry-header', 'padding-top', 'title_background_color', '', '', '30px');
            ofttoCommon::generate_css('.singular .entry-header', 'padding-right', 'title_background_color', '', '', '30px');
            ofttoCommon::generate_css('.singular .entry-title, .blog .page-title, .error404 .page-title', 'font-size', 'page_title_font_size', '', 'rem', absint(get_theme_mod('page_title_font_size')) / 1000);
            ofttoCommon::generate_css('.entry-title, h1.entry-title, .page-title, h1.page-title', 'font-weight', 'page_title_font_weight');
            ofttoCommon::generate_css('.singular .entry-header, .page-header', 'padding-bottom', 'page_title_padding_bottom', '', '', (absint(get_theme_mod('page_title_padding_bottom')) -1) . 'px ');
            ofttoCommon::generate_css('.singular .entry-header, .page-header', 'border-bottom-width', 'title_border_bottom', '', 'px', absint(get_theme_mod('title_border_bottom')) - 1);
            ofttoCommon::generate_css('.singular .entry-header, .page-header', 'margin-bottom', 'title_margin_bottom', '', 'px', absint(get_theme_mod('title_margin_bottom')) - 1);
            ofttoCommon::generate_css('.archive .page-header', 'display', 'hide_archive_titles', '', '', 'none');
            ofttoCommon::generate_css('.archive .page-title, .search .page-title', 'font-size', 'archive_title_font_size', '', 'rem', absint(get_theme_mod('archive_title_font_size')) / 1000);

            $mod = absint(get_theme_mod('archive_post_title_font_size'));

            if ($mod) {
?>
.entry-title {
    font-size: <?php echo $mod / 1000 * 0.9; ?>rem;
}
@media only screen and (min-width: 652px) {
    .entry-title {
        font-size: <?php echo $mod / 1000; ?>rem;
    }
}
<?php
            }

            ofttoCommon::generate_css('.entry-title, .page-title', 'letter-spacing', 'page_title_letter_spacing', '', 'em', absint(get_theme_mod('page_title_letter_spacing')) / 100);

            $mod = absint(get_theme_mod('content_margin_top'));

            if ($mod) {
?>
.site-main > article .entry-content {
	margin-top: <?php echo $mod - 1; ?>px;
}
@media only screen and (min-width: 482px) {
.site-main > article .entry-content {
	margin-top: <?php echo round(($mod - 1) / 3 * 2, 1); ?>px;
}
}
<?php
            }

            ofttoCommon::generate_css('.post-taxonomies', 'display', 'hide_taxonomies', '', '', 'none');
            ofttoCommon::generate_css('.tags-links', 'display', 'hide_tags', '', '', 'none');
            ofttoCommon::generate_css('.archive .entry-footer .cat-links, .single .site-main>article>.entry-footer .cat-links', 'display', 'hide_cat', '', '', 'none');
            ofttoCommon::generate_css('.site-main>article>.entry-header>.entry-title>a', 'color', 'archive_post_title_color');
            ofttoCommon::generate_css('.site-main > article > .entry-footer', 'border-bottom-width', 'post_footer_border_bottom', '', 'px', absint(get_theme_mod('post_footer_border_bottom')) - 1);
            ofttoCommon::generate_css('.single .site-main > article > .entry-footer', 'border-top-width', 'post_footer_border_top', '', 'px', absint(get_theme_mod('post_footer_border_top')) - 1);
            ofttoCommon::generate_css('hr, hr.wp-block-separator', 'border-bottom-width', 'hr_width', '', 'px', absint(get_theme_mod('hr_width')) - 1);
            ofttoCommon::generate_css('.posted-on', 'display', 'hide_date', '', '', 'none');

            $mod = absint(get_theme_mod('comments_titles_font_size'));

            if ($mod) {
?>
.comments-title, .comment-reply-title {
    font-size: <?php echo $mod / 1000 * 0.75; ?>rem;
}
@media only screen and (min-width: 652px) {
    .comments-title, .comment-reply-title {
        font-size: <?php echo $mod / 1000; ?>rem;
    }
}
<?php
            }

            ofttoCommon::generate_css('.post-navigation', 'display', 'hide_post_navigation', '', '', 'none');

            if (get_theme_mod('remove_author')) {

                add_filter('gettext', array($this, 'oftto_replace_post_author_text'), 10, 3);

?>
.single .site-main > article > .entry-footer .byline {
	display: none;
}
<?php

            }

            $footer_color = get_theme_mod('footer_background_color');
            $footer_image = absint(get_theme_mod('footer_background_image'));

            if ($footer_image || $footer_color) {

?>
#footer-wrapper {<?php if ($footer_color) { ?>
    background-color: <?php echo $footer_color; ?>;<?php } if ($footer_image) { ?>
    background-image: url("<?php echo (wp_get_attachment_image_src($footer_image, 'full') ? wp_get_attachment_image_src($footer_image, 'full')[0] : ''); ?>");<?php } ?>
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-position: center;
}
@supports (-webkit-touch-callout: none) {
    #footer-wrapper {
        background-attachment: scroll;
    }
}
<?php

            }

            $mod = absint(get_theme_mod('footer_margin_top'));

            if ($mod) {
?>
.widget-area, .no-widgets .site-footer {
	margin-top: <?php echo $mod -1; ?>px;
}
@media only screen and (max-width: 481px) {
	.widget-area {
		margin-top: <?php echo ceil(($mod -1) / 2); ?>px;
	}
}
<?php
            }

            $mod = absint(get_theme_mod('mobile_widget_columns'));

            if ($mod) {
?>
.widget-area {
	display: grid;
	grid-template-columns: repeat(<?php echo $mod; ?>, 1fr);
	column-gap: calc(2 * var(--global--spacing-horizontal));
}
<?php
            }

            $mod = absint(get_theme_mod('tablet_widget_columns'));

            if ($mod) {
?>
@media only screen and (min-width: 652px) {
	.widget-area {
		grid-template-columns: repeat(<?php echo $mod; ?>, 1fr);
	}
}
<?php
            }

            $mod = absint(get_theme_mod('desktop_widget_columns'));

            if ($mod) {
?>
@media only screen and (min-width: 1024px) {
	.widget-area {
		grid-template-columns: repeat(<?php echo $mod; ?>, 1fr);
	}
}
<?php
            }

            $mod = absint(get_theme_mod('footer_max_width'));

            if ($mod) {

?>
@media only screen and (min-width: 822px) {
	.site-footer {
		max-width: min(calc(100vw - 8 * var(--global--spacing-horizontal)), <?php echo $mod; ?>px);
	}
}
@media only screen and (min-width: 822px) and (max-width: <?php echo $mod + 200; ?>px) {
    .site-footer {
    	max-width: calc(100vw - 200px);
    }
}
@media only screen and (min-width: <?php echo $mod + 201; ?>px) {
    .site-footer {
    	max-width: <?php echo $mod; ?>px;
    }
}
<?php

            }

            ofttoCommon::generate_css('.widget-area', 'font-size', 'footer_widget_font_size', '', 'rem', absint(get_theme_mod('footer_widget_font_size')) / 1000);
            ofttoCommon::generate_css('.widget-area, .site-footer, .site-footer > .site-info', 'color', 'footer_text_color');
            ofttoCommon::generate_css('.site-footer > .site-info a:link, .site-footer > .site-info a:visited, .site-footer > .site-info a:active', 'color', '__footer__color_link');
            ofttoCommon::generate_css(':root', '--footer--color-link', '__footer__color_link');
            ofttoCommon::generate_css('.footer-navigation', 'font-size', 'footer_nav_font_size', '', 'rem', absint(get_theme_mod('footer_nav_font_size')) / 1000);
            ofttoCommon::generate_css('.footer-navigation-wrapper', 'font-weight', 'footer_nav_font_weight');

            if (get_theme_mod('remove_social_icons')) {

                remove_filter('walker_nav_menu_start_el', 'twenty_twenty_one_nav_menu_social_icons');

            }

            ofttoCommon::generate_css('.footer-navigation-wrapper li .svg-icon', 'fill', 'social_icon_color');
            ofttoCommon::generate_css('.site-footer>.site-info', 'border-top-width', 'footer_border_top', '', 'px', absint(get_theme_mod('footer_border_top')) - 1);
            ofttoCommon::generate_css('.footer-navigation', 'margin', 'replace_site_name_with_footer_menu', '', '', '15px calc(0.66 * -20px)');
            ofttoCommon::generate_css('.site-footer>.site-info', 'display', 'hide_site_info', '', '', 'none');
            ofttoCommon::generate_css('.site-name', 'display', 'hide_site_name', '', '', 'none');

            if (get_theme_mod('hide_site_name') && get_theme_mod('center_site_name_powered_by')) {

?>
.site-info {
    justify-content: center;
}
.site-footer > .site-info .powered-by {
    margin-left: 0;
}
<?php

            }

            $mod = absint(get_theme_mod('footer_logo_size'));

            if ($mod) {

?>
.site-footer .site-logo .custom-logo {
    max-width: <?php echo $mod; ?>px;
    max-height: none;
    width: 100%;
}
<?php

            }

            ofttoCommon::generate_css('.site-footer > .site-info .site-name', 'font-size', 'footer_site_title_font_size', '', 'rem', absint(get_theme_mod('footer_site_title_font_size')) / 1000);
            ofttoCommon::generate_css('.site-footer > .site-info .site-name', 'text-transform', 'footer_site_title_text_transform');
            ofttoCommon::generate_css('.powered-by', 'display', 'remove_powered_by_wordpress', '', '', 'none');

            if (get_theme_mod('remove_powered_by_wordpress') && get_theme_mod('center_site_name_powered_by')) {

?>
.site-info {
    justify-content: center;
}
<?php

            }

            ofttoCommon::generate_css('.site-footer > .site-info', 'font-size', 'powered_by_font_size', '', 'rem', absint(get_theme_mod('powered_by_font_size')) / 1000);

            if (get_theme_mod('remove_footer_padding')) {

                add_filter('gettext', array($this, 'oftto_replace_post_author_text'), 10, 3);

?>
.footer-navigation {
	margin: 0;
}
.site-footer>.site-info {
	padding-top: 0;
	margin: 0;
}
.site-footer {
	padding-bottom: 0;
}
<?php

            }

            if (self::oftto_is_dark_mode_enabled()) {

                ofttoCommon::generate_css('.is-dark-theme .site-title', 'color', 'dark_mode_title_color');
                ofttoCommon::generate_css('.is-dark-theme .site-description', 'color', 'dark_mode_description_color');

            }

?>
</style> 
<!--/Customizer CSS-->
<?php

        }

        function oftto_editor_styles() {

        	add_editor_style('https://options-for-twenty-twenty-one/style-editor.css');

        }

        function oftto_pre_http_request($response, $parsed_args, $url) {

        	if ($url === 'https://options-for-twenty-twenty-one/style-editor.css') {

        		$response = array(
        			'body'     => '',
        			'headers'  => new Requests_Utility_CaseInsensitiveDictionary(),
        			'response' => array(
        				'code'    => 200,
        				'message' => 'OK',    
				    ),
                    'cookies'  => array(),
                    'filename' => null,
		        );

                if (get_theme_mod('body_font_size')) { $response['body'] .= 'body { font-size: ' . (get_theme_mod('body_font_size') / 1000) . 'rem; }'; }
                if (get_theme_mod('page_title_font_size')) { $response['body'] .= '.wp-block.editor-post-title__block .editor-post-title__input { font-size: ' . (get_theme_mod('page_title_font_size') / 1000) . 'rem; }'; }
                if (get_theme_mod('page_title_letter_spacing')) { $response['body'] .= '.wp-block.editor-post-title__block .editor-post-title__input { letter-spacing: ' . (get_theme_mod('page_title_letter_spacing') / 100) . 'em; }'; }

        	}

        	return $response;

        }

        function oftto_enqueue_customize_preview_js() {

            wp_enqueue_script('oftto-customize-preview', plugin_dir_url( __FILE__ ) . 'js/customize-preview.js', array('jquery','customize-preview'), self::$version, true);

        }

        function oftto_enqueue_customize_controls_js() {

            wp_enqueue_script('oftn-customize-controls', plugin_dir_url(__FILE__) . 'js/customize-controls.js', array('jquery', 'customize-controls'), ofttoCommon::plugin_version(), true);


        }

        function oftto_enqueue_customizer_css() {

            wp_enqueue_style('oftto-customizer-css', plugin_dir_url( __FILE__ ) . 'css/theme-customizer.css', array(), self::$version);

        }

        public function oftto_walker_nav_menu_start_el($item_output, $item, $depth, $args) {

        	if ('primary' === $args->theme_location) {

        		$svg = twenty_twenty_one_get_social_link_svg($item->url, 24);

        		if (!empty($svg)) {

                    $title = apply_filters('the_title', $item->title, $item->ID);
                    $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);
        			$item_output = str_replace($title, $svg, $item_output);

        		}

        	}

        	return $item_output;

        }

        public function oftto_replace_post_author_text($translation, $text, $domain) {
 
            if ($text === 'By %s' && $domain == 'twentytwentyone') {

                $translation = '';

            }

            return $translation;

        }

        public function oftto_frontend_javascript() {

            if (get_theme_mod('move_nav_below_header')) {

?>
<script type="text/javascript">
    (function () {
    	document.querySelector('#content').parentNode.insertBefore(document.querySelector('#site-navigation'), document.querySelector('#content'));
    }());
</script>
<?php

            }

            $inject_sidebar_args = get_theme_mod('inject_sidebar');

            if ($inject_sidebar_args || (is_singular() && '1' == get_post_meta(get_the_ID(), 'oftto_show_sidebar', true) && !get_post_meta(get_the_ID(), 'oftto_hide_sidebar', true))) {

                if (is_archive()) {

                    global $wp_query;
                    $taxonomy = $wp_query->get_queried_object();

                }

                $show_sidebar = false;

                if (
                    is_active_sidebar('sidebar-2') && ($inject_sidebar_args &&
                        in_array('all', $inject_sidebar_args) ||
                        (in_array('front_page', $inject_sidebar_args) && is_front_page()) ||
                        (in_array('home', $inject_sidebar_args) && is_home()) ||
                        (in_array('page', $inject_sidebar_args) && is_page()) ||
                        (in_array('single', $inject_sidebar_args) && is_single()) ||
                        (in_array('archive', $inject_sidebar_args) && is_archive()) ||
                        (is_single() && in_array('post_type/' . get_post_type() , $inject_sidebar_args)) ||
                        (is_page() && !is_front_page() && in_array('post_type/page', $inject_sidebar_args)) ||
                        (is_archive() && is_object($taxonomy) && in_array('taxonomy/' . get_post_type() . '/' . $taxonomy->taxonomy , $inject_sidebar_args)) ||
                        (is_singular() && '1' == get_post_meta(get_the_ID(), 'oftto_show_sidebar', true) && '1' != get_post_meta(get_the_ID(), 'oftto_hide_sidebar', true))
                    )
                ) {

                    $show_sidebar = true;

                }

                if (is_singular() && '1' == get_post_meta(get_the_ID(), 'oftto_show_sidebar', true)) {

                    $show_sidebar = true;

                }

                if (is_singular() && '1' == get_post_meta(get_the_ID(), 'oftto_hide_sidebar', true)) {

                    $show_sidebar = false;

                }

                if ($show_sidebar) {

?>
<aside id="sidebar" class="widget-area">
<?php dynamic_sidebar('sidebar-2'); ?>
</aside>
<script type="text/javascript">
    (function () {
        var wrapper = document.createElement('div'),
            content = document.getElementById('content'),
            sidebar = document.getElementById('sidebar');
        wrapper.id = 'content-wrapper';
        wrapper.className += 'alignwide';
        content.parentNode.insertBefore(wrapper, content);
        wrapper.appendChild(content);
        wrapper.appendChild(sidebar);
    }());
</script>
<?php

                }

            }

            $mod = (
                absint(get_theme_mod('header_background_image')) || 
                get_theme_mod('header_background_color') ||
                get_theme_mod('sticky_mobile') === 'masthead-wrapper' ||
                get_theme_mod('sticky_desktop') === 'masthead-wrapper'
            );

            if ($mod) {

?>
<script type="text/javascript">
    (function () {
    	var masthead = document.querySelector('#masthead');
        var mastheadWrapper = document.createElement('div');
        mastheadWrapper.id = 'masthead-wrapper';
        masthead.parentNode.insertBefore(mastheadWrapper, masthead);
        mastheadWrapper.appendChild(masthead);
    }());
</script>
<?php

            }

            if (get_theme_mod('inject_breadcrumbs')) {

                $breadcrumbs = apply_filters('oftto_breadcrumbs', false);

                if ($breadcrumbs || function_exists('bcn_display') || function_exists('yoast_breadcrumb')) {

                    if ($breadcrumbs) {

                        echo '<div id="breadcrumbs" class="alignwide">' . $breadcrumbs . '</div>';

                    } elseif (function_exists('bcn_display')) {

                        echo('<div id="breadcrumbs" class="alignwide">');
                        bcn_display();
                        echo('</div>');

                    } elseif (function_exists('yoast_breadcrumb')) {

                        yoast_breadcrumb('<div id="breadcrumbs" class="alignwide">','</div>');

                    }

?>
<script type="text/javascript">
    (function () {
        if (document.getElementById('content-wrapper')) {
    	    document.getElementById('content-wrapper').parentNode.insertBefore(document.getElementById('breadcrumbs'), document.getElementById('content-wrapper'));
        } else {
    	    document.getElementById('content').parentNode.insertBefore(document.getElementById('breadcrumbs'), document.getElementById('content'));
        }
    }());
</script>
<?php

                }

            }

            if ((is_single() || is_home()) && get_theme_mod('move_date')) {

?>
<script type="text/javascript">
    (function () {
        Array.prototype.forEach.call(document.querySelectorAll('article.entry'), function(each_article) {
            var posted_on = each_article.getElementsByClassName('posted-on')[0],
                posted_on_div = document.createElement('div');
            posted_on_div.className = 'posted-on<?php if (is_home()) { ?> default-max-width<?php } ?>';
            posted_on_div.innerHTML = posted_on.innerHTML;
            each_article.getElementsByClassName('entry-header')[0].appendChild(posted_on_div);
            posted_on.parentNode.removeChild(posted_on);
        });
    }());
</script>
<?php

            }

            $mod = (
                absint(get_theme_mod('footer_background_image')) || 
                get_theme_mod('footer_background_color')
            );

            if ($mod) {

?>
<script type="text/javascript">
    (function () {
    	var siteFooter = document.getElementsByClassName('site-footer')[0];
        var footerWrapper = document.createElement('div');
        footerWrapper.id = 'footer-wrapper';
        siteFooter.parentNode.insertBefore(footerWrapper, siteFooter);<?php

                if (get_theme_mod('expand_footer_background')) {

?>
        if (document.getElementsByClassName('widget-area')[1]) {
            footerWrapper.appendChild(document.getElementsByClassName('widget-area')[1]);
        } else if (document.getElementsByClassName('widget-area')[0].id !== 'sidebar') {
            footerWrapper.appendChild(document.getElementsByClassName('widget-area')[0]);
        }<?php

                }

?>
        footerWrapper.appendChild(siteFooter);
    }());
</script>
<?php

            }

            if (get_theme_mod('replace_site_name_with_footer_menu')) {

?>
<script type="text/javascript">
    (function () {
    	document.querySelector('.site-info').prepend(document.querySelector('.footer-navigation'));
    	document.querySelector('.site-name').remove();
    }());
</script>
<?php

            }

        }

        function oftto_widgets_init() {

            register_sidebar(array(
                'name'          => esc_html__('Sidebar', 'options-for-twenty-twenty-one'),
                'id'            => 'sidebar-2',
                'description'   => esc_html__('Add widgets here to appear in your sidebar.', 'options-for-twenty-twenty-one'),
                'before_title'  => '<h2 class="widget-title">',
                'after_title'   => '</h2>',
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>'
            ));

        }

        public static function oftto_are_title_and_tagline_displayed() {

            return (get_theme_mod('display_title_and_tagline', true) ? true : false);

        }

        public static function oftto_is_dark_mode_enabled() {

            return (get_theme_mod('respect_user_color_preference') ? true : false);

        }

	}

    if (!class_exists('ofttoCommon')) {

        require_once(dirname(__FILE__) . '/includes/class-oftto-common.php');

    }

    if (ofttoCommon::is_theme_being_used('twentytwentyone')) {

	    $options_for_twenty_twenty_one_object = new options_for_twenty_twenty_one_class();

    } else {

        if (is_admin()) {

            $themes = wp_get_themes();

            if (!isset($themes['twentytwentyone'])) {

                add_action('admin_notices', 'oftto_wrong_theme_notice');

            }

        }

    }

    function oftto_wrong_theme_notice() {

?>

<div class="notice notice-error">

<p><strong><?php _e('Options for Twenty Twenty-One Plugin Error', 'options-for-twenty-twenty-one'); ?></strong><br />
<?php
        printf(
            __('This plugin requires the default Wordpress theme Twenty Twenty-One to be active or live previewed in order to function. Your theme "%s" is not compatible.', 'options-for-twenty-twenty-one'),
            get_template()
        );
?>

<a href="<?php echo add_query_arg('search', 'twentytwentyone', admin_url('theme-install.php')); ?>" title="<?php _e('Twenty Twenty-One', 'options-for-twenty-twenty-one'); ?>"><?php
        _e('Please install and activate or live preview the Twenty Twenty-One theme (or a child theme thereof)', 'options-for-twenty-twenty-one');
?></a>.</p>

</div>

<?php

    }

}

?>
