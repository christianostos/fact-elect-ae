<?php

function facturaloperu_api_config_settings(){
    register_setting('facturaloperu-api-config-settings-group', 'facturaloperu_api_config_url');
    register_setting('facturaloperu-api-config-settings-group', 'facturaloperu_api_config_token');
}

function facturaloperu_api_config_generals(){
    // register_setting('facturaloperu-api-config-generals-group', 'facturaloperu_api_config_resolution_number');
    register_setting('facturaloperu-api-config-generals-group', 'facturaloperu_api_config_send_email');
    register_setting('facturaloperu-api-config-generals-group', 'facturaloperu_api_config_production');
    register_setting('facturaloperu-api-config-generals-group', 'facturaloperu_api_config_testsetid');
}

function facturaloperu_api_config_environment(){
    register_setting('facturaloperu-api-config-environment-group', 'facturaloperu_api_environment_response');
}

function facturaloperu_api_config_numbering_ranges(){
    register_setting('facturaloperu-api-config-numbering-ranges-group', 'facturaloperu_api_numbering_ranges_response');
}

function facturaloperu_api_config_company(){
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_document_type');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_document');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_dv');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_organization_type');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_regime_type');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_liability_type');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_business_name');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_merchant_registration');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_municipality');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_business_address');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_business_phone');
    register_setting('facturaloperu-api-config-company-group', 'facturaloperu_api_config_business_email');
}

function facturaloperu_api_config_company_response(){
    register_setting('facturaloperu-api-config-company-response-group', 'facturaloperu_api_config_response');
    // register_setting('facturaloperu-api-config-company-response-group', 'facturaloperu_api_config_token');
}

function facturaloperu_api_config_software() {
    register_setting('facturaloperu-api-config-software-group', 'facturaloperu_api_software_id');
    register_setting('facturaloperu-api-config-software-group', 'facturaloperu_api_software_pin');
}

function facturaloperu_api_config_software_response() {
    register_setting('facturaloperu-api-config-software-response-group', 'facturaloperu_api_software_response');
}

function facturaloperu_api_config_certificate() {
    register_setting('facturaloperu-api-config-certificate-group', 'facturaloperu_api_certificate');
    register_setting('facturaloperu-api-config-certificate-group', 'facturaloperu_api_certificate_password');
}

function facturaloperu_api_config_certificate_response() {
    register_setting('facturaloperu-api-config-certificate-response-group', 'facturaloperu_api_certificate_response');
}

function facturaloperu_api_config_resolution() {
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_document_type');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_prefix');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_date');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_technical_key');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_number_from');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_number_to');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_generated_date');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_date_start');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_date_stop');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_initial_docs');
    register_setting('facturaloperu-api-config-resolution-group', 'facturaloperu_api_resolution_number_current');
}

function facturaloperu_api_config_resolution_response() {
    register_setting('facturaloperu-api-config-resolution-response-group', 'facturaloperu_api_resolution_response');
}

function facturaloperu_api_config_initial_response() {
    register_setting('facturaloperu-api-config-initial-response-group', 'facturaloperu_api_initial_response');
}
