<?php
/**
 * Plantilla para la página de ajustes del plugin
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    Gestion_Documentos_AE
 * @subpackage Gestion_Documentos_AE/admin/partials
 */

// Si esta plantilla se llama directamente, abortamos
if (!defined('WPINC')) {
    die;
}

// Obtener opciones guardadas
$license_key = get_option('gdae_license_key', '');
$author_name = get_option('gdae_author_name', 'Acción Eficaz');
$author_description = get_option('gdae_author_description', 'Empresa de desarrollo');
$author_image = get_option('gdae_author_image', 'https://www.accioneficaz.com/wp-content/uploads/2021/12/logo-favicon.png');
$video_url = get_option('gdae_video_url', 'https://youtu.be/0nl-OAZj7lY?si=UyGPN7ieiXtpSe9t');
$support_email = get_option('gdae_support_email', 'info@accioneficaz.com');

// Verificar si la licencia es válida
$is_license_valid = !empty($license_key);

// Extraer el ID del video de YouTube
$video_id = '';
if (!empty($video_url) && (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false)) {
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $video_url, $matches)) {
        $video_id = $matches[1];
    }
}
?>

<div class="wrap gdae-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gdae-settings-container">
        <div class="gdae-settings-main">
            <!-- Banner de presentación -->
            <div class="gdae-banner">
                <div class="gdae-banner-content">
                    <div class="gdae-banner-logo">
                        <img src="<?php echo esc_url($author_image); ?>" alt="<?php echo esc_attr($author_name); ?>">
                    </div>
                    <div class="gdae-banner-text">
                        <h2><?php echo esc_html($author_name); ?></h2>
                        <p><?php echo esc_html($author_description); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="gdae-tabs">
                <div class="gdae-tab-nav">
                    <button type="button" class="gdae-tab-button active" data-tab="license">
                        <span class="dashicons dashicons-lock"></span>
                        <?php _e('Activación', 'gestion-documentos-ae'); ?>
                    </button>
                    <button type="button" class="gdae-tab-button" data-tab="about">
                        <span class="dashicons dashicons-info"></span>
                        <?php _e('Acerca del Plugin', 'gestion-documentos-ae'); ?>
                    </button>
                    <button type="button" class="gdae-tab-button" data-tab="support">
                        <span class="dashicons dashicons-businessperson"></span>
                        <?php _e('Soporte', 'gestion-documentos-ae'); ?>
                    </button>
                </div>
                
                <div class="gdae-tab-content">
                    <!-- Tab de Licencia -->
                    <div class="gdae-tab-panel active" id="gdae-tab-license">
                        <?php 
                        // Verificar si hay mensaje de error
                        if (isset($_GET['error']) && $_GET['error'] === 'license_required') {
                            echo '<div class="gdae-license-notice gdae-license-error">';
                            echo '<p>' . __('Se requiere una licencia válida para gestionar carpetas compartidas.', 'gestion-documentos-ae') . '</p>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="gdae-settings-card">
                            <div class="gdae-settings-card-header">
                                <h2><?php _e('Activación del Plugin', 'gestion-documentos-ae'); ?></h2>
                            </div>
                            <div class="gdae-settings-card-content">
                                <?php
                                // Verificar estado de la licencia
                                $license_key = get_option('gdae_license_key', '');
                                $license_status = get_option('gdae_license_status', 'invalid');
                                $license_data = get_option('gdae_license_data', array());
                                $is_license_valid = ($license_status === 'valid');
                                ?>
                                
                                <div class="gdae-license-box">
                                    <div class="gdae-license-icon">
                                        <span class="dashicons <?php echo $is_license_valid ? 'dashicons-yes-alt' : 'dashicons-lock'; ?>"></span>
                                    </div>
                                    <div class="gdae-license-fields">
                                        <label for="gdae_license_key"><?php _e('Clave de Licencia', 'gestion-documentos-ae'); ?></label>
                                        <div class="gdae-license-input-group">
                                            <input type="password" id="gdae_license_key" name="gdae_license_key" value="<?php echo esc_attr($license_key); ?>" placeholder="GDAE-XXXX-XXXX-XXXX-XXXX" class="regular-text" <?php echo $is_license_valid ? 'readonly' : ''; ?> />
                                            
                                            <?php if ($is_license_valid) : ?>
                                                <button type="button" id="gdae_deactivate_license" class="button"><?php _e('Desactivar', 'gestion-documentos-ae'); ?></button>
                                            <?php else : ?>
                                                <button type="button" id="gdae_activate_license" class="button button-primary"><?php _e('Activar', 'gestion-documentos-ae'); ?></button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div id="gdae_license_status_wrap">
                                            <?php if ($is_license_valid) : ?>
                                                <span class="gdae-license-status gdae-license-valid">
                                                    <span class="dashicons dashicons-yes"></span> <?php _e('Licencia activa', 'gestion-documentos-ae'); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="gdae-license-status gdae-license-invalid">
                                                    <span class="dashicons dashicons-no"></span> <?php _e('Licencia inactiva', 'gestion-documentos-ae'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="description">
                                            <?php _e('Ingrese su clave de licencia para activar todas las funcionalidades del plugin.', 'gestion-documentos-ae'); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($is_license_valid && !empty($license_data)) : ?>
                                    <div class="gdae-license-details">
                                        <h3><?php _e('Detalles de la Licencia', 'gestion-documentos-ae'); ?></h3>
                                        <table class="widefat striped">
                                            <tbody>
                                                <?php if (!empty($license_data['customer_name'])) : ?>
                                                    <tr>
                                                        <td><?php _e('Cliente', 'gestion-documentos-ae'); ?></td>
                                                        <td><?php echo esc_html($license_data['customer_name']); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($license_data['customer_email'])) : ?>
                                                    <tr>
                                                        <td><?php _e('Email', 'gestion-documentos-ae'); ?></td>
                                                        <td><?php echo esc_html($license_data['customer_email']); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                                
                                                <tr>
                                                    <td><?php _e('Tipo de Licencia', 'gestion-documentos-ae'); ?></td>
                                                    <td>
                                                        <?php
                                                        if (isset($license_data['type']) && $license_data['type'] === 'multi') {
                                                            echo __('Múltiples Sitios', 'gestion-documentos-ae');
                                                            if (isset($license_data['domains']) && isset($license_data['max_domains'])) {
                                                                echo ' (' . intval($license_data['domains']) . '/' . intval($license_data['max_domains']) . ')';
                                                            }
                                                        } else {
                                                            echo __('Sitio Único', 'gestion-documentos-ae');
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                
                                                <?php if (!empty($license_data['expiry'])) : ?>
                                                    <tr>
                                                        <td><?php _e('Fecha de expiración', 'gestion-documentos-ae'); ?></td>
                                                        <td><?php echo esc_html($license_data['expiry']); ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab de Acerca de -->
                    <div class="gdae-tab-panel" id="gdae-tab-about">
                        <div class="gdae-settings-card">
                            <div class="gdae-settings-card-header">
                                <h2><?php _e('Acerca de Gestión de Documentos AE', 'gestion-documentos-ae'); ?></h2>
                            </div>
                            <div class="gdae-settings-card-content">
                                <div class="gdae-about-content">
                                    <p><?php _e('El plugin Gestión de Documentos AE permite crear y compartir carpetas con usuarios específicos de WordPress. Es ideal para compartir documentos y archivos de forma organizada y segura.', 'gestion-documentos-ae'); ?></p>
                                    
                                    <div class="gdae-feature-grid">
                                        <div class="gdae-feature">
                                            <span class="dashicons dashicons-portfolio"></span>
                                            <h4><?php _e('Carpetas Compartidas', 'gestion-documentos-ae'); ?></h4>
                                            <p><?php _e('Crea carpetas y asígnalas a usuarios específicos.', 'gestion-documentos-ae'); ?></p>
                                        </div>
                                        <div class="gdae-feature">
                                            <span class="dashicons dashicons-calendar-alt"></span>
                                            <h4><?php _e('Fechas de Caducidad', 'gestion-documentos-ae'); ?></h4>
                                            <p><?php _e('Establece cuando una carpeta ya no estará disponible.', 'gestion-documentos-ae'); ?></p>
                                        </div>
                                        <div class="gdae-feature">
                                            <span class="dashicons dashicons-category"></span>
                                            <h4><?php _e('Categorización', 'gestion-documentos-ae'); ?></h4>
                                            <p><?php _e('Organiza las carpetas por categorías para mejor gestión.', 'gestion-documentos-ae'); ?></p>
                                        </div>
                                        <div class="gdae-feature">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <h4><?php _e('Acceso por Usuario', 'gestion-documentos-ae'); ?></h4>
                                            <p><?php _e('Cada usuario solo ve las carpetas asignadas.', 'gestion-documentos-ae'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($video_id)) : ?>
                                    <div class="gdae-video-container">
                                        <h3><?php _e('Video Tutorial', 'gestion-documentos-ae'); ?></h3>
                                        <div class="gdae-video-wrapper">
                                            <iframe width="100%" height="400" src="https://www.youtube.com/embed/<?php echo esc_attr($video_id); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="gdae-settings-card">
                            <div class="gdae-settings-card-header">
                                <h2><?php _e('Información de Versión', 'gestion-documentos-ae'); ?></h2>
                            </div>
                            <div class="gdae-settings-card-content">
                                <div class="gdae-version-info">
                                    <table class="widefat striped">
                                        <tbody>
                                            <tr>
                                                <td><?php _e('Versión del Plugin', 'gestion-documentos-ae'); ?></td>
                                                <td><strong><?php echo GDAE_VERSION; ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Estado de la Licencia', 'gestion-documentos-ae'); ?></td>
                                                <td>
                                                    <?php if ($is_license_valid) : ?>
                                                        <span class="gdae-license-badge gdae-license-valid"><?php _e('Activa', 'gestion-documentos-ae'); ?></span>
                                                    <?php else : ?>
                                                        <span class="gdae-license-badge gdae-license-invalid"><?php _e('Inactiva', 'gestion-documentos-ae'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?php _e('Desarrollador', 'gestion-documentos-ae'); ?></td>
                                                <td><strong><?php echo esc_html($author_name); ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab de Soporte -->
                    <div class="gdae-tab-panel" id="gdae-tab-support">
                        <div class="gdae-settings-card">
                            <div class="gdae-settings-card-header">
                                <h2><?php _e('Soporte Técnico', 'gestion-documentos-ae'); ?></h2>
                            </div>
                            <div class="gdae-settings-card-content">
                                <div class="gdae-support-content">
                                    <div class="gdae-support-intro">
                                        <div class="gdae-support-icon">
                                            <span class="dashicons dashicons-businessman"></span>
                                        </div>
                                        <div class="gdae-support-text">
                                            <h3><?php _e('¿Necesita ayuda con el plugin?', 'gestion-documentos-ae'); ?></h3>
                                            <p><?php _e('Nuestro equipo de soporte está listo para ayudarle con cualquier duda o problema que pueda tener.', 'gestion-documentos-ae'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="gdae-support-options">
                                        <div class="gdae-support-option">
                                            <span class="dashicons dashicons-email-alt"></span>
                                            <h4><?php _e('Email de Soporte', 'gestion-documentos-ae'); ?></h4>
                                            <p><?php echo esc_html($support_email); ?></p>
                                            <a href="mailto:<?php echo esc_attr($support_email); ?>?subject=<?php echo esc_attr(__('Soporte para Gestión de Documentos AE', 'gestion-documentos-ae')); ?>" class="button button-primary gdae-support-button">
                                                <?php _e('Enviar Email', 'gestion-documentos-ae'); ?>
                                            </a>
                                        </div>
                                        
                                        <div class="gdae-support-option">
                                            <span class="dashicons dashicons-book"></span>
                                            <h4><?php _e('Documentación', 'gestion-documentos-ae'); ?></h4>
                                            <p><?php _e('Consulte nuestra documentación para aprender a utilizar todas las funcionalidades.', 'gestion-documentos-ae'); ?></p>
                                            <a href="https://accioneficaz.com/documentacion" target="_blank" class="button gdae-support-button">
                                                <?php _e('Ver Documentación', 'gestion-documentos-ae'); ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="gdae-contact-form">
                                        <h3><?php _e('Formulario de Contacto Rápido', 'gestion-documentos-ae'); ?></h3>
                                        <form id="gdae-quick-support-form">
                                            <div class="gdae-form-group">
                                                <label for="gdae-support-name"><?php _e('Nombre', 'gestion-documentos-ae'); ?></label>
                                                <input type="text" id="gdae-support-name" name="name" required>
                                            </div>
                                            <div class="gdae-form-group">
                                                <label for="gdae-support-email"><?php _e('Email', 'gestion-documentos-ae'); ?></label>
                                                <input type="email" id="gdae-support-email" name="email" required>
                                            </div>
                                            <div class="gdae-form-group">
                                                <label for="gdae-support-subject"><?php _e('Asunto', 'gestion-documentos-ae'); ?></label>
                                                <input type="text" id="gdae-support-subject" name="subject" required>
                                            </div>
                                            <div class="gdae-form-group">
                                                <label for="gdae-support-message"><?php _e('Mensaje', 'gestion-documentos-ae'); ?></label>
                                                <textarea id="gdae-support-message" name="message" rows="5" required></textarea>
                                            </div>
                                            <div class="gdae-form-submit">
                                                <button type="submit" class="button button-primary gdae-quick-contact-btn">
                                                    <?php _e('Enviar Mensaje', 'gestion-documentos-ae'); ?>
                                                </button>
                                                <div class="gdae-form-message"></div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="gdae-settings-sidebar">
            <div class="gdae-author-card">
                <div class="gdae-author-image">
                    <img src="<?php echo esc_url($author_image); ?>" alt="<?php echo esc_attr($author_name); ?>" />
                </div>
                <h3 class="gdae-author-name"><?php echo esc_html($author_name); ?></h3>
                <div class="gdae-author-description">
                    <?php echo wpautop(esc_html($author_description)); ?>
                </div>
                <div class="gdae-author-links">
                    <a href="https://accioneficaz.com" target="_blank" class="gdae-author-link">
                        <span class="dashicons dashicons-admin-site-alt3"></span> <?php _e('Sitio Web', 'gestion-documentos-ae'); ?>
                    </a>
                    <a href="mailto:<?php echo esc_attr($support_email); ?>" class="gdae-author-link">
                        <span class="dashicons dashicons-email"></span> <?php _e('Contacto', 'gestion-documentos-ae'); ?>
                    </a>
                </div>
            </div>
            
            <div class="gdae-stats-card">
                <h3><?php _e('Estadísticas', 'gestion-documentos-ae'); ?></h3>
                <?php
                // Obtener estadísticas
                $total_carpetas = wp_count_posts('carpeta-compartir')->publish;
                $total_usuarios = count_users();
                global $wpdb;
                $usuarios_con_carpetas = $wpdb->get_var("
                    SELECT COUNT(DISTINCT meta_value) 
                    FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_gdae_usuario_asignado' 
                    AND meta_value != ''
                ");
                ?>
                
                <div class="gdae-stat">
                    <div class="gdae-stat-icon">
                        <span class="dashicons dashicons-portfolio"></span>
                    </div>
                    <div class="gdae-stat-info">
                        <span class="gdae-stat-value"><?php echo esc_html($total_carpetas); ?></span>
                        <span class="gdae-stat-label"><?php _e('Carpetas', 'gestion-documentos-ae'); ?></span>
                    </div>
                </div>
                
                <div class="gdae-stat">
                    <div class="gdae-stat-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="gdae-stat-info">
                        <span class="gdae-stat-value"><?php echo esc_html($usuarios_con_carpetas); ?></span>
                        <span class="gdae-stat-label"><?php _e('Usuarios con Carpetas', 'gestion-documentos-ae'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="gdae-help-card">
                <h3><?php _e('Enlaces Rápidos', 'gestion-documentos-ae'); ?></h3>
                <ul class="gdae-quick-links">
                    <li>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=carpeta-compartir')); ?>">
                            <span class="dashicons dashicons-portfolio"></span> <?php _e('Ver Todas las Carpetas', 'gestion-documentos-ae'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=carpeta-compartir')); ?>">
                            <span class="dashicons dashicons-plus-alt"></span> <?php _e('Añadir Nueva Carpeta', 'gestion-documentos-ae'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=categoria-carpeta&post_type=carpeta-compartir')); ?>">
                            <span class="dashicons dashicons-category"></span> <?php _e('Gestionar Categorías', 'gestion-documentos-ae'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        // Script de respaldo en caso de que el JS principal falle
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Script de respaldo cargado');
            
            // Función para cambiar entre pestañas
            function activateTab(tabId) {
                // Obtener todos los elementos de pestaña
                var tabButtons = document.querySelectorAll('.gdae-tab-button');
                var tabPanels = document.querySelectorAll('.gdae-tab-panel');
                
                // Remover la clase activa de todas las pestañas
                tabButtons.forEach(function(btn) { btn.classList.remove('active'); });
                tabPanels.forEach(function(panel) { panel.classList.remove('active'); });
                
                // Agregar la clase activa a la pestaña seleccionada
                var selectedButton = document.querySelector('.gdae-tab-button[data-tab="' + tabId + '"]');
                var selectedPanel = document.getElementById('gdae-tab-' + tabId);
                
                if (selectedButton) selectedButton.classList.add('active');
                if (selectedPanel) selectedPanel.classList.add('active');
            }
            
            // Agregar eventos a los botones de pestañas
            var tabButtons = document.querySelectorAll('.gdae-tab-button');
            tabButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('data-tab');
                    activateTab(tabId);
                });
            });
        });
    </script>
    <script type="text/javascript">
        // Script específico para botones de licencia
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Script de botones de licencia cargado');
            
            // Activar licencia
            var activateButton = document.getElementById('gdae_activate_license');
            if (activateButton) {
                activateButton.addEventListener('click', function() {
                    var licenseKey = document.getElementById('gdae_license_key').value;
                    var statusWrap = document.getElementById('gdae_license_status_wrap');
                    
                    if (!licenseKey) {
                        alert('Por favor, ingrese una clave de licencia.');
                        document.getElementById('gdae_license_key').focus();
                        return;
                    }
                    
                    this.disabled = true;
                    this.textContent = 'Activando...';
                    
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            var response;
                            try {
                                response = JSON.parse(xhr.responseText);
                            } catch(e) {
                                console.error('Error al parsear respuesta:', e);
                                activateButton.disabled = false;
                                activateButton.textContent = 'Activar';
                                return;
                            }
                            
                            if (response.success) {
                                statusWrap.innerHTML = '<span class="gdae-license-status gdae-license-valid">' +
                                    '<span class="dashicons dashicons-yes"></span> Licencia activa</span>';
                                
                                setTimeout(function() {
                                    location.reload();
                                }, 1500);
                            } else {
                                statusWrap.innerHTML = '<span class="gdae-license-status gdae-license-invalid">' +
                                    '<span class="dashicons dashicons-no"></span> Licencia inactiva</span>';
                                
                                var errorMsg = document.createElement('div');
                                errorMsg.className = 'notice notice-error';
                                errorMsg.innerHTML = '<p>' + (response.data || 'Error al activar la licencia') + '</p>';
                                statusWrap.parentNode.insertBefore(errorMsg, statusWrap.nextSibling);
                                
                                activateButton.disabled = false;
                                activateButton.textContent = 'Activar';
                            }
                        }
                    };
                    
                    // Obtener el nonce si está disponible
                    var nonce = '';
                    if (typeof gdae_license !== 'undefined' && gdae_license.nonce) {
                        nonce = gdae_license.nonce;
                    }
                    
                    xhr.send('action=gdae_activate_license&license_key=' + encodeURIComponent(licenseKey) + '&nonce=' + encodeURIComponent(nonce));
                });
            }
            
            // Desactivar licencia
            var deactivateButton = document.getElementById('gdae_deactivate_license');
            if (deactivateButton) {
                deactivateButton.addEventListener('click', function() {
                    if (!confirm('¿Está seguro de que desea desactivar esta licencia?')) {
                        return;
                    }
                    
                    this.disabled = true;
                    this.textContent = 'Desactivando...';
                    
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxurl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            location.reload();
                        }
                    };
                    
                    // Obtener el nonce si está disponible
                    var nonce = '';
                    if (typeof gdae_license !== 'undefined' && gdae_license.nonce) {
                        nonce = gdae_license.nonce;
                    }
                    
                    xhr.send('action=gdae_deactivate_license&nonce=' + encodeURIComponent(nonce));
                });
            }
        });
    </script>
    <script type="text/javascript">
        // Script de diagnóstico - Eliminar después de resolver el problema
        document.addEventListener('DOMContentLoaded', function() {
            console.log('===== DIAGNÓSTICO =====');
            console.log('Botón activar existe:', !!document.getElementById('gdae_activate_license'));
            console.log('Botón desactivar existe:', !!document.getElementById('gdae_deactivate_license'));
            console.log('Campo de licencia existe:', !!document.getElementById('gdae_license_key'));
            console.log('Status wrap existe:', !!document.getElementById('gdae_license_status_wrap'));
            console.log('ajaxurl disponible:', typeof ajaxurl !== 'undefined');
            console.log('gdae_license disponible:', typeof gdae_license !== 'undefined');
            if (typeof gdae_license !== 'undefined') {
                console.log('gdae_license.nonce disponible:', !!gdae_license.nonce);
            }
            
            // Comprobar que los eventos están funcionando
            var activateButton = document.getElementById('gdae_activate_license');
            if (activateButton) {
                activateButton.addEventListener('click', function() {
                    console.log('Evento click detectado en botón activar');
                });
            }
            console.log('=====================');
        });
    </script>
</div>