$(document).ready(function () {
$('#city-names').on('change', function () {
        
        let cityId = $(this).val();
     
        $.ajax({
            type: 'POST',
            url: window.location.origin + '/wp-admin/admin-ajax.php',
            dataType: "HTML", // add data type
            data: {action: 'get_streets_form_DB', "cityId": cityId},
            success: function (response) {
                jQuery("#streets-name").html(response);
                 
                }
            
            }
        });
  });
    }); 