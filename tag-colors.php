<?php
/**
 * Plugin Name: Tag Colors
 * Plugin URI: https://horttcore.de
 * Description: Assign a color to taxonomy terms (stored as term meta).
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Ralf Hortt
 * Author URI: https://horttcore.de
 * License: GPL-2.0-or-later
 * Text Domain: tag-colors
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin: color picker UI and term meta storage.
 */
final class Tag_Colors {

	public const META_KEY = 'tag_color';
	public const VERSION  = '1.0.0';

	/**
	 * Taxonomies that opted in via register_support().
	 *
	 * @var string[]
	 */
	private static $taxonomies = array();

	public static function init(): void {
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
	}

	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'tag-colors',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	public static function admin_enqueue_scripts( string $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}

		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
		if ( ! $taxonomy || ! in_array( $taxonomy, self::$taxonomies, true ) ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'tag-colors',
			plugins_url( 'css/tag-colors.css', __FILE__ ),
			array( 'wp-color-picker' ),
			self::VERSION
		);
		wp_enqueue_script(
			'tag-colors',
			plugins_url( 'javascript/tag-colors.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			self::VERSION,
			true
		);
	}

	public static function add_form_fields(): void {
		?>
		<div class="form-field tag-colors-field">
			<label for="tag-color"><?php esc_html_e( 'Color', 'tag-colors' ); ?></label>
			<input name="tag-color" id="tag-color" type="text" value="" autocomplete="off" />
		</div>
		<?php
	}

	public static function edit_form_fields( WP_Term $tag ): void {
		$color = tag_colors_get_color( $tag );
		?>
		<tr class="form-field tag-colors-field">
			<th scope="row">
				<label for="tag-color"><?php esc_html_e( 'Color', 'tag-colors' ); ?></label>
			</th>
			<td>
				<input name="tag-color" id="tag-color" type="text" value="<?php echo esc_attr( $color ); ?>" class="regular-text" autocomplete="off" />
			</td>
		</tr>
		<?php
	}

	public static function save_on_create( int $term_id ): void {
		if ( ! isset( $_POST['_wpnonce_add-tag'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce_add-tag'] ) ), 'add-tag' ) ) {
			return;
		}
		self::persist_color( $term_id );
	}

	public static function save_on_edit( int $term_id ): void {
		check_admin_referer( 'update-tag_' . $term_id );
		self::persist_color( $term_id );
	}

	private static function persist_color( int $term_id ): void {
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}
		if ( ! isset( $_POST['tag-color'] ) ) {
			return;
		}

		$raw   = wp_unslash( $_POST['tag-color'] );
		$color = sanitize_hex_color( is_string( $raw ) ? $raw : '' );

		if ( $color ) {
			update_term_meta( $term_id, self::META_KEY, $color );
		} else {
			delete_term_meta( $term_id, self::META_KEY );
		}
	}

	/**
	 * @param array<string, string> $columns
	 * @return array<string, string>
	 */
	public static function add_color_column( array $columns ): array {
		$columns['tag_colors'] = __( 'Color', 'tag-colors' );
		return $columns;
	}

	public static function render_color_column( string $content, string $column_name, int $term_id ): string {
		if ( 'tag_colors' !== $column_name ) {
			return $content;
		}

		$color = tag_colors_get_color( $term_id );
		if ( ! $color ) {
			return $content;
		}

		return sprintf(
			'<span class="tag-color-sample" style="background-color:%s"></span>',
			esc_attr( $color )
		);
	}

	/**
	 * Enable the color field for a taxonomy.
	 *
	 * Call from your theme or plugin on init, e.g. Tag_Colors::register_support( 'post_tag' );
	 */
	public static function register_support( string $taxonomy ): void {
		if ( in_array( $taxonomy, self::$taxonomies, true ) ) {
			return;
		}

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		self::$taxonomies[] = $taxonomy;

		$auth = static function ( bool $allowed, string $meta_key, int $object_id ): bool {
			unset( $allowed, $meta_key );
			return current_user_can( 'edit_term', $object_id );
		};

		register_term_meta(
			$taxonomy,
			self::META_KEY,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_hex_color',
				'auth_callback'     => $auth,
			)
		);

		add_action( $taxonomy . '_add_form_fields', array( __CLASS__, 'add_form_fields' ) );
		add_action( $taxonomy . '_edit_form_fields', array( __CLASS__, 'edit_form_fields' ) );
		add_action( 'created_' . $taxonomy, array( __CLASS__, 'save_on_create' ) );
		add_action( 'edited_' . $taxonomy, array( __CLASS__, 'save_on_edit' ) );
		add_filter( 'manage_edit-' . $taxonomy . '_columns', array( __CLASS__, 'add_color_column' ) );
		add_filter( 'manage_' . $taxonomy . '_custom_column', array( __CLASS__, 'render_color_column' ), 10, 3 );
	}
}

Tag_Colors::init();

/**
 * Get the stored hex color for a term.
 *
 * @param int|WP_Term $term Term ID or object.
 */
function tag_colors_get_color( $term ): string {
	$term_id = $term instanceof WP_Term ? $term->term_id : (int) $term;
	if ( $term_id <= 0 ) {
		return '';
	}

	$value = get_term_meta( $term_id, Tag_Colors::META_KEY, true );
	return is_string( $value ) && sanitize_hex_color( $value ) ? $value : '';
}
