<?php
/**
 * @package Humantide
 * @version 0.1.5
 */
/*
Plugin Name: Humantide
Plugin URI: http://wordpress.org/plugins/humantide/
Description: Humantide.
Version: 0.1.5
 * Author: Humantide
 * Author URI: https://www.humantide.com
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


class Humantide{
	
    private $optionName = "my_option_name";

    private $hideMap = "hide_map";
    private $hideUser = "hide_user";
    private $hidePhoto = "hide_photo";
    private $hideTide = "hide_tide";
    private $hideOpt = "hide_opt";
    private $hideEstad = "hide_estad";
    private $hideShare = "hide_share";
    private $hideCom = "hide_com";

    private $hidePublic = "hide_public";

	public function __construct()
    {     
        $this->options = get_option( $this->optionName );

        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('wp_loaded',array($this, 'initWP'));
        add_action('the_content',array($this, 'my_the_content_filter'));

        add_action( 'admin_footer', array($this, 'my_action_javascript' )); // Write our JS below here
        add_action( 'wp_ajax_new_post_action', array($this, 'my_action_callback') );            
    }

    /**
     * Init al data
     */
	function initWP(){
        
        $ajaxUrl = plugins_url( '/new_post.php', __FILE__ );
        $showHumanMain = "false";
        if(!is_admin()){
            if(!isset($this->options[$this->hidePublic]))
                $showHumanMain = "true";
        }
        $isAdmin = "false";
        if(is_admin())
            $isAdmin = "true";

/** /
// widget error if activate this
        printf('<script type="text/javascript">
            var showHumantideMain = '.$showHumanMain.';
            var isAdmin = '.$isAdmin.';
            var ajaxNewPost ="'.$ajaxUrl.'";
        </script>');
/**/

        // Register the script to show Humantide home in the main page and to detect when a post has been created
        wp_register_script( 'humantide', plugins_url( '/humantide.js', __FILE__ ) );
        // For either a plugin or a theme, you can then enqueue the script:
        wp_enqueue_script( 'humantide' );


//		$this->console("Started");
	}

    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
    }

    /**
     * Ajax to send the order to add a new tide
     */
    function my_action_javascript() { ?>
        <script type="text/javascript" >
            function postTide(title, url){
                jQuery(document).ready(function($) {

                    var data = {
                        'action': 'new_post_action',
                        'title': title,
                        'url': url
                    };

                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    $.post(ajaxurl, data, function(response) {
                        // Nothing to do here...
                    });
                });
            }
        </script><?php
    }

    
    /**
    * Create a new post (Called from ajax)
    */
    function my_action_callback() {

        global $wpdb; // this is how you get access to the database

        $title = $_POST['title'];
        $url = $_POST['url'];

        // Create post object
        $my_post = array(
          'post_title'    => $title,
          'post_content'  => $url,
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_category' => array(8,39)
        );

        // Insert the post into the database
        wp_insert_post( $my_post );


        die(); // this is required to terminate immediately and return a proper response
    }


	function console($data) {

	    if ( is_array( $data ) )
	        $output = "<script>console.log( '" . implode( ',', $data) . "' );</script>";
	    else
	        $output = "<script>console.log( '" . $data . "' );</script>";

	    echo $output;
   
	}

	// the_content
	function my_the_content_filter($content) {
//        $pattern = '/^(|<p>)(http|https):\/\/(|www.)humantide.(com|es)\/(calls|encuestas|mvps|pro\/vista\/channel)\/([0-9]+)(public|config|\/public|\/config|)(|\/)(|<\/p>)$/';
        $pattern = '/^(|<p>)(http|https):\/\/(|www.)humantide.(com|es)\/(calls|encuestas|mvps|pro\/vista\/channel)\/([0-9]+)(public|config|\/public|\/config|)(.*)(|<\/p>)$/';

		$isTide = preg_match($pattern, $content, $matches);

		if ($isTide)
		{
            $params = "";
            if(!isset($this->options[$this->hideMap]))
                $params.="&map=y";
            if(!isset($this->options[$this->hideUser]))
                $params.="&user=y";
            if(!isset($this->options[$this->hidePhoto]))
                $params.="&photo=y";
            if(!isset($this->options[$this->hideTide]))
                $params.="&tide=y";
            if(!isset($this->options[$this->hideOpt]))
                $params.="&opt=y";
            if(!isset($this->options[$this->hideEstad]))
                $params.="&estad=y";
            if(!isset($this->options[$this->hideShare]))
                $params.="&share=y";
            if(!isset($this->options[$this->hideCom]))
                $params.="&com=y";
            $type = $matches[5];
			$idTide = $matches[6];
            $config = "";

            if($type=="calls")
                $config = "/config";

            return '<iframe frameborder="0" id="humantide_'.$idTide.'" src="https://www.humantide.com/'.$type.'/'.$idTide.$config.'/?n=n'.$params.'" height="600px" width="100%" scrolling="yes"></iframe>';
		}
		else
			return $content;
	}


	//admin_menu
	function add_menu_page(){
		add_menu_page('humantide_page', 'Humantide', 'manage_options','humantide_main', array($this,'main_page'), plugins_url( 'humantide/logo_min.png' ));
		add_submenu_page('humantide_main', 'Submenu', 'New Tide','manage_options', 'humantide_new_tide', array($this,'new_tide'));
        add_submenu_page('humantide_main', 'Submenu', 'New Multi Survey','manage_options', 'humantide_new_multi_survey', array($this,'new_multi_survey'));
        add_submenu_page('humantide_main', 'Submenu', 'New Most Valuable Survey','manage_options', 'humantide_new_mvp', array($this,'new_mvp'));
        add_submenu_page('humantide_main', 'Submenu', 'New Channel','manage_options', 'humantide_new_channel', array($this,'new_channel'));
	}



	function main_page(){
    	$this->createIframe("http://www.humantide.com/pro");
	}

	function new_tide(){
		$this->createIframe("https://www.humantide.com/plugin/events/create/");
	}

    function new_multi_survey(){
        $this->createIframe("https://www.humantide.com/plugin/encuesta/create/");
    }

    function new_mvp(){
        $this->createIframe("https://www.humantide.com/plugin/mvp/create/");
    }

    function new_channel(){
        $this->createIframe("https://www.humantide.com/plugin/canal/create/");
    }

	function createIframe($url){
        $domain = urlencode(get_option('siteurl'));

		echo '<iframe src="'.$url.'?url='.$domain.'" width="100%" height="840px" scrolling="no" style="display:block;margin:0 auto;overflow:hidden"></iframe>';	
	}

}




