
$("#biwi-form").submit(function(event) {
    event.preventDefault(); //prevent default action 
    let func = $(this).attr("action"); //get form action url
    let requestMethod = $(this).attr("method"); //get form GET/POST method
    let formData = $(this).serializeArray(); //Encode form elements for submission

    let requestData = {
        function: func,
        args: {formPacket: formData}
    };

    $.ajax({
        url: '../core/php/RequestHandler.php',
        type: requestMethod,
        data: JSON.stringify(requestData),
        //data: formData,
        success:function(data) {
            console.log(JSON.parse(data).success);
        }
    }).done(function(response) {
            //console.log(response);
    });
});