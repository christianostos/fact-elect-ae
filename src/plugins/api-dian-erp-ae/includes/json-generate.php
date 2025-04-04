<?php

// JSON ENVIADO A LA SUNAT
add_action( 'add_meta_boxes', 'erplugin_response_add_meta_boxes' );
if ( ! function_exists( 'erplugin_response_add_meta_boxes' ) )
{
    function erplugin_response_add_meta_boxes()
    {
        add_meta_box( 'erplugin_response_json', 'JSON Generado', 'erplugin_response_json_api', 'shop_order', 'side', 'core' );
    }
}
if ( ! function_exists( 'erplugin_response_json_api' ) )
{
    function erplugin_response_json_api()
    {
        global $post;

        $order = wc_get_order( $post->ID );
        $json_invoice = get_post_meta( $post->ID, 'json_invoice', true ) ?? '';

        if ('' != $json_invoice) {
            echo '<div style="max-height:600px; overflow: auto;"><pre>'.$json_invoice.'</pre></div>';
        } else {
            echo 'Se generará al guardar';
        }

    };

}

