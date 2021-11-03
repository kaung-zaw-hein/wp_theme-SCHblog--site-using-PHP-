<?php
/**
 * Plugin Name: Sparkle Demo Importer
 * Plugin URI: https://github.com/sparklewpthemes/sparkle-demo-importer
 * Description: Import Sparkle Themes Demo Site by one click.
 * Version: 1.2.1
 * Author: sparklewpthemes
 * Author URI:  https://sparklewpthemes.com
 * Text Domain: sparkle-demo-importer
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 */

if (!defined('ABSPATH')) exit;


if (!class_exists('Sparkle_Demo_Importer_Main')) {

    class Sparkle_Demo_Importer_Main {

        public $this_uri;
        public $this_dir;
        public $configFile;
        public $uploads_dir;
        public $plugin_install_count;
        public $ajax_response = array();
        public $theme_name;
        /*
         * Constructor
         */

        public function __construct() {
            /**
             * Constants Define
             */
            define('SPARKLE_DEMOI_VERSION', '1.0.9');
            define('SPARKLE_DEMOI_FILE', __FILE__);
            define('SPARKLE_DEMOI_PLUGIN_BASENAME', plugin_basename(SPARKLE_DEMOI_FILE));
            define('SPARKLE_DEMOI_PATH', plugin_dir_path(SPARKLE_DEMOI_FILE));
            define('SPARKLE_DEMOI_URL', plugins_url('/', SPARKLE_DEMOI_FILE));
            define('SPARKLE_DEMOI_ASSETS_URL', SPARKLE_DEMOI_URL . 'assets/');



            // This uri & dir
            $this->this_uri = SPARKLE_DEMOI_URL;
            $this->this_dir = SPARKLE_DEMOI_PATH;

            $this->uploads_dir = wp_get_upload_dir();

            $this->plugin_install_count = 0;

            
            $theme = wp_get_theme();
            $this->theme_name = $theme->Name;

            // Include necesarry files
            $this->configFile = include $this->this_dir . 'import_config.php';

            require_once $this->this_dir . 'classes/class-demo-importer.php';
            require_once $this->this_dir . 'classes/class-customizer-importer.php';
            require_once $this->this_dir . 'classes/class-widget-importer.php';

            // WP-Admin Menu
            add_action('admin_menu', array($this, 'sparkle_demo_import_menu'));

            // Add necesary backend JS
            add_action('admin_enqueue_scripts', array($this, 'admin_script'));

            // Actions for the ajax call
            add_action('wp_ajax_sparkle_demo_import_install_demo', array($this, 'sparkle_demo_import_install_demo'));
            add_action('wp_ajax_sparkle_demo_import_install_plugin', array($this, 'sparkle_demo_import_install_plugin'));
            add_action('wp_ajax_sparkle_demo_import_download_files', array($this, 'sparkle_demo_import_download_files'));
            add_action('wp_ajax_sparkle_demo_import_import_xml', array($this, 'sparkle_demo_import_import_xml'));
            add_action('wp_ajax_sparkle_demo_import_customizer_import', array($this, 'sparkle_demo_import_customizer_import'));
            add_action('wp_ajax_sparkle_demo_import_menu_import', array($this, 'sparkle_demo_import_menu_import'));
            add_action('wp_ajax_sparkle_demo_import_theme_option', array($this, 'sparkle_demo_import_theme_option'));
            add_action('wp_ajax_sparkle_demo_import_importing_widget', array($this, 'importing_widget'));
            
            if ( ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) ) {
                remove_filter( 'wp_import_post_meta', array( 'Elementor\Compatibility', 'on_wp_import_post_meta' ) );
                remove_filter( 'wxr_importer.pre_process.post_meta', array( 'Elementor\Compatibility', 'on_wxr_importer_pre_process_post_meta' ) );

                add_filter( 'wp_import_post_meta', array( $this, 'on_wp_import_post_meta' ) );
                add_filter( 'wxr_importer.pre_process.post_meta', array( $this, 'on_wxr_importer_pre_process_post_meta' ) );
            }
        }

        /**
		 * Process post meta before WP importer.
		 *
		 * Normalize Elementor post meta on import, We need the `wp_slash` in order
		 * to avoid the unslashing during the `add_post_meta`.
		 *
		 * Fired by `wp_import_post_meta` filter.
		 *
		 * @since 1.0.3
		 *
		 * @param array $post_meta Post meta.
		 *
		 * @return array Updated post meta.
		 */
		public function on_wp_import_post_meta( $post_meta ) {
			foreach ( $post_meta as &$meta ) {
				if ( '_elementor_data' === $meta['key'] ) {
					$meta['value'] = wp_slash( $meta['value'] );
					break;
				}
			}

			return $post_meta;
        }

        /**
		 * Process post meta before WXR importer.
		 *
		 * Normalize Elementor post meta on import with the new WP_importer, We need
		 * the `wp_slash` in order to avoid the unslashing during the `add_post_meta`.
		 *
		 * Fired by `wxr_importer.pre_process.post_meta` filter.
		 *
		 * @since 1.0.3
		 *
		 * @param array $post_meta Post meta.
		 *
		 * @return array Updated post meta.
		 */
		public function on_wxr_importer_pre_process_post_meta( $post_meta ) {
			if ( '_elementor_data' === $post_meta['key'] ) {
				$post_meta['value'] = wp_slash( $post_meta['value'] );
			}

			return $post_meta;
		}

        /**
         * Memu on Dashboard
         * @since 1.0.0
         */
        function sparkle_demo_import_menu() {
            add_submenu_page('themes.php', 'Sparkle OneClick Demo Installer', 'Sparkle Demo Import', 'manage_options', 'sparkle-theme-demo-importer', array($this, 'sparkle_demo_import_display_demos'));

        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * */
        function sparkle_demo_import_category(){
            $categories = array_column($this->configFile, 'categories');
            if( is_array( $categories) && !empty( $categories )){
            ?>
             <div class="category-sidebar sparkle-theme-tab-filter">
                <div class="available-categories">
                    <h3><?php esc_html_e( 'Categories', 'sparkle-demo-importer' ); ?></h3>
                    <ul class="available-categories-lists sparkle-theme-tab-group" data-filter-group="category">
                        <li class="sparkle-theme-tab sparkle-theme-active" data-filter="*">
                            <?php esc_html_e( 'All Categories', 'advanced-import' ); ?>
                            <span class="cat-count"></span>
                        </li>
                        <?php
                        $categories = array_column($this->configFile, 'categories');
                        $unique_categories = array();
                        if( is_array( $categories) && !empty( $categories )){
                            foreach ( $categories as $demo_index => $demo_cats ){
                                foreach ( $demo_cats as $cat_index => $single_cat ){
                                    if (in_array($single_cat, $unique_categories)){
                                        continue;
                                    }
                                    $unique_categories[] = $single_cat;
                                    ?>
                                    <li class="sparkle-theme-tab" data-filter=".<?php echo strtolower( esc_attr($cat_index) );?>">
                                        <?php echo ucfirst( esc_html($single_cat) );?>
                                        <span class="cat-count"></span>
                                    </li>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <?php
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * */
        function sparkle_demo_import_welcome(){
            ?>
             <h2><?php echo sprintf(esc_html__('Welcome to the Sparkle Demo Importer for %s', 'sparkle-demo-importer'), $this->theme_name); ?></h2>
            <p>
                <?php echo sprintf(esc_html__('Thank you for choosing the %s theme. Quick demo import setup will help you configure your new website like our demo. It will install the required WordPress plugins, default content and tell you a little about Help & Support options. It should only take less than 5 minutes.', 'sparkle-demo-importer'), $this->theme_name); ?>
            </p>
            <?php

        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_tag_search_filter
         * Display the available demos
         * */
        function sparkle_demo_import_tag_search_filter(){
            if (is_array($this->configFile) && !is_null($this->configFile)) {  
                $tags = $pagebuilders = array();
                foreach ($this->configFile as $demo_slug => $demo_pack) {
                    if (isset($demo_pack['tags']) && is_array($demo_pack['tags'])) {
                        foreach ($demo_pack['tags'] as $key => $tag) {
                            $tags[$key] = $tag;
                        }
                    }
                }

                foreach ($this->configFile as $demo_slug => $demo_pack) {
                    if (isset($demo_pack['pagebuilder']) && is_array($demo_pack['pagebuilder'])) {
                        foreach ($demo_pack['pagebuilder'] as $key => $pagebuilder) {
                            $pagebuilders[$key] = $pagebuilder;
                        }
                    }
                }
                asort($tags);
                asort($pagebuilders);

                if (!empty($tags) || !empty($pagebuilders)) {
                    ?>
                    <div class="sparkle-theme-tab-filter sparkle-theme-clearfix">
                        <?php
                        if (!empty($tags)) {
                            ?>
                            <div class="sparkle-theme-tab-group sparkle-theme-tag-group" data-filter-group="tag">
                                <div class="sparkle-theme-tab" data-filter="*">
                                    <?php esc_html_e('All', 'sparkle-demo-importer'); ?>
                                </div>
                                <?php
                                foreach ($tags as $key => $value) {
                                    ?>
                                    <div class="sparkle-theme-tab" data-filter=".<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($value); ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }

                        if (!empty($pagebuilders)) {
                            ?>
                            <div class="sparkle-theme-tab-group sparkle-theme-pagebuilder-group" data-filter-group="pagebuilder">
                                <div class="sparkle-theme-tab" data-filter="*">
                                    <?php esc_html_e('All', 'sparkle-demo-importer'); ?>
                                </div>
                                <?php
                                foreach ($pagebuilders as $key => $value) {
                                    ?>
                                    <div class="sparkle-theme-tab" data-filter=".<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($value); ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        <?php }
                        ?>
                    </div>
                    <?php
                }
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_display_demos
         * Display the available demos
         * */
        function sparkle_demo_import_display_demos() {
            ?>
            <div class="wrap sparkle-theme-demo-importer-wrap">
               <?php $this->sparkle_demo_import_welcome(); ?>
                <div class="main-wrapper">
                        <?php $this->sparkle_demo_import_tag_search_filter();
                        if (is_array($this->configFile) && !is_null($this->configFile)) { ?>

                            <div class="sparkle-theme-demo-list-wrap wp-clearfix">
                                <?php $this->sparkle_demo_import_category(); ?>

                                <div class="sparkle-theme-demo-box-wrap">
                                    <?php
                                    // Loop through Demos
                                    foreach ($this->configFile as $demo_slug => $demo_pack) {
                                        $tags = $pagebuilder  = $categories = "";
                                        if (isset($demo_pack['tags'])) {
                                            $tags = implode(' ', array_keys($demo_pack['tags']));
                                        }
                                        if (isset($demo_pack['pagebuilder'])) {
                                            $pagebuilder = implode(' ', array_keys($demo_pack['pagebuilder']));
                                        }
                                        if (isset($demo_pack['categories'])) {
                                            $categories = implode(' ', array_keys($demo_pack['categories']));
                                        }

                                        $classes = $tags . ' '. $pagebuilder. ' '. $categories;
                                        $type = isset($demo_pack['type']) ? $demo_pack['type'] : 'free';
                                        ?>
                                        <div id="<?php echo esc_attr($demo_slug); ?>" class="sparkle-theme-demo-box <?php echo esc_attr($classes); ?>">
                                            <?php if ($type == 'pro') { ?>
                                                <div class="sparkle-demo-ribbon"><span>Premium</span></div>
                                            <?php } ?>

                                            <img src="<?php echo esc_url($demo_pack['image']); ?> ">

                                            <div class="sparkle-theme-demo-actions">
                                                <h4><?php echo esc_html($demo_pack['name']); ?></h4>

                                                <div class="sparkle-theme-demo-buttons">
                                                    <a href="<?php echo esc_url($demo_pack['preview_url']); ?>" target="_blank" class="button">
                                                        <?php echo esc_html__('Preview', 'sparkle-demo-importer'); ?>
                                                    </a> 

                                                    
                                                     <?php
                                                        if ($type == 'pro') {
                                                            $buy_url = isset($demo_pack['buy_url']) ? $demo_pack['buy_url'] : '#';
                                                            ?>
                                                            <a target="_blank" href="<?php echo esc_url($buy_url) ?>" class="button button-primary">
                                                                <?php echo esc_html__('Buy Now', 'sparkle-demo-importer') ?>
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="#sparkle-theme-modal-<?php echo esc_attr($demo_slug) ?>" class="sparkle-theme-modal-button button button-primary">
                                                                <?php echo esc_html__('Install', 'sparkle-demo-importer') ?>
                                                            </a>
                                                        <?php }
                                                        ?>

                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    ?>
                                </div>
                            
                        <?php } else {
                            ?>
                            <div class="sparkle-theme-demo-list-wrap wp-clearfix">
                                <div class="sparkle-theme-demo-wrap">
                                    <?php esc_html_e("It looks like the config file for the demos is missing or conatins errors!. Demo install can\'t go futher!", 'sparkle-demo-importer'); ?>  
                                </div>
                        <?php } ?>

                        <?php
                        /* Demo Modals */
                        if (is_array($this->configFile) && !is_null($this->configFile)) {
                            foreach ($this->configFile as $demo_slug => $demo_pack) {
                                ?>
                                <div id="sparkle-theme-modal-<?php echo esc_attr($demo_slug) ?>" class="sparkle-theme-modal" style="display: none;">

                                    <div class="sparkle-theme-modal-header">
                                        <h2><?php printf(esc_html('Import %s Demo', 'sparkle-demo-importer'), esc_html($demo_pack['name'])); ?></h2>
                                        <div class="sparkle-theme-modal-back"><span class="dashicons dashicons-no-alt"></span></div>
                                    </div>

                                    <div class="sparkle-theme-modal-wrap">
                                        <p><?php echo sprintf(esc_html__('We recommend you backup your website content before attempting to import the demo so that you can recover your website if something goes wrong. You can use %s plugin for it.', 'sparkle-demo-importer'), '<a href="https://wordpress.org/plugins/all-in-one-wp-migration/" target="_blank">' . esc_html__('All in one migration', 'sparkle-demo-importer') . '</a>'); ?></p>

                                        <p><?php echo esc_html__('This process will install all the required plugins, import contents and setup customizer and theme options.', 'sparkle-demo-importer'); ?></p>

                                        <div class="sparkle-theme-modal-recommended-plugins">
                                            <h4><?php esc_html_e('Required Plugins', 'sparkle-demo-importer') ?></h4>
                                            <p><?php esc_html_e('For your website to look exactly like the demo,the import process will install and activate the following plugin if they are not installed or activated.', 'sparkle-demo-importer') ?></p>
                                            <?php
                                            $plugins = isset($demo_pack['plugins']) ? $demo_pack['plugins'] : '';

                                            if (is_array($plugins)) {
                                                ?>
                                                <ul>
                                                    <?php
                                                    foreach ($plugins as $plugin) {
                                                        $name = isset($plugin['name']) ? $plugin['name'] : '';
                                                        $status = Sparkle_Demo_Importer::plugin_active_status($plugin['file_path']);
                                                        ?>
                                                        <li>
                                                            <?php
                                                            echo esc_html($name) . ' - ' . $this->get_plugin_status($status);
                                                            ?>
                                                        </li>
                                                    <?php }
                                                    ?>
                                                </ul>
                                                <?php
                                            }
                                            ?>
                                        </div>

                                        <div class="sparkle-theme-reset-checkbox">
                                            <h4><?php esc_html_e('Reset Website', 'sparkle-demo-importer') ?></h4>
                                            <p><?php esc_html_e('Reseting the website will delete all your post, pages, custom post types, categories, taxonomies, images and all other customizer and theme option settings.', 'sparkle-demo-importer') ?></p>
                                            <p><?php esc_html_e('It is always recommended to reset the database for a complete demo import.', 'sparkle-demo-importer') ?></p>
                                            <label>
                                                <input id="checkbox-reset-<?php echo esc_attr($demo_slug); ?>" type="checkbox" value='1' checked="checked"/>
                                                <?php echo esc_html('Reset Website - Check this box only if you are sure to reset the', 'sparkle-demo-importer'); ?>
                                            </label>
                                        </div>

                                        <a href="javascript:void(0)" data-demo-slug="<?php echo esc_attr($demo_slug) ?>" class="button button-primary sparkle-theme-import-demo"><?php esc_html_e('Import Demo', 'sparkle-demo-importer'); ?></a>
                                        <a href="javascript:void(0)" class="button sparkle-theme-modal-cancel"><?php esc_html_e('Cancel', 'sparkle-demo-importer'); ?></a>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <div id="sparkle-theme-import-progress" style="display: none">
                            <h2 class="sparkle-theme-import-progress-header"><?php echo esc_html__('Demo Import Progress', 'sparkle-demo-importer'); ?></h2>

                            <div class="sparkle-theme-import-progress-wrap">
                                <div class="sparkle-theme-import-loader">
                                    <div class="sparkle-theme-loader-content">
                                        <div class="sparkle-theme-loader-content-inside">
                                            <div class="sparkle-theme-loader-rotater"></div>
                                            <div class="sparkle-theme-loader-line-point"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sparkle-theme-import-progress-message"></div>
                            </div>
                        </div>
                    </div> <!-- demo import list wrap -->
                </div> <!-- main-wrapper -->
            </div>
            <?php
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_install_demo
         * Do the install command on ajax call
         * */
        function sparkle_demo_import_install_demo() {
            check_ajax_referer('demo-importer-ajax', 'security');

            // Get the demo content from the right file
            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            $this->ajax_response['demo'] = $demo_slug;

            if (isset($_POST['reset']) && $_POST['reset'] == 'true') {
                $this->reset_database();
                $this->ajax_response['complete_message'] = esc_html__('Database reset complete', 'sparkle-demo-importer');
            }

            $this->ajax_response['next_step'] = 'sparkle_demo_import_install_plugin';
            $this->ajax_response['next_step_message'] = esc_html__('Installing required plugins', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_install_plugin
         * */
        function sparkle_demo_import_install_plugin() {
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            // Install Required Plugins
            $this->install_plugins($demo_slug);

            $plugin_install_count = $this->plugin_install_count;

            $this->ajax_response['demo'] = $demo_slug;

            if ($plugin_install_count > 0) {
                $this->ajax_response['complete_message'] = esc_html__('All the required plugins installed and activated successfully', 'sparkle-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No plugin required to install', 'sparkle-demo-importer');
            }
            $this->ajax_response['next_step'] = 'sparkle_demo_import_download_files';
            $this->ajax_response['next_step_message'] = esc_html__('Downloading demo files', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_download_files
         * Forked from HashThemes Demo Importer Plugin
         * */
        function sparkle_demo_import_download_files() {
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            $this->download_files($this->configFile[$demo_slug]['external_url']);

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['complete_message'] = esc_html__('All demo files downloaded', 'sparkle-demo-importer');
            $this->ajax_response['next_step'] = 'sparkle_demo_import_import_xml';
            $this->ajax_response['next_step_message'] = esc_html__('Importing posts, pages and medias. It may take a bit longer time', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_import_xml
         * import xml file
         * Forked from HashThemes Demo Importer Plugin
         * */
        
        function sparkle_demo_import_import_xml() {
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            // Import XML content
            $this->import_demo_content($demo_slug);

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['complete_message'] = esc_html__('All content imported', 'sparkle-demo-importer');
            $this->ajax_response['next_step'] = 'sparkle_demo_import_customizer_import';
            $this->ajax_response['next_step_message'] = esc_html__('Importing customizer settings', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_customizer_import
         * import customizer data
         * Forked from HashThemes Demo Importer Plugin
         * */
        function sparkle_demo_import_customizer_import() {
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            $customizer_filepath = $this->demo_upload_dir($demo_slug) . '/customizer.dat';

            if (file_exists($customizer_filepath)) {
                ob_start();
                Sparkle_Demo_Customizer_Importer::import($customizer_filepath);
                ob_end_clean();
                $this->ajax_response['complete_message'] = esc_html__('Customizer settings imported', 'sparkle-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No Customizer settings found', 'sparkle-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = 'sparkle_demo_import_menu_import';
            $this->ajax_response['next_step_message'] = esc_html__('Setting primary menu', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_menu_import
         * set menu
         * Forked from HashThemes Demo Importer Plugin
         * */
        function sparkle_demo_import_menu_import() {
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            $menu_array = isset($this->configFile[$demo_slug]['menuArray']) ? $this->configFile[$demo_slug]['menuArray'] : '';
            // Set menu
            if ($menu_array) {
                $this->setMenu($menu_array);
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['complete_message'] = esc_html__('Primary menu saved', 'sparkle-demo-importer');
            $this->ajax_response['next_step'] = 'sparkle_demo_import_theme_option';
            $this->ajax_response['next_step_message'] = esc_html__('Importing theme option settings', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_demo_import_theme_option
         * import widget file
         * Forked from HashThemes Demo Importer Plugin
         * */
        function sparkle_demo_import_theme_option() {
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';

            $themeoption_filepath = $this->demo_upload_dir($demo_slug) . '/themeoption.json';

            if (file_exists($themeoption_filepath)) {
                $data = file_get_contents($themeoption_filepath);

                if ($data) {
                    if (update_option('sparkle-theme-options', json_decode($data, true), '', 'yes')) {
                        $this->ajax_response['complete_message'] = esc_html__('Theme options settings imported', 'sparkle-demo-importer');
                    }
                }
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No theme options found', 'sparkle-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = 'sparkle_demo_import_importing_widget';
            $this->ajax_response['next_step_message'] = esc_html__('Importing Widgets', 'sparkle-demo-importer');
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * importing_widget
         * import widget file
         * Forked from HashThemes Demo Importer Plugin
         * */
        function importing_widget() {
            check_ajax_referer('demo-importer-ajax', 'security');
            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo'])) : '';
            $widget_filepath = $this->demo_upload_dir($demo_slug) . '/widget.wie';

            if (file_exists($widget_filepath)) {
                ob_start();
                Sparkle_Demo_Widget_Importer::import($widget_filepath);
                ob_end_clean();
                $this->ajax_response['complete_message'] = esc_html__('Widgets Imported', 'sparkle-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No Widgets found', 'sparkle-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = '';
            $this->ajax_response['next_step_message'] = '';
            $this->send_ajax_response();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * sparkle_mkdir
         * create directory recursively 
         * */
        public function sparkle_mkdir($path){
            return mkdir($path, 0777, true);
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * download_files
         * Download files from given url
         * Forked from HashThemes Demo Importer Plugin
         * */   
        public function download_files($external_url) {
            // Make sure we have the dependency.
            if (!function_exists('WP_Filesystem')) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            /**
             * Initialize WordPress' file system handler.
             *
             * @var WP_Filesystem_Base $wp_filesystem
             */
            WP_Filesystem();
            global $wp_filesystem;
            
            $result = true;
            if (!($wp_filesystem->exists($this->demo_upload_dir()))) {
                $result = $this->sparkle_mkdir($this->demo_upload_dir());
            }

            // Abort the request if the local uploads directory couldn't be created.
            if (!$result) {
                $this->ajax_response['message'] = esc_html__('The directory for the demo packs couldn\'t be created.', 'sparkle-demo-importer');
                $this->ajax_response['error'] = true;
                $this->send_ajax_response();
            }

            $demo_pack = $this->demo_upload_dir() . 'demo-pack.zip';

            $file = wp_remote_retrieve_body(wp_remote_get($external_url, array(
                'timeout' => 60,
            )));
            
            $wp_filesystem->put_contents($demo_pack, $file);
            unzip_file($demo_pack, $this->demo_upload_dir());
            $wp_filesystem->delete($demo_pack);
            
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * reset_database
         * Reset the database, if the case
         * Forked from HashThemes Demo Importer Plugin
         * */
        function reset_database() {
            global $wpdb;
            $options = array(
                'offset' => 0,
                'orderby' => 'post_date',
                'order' => 'DESC',
                'post_type' => 'post',
                'post_status' => 'publish'
            );

            $statuses = array('publish', 'future', 'draft', 'pending', 'private', 'trash', 'inherit', 'auto-draft', 'scheduled');
            $types = array(
                'post',
                'page',
                'attachment',
                'nav_menu_item',
                'wpcf7_contact_form',
                'product',
                'portfolio',
                'custom_css'
            );

            // delete posts
            foreach ($types as $type) {
                foreach ($statuses as $status) {
                    $options['post_type'] = $type;
                    $options['post_status'] = $status;

                    $posts = get_posts($options);
                    $offset = 0;
                    while (count($posts) > 0) {
                        if ($offset == 10) {
                            break;
                        }
                        $offset++;
                        foreach ($posts as $post) {
                            wp_delete_post($post->ID, true);
                        }
                        $posts = get_posts($options);
                    }
                }
            }


            // Delete categories, tags, etc
            $taxonomies_array = array('category', 'post_tag', 'portfolio_type', 'nav_menu', 'product_cat');
            foreach ($taxonomies_array as $tax) {
                $cats = get_terms($tax, array('hide_empty' => false, 'fields' => 'ids'));
                foreach ($cats as $cat) {
                    wp_delete_term($cat, $tax);
                }
            }


            // Delete Slider Revolution Sliders
            if (class_exists('RevSlider')) {
                $sliderObj = new RevSlider();
                foreach ($sliderObj->getArrSliders() as $slider) {
                    $slider->initByID($slider->getID());
                    $slider->deleteSlider();
                }
            }

            // Delete Widgets
            global $wp_registered_widget_controls;

            $widget_controls = $wp_registered_widget_controls;

            $available_widgets = array();

            foreach ($widget_controls as $widget) {
                if (!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) {
                    $available_widgets[] = $widget['id_base'];
                }
            }

            update_option('sidebars_widgets', array('wp_inactive_widgets' => array()));
            foreach ($available_widgets as $widget_data) {
                update_option('widget_' . $widget_data, array());
            }

            // Delete Thememods
            $theme_slug = get_option('stylesheet');
            $mods = get_option("theme_mods_$theme_slug");
            if (false !== $mods) {
                delete_option("theme_mods_$theme_slug");
            }

            //Clear "uploads" folder
            $this->clear_uploads($this->uploads_dir['basedir']);
        }

        /**
         * Clear "uploads" folder
         * @param string $dir
         * @return bool
         * Forked from HashThemes Demo Importer Plugin
         */
        private function clear_uploads($dir) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                ( is_dir("$dir/$file") ) ? $this->clear_uploads("$dir/$file") : unlink("$dir/$file");
            }

            return ( $dir != $this->uploads_dir['basedir'] ) ? rmdir($dir) : true;
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * setMenu
         * Set the menu on theme location
         * Forked from HashThemes Demo Importer Plugin
         * */
        function setMenu($menuArray) {

            if (!$menuArray) {
                return;
            }

            $locations = get_theme_mod('nav_menu_locations');
            foreach ($menuArray as $menuId => $menuname) {
                $menu_exists = wp_get_nav_menu_object($menuname);
                if (!$menu_exists) {
                    $term_id_of_menu = wp_create_nav_menu($menuname);
                } else {
                    $term_id_of_menu = $menu_exists->term_id;
                }
                $locations[$menuId] = $term_id_of_menu;
            }
            set_theme_mod('nav_menu_locations', $locations);
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * Import demo XML content
         * Forked from HashThemes Demo Importer Plugin
         */
        function import_demo_content($slug) {

            if (!defined('WP_LOAD_IMPORTERS'))
                define('WP_LOAD_IMPORTERS', true);

            if (!class_exists('WP_Import')) {
                $class_wp_importer = $this->this_dir . "wordpress-importer/wordpress-importer.php";
                if (file_exists($class_wp_importer)) {
                    require_once $class_wp_importer;
                }
            }

            // Import demo content from XML
            if (class_exists('WP_Import')) {
                $import_filepath = $this->demo_upload_dir($slug) . '/content.xml'; // Get the xml file from directory 
                $home_slug = isset($this->configFile[$slug]['home_slug']) ? $this->configFile[$slug]['home_slug'] : '';
                $blog_slug = isset($this->configFile[$slug]['blog_slug']) ? $this->configFile[$slug]['blog_slug'] : '';

                if (file_exists($import_filepath)) {
                    $wp_import = new WP_Import();
                    $wp_import->fetch_attachments = true;
                    // Capture the output.
                    ob_start();
                    $wp_import->import($import_filepath);
                    // Clean the output.
                    ob_end_clean();
                    //complete import

                    // set homepage as front page
                    $page = get_page_by_path($home_slug);
                    if ($page) {
                        update_option('show_on_front', 'page');
                        update_option('page_on_front', $page->ID);
                    }

                    $blog = get_page_by_path($blog_slug);
                    if ($blog) {
                        update_option('show_on_front', 'page');
                        update_option('page_for_posts', $blog->ID);
                    }
                }
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * demo_upload_dir
         * Forked from HashThemes Demo Importer Plugin
         */
        function demo_upload_dir($path = '') {
            $upload_dir = $this->uploads_dir['basedir'] . '/demo-pack/' . $path;
            return $upload_dir;
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * install_plugins
         * Forked from HashThemes Demo Importer Plugin
         */
        function install_plugins($slug) {
            $demo = $this->configFile[$slug];

            $plugins = $demo['plugins'];

            foreach ($plugins as $plugin_slug => $plugin) {
                $name = isset($plugin['name']) ? sanitize_text_field($plugin['name']) : '';
                $source = isset($plugin['source']) ? sanitize_text_field($plugin['source']) : '';
                $file_path = isset($plugin['file_path']) ? sanitize_text_field($plugin['file_path']) : '';
                $location = isset($plugin['location']) ? sanitize_text_field($plugin['location']) : '';

                if ($source == 'wordpress') {
                    $this->plugin_callback($file_path, $plugin_slug);
                } else {
                    $this->plugin_installer_callback($file_path, $location);
                }
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * plugin_callback
         * Forked from HashThemes Demo Importer Plugin
         */
        public function plugin_callback($path, $slug) {
            $plugin_status = $this->plugin_status($path);

            if ($plugin_status == 'install') {
                // Include required libs for installation
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
                require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

                // Get Plugin Info
                $api = $this->call_plugin_api($slug);

                $skin = new WP_Ajax_Upgrader_Skin();
                $upgrader = new Plugin_Upgrader($skin);
                $upgrader->install($api->download_link);
                $this->activate_plugin($path);
                $this->plugin_install_count++;

            } else if ($plugin_status == 'inactive') {
                $this->activate_plugin($path);
                $this->plugin_install_count++;
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * Offline Plugin installer
         * Forked from HashThemes Demo Importer Plugin
         */
        public function plugin_installer_callback($path, $external_url) {

            $plugin_status = $this->plugin_status($path);

            if ($plugin_status == 'install') {
                // Make sure we have the dependency.
                if (!function_exists('WP_Filesystem')) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }

                /**
                 * Initialize WordPress' file system handler.
                 *
                 * @var WP_Filesystem_Base $wp_filesystem
                 */
                WP_Filesystem();
                global $wp_filesystem;

                $plugin = $this->demo_upload_dir() . 'plugin.zip';

                $file = wp_remote_retrieve_body(wp_remote_get($external_url, array(
                    'timeout' => 60,
                )));

                $wp_filesystem->mkdir($this->demo_upload_dir());

                $wp_filesystem->put_contents($plugin, $file);

                unzip_file($plugin, WP_PLUGIN_DIR);

                $plugin_file = WP_PLUGIN_DIR . '/' . esc_html($path);

                if (file_exists($plugin_file)) {
                    $this->activate_plugin($path);
                    $this->plugin_install_count++;
                }

                $wp_filesystem->delete($plugin);
            } else if ($plugin_status == 'inactive') {
                $this->activate_plugin($path);
                $this->plugin_install_count++;
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * call_plugin_api
         * Call plugin api
         * Forked from HashThemes Demo Importer Plugin
         * */
        public function call_plugin_api($slug) {
            include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

            $call_api = plugins_api('plugin_information', array(
                'slug' => $slug,
                'fields' => array(
                    'downloaded' => false,
                    'rating' => false,
                    'description' => false,
                    'short_description' => false,
                    'donate_link' => false,
                    'tags' => false,
                    'sections' => false,
                    'homepage' => false,
                    'added' => false,
                    'last_updated' => false,
                    'compatibility' => false,
                    'tested' => false,
                    'requires' => false,
                    'downloadlink' => true,
                    'icons' => false
            )));

            return $call_api;
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * activate_plugin
         * Check plugin files is exits or not
         * Forked from HashThemes Demo Importer Plugin
         * */
        public function activate_plugin($file_path) {
            if ($file_path) {
                $activate = activate_plugin($file_path, '', false, true);
            }
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * plugin_status
         * Check plugin files is exits or not
         * Forked from HashThemes Demo Importer Plugin
         * */
        public function plugin_status($file_path) {
            $status = 'install';
            $plugin_path = WP_PLUGIN_DIR . '/' . $file_path;
            if (file_exists($plugin_path)) {
                $status = is_plugin_active($file_path) ? 'active' : 'inactive';
            }
            return $status;
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * get_plugin_status
         * Plugin status compare
         */
        
        public function get_plugin_status($status) {
            switch ($status) {
                case 'install':
                    $status = esc_html__('Not Installed', 'sparkle-demo-importer');
                    break;
                case 'active':
                    $status = esc_html__('Installed and Active', 'sparkle-demo-importer');
                    break;
                case 'inactive':
                    $status = esc_html__('Installed but Not Active', 'sparkle-demo-importer');
                    break;
            }
            return $status;
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * send_ajax_response
         */
        public function send_ajax_response() {
            $json = wp_json_encode($this->ajax_response);
            echo $json;
            die();
        }

        /**
         * @package Sparkle Demo Importer
         * @since 1.0.0
         * admin_script
         * Register necessary admin js
         */
        function admin_script() {
            $localdata = array(
                'nonce' => wp_create_nonce('demo-importer-ajax'),
                'prepare_importing' => esc_html__('Preparing to import demo', 'sparkle-demo-importer'),
                'reset_database' => esc_html__('Reseting database', 'sparkle-demo-importer'),
                'no_reset_database' => esc_html__('Database was not reset', 'sparkle-demo-importer'),
                'import_error' => __('<p>There was an error in importing demo. Please reload the page and try again.</p> <a class="button" href="' . esc_url(admin_url('/admin.php?page=sparkle-theme-demo-importer')) . '">Refresh</a>', 'sparkle-demo-importer'),
                'import_success' => '<h2>' . esc_html__('All done. Have fun!', 'sparkle-demo-importer') . '</h2><p>' . esc_html__('Your website has been successfully setup.', 'sparkle-demo-importer') . '</p><a class="button" target="_blank" href="' . esc_url(home_url('/')) . '">View your Website</a><a class="button" href="' . esc_url(admin_url('/admin.php?page=sparkle-theme-demo-importer')) . '">Go Back</a>'
            );

            wp_enqueue_script('isotope-pkgd', $this->this_uri . 'assets/isotope.pkgd.js', array('jquery'), SPARKLE_DEMOI_VERSION, true);
            wp_enqueue_script('sparkle-theme-demo-ajax', $this->this_uri . 'assets/demo-importer-ajax.js', array('jquery', 'imagesloaded'), SPARKLE_DEMOI_VERSION, true);
            wp_localize_script('sparkle-theme-demo-ajax', 'sparkle_ajax_data', $localdata);
            wp_enqueue_style('sparkle-theme-demo-style', $this->this_uri . 'assets/demo-importer-style.css', array(), SPARKLE_DEMOI_VERSION);
        }

    }

}


add_action('after_setup_theme', function(){
    new Sparkle_Demo_Importer_Main;
});