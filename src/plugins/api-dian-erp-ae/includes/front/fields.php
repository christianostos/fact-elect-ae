<?php

// add fields
add_action( 'woocommerce_before_checkout_billing_form', 'misha_select_field' );

// save fields to order meta
add_action( 'woocommerce_checkout_update_order_meta', 'misha_save_what_we_added', 10, 1);

// select
function misha_select_field( $checkout ){

    woocommerce_form_field( 'type_organization', array(
        'type' => 'select',
        'required'  => true,
        'class' => array('misha-field', 'form-row-wide'),
        'label' => 'Tipo de organización',
        'label_class' => 'misha-label',
        'options' => array(
            ''   => 'Seleccione',
            '1'  => 'Persona Jurídica',
            '2'  => 'Persona Natural'
            )
    ), $checkout->get_value( 'type_organization' ) );

    woocommerce_form_field( 'type_regime', array(
        'type' => 'select',
        'required'  => false,
        'class' => array('misha-field', 'form-row-wide'),
        'label' => 'Tipo de Regimen',
        'label_class' => 'misha-label',
        'options' => array(
            '1'  => 'Responsable de IVA',
            '2'  => 'No Responsable de IVA'
        ),
        'default' => '2'
    ), $checkout->get_value( 'type_regime' ) );

    woocommerce_form_field( 'type_document_identification', array(
        'type' => 'select',
        'required'  => true,
        'class' => array('misha-field', 'form-row-wide'),
        'label' => 'Tipo de Documento',
        'label_class' => 'misha-label',
        'options' => array(
            ''   => 'Seleccione',
            '1'  => 'Registro civil',
            '3'  => 'Cédula ciudadana',
            '6'  => 'NIT',
            '7'  => 'Pasaporte',
            )
    ), $checkout->get_value( 'type_document_identification' ) );

    woocommerce_form_field( 'document_dv', array(
        'type' => 'text',
        'required'  => false,
        'class' => array('misha-field', 'form-row-wide'),
        'label' => 'DV',
        'label_class' => 'misha-label'
    ), $checkout->get_value( 'document_dv' ) );

    woocommerce_form_field( 'merchant_registration', array(
        'type' => 'text',
        'required'  => false,
        'class' => array('misha-field', 'form-row-wide'),
        'label' => 'Registro Mercantil',
        'label_class' => 'misha-label'
    ), $checkout->get_value( 'merchant_registration' ) );

    woocommerce_form_field( 'is_number_suffix', array(
        'type' => 'hidden',
        'required'  => true,
        'default' => '0',
    ), $checkout->get_value( 'is_number_suffix' ) );

}

// save field values
function misha_save_what_we_added( $order_id ){

    if( !empty( $_POST['type_document_identification'] ) )
        update_post_meta( $order_id, 'type_document_identification', sanitize_text_field( $_POST['type_document_identification'] ) );
    if( !empty( $_POST['document_dv'] ) )
        update_post_meta( $order_id, 'document_dv', sanitize_text_field( $_POST['document_dv'] ) );
    if( !empty( $_POST['type_organization'] ) )
        update_post_meta( $order_id, 'type_organization', sanitize_text_field( $_POST['type_organization'] ) );
    if( !empty( $_POST['type_regime'] ) )
        update_post_meta( $order_id, 'type_regime', sanitize_text_field( $_POST['type_regime'] ) );
    if( !empty( $_POST['merchant_registration'] ) )
        update_post_meta( $order_id, 'merchant_registration', sanitize_text_field( $_POST['merchant_registration'] ) );
    update_post_meta( $order_id, 'is_number_suffix', '0' );

}

add_action('woocommerce_checkout_process', 'misha_check_if_selected');

function misha_check_if_selected() {

    // you can add any custom validations here
    if ( empty( $_POST['type_document_identification'] ) )
        wc_add_notice( 'Por favor seleccione un tipo de documento', 'error' );
    // if ( empty( $_POST['document_dv'] ) )
    //     wc_add_notice( 'Por favor ingrese dv', 'error' );
    if ( empty( $_POST['type_organization'] ) )
        wc_add_notice( 'Por favor ingrese tipo de organización', 'error' );
    if ( empty( $_POST['type_regime'] ) )
        wc_add_notice( 'Por favor ingrese tipo de regimen', 'error' );
    // if ( empty( $_POST['merchant_registration'] ) )
    //     wc_add_notice( 'Por favor ingrese registro mercantil', 'error' );

}

add_filter( 'woocommerce_checkout_fields', 'misha_document_type_first' );

function misha_document_type_first( $checkout_fields ) {
    $checkout_fields['billing']['billing_nif']['priority'] = 4;
    return $checkout_fields;
}

add_filter( 'woocommerce_checkout_fields' , 'misha_labels_placeholders', 9999, 1 );

function misha_labels_placeholders( $f ) {
    $f['billing']['billing_nif']['label'] = 'Número de documento';
    $f['billing']['billing_company']['label'] = 'Nombre';
    return $f;
}

add_filter( 'woocommerce_checkout_fields', 'misha_remove_fields', 9999 );

function misha_remove_fields( $woo_checkout_fields_array ) {
    unset( $woo_checkout_fields_array['billing']['billing_first_name'] );
    unset( $woo_checkout_fields_array['billing']['billing_last_name'] );
    // unset( $woo_checkout_fields_array['billing']['billing_address_2'] );
    return $woo_checkout_fields_array;
}

add_filter( 'woocommerce_checkout_fields' , 'misha_required_fields', 9999 );

function misha_required_fields( $f ) {
    $f['billing']['billing_company']['required'] = true;
    $f['billing']['billing_nif']['required'] = true;
    $f['billing']['billing_phone']['required'] = false;
    $f['billing']['billing_address_1']['default'] = 'Xxxxxxxxxxxxxxxxxxxxxx';
    $f['billing']['billing_postcode']['required'] = true;
    $f['billing']['billing_postcode']['label'] = 'ID Municipalidad';
    $f['billing']['billing_postcode']['default'] = '149';
    $f['billing']['billing_new_state']['default'] = 'CO11';
    $f['billing']['billing_state']['default'] = '149';
    return $f;
}

function add_scripts_checkout() {
    wp_enqueue_script(
        'checkout-script',
        plugin_dir_url( __FILE__ ) . '/js/fields.js',
        array( 'jquery' )
    );

    wp_localize_script('my-script', 'myScript', array(
        'pluginsUrl' => plugins_url(),
    ));
}
add_action( 'woocommerce_after_checkout_form', 'add_scripts_checkout');