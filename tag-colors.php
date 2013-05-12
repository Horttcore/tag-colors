<?php
/*
Plugin Name: Tag Colors
Plugin URI: http://horttcore.de
Description: Color your tags
Version: 0.2
Author: Ralf Hortt
Author URI: http://horttcore.de
License: GPL2
*/



/**
 *
 *  Tag Colors Class
 *
 */
class Tag_Colors
{



	/**
	 * Plugin constructor
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function __construct()
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_styles-edit-tags.php', array( $this, 'admin_print_styles' ) );

		load_plugin_textdomain( 'tag-colors', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'  );
	}



	/**
	 * Tag Color input field on add tag screen
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function add_form_fields()
	{
		wp_enqueue_script( 'tag-colors' );
		?>
		<div class="form-field">
			<label for="tag-color"><?php _e('Color', 'tag-colors'); ?></label>
			<input name="tag-color" id="tag-color" type="text" value="" />
		</div>
		<?php
	}



	/**
	 * Register javascripts
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function admin_enqueue_scripts()
	{
		wp_register_script( 'tag-colors', plugins_url( 'tag-colors/javascript/tag-colors.js' ), array( 'jquery', 'wp-color-picker'), '1.0', TRUE );
		wp_register_style( 'tag-colors', plugins_url( 'tag-colors/css/tag-colors.css' ), array( 'wp-color-picker') );
	}



	/**
	 * Inject Tag Colors styles
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function admin_print_styles()
	{
		wp_enqueue_style( 'tag-colors' );
	}



	/**
	 * Save tag colors
	 *
	 * @access public
	 * @param int $term_id Term ID
	 * @param int $tt_id Taxonomy term ID
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function created_tag_color( $term_id = FALSE, $tt_id = FALSE )
	{
		global $wpdb;

		$tag_colors = get_option( 'tag-colors' );
		
		if ( $_POST['tag-color'] ) :

			$tag_colors[ $term_id ] = $_POST['tag-color'];

		else :

			unset( $tag_colors );

		endif;
		
		update_option( 'tag-colors', $tag_colors);
	}



	/**
	 * Save tag colors
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function edit_tag_color()
	{
		$tag_colors = get_option( 'tag-colors' );
		
		if ( $_POST['tag-color'] && $_POST['tag_ID'] ) :
		
			$tag_colors[ $_POST['tag_ID'] ] = $_POST['tag-color'];

		elseif ( '' == $_POST['tag-color'] ) :

			unset( $tag_colors[ $_POST['tag_ID'] ] );
		
		endif;
		
		update_option( 'tag-colors', $tag_colors);
	}



	/**
	 * Tag Color input field on edit tag screen
	 *
	 * @access public
	 * @param obj $tag Tag object
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function edit_form_fields( $tag )
	{
		wp_enqueue_script( 'tag-colors' );
		$tag_colors = get_option( 'tag-colors' );
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="tag-color"><?php _e('Color', 'tag-colors'); ?></label>
			</th>
			<td>
				<input name="tag-color" id="tag-color" type="text" value="<?php if ( $tag_colors ) echo esc_attr( $tag_colors[ $tag->term_id ] ); ?>" size="40" />
			</td>
		</tr>
		<?php
	}





	/**
	 * Add tag color column to taxonomies
	 *
	 * @access public
	 * @param array $columns Columns
	 * @return array Columns
	 * @author Ralf Hortt
	 **/
	public function manage_edit_taxonomy_custom_column( $columns )
	{
		$columns['color'] = __( 'Color', 'tag-colors' );
		return $columns;
	}



	/**
	 * Add tag color row to taxonomies
	 *
	 * @access public
	 * @param str $content
	 * @param str $column_name Column name
	 * @param int $term_id Term ID
	 * @return str Content
	 * @author Ralf Hortt
	 **/
	public function manage_taxonomy_custom_column( $content, $column_name, $term_id )
	{
		$tag_colors = get_option( 'tag-colors' );
		switch ( $column_name ) :
			case 'color':
				return ( isset( $tag_colors[$term_id] ) ) ? '<span class="tag-color-sample" style="background-color: ' . $tag_colors[$term_id] . '"></span>' : '';
			break;
		endswitch;
	}



	/**
	 * Include Tag Color Support
	 *
	 * @static
	 * @access public
	 * @param str $taxonomy Taxonomy name
	 * @return void
	 * @author Ralf Hortt
	 **/
	static public function register_support( $taxonomy )
	{
		add_action( $taxonomy . '_add_form_fields', array( 'Tag_Colors', 'add_form_fields' ) );
		add_action( $taxonomy . '_edit_form_fields', array( 'Tag_Colors', 'edit_form_fields' ) );
		add_action( 'edited_' . $taxonomy, array( 'Tag_Colors' , 'edit_tag_color' ) );
		add_action( 'created_' . $taxonomy, array( 'Tag_Colors' , 'created_tag_color' ) );
		add_filter( 'manage_edit-' . $taxonomy . '_columns', array( 'Tag_Colors', 'manage_edit_taxonomy_custom_column' ) );
		add_filter( 'manage_' . $taxonomy . '_custom_column', array( 'Tag_Colors', 'manage_taxonomy_custom_column' ), 10, 3 );
	}



}

new Tag_Colors;
