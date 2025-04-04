<h1>Envío de Id de Software</h1>
<div>
    <button
      type="button"
      name="numbering-ranges"
      id="numbering-ranges"
      class="button button-primary">
      Enviar a API
    </button>
</div>
<form method="POST" action="options.php" style="display: block;">
    <?php
        settings_fields('facturaloperu-api-config-numbering-ranges-group');
        do_settings_sections('facturaloperu-api-config-numbering-ranges-group');
    ?>
    <h2>Resultado</h2>
    <textarea
      name="facturaloperu_api_numbering_ranges_response"
      id="facturaloperu_api_numbering_ranges_response"
      style="min-width: 700px;min-height: 400px;"
      readonly
      class="input-text regular-input"><?php echo get_option('facturaloperu_api_numbering_ranges_response'); ?>
    </textarea>

    <?php submit_button(); ?>
</form>