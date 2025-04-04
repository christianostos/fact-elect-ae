<?php
// Añade pagina al menu administrador
function api_config_menu(){
    add_submenu_page( 'erp-ae', 'Ajustes API AE Api', 'Facturas AE API', 'administrator', 'facturaloperu-api-config-settings', 'facturaloperu_api_config_page_settings');
}

add_action('admin_menu', 'api_config_menu');

function add_admin_page() {
    add_menu_page(
        'Ajustes API AE Api',
        'Facturas AE API',
        'manage_options',
        'facturaloperu-api',
        'facturaloperu_api_config_page_settings'
    );
}

// html con el formulario de opciones
function facturaloperu_api_config_page_settings(){
    $default_tab = null;
    $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
    ?>
    <div class="wrap">
        <h2>Configuración de API DIAN</h2>
        <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="?page=facturaloperu-api-config-settings" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">General</a>
            <!-- <a href="?page=facturaloperu-api-config-settings&tab=guide" class="nav-tab <?php if($tab==='guide'):?>nav-tab-active<?php endif; ?>">Guía</a> -->
            <a href="?page=facturaloperu-api-config-settings&tab=conection" class="nav-tab <?php if($tab==='conection'):?>nav-tab-active<?php endif; ?>">Conexión</a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company" class="nav-tab <?php if($tab==='dian-config-company'):?>nav-tab-active<?php endif; ?>">Empresa</a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company-response" class="nav-tab <?php if($tab==='dian-config-company-response'):?>nav-tab-active<?php endif; ?>">Empresa <small>(rpta.)</small></a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software" class="nav-tab <?php if($tab==='dian-config-software'):?>nav-tab-active<?php endif; ?>">Software</a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software-response" class="nav-tab <?php if($tab==='dian-config-software-response'):?>nav-tab-active<?php endif; ?>">Software <small>(rpta.)</small></a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate" class="nav-tab <?php if($tab==='dian-config-certificate'):?>nav-tab-active<?php endif; ?>">Certificado</a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate-response" class="nav-tab <?php if($tab==='dian-config-certificate-response'):?>nav-tab-active<?php endif; ?>">Certificado <small>(rpta.)</small></a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution" class="nav-tab <?php if($tab==='dian-config-resolution'):?>nav-tab-active<?php endif; ?>">Resolución</a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution-response" class="nav-tab <?php if($tab==='dian-config-resolution-response'):?>nav-tab-active<?php endif; ?>">Resolución <small>(rpta.)</small></a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-initial-response" class="nav-tab <?php if($tab==='dian-config-initial-response'):?>nav-tab-active<?php endif; ?>">Inicializar</a>
            <?php
            if(get_option('facturaloperu_api_config_production') === 'true') {
                ?>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-environment-response" class="nav-tab <?php if($tab==='dian-config-environment-response'):?>nav-tab-active<?php endif; ?>">Entorno</a>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-numbering-range-response" class="nav-tab <?php if($tab==='dian-numbering-range-response'):?>nav-tab-active<?php endif; ?>">Resolución (obtener)</a>
                <?php
            }
            ?>
        </nav>

        <div class="tab-content">
        <?php
            switch($tab) :
                case 'conection':
                    ?>

                    <h1>Conexión API</h1>

                    <form method="POST" action="options.php">
                        <?php
                            settings_fields('facturaloperu-api-config-settings-group');
                            do_settings_sections('facturaloperu-api-config-settings-group');
                        ?>
                        <table class="form-table">
                            <tr>
                                <th class="titledesc">
                                    <label>API_URL <span style="color:red;">*</span></label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" name="facturaloperu_api_config_url" id="facturaloperu_api_config_url" value="<?php echo get_option('facturaloperu_api_config_url'); ?>" style="min-width: 400px" class="input-text regular-input">
                                    <br>
                                    <small>Ejemplo: http://co-apidian2023.oo/</small>
                                </td>
                            </tr>
                            <?php
                                //if(get_option('facturaloperu_api_config_url') != ''){
                            ?>
                                <tr valign="top">
                                    <th class="titledesc">
                                        <label>API_TOKEN</label>
                                    </th>
                                    <td class="forminp forminp-text">
                                        <input type="text" name="facturaloperu_api_config_token" id="facturaloperu_api_config_token" value="<?php echo get_option('facturaloperu_api_config_token'); ?>" style="min-width: 400px" class="input-text regular-input">
                                    </td>
                                </tr>
                            <?php
                                //}
                            ?>
                        </table>
                        <?php submit_button(); ?>
                    </form>

                    <?php
                    break;
                case 'guide':
                    ?>

                    <h1>Guía</h1>

                    <?php
                    break;
                case 'dian-config-company':

                    include('forms/config-company.php');

                    break;
                case 'dian-config-company-response':

                    include('api/company-response.php');

                    break;
                case 'dian-config-software':

                    include('forms/config-software.php');

                    break;
                case 'dian-config-software-response':

                    include('api/software-response.php');

                    break;
                case 'dian-config-certificate':

                    include('forms/certificate.php');

                    break;
                case 'dian-config-certificate-response':

                    include('api/certificate.php');

                    break;
                case 'dian-config-resolution':

                    include('forms/resolution.php');

                    break;
                case 'dian-config-resolution-response':

                    include('api/resolution-response.php');

                    break;
                case 'dian-config-initial-response':

                    include('api/initial-response.php');

                    break;
                case 'dian-config-environment-response':

                    include('api/environment.php');

                    break;
                case 'dian-numbering-range-response';

                    include('api/numbering-ranges.php');

                    break;
                case 'dian-config-resolution-two';

                    include('api/config-resolution-two.php');

                    break;
                default:
                    ?>

                    <form method="POST" action="options.php">
                        <?php
                            settings_fields('facturaloperu-api-config-generals-group');
                            do_settings_sections('facturaloperu-api-config-generals-group');
                        ?>
                        <table class="form-table">
                            <!-- <tr valign="top">
                                <th class="titledesc">
                                    <label>Número de resolución</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" name="facturaloperu_api_config_resolution_number" id="facturaloperu_api_config_resolution_number" value="<?php echo get_option('facturaloperu_api_config_resolution_number'); ?>" style="min-width: 400px" class="input-text regular-input">
                                </td>
                            </tr> -->
                            <tr>
                                <th class="titledesc">
                                    <label>Habilitar modo producción</label>
                                </th>
                                <td class="forminp forminp-text">
                                    SI <input
                                        type="radio"
                                        name="facturaloperu_api_config_production"
                                        id="facturaloperu_api_config_production"
                                        value="true"
                                        class="input-text regular-input"
                                        <?php checked('true', get_option('facturaloperu_api_config_production')); ?>
                                    >
                                    NO <input
                                        type="radio"
                                        name="facturaloperu_api_config_production"
                                        id="facturaloperu_api_config_production"
                                        value="false"
                                        class="input-text regular-input"
                                        <?php checked('false', get_option('facturaloperu_api_config_production')); ?>
                                    >
                                </td>
                            </tr>
                            <tr>
                                <th class="titledesc">
                                    <label>Enviar correo electrónico</label>
                                </th>
                                <td class="forminp forminp-text">

                                        SI <input
                                            type="radio"
                                            name="facturaloperu_api_config_send_email"
                                            id="facturaloperu_api_config_send_email"
                                            value="true"
                                            class="input-text regular-input"
                                            <?php checked('true', get_option('facturaloperu_api_config_send_email')); ?>
                                        >
                                        NO <input
                                            type="radio"
                                            name="facturaloperu_api_config_send_email"
                                            id="facturaloperu_api_config_send_email"
                                            value="false"
                                            class="input-text regular-input"
                                            <?php checked('false', get_option('facturaloperu_api_config_send_email')); ?>
                                        >
                                </td>
                            </tr>
                            <tr>
                                <th class="titledesc">
                                    <label>TestSetId</label>
                                </th>
                                <td class="forminp forminp-text">
                                    <input type="text" name="facturaloperu_api_config_testsetid" id="facturaloperu_api_config_testsetid" value="<?php echo get_option('facturaloperu_api_config_testsetid'); ?>" style="min-width: 400px" class="input-text regular-input">
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(); ?>
                    </form>

                    <?php
                    break;
            endswitch;
        ?>
        </div>

    </div>

    <?php
}

include('register-settings.php');

include('actions.php');




// add_filter('the_content', 'facturaloperu_api_config_settings_action');
