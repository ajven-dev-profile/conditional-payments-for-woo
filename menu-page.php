<?php

add_action( 'admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Conditional payments',
        'Conditional payments',
        'manage_woocommerce',
        'conditional-payments-for-woo',
        'cpfw_render_plugin_settings_page',
        99999
    );
});

//add "settings" link to the plugin list
add_filter( 'plugin_action_links_' . CPFW_PLUGIN_BASE_NAME, function ($links) {
    $url = menu_page_url('conditional-payments-for-woo', false);
    $settings_link = '<a href="' . $url . '">' . __('Settings') . '</a>';
    $links[] = $settings_link;
    return $links;
} );

function cpfw_render_plugin_settings_page() {
    ?>
    <h2>Conditional Payments for Woocommerce</h2>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'dbi_example_plugin_options' );
        do_settings_sections( 'dbi_example_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}


function dbi_register_settings() {
    register_setting( 'dbi_example_plugin_options', 'dbi_example_plugin_options', 'cpfw_options_update_validate' );
    add_settings_section( 'api_settings', 'Settings', 'dbi_plugin_section_text', 'dbi_example_plugin' );

    add_settings_field( 'dbi_plugin_setting_start_date', 'Restrictions', 'dbi_plugin_setting_start_date', 'dbi_example_plugin', 'api_settings' );
}
add_action( 'admin_init', 'dbi_register_settings' );

function cpfw_options_update_validate( $input ): array {
    if(!is_array($input)) {
        $input = [];
    }

    return $input;
}


function dbi_plugin_section_text() {
    echo '<p>Here you can configure payment methods to be displayed based on selected shipping method.</p>';
}

function dbi_plugin_setting_start_date() {

    $options = get_option( 'dbi_example_plugin_options' );
    $restrictions = $options['restrictions'] ?? [];

    /**
     * @var $shippingOptions array<WC_Shipping_Method>
     */
    $shippingOptions = WC()->shipping->get_shipping_methods();

    /**
     * @var $paymentMethods array<WC_Payment_Gateway>
     */
    $paymentMethods = WC()->payment_gateways()->payment_gateways();

    ?>
    <div>
        <?php foreach($paymentMethods as $paymentMethod):?>
            <div style="margin-bottom: 40px">
                <h3 style="margin: 0; white-space: nowrap">
                    <?php echo $paymentMethod->get_title();?>
                    (<?php echo $paymentMethod->get_method_title();?>)
                </h3>
                <div>
                    <h4 style="margin-bottom: .3em">Restrict to shipping methods</h4>
                    <?php foreach($shippingOptions as $shippingOption):
                        $isChecked = isset($restrictions[$paymentMethod->id]) && is_array($restrictions[$paymentMethod->id]) && in_array($shippingOption->id, $restrictions[$paymentMethod->id]);


                        ?>
                        <div style="margin-bottom: .3em">
                            <label>
                                <input <?php echo $isChecked ? " checked " : "";?> name="dbi_example_plugin_options[restrictions][<?php echo $paymentMethod->id;?>][]" value="<?php echo $shippingOption->id;?>" type="checkbox" autocomplete="off" />
                                <?php echo $shippingOption->get_method_title();?>
                            </label>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        <?php endforeach;?>
    </div>

    <?php
}

