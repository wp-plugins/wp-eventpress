<?php /**
 * GPL v2.0 or later License
 * ===========
 *
 * Copyright (c) 2015 DuoGeek <contact@duogeek.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Framework
 * @package    DuoGeek
 * @subpackage Master
 * @author     DuoGeek <contact@duogeek.com>
 * @copyright  2015 DuoGeek.
 * @license    https://www.gnu.org/licenses/gpl-2.0.html  GPV v2.0 or later License
 * @version    1.0
 * @link       https://duogeek.com
 */


/*
 * Defines for the framework and plugins
 */
if( ! defined( 'DG_HACK_MSG' ) ) define( 'DG_HACK_MSG',  'Sorry hackers! This is not your place!' );
if( ! defined( 'DUO_PLUGIN_DIR' ) ) define( 'DUO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if( ! defined( 'DUO_MENU_POSITION' ) ) define( 'DUO_MENU_POSITION', '38' );
if( ! defined( 'DUO_PANEL_SLUG' ) ) define( 'DUO_PANEL_SLUG', 'duogeek-panel' );
if( ! defined( 'DUO_HELP_SLUG' ) ) define( 'DUO_HELP_SLUG', 'duogeek-panel-help' );
if( ! defined( 'DUO_LICENSES_SLUG' ) ) define( 'DUO_LICENSES_SLUG', 'duogeek-pro-licenses' );
if( ! defined( 'DUO_VERSION' ) ) define( 'DUO_VERSION', '1.1' );
if( ! defined( 'DUO_SETTINGS_PAGE' ) ) define( 'DUO_SETTINGS_PAGE', 'admin.php?page=duogeek-panel' );
if( ! defined( 'DUO_PLUGIN_URI' ) ) define( 'DUO_PLUGIN_URI', plugin_dir_url( __FILE__ ) );

/*
 * Restrict direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( DG_HACK_MSG );


/**
 * Including required files
 */
require_once 'lib/dg-helper.php';
require_once 'lib/dg-custom-post-type.php';
require_once 'lib/dg-options.php';


if( ! class_exists( 'DuoGeekPlugins' ) ){

	/**
	 * Class as a framework
	 *
	 * @since 1.0
	 */
	class DuoGeekPlugins{

		/**
		 * DuoGeek Menu Position
		 *
		 * @since 1.0
		 * @access private
		 * @var INT
		 */
		private $menuPos;

		/**
		 * Array of Donation links of free plugins
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
        private $donate = array();

        /**
		 * Array of Ratings links of free plugins
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
        private $rating = array();

        /**
		 * Subscription form at the sidebar
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
        private $subscribe;

        /**
		 * Facebook Like iframe code
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
        private $fb;

        /**
		 * Twitter profile URL
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
        private $twt;

        /**
		 * Enqueue of styles and scripts in admin end
		 *
		 * @since 1.0
		 * @access protected
		 * @var ARRAY
		 */
        protected $admin_enq = array();

        /**
		 * Enqueue of styles and scripts in front end
		 *
		 * @since 1.0
		 * @access protected
		 * @var ARRAY
		 */
        protected $front_enq = array();

        /**
		 * Array of help tab content - shortcodes, hooks etc
		 *
		 * @since 1.0
		 * @access public
		 * @var ARRAY
		 */
        public $help = array();

        /**
		 * Option variable for the framework
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
        private $DuoOptions;

        /**
		 * Array of admin pages
		 *
		 * @since 1.0
		 * @access protected
		 * @var ARRAY
		 */
        protected $admin_pages = array();



        /**
         * Notice durations
         *
         * @since 1.0
         * @access protected
         * @var ARRAY
         */
        protected $notice_duration = array();



        /**
		 * Class constructor
		 *
		 * @since 1.0
		 * @access public
		 */
        public function __construct() {

            // Notice Duration
            $this->notice_duration = array( 7, 15, 22, 30, 60, 90 );

        	// Facebook Like Page
            $this->fb = '<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fpages%2FDuo-Geek%2F329994980535258&amp;width&amp;height=258&amp;colorscheme=dark&amp;show_faces=true&amp;header=false&amp;stream=false&amp;show_border=false&amp;appId=723137171103956" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:258px;" allowTransparency="true"></iframe>';

            // Twitter profile page URL
        	$this->twt = 'https://twitter.com/duogeekdev';

            // Subscription form
            $this->subscribe = '<center>
                <div id="mc_embed_signup"><form id="mc-embedded-subscribe-form" class="validate" action="//duogeek.us9.list-manage.com/subscribe/post?u=8c05a468e4f384bd52b5ec8c9&amp;id=a52e938757" method="post" name="mc-embedded-subscribe-form" novalidate="" target="_blank">
                <div id="mc_embed_signup_scroll">

                <input id="mce-EMAIL" class="email" style="width: 100%; text-align: center;" name="EMAIL" required="" type="email" value="" placeholder="Enter Your Email Address" /><br><br>
                <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                <div style="position: absolute; left: -5000px;"><input tabindex="-1" name="b_8c05a468e4f384bd52b5ec8c9_a52e938757" type="text" value="" /></div>
                <div><input id="mc-embedded-subscribe" class="button avia-button  avia-icon_select-yes-left-icon avia-color-theme-color-subtle avia-size-large button button-primary" name="subscribe" type="submit" value="Subscribe" /></div>
                </div>
                </form></div>
                </center>';

            // Donation links for free plugins
            $this->donate = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="XBW47E6H8NM6Y"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form>';

            $this->menuPos = DUO_MENU_POSITION;

            add_action( 'init', array( $this, 'DuoPlugin_init' ) );
            add_action( 'admin_menu', array( $this, 'register_duogeek_menu_page' ) );
            add_action( 'admin_menu', array( $this, 'register_duogeek_submenu_page' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_styles_scripts' ) );
            add_action( 'wp_footer', array( $this, 'dg_equal_column' ) );
            add_action( 'dg_settings_sidebar', array( $this, 'dg_settings_sidebar_cb' ), 10, 3 );

            add_action( 'admin_notices', array( &$this, 'dg_admin_notice' ) );
            add_action( 'admin_action_set_dg_cookie', array( $this, 'set_dg_cookie_cb' ) );

            add_shortcode( 'dg_row', array( $this, 'duogeek_row_grid' ) );
            add_shortcode( 'dg_grid', array( $this, 'duogeek_column_grid' ) );              

        }



        /**
         * Generate DGStrap Row
         *
         * @since 1.0
         * @access public
         */
        public function duogeek_row_grid( $atts, $content = '' ){
            return '<div class="dg-row">'.do_shortcode( $content ).'</div>';
        }


        /**
         * Generate DGStrap Columns
         *
         * @since 1.0
         * @access public
         */
        public function duogeek_column_grid( $atts, $content = '' ) {
            extract( shortcode_atts( array(
                'col' => 12
            ), $atts, 'dg_grid' ) );
          
            return '<div class="dg-col-md-'. $atts['col'] .'">'.do_shortcode( $content ).'</div>';
        }        


        /**
		 * Intialization
		 *
		 * @since 1.0
		 * @access public
		 */
        public function DuoPlugin_init() {
            $this->DuoOptions = get_option( 'DuoOptions' );
            $this->admin_pages = apply_filters( 'duogeek_panel_pages', array() );
            $this->admin_pages = array_merge( $this->admin_pages, array( DUO_PANEL_SLUG,DUO_HELP_SLUG  ) );
        }

        /**
         * Set DG cookies for notices
         *
         * @since 1.0
         * @access public
         */
        public function set_dg_cookie_cb() {

            $time = get_user_meta( get_current_user_id(), 'dg_c_time', true );
            if( ! isset( $time ) ) $time = 0;
            else $time++;

            if( $time > 5 ) $time = 5;

            if( isset( $_GET['dismiss_dg_notice'] ) ) {
                $time = $_GET['dismiss_dg_notice'];
                if( $time > 5 ) $time = 5;
                setcookie( 'dismiss_dg_notice', 'yes', time() + ( 86400 * $this->notice_duration[$time] ), "/" );
                update_user_meta( get_current_user_id(), 'dg_c_time', $time );
            }

            wp_redirect( urldecode( $_GET['redirect_to'] ) );
            die();

        }


        /**
         * DG Admin Notice
         *
         * @since 1.0
         * @access public
         */
        public function dg_admin_notice()  {

            $time = get_user_meta( get_current_user_id(), 'dg_c_time', true );
            if( ! isset( $time ) ) $time = 0;
            else $time++;

            if( $time > 5 ) $time = 5;

            if( ! isset( $_COOKIE['dismiss_dg_notice'] ) ) {
                $message = 'Got a WordPress question? Feel free to ask us. We have a dedicated support team to answer all of your questions. We also have an exclusive support zone where all members will get upto 100% discount to all of our plugins. So, what are you waiting for? <a style="color: #ccc; text-decoration: underline" href="http://utm.io/181784" target="_blank">Ask us now!</a>';
            ?>

            <div class="dg-notice updated">
                <p><?php echo $message; ?></p>
                <div class="dg-dismiss">
                    <a href="<?php echo admin_url( 'admin.php?action=set_dg_cookie&dismiss_dg_notice=' . $time . '&redirect_to=' . urlencode( curPageURL( true ) ) ); ?>">Dismiss</a>
                </div>
            </div>
            <?php
            }

        }


        /**
		 * Enqueue styles and scripts for admin end
		 *
		 * @since 1.0
		 * @access public
		 */
        public function admin_styles_scripts() {

            $styles = array(
                array(
                    'name' => 'dgstrap_css',
                    'src' => DUO_PLUGIN_URI . 'assets/css/dgstrap.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => true
                ),
                array(
                    'name' => 'dg-admin-css',
                    'src' => DUO_PLUGIN_URI . 'assets/css/dg-admin.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => true
                ),
                array(
                    'name' => 'wp-color-picker',
                    'condition' => true
                )
            );

            $scripts = array(
                array(
                    'name' => 'duogeek-js',
                    'src' => DUO_PLUGIN_URI . 'assets/js/dg-admin.js',
                    'dep' => array( 'jquery' ),
                    'version' => DUO_VERSION,
                    'footer' => true,
                    'condition' => true
                ),
                array(
                    'name' => 'wp-color-picker',
                    'condition' => true
                )
            );

            $this->admin_enq = apply_filters( 'admin_scripts_styles', array() );

            if( count( $this->admin_enq ) > 0 ){
                $this->admin_enq['scripts'] = array_merge( $scripts, $this->admin_enq['scripts'] );
                $this->admin_enq['styles'] = array_merge( $styles, $this->admin_enq['styles'] );
            }else{
                $this->admin_enq['scripts'] = $scripts;
                $this->admin_enq['styles'] = $styles;
            }


            foreach( $this->admin_enq['scripts'] as $script ){

                if( $script['name'] == 'media' ){
                    wp_enqueue_media();
                }

                if( $script['condition'] ){
                    if( isset( $script['src'] ) ) {
                        wp_register_script( $script['name'], $script['src'], $script['dep'], $script['version'], $script['footer'] );
                    }
                    wp_enqueue_script( $script['name'] );


                    if( isset( $script['localize'] ) ){
                        wp_localize_script( $script['name'], $script['localize_data']['object'], $script['localize_data']['passed_data'] );
                    }
                }

            }

            foreach( $this->admin_enq['styles'] as $style ){

                if( $style['condition'] ){
                    if( isset( $style['src'] ) ) {
                        wp_register_style( $style['name'], $style['src'], $style['dep'], $style['version'], $style['media'] );
                    }
                    wp_enqueue_style( $style['name'] );
                }

            }

        }



        /**
		 * Enqueue styles and scripts for front end
		 *
		 * @since 1.0
		 * @access public
		 */
        public function front_styles_scripts() {

            $styles = array(
                array(
                    'name' => 'sn-fontAwesome-css',
                    'src' => '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => $this->DuoOptions['fontAwesome'] != 1
                ),
                array(
                    'name' => 'dgstrap_css',
                    'src' => DUO_PLUGIN_URI . 'assets/css/dgstrap.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => true
                ),
                array(
                    'name' => 'dg-front',
                    'src' => DUO_PLUGIN_URI . 'assets/css/dg-front.css',
                    'dep' => '',
                    'version' => DUO_VERSION,
                    'media' => 'all',
                    'condition' => true
                ),
            );

            $scripts = array(
                array(
                    'name' => 'duogeek-js',
                    'src' => DUO_PLUGIN_URI . 'assets/js/dg-front.js',
                    'dep' => array( 'jquery' ),
                    'version' => DUO_VERSION,
                    'footer' => true,
                    'condition' => true
                )
            );

            $this->front_enq = apply_filters( 'front_scripts_styles', array() );

            if( count( $this->front_enq ) > 0 ){
                $this->front_enq['scripts'] = array_merge( $scripts, $this->front_enq['scripts'] );
                $this->front_enq['styles'] = array_merge( $styles, $this->front_enq['styles'] );
            }
            else{
                $this->front_enq['scripts'] = $scripts;
                $this->front_enq['styles'] = $styles;
            }


            foreach( $this->front_enq['scripts'] as $script ){

                if( $script['name'] == 'media' ){
                    wp_enqueue_media();
                }

                if( $script['condition'] ){
                    if( isset( $script['src'] ) ) {
                        wp_register_script( $script['name'], $script['src'], $script['dep'], $script['version'], $script['footer'] );
                    }
                    wp_enqueue_script( $script['name'] );


                    if( isset( $script['localize'] ) ){
                        wp_localize_script( $script['name'], $script['localize_data']['object'], $script['localize_data']['passed_data'] );
                    }
                }

            }

            foreach( $this->front_enq['styles'] as $style ){

                if( $style['condition'] ){
                    if( isset( $style['src'] ) ) {
                        wp_register_style( $style['name'], $style['src'], $style['dep'], $style['version'], $style['media'] );
                    }
                    wp_enqueue_style( $style['name'] );
                }

            }

        }



        /**
		 * Registering DuoGeek Menu Page
		 *
		 * @since 1.0
		 * @access public
		 */
        public function register_duogeek_menu_page()
        {
            if( empty( $GLOBALS['admin_page_hooks']['duogeek-panel'] ) ) {
                add_menu_page( __('DuoGeek', 'dp'), __('DuoGeek', 'dp'), 'manage_options', DUO_PANEL_SLUG, array($this, 'duogeek_panel_cb'), DUO_PLUGIN_URI . '/assets/images/dg-20-20.png', $this->menuPos );
            }
        }



        /**
		 * DuoGeek Panel Page Settings
		 *
		 * @since 1.0
		 * @access public
		 */
        public function duogeek_panel_cb() {

            $duo = $this->DuoOptions;

            if( isset( $_POST['dp_save'] ) ){

                if ( ! check_admin_referer( 'dp_nonce_action', 'dp_nonce_field' )){
                    return;
                }

                if( isset( $_POST['duo'] ) ){
                    foreach( $_POST['duo'] as $key => $val ){
                        $duo_post[$key] = $_POST['duo'][$key];
                    }
                }

                $duo_post['fontAwesome'] = isset( $duo_post['fontAwesome'] ) ? $duo_post['fontAwesome'] : 0;
                $duo_post['animate'] = isset( $duo_post['animate'] ) ? $duo_post['animate'] : 0;
                $duo_post['cookie'] = isset( $duo_post['cookie'] ) ? $duo_post['cookie'] : 24;


                update_option( 'DuoOptions', $duo_post );

                wp_redirect( urldecode( $_REQUEST['redirect_url'] ) . '&msg=Settings+saved+successfully.' );

            }

            $promo_content = wp_remote_get( 'http://duogeek.com/duo-promo.html' );

            ?>
            <div class="wrap duo_prod_panel">

                <h2><?php _e( 'DuoGeek Settings', 'dp' ) ?></h2>

                <?php if( isset( $_REQUEST['msg'] ) ) { ?>
                    <div id="message" class="<?php echo isset( $_REQUEST['duoaction'] ) ? $_REQUEST['duoaction'] : 'updated' ?> below-h2"><p><?php echo str_replace( '+', ' ', $_REQUEST['msg'] ) ?></p></div>
                <?php } ?>
                <div id="poststuff">
                    <form action="<?php echo admin_url( 'admin.php?page=' . DUO_PANEL_SLUG . '&noheader=true&redirect_url=' . urlencode( admin_url(  'admin.php?page=' . DUO_PANEL_SLUG ) ) ) ?>" method="post">
                        <?php wp_nonce_field('dp_nonce_action','dp_nonce_field'); ?>
                        <div class="postbox">
                            <h3 class="hndle"><?php _e( 'General Settings', 'dp' ) ?></h3>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th><?php _e( 'Disable FontAwesome', 'dp' ) ?></th>
                                        <td><input <?php echo isset( $duo['fontAwesome'] ) && $duo['fontAwesome'] == 1 ? 'checked="checked"' : '' ?> type="checkbox" name="duo[fontAwesome]" value="1" /> <span class="description"><?php _e( 'Check if your theme already provides it', 'dp' ) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e( 'Disable Animate', 'dp' ) ?></th>
                                        <td><input <?php echo isset( $duo['animate'] ) && $duo['animate'] == 1 ? 'checked="checked"' : '' ?> type="checkbox" name="duo[animate]" value="1" /> <span class="description"><?php _e( 'Check if your theme already provides it', 'dp' ) ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <p><input type="submit" name="dp_save" class="button button-primary" value="<?php _e( 'Save Settings', 'dp' ) ?>" /></p>
                    </form>
                </div>

                <?php echo $promo_content['body']; ?>

            </div>
        <?php
        }



        /**
		 * Registering DuoGeek Sub Menu Page
		 *
		 * @since 1.0
		 * @access public
		 */
        public function register_duogeek_submenu_page() {

            $submenus = apply_filters( 'duogeek_submenu_pages', array() );

            if( count( $submenus ) > 0 ) {
                foreach( $submenus as $submenu ){
                    if( isset( $submenu['object'] ) )
                        add_submenu_page( DUO_PANEL_SLUG, $submenu['title'], $submenu['menu_title'], $submenu['capability'], $submenu['slug'], array( $submenu['object'], $submenu['function'] ) );
                    else
                        add_submenu_page( DUO_PANEL_SLUG, $submenu['title'], $submenu['menu_title'], $submenu['capability'], $submenu['slug'], $submenu['function'] );
                }
            }

            add_submenu_page( DUO_PANEL_SLUG, __( 'Help', 'dp' ), __( 'Help', 'dp' ), 'manage_options', DUO_HELP_SLUG, array( $this, 'duogeek_panel_help_cb' ) );

            add_submenu_page( DUO_PANEL_SLUG, __( 'Licenses', 'dp' ), __( 'Licenses', 'dp' ), 'manage_options', DUO_LICENSES_SLUG, array( $this, 'duogeek_panel_licenses_cb' ) );
        }



        /**
		 * Generate DuoGeek Help page
		 *
		 * @since 1.0
		 * @access public
		 */
        public function duogeek_panel_help_cb() {

            $this->help = array(
                'shortcodes'    => apply_filters( 'duo_panel_help_shortcodes', array( ) ),
                'filters'       => apply_filters( 'duo_panel_help_filters', array( ) ),
                'actions'       => apply_filters( 'duo_panel_help_actions', array( ) ),
                'tips'          => apply_filters( 'duo_panel_help_tips', array( ) ),
            );

            $this->help = apply_filters( 'duo_panel_help', array( ) );

            ?>
            <div class="wrap duo-kb">
                <h2><?php _e( 'Help', 'dp' ) ?></h2>
                <?php foreach( $this->help as $key => $helps ) { ?>
                    <div id="poststuff">
                        <div class="postbox">
                            <h3 class="hndle"><?php echo $helps['name'] ?> <span><?php _e( 'Click to expand/collapse', 'dp' ) ?></span></h3>
                            <div class="inside">
                                <div class="duo_help">
                                    <ul>
                                        <?php foreach( $helps as $key => $help ){ if( $key == 'name' ) continue; ?>
                                            <li>
                                                <h5><?php echo ucfirst( $key ) ?></h5>
                                                <div class="item_details">
                                                    <ul>
                                                        <?php foreach( $help as $details ){ ?>
                                                            <li>

                                                                <?php if( isset( $details['source'] ) ) { ?>
                                                                    <p>
                                                                        <b>
                                                                            <?php
                                                                            _e( 'Source: ', 'dp' );
                                                                            echo $details['source'];
                                                                            ?>
                                                                        </b>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['code'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Code: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo '<span class="code">' . $details['code'] . '</span>';
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['example'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Example: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo $details['example'];
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['default'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Default: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo $details['default'];
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                                <?php if( isset( $details['desc'] ) ) { ?>
                                                                    <p>
                                                                        <?php
                                                                        echo '<b>';
                                                                        _e( 'Description: ', 'dp' );
                                                                        echo '</b>';
                                                                        echo $details['desc'];
                                                                        ?>
                                                                    </p>
                                                                <?php } ?>

                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php
        }



        /**
		 * Generate DuoGeek License page
		 *
		 * @since 1.0
		 * @access public
		 */
        public function duogeek_panel_licenses_cb() {
            ?>

            <div class="wrap">

            <?php

            $ltabs = apply_filters( 'dg_pro_licenses', array() );

            if( count( $ltabs ) < 1 ){
                echo '<p>You don\'t have any pro version yet!</p>';
            }else{
                $i = 0;
                echo '<h2 class="nav-tab-wrapper">';
                foreach( $ltabs as $ltab ){

                    if( ! isset( $_REQUEST['tab'] ) && $i == 0){
                        $active = 'nav-tab-active';
                    } elseif ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == strtolower( str_replace( ' ', '_', $ltab ) ) ) {
                        $active = 'nav-tab-active';
                    } else {
                        $active = '';
                    }

                    echo '<a class="nav-tab '. $active .'" href="' . admin_url( 'admin.php?page=' . DUO_LICENSES_SLUG . '&tab=' . strtolower( str_replace( ' ', '_', $ltab ) ) ) . '">' . $ltab . '</a>';
                    $i++;
                }
                echo '</h2>';

                echo '<div class="lisence_wrap">';

                $tab = strtolower( str_replace( ' ', '_', isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : $ltabs[0] ) );

                if( ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] == $tab ){
                    do_action( 'dg_pro_license_form_' . $tab );
                }

                echo '</div>';

            }
            ?>
            </div>
            <?php

        }



        /**
		 * Generate DuoGeek Settings page Sidebar
		 *
		 * @since 1.0
		 * @access public
		 */
        public function dg_settings_sidebar_cb( $status, $plugin, $rating ){

            ?>
            <div class="dg-settings-sidebar-mods">
                <div class="postbox">
                    <h3 class="hndle"><span><?php _e( 'Support / Report a bug', 'eventpress' ) ?></span></h3>
                    <div class="inside centerAlign">
                        <p>Please feel free to let us know if you got any bug to report. For any type of support query, be our free member to get access on our support forum. Free members get unlimited support for all of our products.</p>
                        <p><a href="https://duogeek.com/register/" target="_blank" class="button button-primary">Get Support</a></p>
                    </div>
                </div>

                <?php if( $status == 'free' ) { ?>
	                <div class="postbox">
	                    <h3 class="hndle"><span><?php _e( 'Buy us a coffee', 'eventpress' ) ?></span></h3>
	                    <div class="inside centerAlign">
	                        <p>If you like the plugin, please buy us a coffee to inspire us to develop further.</p>
	                        <p><?php echo $this->donate ?></p>
	                    </div>
	                </div>


	                <div class="postbox">
	                    <h3 class="hndle"><span><?php _e( 'Rate us', 'eventpress' ) ?></span></h3>
	                    <div class="inside centerAlign">
	                        <p>Please give us a 5 star review, if you like our products and support.</p>
	                        <p class="star-icons">
	                            <a href="<?php echo $rating ?>" target="_blank">
	                                <span class="dashicons dashicons-star-filled"></span>
	                                <span class="dashicons dashicons-star-filled"></span>
	                                <span class="dashicons dashicons-star-filled"></span>
	                                <span class="dashicons dashicons-star-filled"></span>
	                                <span class="dashicons dashicons-star-filled"></span>
	                            </a>
	                        </p>
	                    </div>
	                </div>


                <?php } ?>

                <div class="postbox">
                    <h3 class="hndle"><span><?php _e( 'Subscribe to our NewsLetter', 'eventpress' ) ?></span></h3>
                    <div class="inside centerAlign">
                        <p>Please join our newsletter program to get updates, offers, promotion and blog post. We don't send any spam emails and your email address is totally secured.</p>
                        <p><?php echo $this->subscribe; ?></p>
                    </div>
                </div>

                <div class="postbox">
                    <h3 class="hndle"><span><?php _e( 'Join us on facebook', 'eventpress' ) ?></span></h3>
                    <div class="inside centerAlign">
                        <?php echo $this->fb ?>
                    </div>
                </div>

                <div class="postbox">
                    <h3 class="hndle"><span><?php _e( 'Follow us on twitter', 'eventpress' ) ?></span></h3>
                    <div class="inside centerAlign">
                        <a href="<?php echo $this->twt ?>" target="_blank" class="button button-secondary">Follow @duogeekdev <span class="dashicons dashicons-twitter" style="position: relative; top: 3px"></span></a>
                    </div>
                </div>

            </div>
            <?php

        }



        /**
		 * JS Code for equal column, needs to be moved into separate js file
		 *
		 * @since 1.0
		 * @access public
		 */
        public function dg_equal_column() {

            ?>
            <div class="dg-popup-wrap"><div class="dg-popup"><div class="dg-pop-close"></div><div class="dg-pop-content"><div class="dg-pop-content-details"></div></div></div></div>
            <script type="text/javascript">
                jQuery(function($) {
                    function equalHeight(group) {
                        tallest = 0;
                        group.each(function() {
                            thisHeight = $(this).height();
                            if(thisHeight > tallest) {
                                tallest = thisHeight;
                            }
                        });
                        group.height(tallest);
                    }

                    equalHeight($(".dg-grid-shortcode .dg_grid-shortcode-col"));

                    $(window).resize(function() {
                        equalHeight($(".dg-grid-shortcode .dg_grid-shortcode-col"));
                    });
                });
            </script>
            <?php
        }

	}

	new DuoGeekPlugins();

}
