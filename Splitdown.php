<?php
/**
 * Plugin Name:     Splitdown
 * Description:     Replaces the default editor
 * Author:          Andre 'Necrotex' Peiffer
 * Version:         0.1
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
		add_action( 'init', 					array( __CLASS__, 'remove_editor' ), 0, 10 );
		add_action( 'edit_form_after_editor', 	array( __CLASS__, 'load_editor' ), 0, 11 );
		add_action( 'admin_menu',				array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_menu', 				array( __CLASS__, 'load_style' ) );
		add_action( 'save_post',				array( __CLASS__, 'save' ) );
		add_action( 'edit_post',				array( __CLASS__, 'save' ) );
	}


	public function enqueue_scripts(){
			wp_enqueue_script( 'showdown', plugins_url( '/js/showdown/compressed/showdown.js', __FILE__ ) );
			wp_enqueue_script( 'showdown', plugins_url( '/js/showdown/compressed/extensions/github.js', __FILE__ ) );
			wp_enqueue_script( 'splitdown', plugins_url( '/js/splitdown.js', __FILE__ ), array( 'jquery-ui-dialog' ) );
	}


	public static function load_style(){
		wp_enqueue_style( 'splitdown-style', plugins_url( '/css/style.css', __FILE__ ) );
	}


	public static function remove_editor(){
		foreach( get_post_types() as $post_type ){
			remove_post_type_support( $post_type, 'editor' );
		}
	}


	public static function load_editor(){
		$content = file_get_contents( plugins_url( '/templates/editor.html', __FILE__ ) );

		$post = get_post( get_the_id() );
		$meta = get_post_meta( $post->ID, '_splitdown_markdown', TRUE );

		$content = strtr( $content, array( '{:markdown}' => $meta, '{:html}' => $post->post_content ) );

		add_thickbox();
		echo $content;
	}


	public static function save( $post_id ){
		$html = $_POST[ 'splitdown-markdown' ];
		$markdown = $_POST[ 'content' ];

		update_post_meta( $post_id, '_splitdown_markdown', $markdown );

		remove_action( 'save_post',				array( __CLASS__, 'save' ) );
		remove_action( 'edit_post',				array( __CLASS__, 'save' ) );

		wp_update_post( array( 'ID' => $post_id, 'post_content' => $html ) );

		add_action( 'save_post',				array( __CLASS__, 'save' ) );
		add_action( 'edit_post',				array( __CLASS__, 'save' ) );
	}

} //Class End


// Initilize the Plugin
if ( class_exists( 'Splitdown' ) ) {

	if( ! version_compare( phpversion(), '5.3', '>=' ) )
		exit( 'Sorry, PHP5.3+ is required to run this plugin.' );

	add_action( 'plugins_loaded', array( 'Splitdown', 'get_instance' ) );
}
?>