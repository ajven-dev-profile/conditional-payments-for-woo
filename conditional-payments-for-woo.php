<?php
/**
 *
"Conditional Payments for Woocommerce" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
"Conditional Payments for Woocommerce" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with "Conditional Payments for Woocommerce". If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 *
 * Plugin Name: Conditional Payments for Woocommerce
 * Description: This plugin will allow you to display payment methods based on selected shipping method
 * Version: 1.0
 * Author: Ivan Hanák <ajven@ajven.sk>
 * Author URI: https://ajven.sk
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

define("CPFW_PLUGIN_BASE_NAME", plugin_basename(__FILE__));
require_once dirname(__FILE__)."/menu-page.php";



/**
 * @copyright chgtp question "hide woocommerce cheque payment method for specific shipping"
 * @author @ajven.sk
 */
add_filter( 'woocommerce_available_payment_gateways', function (array $available_gateways): array {
    if(is_cart() || is_checkout()) {
        $options = get_option( 'dbi_example_plugin_options' );

        //Array ( [cheque] => Array ( [0] => local_pickup ) )
        $restrictions = $options['restrictions'] ?? [];

        $chosenShippingMethods = WC()->session->get( 'chosen_shipping_methods' ) ?? [];
        $chosenShippingMethod = (string)(is_array($chosenShippingMethods) ? $chosenShippingMethods[0] ?? null : null);

        $chosenShippingMethod = explode(":", $chosenShippingMethod, 3);
        $chosenShippingMethod = $chosenShippingMethod[0] ?? null;

        if($chosenShippingMethod) {
            $available_gateways = array_filter($available_gateways, function (WC_Payment_Gateway $gateway) use($restrictions, $chosenShippingMethod) {
                if(isset($restrictions[$gateway->id])) {
                    return in_array($chosenShippingMethod, $restrictions[$gateway->id]);
                }

                return true;
            });
        }
    }

    return $available_gateways;
} );