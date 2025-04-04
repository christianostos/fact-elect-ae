jQuery(document).ready(function($) {
  $('#numbering-ranges').click(function() {
      console.log('aqui');
      var abs_url = api_script.ajaxurl
      $(this).nextAll().remove();
      $.ajax({
          type: "POST",
          url: abs_url,
          data: {
              'action': 'api_service_config_numbering_ranges'
          },
          success: function(reponse){
              console.log(reponse);
              reponse = reponse.split('Array').join('');
              var obj = JSON.parse(reponse);
              var strong = JSON.stringify(obj, undefined, 4);
              $('#facturaloperu_api_numbering_ranges_response').val(strong);
          }
      });
  });
});