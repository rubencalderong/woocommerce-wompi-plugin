<?php
defined( 'ABSPATH' ) || exit;

/**
 * Main class
 */
class WC_Wompi {

    /**
     * The single instance of the class.
     */
    protected static $_instance = null;

    /**
     * Main Instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     */
    public function __clone() {}

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup() {}

    /**
     * Constructor.
     */
    public function __construct() {

        // Get settings
        $settings = get_option('woocommerce_wompi_settings');

        // Includes
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-wompi-helper.php';
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-wompi-logger.php';
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-wompi-api.php';
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-wompi-webhook-handler.php';
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-gateway-wompi-custom.php';
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-gateway-wompi.php';
        require_once WC_WOMPI_PLUGIN_PATH . '/includes/class-wc-wompi-order-statuses.php';

        if ( is_admin() ) {
            require_once WC_WOMPI_PLUGIN_PATH . '/includes/admin/class-wc-wompi-admin-notices.php';
        }

        // Hooks
        add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

        if ( $settings['enabled'] == 'yes' ) {
            add_action( 'woocommerce_before_checkout_billing_form', array( 'WC_Gateway_Wompi_Custom', 'before_checkout_billing_form' ) );
            add_action( 'woocommerce_after_checkout_validation', array( 'WC_Gateway_Wompi_Custom', 'checkout_validation' ), 10, 2 );
            add_action( 'woocommerce_thankyou_order_received_text', array( 'WC_Gateway_Wompi_Custom', 'thankyou_order_received_text' ) );

            add_filter( 'woocommerce_billing_fields', array( 'WC_Gateway_Wompi_Custom', 'billing_fields' ) );
            add_filter( 'woocommerce_thankyou_order_key', array( 'WC_Gateway_Wompi_Custom', 'thankyou_order_key' ) );
        }
    }

    /**
     * Add plugin action links
     */
    public static function plugin_action_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=wompi') . '">' . __( 'Settings', 'woocommerce-gateway-wompi' ) . '</a>',
            '<a href="https://docs.wompi.co/">' . esc_html__( 'Docs', 'woocommerce-gateway-wompi' ) . '</a>',
            '<a href="https://wompi.co/">' . esc_html__( 'Support', 'woocommerce-gateway-wompi' ) . '</a>',
        );

        return array_merge( $plugin_links, $links );
    }

    /**
     * Add the gateway to WooCommerce
     */
    public static function add_gateway( $methods ) {
        $methods[] = 'WC_Gateway_Wompi';

        return $methods;
    }
}