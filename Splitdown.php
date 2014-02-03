<?php
/**
 * Plugin Name:     Splitdown
 * Description:     Replaces the default editor
 * Author:          Andre 'Necrotex' Peiffer
 * Version:         0.11
 * Licence:         GPLv3
 * Author URI:      http://necrotex.github.io
 * Text Domain:     splitdown
 * Domain Path:     /languages
 */

class Splitdown {

	protected static $instance = NULL;


	static function get_instance(){
		if( is_null( static::$instance ) )
			static::$instance = new self;

		return static::$instance;
	}


	public function __construct(){
		add_action( 'init',						array( $this, 'remove_editor' ), 0, 10 );
		add_action( 'edit_form_after_editor',	array( $this, 'load_editor' ), 0, 11 );
		add_action( 'admin_menu',				array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu',				array( $this, 'load_style' ) );
		add_action( 'save_post',				array( __CLASS__, 'save' ) );
		add_action( 'edit_post',				array( __CLASS__, 'save' ) );
		add_action( 'admin_init',				array( $this, 'add_options' ) );
	}


	public function enqueue_scripts(){

		// Only enqueue scripts on editor pages
		$extensions = get_option( 'splitdown_extensions', array() );

		wp_enqueue_script( 'showdown', plugins_url( '/js/showdown/compressed/showdown.js', __FILE__ ), array(), '0.1', true );
		wp_enqueue_script( 'markdown-parser', plugins_url( '/js/html2markdown/markdown_dom_parser.js', __FILE__ ) );
		wp_enqueue_script( 'markdown-renderer', plugins_url( '/js/html2markdown/html2markdown.js', __FILE__ ) );

    if( is_array( $extensions ) ){

    foreach( $extensions as $extension ){
    	wp_enqueue_script( "showdown-{$extension}", plugins_url( "/js/showdown/compressed/extensions/{$extension}", __FILE__ ) );
    }
    
    }

		wp_enqueue_script( 'splitdown', plugins_url( '/js/splitdown.js', __FILE__ ), array( 'jquery' ) );

    // Need for distraction free mode
    wp_enqueue_script('screenfull', '//cdnjs.cloudflare.com/ajax/libs/screenfull.js/1.0.4/screenfull.min.js', array(), '3', true);


	}


	public static function load_style(){
		wp_enqueue_style( 'splitdown-style', plugins_url( '/css/style.css', __FILE__ ) );
	}


	public static function remove_editor(){
		$allowed = get_option( 'splitdown_posttypes' );

		if( !$allowed )
			return;

		foreach( $allowed as $post_type ){
			remove_post_type_support( $post_type, 'editor' );
		}
	}


	public static function load_editor(){

		$post = get_post( get_the_id() );

		if( !in_array( $post->post_type, get_option( 'splitdown_posttypes', array() ) ) )
			return;

		$meta = get_post_meta( $post->ID, '_splitdown_markdown', TRUE );

		$content = static::_load_template( 'editor.html', array( 'markdown' => $meta, 'html' => $post->post_content ) );

		add_thickbox();
		echo $content;
	}


	public static function save( $post_id ){

		if ($_POST && isset($_POST["splitdown-markdown"])) {
			$html = $_POST[ 'splitdown-markdown' ];
		} else {
			$html = "";
		}

		if ($_POST && isset($_POST["content"])) {
			$markdown	= $_POST[ 'content' ];
		} else {
			$markdown = "";
		}
		
		update_post_meta( $post_id, '_splitdown_markdown', $markdown );

		// remove actions to avoid endless loop
		remove_action( 'save_post',				array( __CLASS__, 'save' ) );
		remove_action( 'edit_post',				array( __CLASS__, 'save' ) );

		// Note - this can cause Wordpress to hang on certain pages
		wp_update_post( array( 'ID' => $post_id, 'post_content' => $html ) );

		add_action( 'save_post',				array( __CLASS__, 'save' ) );
		add_action( 'edit_post',				array( __CLASS__, 'save' ) );
	}

	public static function add_options(){
		add_settings_section(
			'splitdown_settings',
			'Splitdown Settings',
			array( __CLASS__, 'options_section' ),
			'writing'
		);

		add_settings_field(
			'splitdown_setting_post_types',
			'Allowed Post types',
			array( __CLASS__, 'options_field_post_types' ),
			'writing',
			'splitdown_settings'
		);

		add_settings_field(
			'splitdown_setting_showdown_extension',
			'Showdown extensions',
			array( __CLASS__, 'options_field_showdown_extensions' ),
			'writing',
			'splitdown_settings'
		);

		register_setting( 'writing', 'splitdown_posttypes' );
		register_setting( 'writing', 'splitdown_extensions' );
	}


	public static function options_section(){
		echo "Splitdown Markdown Editor Options";
	}

	public static function options_field_post_types(){
		$types = "";
		$current = get_option( 'splitdown_posttypes', array() );

		foreach( get_post_types() as $post_type ){
			$vals = array(
				'value' => $post_type,
				'name' => $post_type,
				'selected' => ( in_array( $post_type, $current ) ) ? 'selected' : ''
			);

			$types .= static::_load_template( 'option-select-option.html', $vals );
			$types .= "\n";
		}

 		echo static::_load_template( 'option-posttype.html', array( 'options' => $types ) );
	}

	private static function _get_showdown_extensions(){
		$path = __DIR__ .
			DIRECTORY_SEPARATOR . 'js' .
			DIRECTORY_SEPARATOR . 'showdown' .
			DIRECTORY_SEPARATOR . 'compressed' .
			DIRECTORY_SEPARATOR . 'extensions' .
			DIRECTORY_SEPARATOR;

		if( ( $data = scandir( $path ) ) === FALSE )
            return apply_filters( 'splitdown_filter_showdown_extions', array() );

		// Remove . and ..
		unset( $data[0] );
		unset( $data[1] );

		// fix array indices
		$data = array_merge( array(), $data );

		return apply_filters( 'splitdown_filter_showdown_extions', $data );
	}

	public static function options_field_showdown_extensions(){
		$current = get_option( 'splitdown_extensions', array() );
    $extensions = static::_get_showdown_extensions();

    if( empty( $current ) )
        $current = array();

    $out = "";

		foreach( $extensions as $extension ){
			$vals = array(
				'value' => $extension,
				'name' => $extension,
				'selected' => ( in_array( $extension, $current ) ) ? 'selected' : ''
			);

			$out .= static::_load_template( 'option-select-option.html', $vals );
			$out .= "\n";
		}

		echo static::_load_template( 'option-showdown-extension.html', array( 'options' => $out ) );
	}


	private static function _load_template( $template, array $values = array() ) {

		$path = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template;

		if( file_exists( $path ) ){
			$file = file_get_contents( $path );

			//interpolate the template if values where provided
			if( count( $values ) > 0 ) {
				$out = array();

				foreach( @$values as $key => $val )
					$out[ '{:' . $key . '}' ] = $val;

				$file = strtr( $file, $out );
			}

			return $file;
		}
		else
			throw new \Exception( "Template $template not found!" );
	}

} //Class End


// Initilize the Plugin
if ( class_exists( 'Splitdown' ) ) {

	if( ! version_compare( phpversion(), '5.3', '>=' ) )
		exit( 'Sorry, PHP5.3+ is required to run this plugin.' );

	add_action( 'plugins_loaded', array( 'Splitdown', 'get_instance' ) );
}
?>