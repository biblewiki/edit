<!DOCTYPE html>
<html>

<head>
      <!-- Include JQUERY -->
      <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <form id="testformular" action="backend.php" method="POST">
        Name:<br>
        <input type="text" name="name"><input type="text" name="nameSource" placeholder="Quelle"><br>
        Beschreibung:<br>
        <input type="text" name="description"><br>
        Geschlecht: <br>
        <input type="radio" name="sex" value="m"> Mann 
        <input type="radio" name="sex" value="f"> Frau<br>
        Geburtsdatum:<br>
        <input type="text" name="description"><input type="checkbox" name="beforeChristBirth" value=true><br>
        Christ:<br>
        <input type="checkbox" name="belevier" value=true><br>
        Nur Stammbaum:<br>
        <input type="checkbox" name="familyTreeOnly" value=true><br>
        Text:<br>
        <textarea name="text" rows="4" cols="50">
At w3schools.com you will learn how to make a website. We offer free tutorials in all web development technologies.
        </textarea><br>
        <button type="submit" vname="save">Speichern</button>


    </form>
    <div id="server-results"></div>
    <script>
        $("#testformular").submit(function(event) {
            event.preventDefault(); //prevent default action 
            var post_url = $(this).attr("action"); //get form action url
            var request_method = $(this).attr("method"); //get form GET/POST method
            var form_data = $(this).serialize(); //Encode form elements for submission

            $.ajax({
                url: post_url,
                type: request_method,
                data: form_data,
                success:function(data) {
                    //alert(data);
                }
            }).done(function(response) { //
                $("#server-results").html(response);
            });
        });
    </script>

</body>

</html>