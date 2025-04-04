<?php

add_action('admin_init', 'facturaloperu_api_config_settings');
add_action('admin_init', 'facturaloperu_api_config_generals');
add_action('admin_init', 'facturaloperu_api_config_environment');
add_action('admin_init', 'facturaloperu_api_config_numbering_ranges');
add_action('admin_init', 'facturaloperu_api_config_company');
add_action('admin_init', 'facturaloperu_api_config_company_response');
add_action('admin_init', 'facturaloperu_api_config_software');
add_action('admin_init', 'facturaloperu_api_config_software_response');
add_action('admin_init', 'facturaloperu_api_config_certificate');
add_action('admin_init', 'facturaloperu_api_config_certificate_response');
add_action('admin_init', 'facturaloperu_api_config_resolution');
add_action('admin_init', 'facturaloperu_api_config_resolution_response');
add_action('admin_init', 'facturaloperu_api_config_initial_response');

function facturaloperu_api_config_settings_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $url = get_option('facturaloperu_api_config_url');
        return $content;
    }
}

function facturaloperu_api_config_generals_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        // $resolution_number = get_option('facturaloperu_api_config_resolution_number');
        $send_email = get_option('facturaloperu_api_config_send_email');
        $production = get_option('facturaloperu_api_config_production');
        $testsetid = get_option('facturaloperu_api_config_testsetid');
        return $content;
    }
}

function facturaloperu_api_config_environment_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $environment_response = get_option('facturaloperu_api_environment_response');
        return $content;
    }
}

function facturaloperu_api_config_numbering_ranges_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $numbering_ranges_response = get_option('facturaloperu_api_numbering_ranges_response');
        return $content;
    }
}

function facturaloperu_api_config_company_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $api_document_type = get_option('facturaloperu_api_config_document_type');
        $api_document = get_option('facturaloperu_api_config_document');
        $api_dv = get_option('facturaloperu_api_config_dv');
        $api_organization_type = get_option('facturaloperu_api_config_organization_type');
        $api_regime_type = get_option('facturaloperu_api_config_regime_type');
        $api_liability_type = get_option('facturaloperu_api_config_liability_type');
        $api_business_name = get_option('facturaloperu_api_config_business_name');
        $api_merchant_registration = get_option('facturaloperu_api_config_merchant_registration');
        $api_municipality = get_option('facturaloperu_api_config_municipality');
        $api_business_address = get_option('facturaloperu_api_config_business_address');
        $api_business_phone = get_option('facturaloperu_api_config_business_phone');
        $api_business_email = get_option('facturaloperu_api_config_business_email');
        return $content;
    }
}

function facturaloperu_api_config_company_response_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        // $token = get_option('facturaloperu_api_config_token');
        $api_response = get_option('facturaloperu_api_config_response');
        echo "Ejecutado";
        return $content;
    }
    echo "no Ejecutado";
}

function facturaloperu_api_config_software_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $resolution_number = get_option('facturaloperu_api_software_id');
        $send_email = get_option('facturaloperu_api_software_pin');
        return $content;
    }
}

function facturaloperu_api_config_software_response_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $api_response = get_option('facturaloperu_api_software_response');
        return $content;
    }
}

function facturaloperu_api_config_certificate_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $certificate = get_option('facturaloperu_api_certificate');
        $certificate_password = get_option('facturaloperu_api_certificate_password');
        return $content;
    }
}

function facturaloperu_api_config_certificate_response_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $api_response = get_option('facturaloperu_api_certificate_response');
        return $content;
    }
}

function facturaloperu_api_config_resolution_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        // if(isset($_POST["facturaloperu_api_resolution_number_current"])){
            // update_post_meta($post->ID, "facturaloperu_api_resolution_number_current", 'qoweiujo');
        // }
        $resolution_document_type = get_option('facturaloperu_api_resolution_document_type');
        $resolution = get_option('facturaloperu_api_resolution');
        $resolution_prefix = get_option('facturaloperu_api_resolution_prefix');
        $resolution_date = get_option('facturaloperu_api_resolution_date');
        $resolution_technical_key = get_option('facturaloperu_api_resolution_technical_key');
        $resolution_number_from = get_option('facturaloperu_api_resolution_number_from');
        $resolution_number_to = get_option('facturaloperu_api_resolution_number_to');
        $resolution_generated_date = get_option('facturaloperu_api_resolution_generated_date');
        $resolution_date_start = get_option('facturaloperu_api_resolution_date_start');
        $resolution_date_stop = get_option('facturaloperu_api_resolution_date_stop');
        $resolution_initial_docs = get_option('facturaloperu_api_initial_docs');
        $resolution_number_current = get_option('facturaloperu_api_resolution_number_current');
        return $content;
    }
}

function facturaloperu_api_config_resolution_response_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $api_response = get_option('facturaloperu_api_resolution_response');
        return $content;
    }
}

function facturaloperu_api_config_resolution_initial_action($content){
    global $post;

    if ($post && $post->pots_type == 'post' && !is_singular('post')) {
        $api_response = get_option('facturaloperu_api_initial_response');
        return $content;
    }
}