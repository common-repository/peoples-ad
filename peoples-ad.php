<?php
/**
 * Plugin Name:Peoples-Ad
 * Plugin URI: https://wordpress.org/plugins/peoples-ad
 * Description: Peoples-Ad Iframe will display after payment confirmation
 * Author: Peoples-Ad, Gunnar Risnes and Gyrix TechnoLabs
 * Author URI: https://www.peoples-ad.com/
 * Requires at least: 4.0
 * Tested up to: 4.9
 * Version: 1.1.6
 */
if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly
}
if(!defined('PAYMENT_TEMPLATEPATH'))
{
	define('PAYMENT_TEMPLATEPATH', plugin_dir_path(__FILE__));
	define('PAYMENT_TEMPLATEURL', plugin_dir_url(__FILE__));
}
// Include main file of plugin
include_once( dirname(__FILE__).'/includes/peoples-ad-confirmation.php' );

/**
 * Main instance of plugin.
*/

// to do when activate plugin
function pci_gyrix_register()
{
	if(is_admin() || current_user_can('manage_opions'))
	{
		pci_hooks_gyrix::pci_get_instance();
	}
	
	
}
add_action('init', 'pci_gyrix_register');
 
function pci_pluginprefix_install()
{
	if(is_admin() || current_user_can('manage_opions'))
	{	    
	    pci_gyrix_register();
	}
}
register_activation_hook(__FILE__, 'pci_pluginprefix_install');

// to do when de-activate plugin
function pci_gyrix_plugin_deactivation() 
{	
    flush_rewrite_rules(); 
}
register_deactivation_hook( __FILE__, 'pci_gyrix_plugin_deactivation' );

//add iframe when woocommerce_thankyou hook call
add_action( 'woocommerce_thankyou', 'pci_payment_confirm' , 10 , 3 );

function pci_payment_confirm($order_id){

	$order = new WC_Order( $order_id ); 
	if(isset($order) && !$order->has_status( 'failed' ) && !$order->has_status( 'cancelled' ))
	{
		$items = $order->get_items(); 
		$product_id = 0;
		foreach ($items as $item) 
		{
			$product_id = $item['product_id'];
			break;
		}
		$pci_store_id_value = get_option('_peoples_ad_iframe_id');

		if($product_id != 0 && $pci_store_id_value != '')
	    {
			$product = wc_get_product($product_id);
			$prodimage= wp_get_attachment_url($product->image_id);
			if(strpos($prodimage,'http')===false)
			{
				$prodimage=get_site_url(null,wp_get_attachment_url($product->image_id),null);
			}

	    	echo '<script type="text/javascript" src="https://www.peoples-ad.com/scripts/plugin/paplugin.js"></script>
			<input type="hidden" id="pa_StoreProductId" value="'.$product_id.'" />
            <input type="hidden" id="pa_TargetLink" value="'.$product->get_permalink().'" />
            <input type="hidden" id="pa_Title" value="'.$product->name.'" />
			<textarea style="display:none" id="pa_Description">'.wp_strip_all_tags($product->short_description).'</textarea>
            <input type="hidden" id="pa_ImagePath" value="'.$prodimage.'" />
			<div style="text-align: center;">
			<iframe id="pa_frame" src="https://www.peoples-ad.com/Publishad/ExternalView?id='.$product_id.'&providerid='.$pci_store_id_value.'&orderNumber='.$order_id.'&saleAmount='.$order->subtotal.'&email='.$order->billing_email.'"></iframe>
			</div>';
		}
	}
	
}

class pci_hooks_gyrix 
{
	private static $instance;

    static function pci_get_instance()
	{
		if (!isset(self::$instance))
	    {
	        self::$instance = new self();
	    }
	    return self::$instance;
	}

	public function __construct()
	{	
		//Create CPT			
		$obj_gyrix_manager = new pci_gyrix_manager;
	}
	
}
