<?php
/**
 * Plantilla para mostrar las carpetas asignadas a un usuario
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    Gestion_Documentos_AE
 * @subpackage Gestion_Documentos_AE/public/partials
 */

// Si esta plantilla se llama directamente, abortamos
if (!defined('WPINC')) {
    die;
}
?>

<div class="gdae-carpetas-container">
    <div class="gdae-header">
        <h2 class="gdae-title"><?php _e('Mis Carpetas Compartidas', 'gestion-documentos-ae'); ?></h2>
        <p class="gdae-description">
            <?php _e('A continuación se muestran las carpetas que han sido compartidas con usted.', 'gestion-documentos-ae'); ?>
        </p>
    </div>

    <?php if (empty($carpetas)) : ?>
    
    <div class="gdae-empty-state">
        <div class="gdae-empty-state__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                <line x1="9" y1="14" x2="15" y2="14"></line>
            </svg>
        </div>
        <h3 class="gdae-empty-state__title"><?php _e('No hay carpetas compartidas', 'gestion-documentos-ae'); ?></h3>
        <p class="gdae-empty-state__description">
            <?php _e('Actualmente no hay carpetas compartidas asignadas a su cuenta.', 'gestion-documentos-ae'); ?>
        </p>
    </div>
    
    <?php else : ?>
    
    <div class="gdae-carpetas-grid">
        <?php foreach ($carpetas as $carpeta) : 
            // Obtener metadatos
            $estado = get_post_meta($carpeta->ID, '_gdae_estado', true) ?: 'publicada';
            $fecha_caducidad = get_post_meta($carpeta->ID, '_gdae_fecha_caducidad', true);
            $url_carpeta = get_post_meta($carpeta->ID, '_gdae_url_carpeta', true);
            
            // Verificar caducidad
            $caducada = false;
            if (!empty($fecha_caducidad)) {
                $fecha_caducidad_timestamp = strtotime($fecha_caducidad);
                $caducada = $fecha_caducidad_timestamp < time();
            }
            
            // Obtener categorías
            $categorias = get_the_terms($carpeta->ID, 'categoria-carpeta');
            ?>
            
            <div class="gdae-carpeta-card <?php echo $caducada ? 'gdae-carpeta-card--caducada' : ''; ?>">
                <div class="gdae-carpeta-card__header">
                    <h3 class="gdae-carpeta-card__title"><?php echo esc_html($carpeta->post_title); ?></h3>
                    <span class="gdae-estado gdae-estado--<?php echo esc_attr($estado); ?>">
                        <?php echo esc_html(ucfirst($estado)); ?>
                    </span>
                </div>
                
                <?php if (!empty($carpeta->post_content)) : ?>
                <div class="gdae-carpeta-card__content">
                    <?php echo wp_kses_post(wpautop($carpeta->post_content)); ?>
                </div>
                <?php endif; ?>
                
                <div class="gdae-carpeta-card__meta">
                    <?php if (!empty($categorias) && !is_wp_error($categorias)) : ?>
                    <div class="gdae-carpeta-card__categories">
                        <?php foreach ($categorias as $categoria) : ?>
                        <span class="gdae-categoria-tag"><?php echo esc_html($categoria->name); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($fecha_caducidad)) : ?>
                    <div class="gdae-carpeta-card__fecha">
                        <span class="gdae-carpeta-card__label"><?php _e('Caducidad:', 'gestion-documentos-ae'); ?></span>
                        <span class="gdae-carpeta-card__value <?php echo $caducada ? 'gdae-caducada' : ''; ?>">
                            <?php echo esc_html($fecha_caducidad); ?>
                            <?php if ($caducada) : ?>
                            <span class="gdae-badge gdae-badge--caducada"><?php _e('Caducada', 'gestion-documentos-ae'); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="gdae-carpeta-card__actions">
                    <?php if (!$caducada && !empty($url_carpeta)) : ?>
                    <!-- Botón para ver carpeta embebida -->
                    <a href="#" class="gdae-button gdae-button--primary gdae-ver-carpeta" data-url="<?php echo esc_url($url_carpeta); ?>" data-title="<?php echo esc_attr($carpeta->post_title); ?>">
                        <?php _e('Ver Carpeta', 'gestion-documentos-ae'); ?>
                    </a>
                    
                    <!-- Enlace directo (opcional) -->
                    <a href="<?php echo esc_url($url_carpeta); ?>" target="_blank" class="gdae-button gdae-button--secondary">
                        <?php _e('Abrir en Drive', 'gestion-documentos-ae'); ?>
                    </a>
                    
                    <?php if ($estado === 'publicada') : ?>
                    <button class="gdae-button gdae-button--secondary gdae-marcar-contestada" data-carpeta-id="<?php echo esc_attr($carpeta->ID); ?>">
                        <?php _e('Marcar como Contestada', 'gestion-documentos-ae'); ?>
                    </button>
                    <?php endif; ?>
                    
                    <?php else : ?>
                    <span class="gdae-mensaje-caducada">
                        <?php _e('No se puede acceder a esta carpeta', 'gestion-documentos-ae'); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Contenedor para previsualización de carpeta -->
    <div id="gdae-carpeta-preview-container" class="gdae-carpeta-preview" style="display: none;">
        <div class="gdae-carpeta-preview__header">
            <h3 id="gdae-carpeta-preview-title" class="gdae-carpeta-preview__title"><?php _e('Contenido de la Carpeta', 'gestion-documentos-ae'); ?></h3>
            <button class="gdae-carpeta-preview__close">&times;</button>
        </div>
        <div class="gdae-carpeta-preview__content">
            <iframe id="gdae-carpeta-iframe" src="" width="100%" height="500" frameborder="0"></iframe>
        </div>
    </div>
    
    <!-- Overlay para el fondo -->
    <div class="gdae-overlay"></div>
</div>