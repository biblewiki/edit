
$("#biwi-form").submit(function(event) {
    event.preventDefault(); //prevent default action 
    var post_url = $(this).attr("action"); //get form action url
    var request_method = $(this).attr("method"); //get form GET/POST method
    var form_data = $(this).serializeArray(); //Encode form elements for submission

    $.ajax({
        url: post_url,
        type: request_method,
        data: form_data,
        success:function(data) {
            console.log(data);
        }
    }).done(function(response) { //
        $("#server-results").html(response);
            console.log(response);
    });
});