class HumantideSettings
{
    private $optionName = "my_option_name";

	private $hideMap = "hide_map";
    private $hideUser = "hide_user";
    private $hidePhoto = "hide_photo";
    private $hideTide = "hide_tide";
    private $hideOpt = "hide_opt";
    private $hideEstad = "hide_estad";
    private $hideShare = "hide_share";
    private $hideCom = "hide_com";

    private $hidePublic = "hide_public";



    /**
     * Holds the values to be used in the fields callbacks
     */
    public $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
    	add_submenu_page(
    		'humantide_main',
    		'Submenu', 
    		'Settings', 
    		'manage_options', 
    		'humantide_settings', 
    		array($this,'create_admin_page')
    	);   
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( $this->optionName );


        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Humantide Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );   
                do_settings_sections( 'my-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            $this->optionName, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Tides Visualization Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

/** /
        add_settings_field(
            'id_number', // ID
            'ID Number', // Title 
            array( $this, 'id_number_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'title', 
            'Title', 
            array( $this, 'title_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

        add_settings_field(
            'test', 
            'Test', 
            array( $this, 'test_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );   
/**/

		add_settings_field(
		    $this->hideMap,
		    'Hide Map',
		    array( $this, 'tide_hide_map_callback'),
		    'my-setting-admin',
		    'setting_section_id'
		);
        add_settings_field(
            $this->hideUser,
            'Hide User',
            array( $this, 'tide_hide_user_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            $this->hidePhoto,
            'Hide Photo',
            array( $this, 'tide_hide_photo_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            $this->hideTide,
            'Hide Tide',
            array( $this, 'tide_hide_tide_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            $this->hideOpt,
            'Hide Settings',
            array( $this, 'tide_hide_opt_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            $this->hideEstad,
            'Hide Statistics',
            array( $this, 'tide_hide_estad_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            $this->hideShare,
            'Hide Share Buttons',
            array( $this, 'tide_hide_share_callback'),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            $this->hideCom,
            'Hide Commentaries',
            array( $this, 'tide_hide_com_callback'),
            'my-setting-admin',
            'setting_section_id'
        );

/** /
// Do not delete
        add_settings_section(
            'setting_section_channel', // ID
            'Public Tides', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            $this->hidePublic,
            'Hide Public Section',
            array( $this, 'tide_hide_public_callback'),
            'my-setting-admin',
            'setting_section_channel'
        );
/**/
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        if( isset( $input['test'] ) )
            $new_input['test'] = sanitize_text_field( $input['test'] );

        // hide Tide Settings
        if( isset( $input[$this->hideMap] ) )
            $new_input[$this->hideMap] = $input[$this->hideMap];

        if( isset( $input[$this->hideUser] ) )
            $new_input[$this->hideUser] = $input[$this->hideUser];

        if( isset( $input[$this->hidePhoto] ) )
            $new_input[$this->hidePhoto] = $input[$this->hidePhoto];

        if( isset( $input[$this->hideTide] ) )
            $new_input[$this->hideTide] = $input[$this->hideTide];

        if( isset( $input[$this->hideOpt] ) )
            $new_input[$this->hideOpt] = $input[$this->hideOpt];

        if( isset( $input[$this->hideEstad] ) )
            $new_input[$this->hideEstad] = $input[$this->hideEstad];

        if( isset( $input[$this->hideShare] ) )
            $new_input[$this->hideShare] = $input[$this->hideShare];

        if( isset( $input[$this->hideCom] ) )
            $new_input[$this->hideCom] = $input[$this->hideCom];

        if( isset( $input[$this->hidePublic] ) )
            $new_input[$this->hidePublic] = $input[$this->hidePublic];

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="'.$this->optionName.'[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="'.$this->optionName.'[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }

    public function test_callback()
    {
        printf(
            '<input type="text" id="test" name="'.$this->optionName.'[test]" value="%s" />',
            isset( $this->options['test'] ) ? esc_attr( $this->options['test']) : ''
        );
    }

    public function tide_hide_map_callback(){
    	$this->sandbox_checkbox_element_callback($this->hideMap, "Hide Map in the view");
    }

    public function tide_hide_user_callback(){
    	$this->sandbox_checkbox_element_callback($this->hideUser, "Hide User in the view");
    }

    public function tide_hide_photo_callback(){
        $this->sandbox_checkbox_element_callback($this->hidePhoto, "Hide Tide Photo in the view");
    }

    public function tide_hide_tide_callback(){
        $this->sandbox_checkbox_element_callback($this->hideTide, "Hide Question in the view");
    }

    public function tide_hide_opt_callback(){
        $this->sandbox_checkbox_element_callback($this->hideOpt, "Hide Vote Options in the view");
    }

    public function tide_hide_estad_callback(){
        $this->sandbox_checkbox_element_callback($this->hideEstad, "Hide Statistics in the view");
    }

    public function tide_hide_share_callback(){
        $this->sandbox_checkbox_element_callback($this->hideShare, "Hide Share options in the view");
    }

    public function tide_hide_com_callback(){
        $this->sandbox_checkbox_element_callback($this->hideCom, "Hide commentaries in the view");
    }

    public function tide_hide_public_callback(){
        $this->sandbox_checkbox_element_callback($this->hidePublic, "Hide public section in main page");
    }

	public function sandbox_checkbox_element_callback($id,$label){

		printf(
		    '<input id="%1$s" name="'.$this->optionName.'[%1$s]" type="checkbox" %2$s /><label for="%1$s">%3$s</label>',
		    $id,
		    checked( isset( $this->options[$id] ), true, false ),
            $label
		);
	}

}



// Widget
class HumantideWidget extends WP_Widget
{

    private $urlDescription = "Url of the Tide (You can get this in the tides section from humantide's web):";

    private $hideMapName = "hideMap";
    private $hideUserName = "hideUser";
    private $hidePhotoName = "hidePhoto";
    private $hideTideName = "hideTide";
    private $hideSettingsName = "hideSettings";
    private $hideStatsName = "hideStats";
    private $hideShareName = "hideShare";
    private $hideComName = "hideCom";
    private $defaultHeight = "600px";

    function HumantideWidget()
    {
        add_action( 'widgets_init', create_function('', 'return register_widget("HumantideWidget");') );

        $widget_ops = array('classname' => 'HumantideWidget', 'description' => 'Displays a Tide from humantide.com' );
        $this->WP_Widget('HumantideWidget', 'Humantide - Tide', $widget_ops);
    }
 
    function form($instance)
    {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'url' => '', 'height' => $this->defaultHeight) );
        $title = $instance['title'];

        $url = $instance['url'];

        $hideMap = $instance[$this->hideMapName];
        $hideUser = $instance[$this->hideUserName];
        $hidePhoto = $instance[$this->hidePhotoName];
        $hideTide = $instance[$this->hideTideName];
        $hideSettings = $instance[$this->hideSettingsName];
        $hideStats = $instance[$this->hideStatsName];
        $hideShare = $instance[$this->hideShareName];
        $hideCom = $instance[$this->hideComName];

        $height = $instance['height'];
    ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('url'); ?>"><?php echo $this->urlDescription ?> <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo attribute_escape($url); ?>" /></label></p>

        <p><label for="<?php echo $this->get_field_id('height'); ?>">Height: <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo attribute_escape($height); ?>" /></label></p>

    <?php
        $this->addCheckbox($this->hideMapName, "Hide Map", $instance[$this->hideMapName]);
        $this->addCheckbox($this->hideUserName, "Hide User", $instance[$this->hideUserName]);
        $this->addCheckbox($this->hidePhotoName, "Hide Photo", $instance[$this->hidePhotoName]);
        $this->addCheckbox($this->hideTideName, "Hide Tide", $instance[$this->hideTideName]);
        $this->addCheckbox($this->hideSettingsName, "Hide Settings", $instance[$this->hideSettingsName]);
        $this->addCheckbox($this->hideStatsName, "Hide Stats", $instance[$this->hideStatsName]);
        $this->addCheckbox($this->hideShareName, "Hide Share", $instance[$this->hideShareName]);
        $this->addCheckbox($this->hideComName, "Hide Commentaries", $instance[$this->hideComName]);
    }

    function addCheckbox($id, $title, $instanceId){
        ?>
            <p>
                <input class="checkbox" type="checkbox" <?php checked($instanceId, 'on') ?> id="<?php echo $this->get_field_id($id); ?>" name="<?php echo $this->get_field_name($id); ?>" /> 
                <label for="<?php echo $this->get_field_id($id); ?>"><?php echo $title; ?></label>
            </p>
        <?php
    }
 
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['url'] = $new_instance['url'];

        $instance[$this->hideMapName] = $new_instance[$this->hideMapName];
        $instance[$this->hideUserName] = $new_instance[$this->hideUserName];
        $instance[$this->hidePhotoName] = $new_instance[$this->hidePhotoName];
        $instance[$this->hideTideName] = $new_instance[$this->hideTideName];
        $instance[$this->hideSettingsName] = $new_instance[$this->hideSettingsName];
        $instance[$this->hideStatsName] = $new_instance[$this->hideStatsName];
        $instance[$this->hideShareName] = $new_instance[$this->hideShareName];
        $instance[$this->hideComName] = $new_instance[$this->hideComName];

        $instance['height'] = $new_instance['height'];
        return $instance;
    }
 
    function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $url = empty($instance['url']) ? ' ' : apply_filters('widget_title', $instance['url']);

        $hideMap = $instance[$this->hideMapName] ? true : false;
        $hideUser = $instance[$this->hideUserName] ? true : false;
        $hidePhoto = $instance[$this->hidePhotoName] ? true : false;
        $hideTide = $instance[$this->hideTideName] ? true : false;
        $hideSettings = $instance[$this->hideSettingsName] ? true : false;
        $hideStats = $instance[$this->hideStatsName] ? true : false;
        $hideShare = $instance[$this->hideShareName] ? true : false;
        $hideCom = $instance[$this->hideComName] ? true : false;

        $height = empty($instance['height']) ? $this->defaultHeight : apply_filters('widget_title', $instance['height']);

        if (!empty($title))
            echo $before_title . $title . $after_title;;

        // WIDGET CODE GOES HERE
        echo $this->isHumantide($url, $height, $hideMap, $hideUser, $hidePhoto, $hideTide, $hideSettings, $hideStats, $hideShare, $hideCom);

        echo $after_widget;
    }

    function isHumantide($content, $height, $hideMap, $hideUser, $hidePhoto, $hideTide, $hideSettings, $hideStats, $hideShare, $hideCom) {

        $pattern = '/^(|<p>)(http|https):\/\/(|www.)humantide.(com|es)\/(calls|encuestas|mvps|pro\/vista\/channel)\/([0-9]+)(public|config|\/public|\/config|)(.*)(|<\/p>)$/';

        $isTide = preg_match($pattern, $content, $matches);

        if ($isTide)
        {
            $params = "";

            if(!$hideMap)
                $params.="&map=y";
            if(!$hideUser)
                $params.="&user=y";
            if(!$hidePhoto)
                $params.="&photo=y";
            if(!$hideTide)
                $params.="&tide=y";
            if(!$hideSettings)
                $params.="&opt=y";
            if(!$hideStats)
                $params.="&estad=y";
            if(!$hideShare)
                $params.="&share=y";
            if(!$hideCom)
                $params.="&com=y";
 
            $type = $matches[5];
            $idTide = $matches[6];
            $config = "";

            if($type=="calls")
                $config = "/config";

            return '<iframe frameborder="0" id="humantide_'.$idTide.'" src="https://www.humantide.com/'.$type.'/'.$idTide.$config.'/?n=n'.$params.'" height="'.$height.'" width="100%" scrolling="yes"></iframe>';
        }
        else
            return "<h1>Url not valid</h1>";
    }
 
}





// Widget
class HumantideBasicWidget extends WP_Widget
{
    protected $urlDescription = "Url:";

    private $defaultHeight = "600px";

    function HumantideBasicWidget($id, $title, $description)
    {
        add_action( 'widgets_init', create_function('', 'return register_widget("'.$id.'");') );

        $widget_ops = array('classname' => $id, 'description' => $description );
        $this->WP_Widget($id, $title, $widget_ops);
    }
 
    function form($instance)
    {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'url' => '', 'height' => $this->defaultHeight) );
        $title = $instance['title'];

        $url = $instance['url'];

        $height = $instance['height'];
    ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('url'); ?>"><?php echo $this->urlDescription ?> <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo attribute_escape($url); ?>" /></label></p>

        <p><label for="<?php echo $this->get_field_id('height'); ?>">Height: <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo attribute_escape($height); ?>" /></label></p>

    <?php

    }

    function addCheckbox($id, $title, $instanceId){
        ?>
            <p>
                <input class="checkbox" type="checkbox" <?php checked($instanceId, 'on') ?> id="<?php echo $this->get_field_id($id); ?>" name="<?php echo $this->get_field_name($id); ?>" /> 
                <label for="<?php echo $this->get_field_id($id); ?>"><?php echo $title; ?></label>
            </p>
        <?php
    }
 
    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['url'] = $new_instance['url'];

        $instance['height'] = $new_instance['height'];
        return $instance;
    }
 
    function widget($args, $instance)
    {
        extract($args, EXTR_SKIP);

        echo $before_widget;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $url = empty($instance['url']) ? ' ' : apply_filters('widget_title', $instance['url']);

        $height = empty($instance['height']) ? $this->defaultHeight : apply_filters('widget_title', $instance['height']);

        if (!empty($title))
            echo $before_title . $title . $after_title;;

        // WIDGET CODE GOES HERE
        echo $this->isHumantide($url, $height, $hideMap, $hideUser, $hidePhoto, $hideTide, $hideSettings, $hideStats, $hideShare, $hideCom);

        echo $after_widget;
    }

    function isHumantide($content, $height, $hideMap, $hideUser, $hidePhoto, $hideTide, $hideSettings, $hideStats, $hideShare, $hideCom) {
        $pattern = '/^(|<p>)(http|https):\/\/(|www.)humantide.(com|es)\/(encuestas|mvps|pro\/vista\/channel)\/([0-9]+)(public|config|\/public|\/config|)(.*)(|<\/p>)$/';

        $isTide = preg_match($pattern, $content, $matches);

        if ($isTide)
        {
            $params = "";
 
            $type = $matches[5];
            $idTide = $matches[6];
            $config = "";

            if($type=="calls")
                $config = "/config";

            return '<iframe frameborder="0" id="humantide_'.$idTide.'" src="https://www.humantide.com/'.$type.'/'.$idTide.$config.'/?n=n'.$params.'" height="'.$height.'" width="100%" scrolling="yes"></iframe>';
        }
        else
            return "<h1>Url not valid</h1>";
    }
 
}

class HumantideSurveyWidget extends HumantideBasicWidget
{
    function HumantideSurveyWidget(){
        $this->urlDescription = "Url of the Survey (You can get this in the survey section from humantide's web):";
        $this->HumantideBasicWidget('HumantideSurveyWidget','Humantide - Survey', 'Displays a Survey from humantide.com');
    }
}

class HumantideChannelWidget extends HumantideBasicWidget
{

    function HumantideChannelWidget(){
        $this->urlDescription = "Url of the Channel (You can get this in the channel section from humantide's web):";
        $this->HumantideBasicWidget('HumantideChannelWidget','Humantide - Channel', 'Displays a Channel from humantide.com');
    }
}


$humantide = new Humantide();

if( is_admin() )
    $my_settings_page = new HumantideSettings();

$widget = new HumantideWidget();

$surveyWidget = new HumantideSurveyWidget();

$channelWidget = new HumantideChannelWidget();

?>