<?php
/**
 * WC_CSP_Condition_Shipping_City class
 *
 * @package  WooCommerce Conditional Shipping and Payments - City Extension
 * @since    1.3.0
 */
class WC_CSP_Condition_Shipping_City extends WC_CSP_Package_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                             = 'shipping_city';
		$this->title                          = __( 'Shipping City', 'woocommerce-conditional-shipping-and-payments-city-extension' );
		$this->priority                       = 30;
		$this->supported_global_restrictions  = array( 'shipping_methods', 'payment_gateways', 'shipping_countries' );
		$this->supported_product_restrictions = array( 'shipping_methods', 'payment_gateways', 'shipping_countries' );
	}

	/**
	 * Return condition field-specific resolution message which is combined along with others into a single restriction "resolution message".
	 *
	 * @param  array $data  Condition field data.
	 * @param  array $args  Optional arguments passed by restriction.
	 * @return string|false
	 */
	public function get_condition_resolution( $data, $args ) {

		// Empty conditions always return false (not evaluated).
		if ( empty( $data['value'] ) ) {
			return false;
		}

		return __( 'Enter a valid shipping city/suburb', 'woocommerce-conditional-shipping-and-payments-city-extension' );
	}

	/**
	 * Evaluate if the condition is in effect or not.
	 *
	 * @param  string $data  Condition field data.
	 * @param  array  $args  Optional arguments passed by restrictions.
	 * @return boolean
	 */
	public function check_condition( $data, $args ) {

		// Empty conditions always apply (not evaluated).
		if ( empty( $data['value'] ) ) {
			return true;
		}

		$is_matching_package = false;

		// Check for the city in the passed order args.
		if ( ! empty( $args['order'] ) ) {

			$order = $args['order'];

			$city = $order->get_shipping_city();

			$is_matching_package = $this->is_matching_package( $city, $data );

		// Else check the customer object.
		} else {

			$city = WC()->customer->get_shipping_city();

			if ( empty( $city ) ) {
				$is_matching_package = apply_filters( 'woocommerce_csp_shipping_city_condition_match_empty_shipping_address', $this->modifier_is( $data['modifier'], array( 'not-has' ) ), $data, $args );
			} elseif ( $this->is_matching_package( $city, $data ) ) {
				$is_matching_package = true;
			}
		}

		return $is_matching_package;
	}

	/**
	 * Condition matching package?
	 *
	 * @since  1.0.0
	 *
	 * @param  string $city City/Suburb to check.
	 * @param  array  $data Data containing value and modifier.
	 * @return boolean      Whether the address matches the condition.
	 */
	protected function is_matching_package( $city, $data ) {

		// Validate inputs to prevent errors.
		if ( empty( $city ) || empty( $data['value'] ) ) {
			return false;
		}

		// Prepare data once before looping.
		$needles = $data['value'];
		$stack   = trim( $city );
		$found   = false;

		// Early return if no needles to check.
		if ( empty( $needles ) ) {
			$found = false;
		} else {
			// Use faster method to check for substring existence.
			foreach ( $needles as $needle ) {
				if ( false !== stripos( $stack, $needle ) ) {
					$found = true;
					break;
				}
			}
		}

		// Simplified conditional logic using direct boolean comparison.
		if ( $this->modifier_is( $data['modifier'], array( 'has' ) ) ) {
			return $found;
		}

		if ( $this->modifier_is( $data['modifier'], array( 'not-has' ) ) ) {
			return ! $found;
		}

		return false; // Default return if no modifiers match.
	}

	/**
	 * Validate, process and return condition fields.
	 *
	 * @param  array $posted_condition_data All the restriction condition data to precess and save.
	 * @return array
	 */
	public function process_admin_fields( $posted_condition_data ) {

		$processed_condition_data = array();

		if ( isset( $posted_condition_data['value'] ) ) {

			$processed_condition_data['condition_id'] = $this->id;
			$processed_condition_data['value']        = array_filter( array_map( 'wc_clean', explode( '|', $posted_condition_data['value'] ) ) );
			$processed_condition_data['modifier']     = stripslashes( $posted_condition_data['modifier'] );

			return $processed_condition_data;
		}

		return false;
	}

	/**
	 * Get cart total conditions content for admin restriction metaboxes.
	 *
	 * @param  int   $index the index key of the restriction.
	 * @param  int   $condition_index the index key of the condition.
	 * @param  array $condition_data the condition data to test.
	 * @return void
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ) {

		$modifier              = 'used';
		$zero_config_modifiers = array( '' );
		$city        = '';

		if ( ! empty( $condition_data['value'] ) && is_array( $condition_data['value'] ) ) {
			$city = implode( '|', $condition_data['value'] );
		}

		if ( ! empty( $condition_data['modifier'] ) ) {
			$modifier = $condition_data['modifier'];
		}

		?>
		<input type="hidden" name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][condition_id]" value="<?php echo esc_attr( $this->id ); ?>" />
		<div class="condition_row_inner">
			<div class="condition_modifier">
				<div class="sw-enhanced-select">
					<select name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][modifier]" data-zero_config_mods="<?php echo esc_attr( json_encode( $zero_config_modifiers ) ); ?>">
						<option value="has" <?php selected( $modifier, 'has', true ); ?>><?php esc_html_e( 'contains', 'woocommerce-conditional-shipping-and-payments-city-extension' ); ?></option>
						<option value="not-has" <?php selected( $modifier, 'not-has', true ); ?>><?php esc_html_e( 'does not contain', 'woocommerce-conditional-shipping-and-payments-city-extension' ); ?></option>
					</select>
				</div>
			</div>
			<div class="condition_value" style="<?php echo in_array( $modifier, $zero_config_modifiers ) ? 'display:none;' : ''; ?>">
				<input type="text"  name="restriction[<?php echo esc_attr( $index ); ?>][conditions][<?php echo esc_attr( $condition_index ); ?>][value]" value="<?php echo esc_attr( $city ); ?>" placeholder="<?php esc_attr_e( 'Enter city/suburb name', 'woocommerce-conditional-shipping-and-payments-city-extension' ); ?>" step="any" min="0"/>
				<span class="description"><?php esc_attr_e( 'Enter the city/suburb (case-insensitive), separated by pipe (|) to search for in the Shipping City/Suburb field.', 'woocommerce-conditional-shipping-and-payments-city-extension' ); ?></span>
			</div>
			<div class="condition_value condition--disabled" style="<?php echo ! in_array( $modifier, $zero_config_modifiers ) ? 'display:none;' : ''; ?>"></div>
		</div>
		<?php
	}
}
