<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */

add_action("wp_enqueue_scripts", "vendor_scripts_and_styles");
function vendor_scripts_and_styles()
{
    wp_enqueue_style("bootstrap", "https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css");
    wp_enqueue_script('bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js", ['jquery']);
}

add_action("wp_enqueue_scripts", "theme_scripts_and_styles");
function theme_scripts_and_styles()
{
    if (is_page('create-product')){
        wp_enqueue_media();
    }
    wp_enqueue_script( "front-script", get_stylesheet_directory_uri() . '/assets/js/front-script.js', ['jquery']);
    wp_enqueue_style( "front-style", get_stylesheet_directory_uri() . '/assets/css/front-style.css', ['jquery']);
}

add_action('admin_enqueue_scripts', 'admin_scripts_and_styles');
function admin_scripts_and_styles()
{
    wp_enqueue_style('custom-style', get_stylesheet_directory_uri() . '/assets/css/admin-custom-style.css');
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/assets/js/admin-custom-script.js', ['jquery']);
}

add_action('woocommerce_product_options_general_product_data', 'woo_general_product_data_custom_field');
function woo_general_product_data_custom_field()
{
    global $post;

    // Image Field
    woocommerce_wp_hidden_input(
        [
            'id' => '_image_field',
            'value' => get_post_meta($post->ID, '_image_field', true)
        ]
    );

    // Date Field
    woocommerce_wp_text_input(
        [
            'id' => '_date_field',
            'label' => __('Date created', 'woocommerce'),
            'description' => __('View product create date.', 'woocommerce'),
            'type' => 'date',
            'value' => get_the_date('Y-m-d', $post->ID),
            'custom_attributes' => ['readonly' => 'readonly']
        ]
    );

    // Select
    woocommerce_wp_select(
        [
            'id' => '_select',
            'label' => __('Custom Select Field', 'woocommerce'),
            'options' => [
                'rare' => __('rare', 'woocommerce'),
                'frequent' => __('frequent', 'woocommerce'),
                'unusual' => __('unusual', 'woocommerce')
            ]
        ]
    );

}

add_action('woocommerce_process_product_meta', 'woo_save_general_custom_field');
function woo_save_general_custom_field($post_id)
{
    // Save Image Field
    $image_field = $_POST['_image_field'];
    update_post_meta($post_id, '_image_field', esc_attr($image_field));
    if (empty($_POST['_image_field'])) {
        delete_post_thumbnail( $post_id );
    }

    // Save Date Field
    $date_field = $_POST['_date_field'];
    update_post_meta($post_id, '_date_field', esc_attr($date_field));

    // Save Select
    $select = $_POST['_select'];
    update_post_meta($post_id, '_select', esc_attr($select));

}

add_action("woocommerce_product_options_pricing", "show_image_field");
function show_image_field()
{
    global $post;
    $img = wp_get_attachment_image(get_post_meta($post->ID, '_image_field', true), 'thumbnail');
    echo "
    <p>Custom image</p>
    <div class='custom-product-image'>
        <div class='img-wrapper'>$img</div>
        <button type='button' class='remove-custom-image notice-dismiss'></button>
    </div>";
}

add_action('admin_post_create_product', 'create_front_product');
function create_front_product()
{

    $post = array(
        'post_status' => "publish",
        'post_title' => esc_html($_POST['product_title']),
        'post_type' => "product",
    );

    $product_id = wp_insert_post($post, "Can't create product");

    wp_set_object_terms($product_id, 'simple', 'product_type');

    update_post_meta($product_id, '_regular_price', $_POST['product_price']);
    update_post_meta($product_id, '_price', $_POST['product_price']);
    update_post_meta( $product_id, '_image_field', $_POST['_image_field'] );
    update_post_meta($product_id, '_date_field', $_POST['_date_field']);
    update_post_meta($product_id, '_select', $_POST['_select']);

    set_post_thumbnail( $product_id, $_POST['_image_field'] );

    wp_redirect( get_permalink( $product_id) );

}










$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */
