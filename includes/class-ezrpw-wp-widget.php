<?php
/**
 * Classic widget (Appearance › Widgets in classic themes): shows one saved EZRate widget.
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sidebar widget.
 */
class EZRPW_WP_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ezrpw_widget',
			__( 'EZRate price widget', 'ezrate-price-widgets' ),
			array(
				'description'                 => __( 'Live dollar, gold and coin prices from one of your EZRate widgets.', 'ezrate-price-widgets' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Front end.
	 *
	 * @param array $args     Sidebar arguments.
	 * @param array $instance Settings.
	 */
	public function widget( $args, $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup
		}
		echo EZRPW_Front::shortcode( array( 'id' => isset( $instance['widget_id'] ) ? (int) $instance['widget_id'] : 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in EZRPW_Render
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme markup
	}

	/**
	 * Settings form.
	 *
	 * @param array $instance Settings.
	 * @return string
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$cur   = isset( $instance['widget_id'] ) ? (int) $instance['widget_id'] : 0;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'ezrate-price-widgets' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>"><?php esc_html_e( 'Widget:', 'ezrate-price-widgets' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'widget_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widget_id' ) ); ?>">
				<?php foreach ( EZRPW_Widgets::all() as $w ) : ?>
					<option value="<?php echo (int) $w['id']; ?>" <?php selected( $cur, $w['id'] ); ?>>#<?php echo (int) $w['id']; ?> — <?php echo esc_html( '' !== $w['title'] ? $w['title'] : EZRPW_Widgets::type_label( $w['type'] ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ezrpw-widgets' ) ); ?>"><?php esc_html_e( 'Create or edit widgets', 'ezrate-price-widgets' ); ?></a></p>
		<?php
		return '';
	}

	/**
	 * Save.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'     => sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' ),
			'widget_id' => isset( $new_instance['widget_id'] ) ? absint( $new_instance['widget_id'] ) : 0,
		);
	}
}
