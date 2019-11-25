/* global tinymce, app */


//Tinymce editor
if ($('#flowText').length) {
    tinymce.init({
        selector: '#flowText',
        height: 500,
        theme: 'silver',
        language: 'de',
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen'
        ],
        toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        toolbar2: 'print preview media | forecolor backcolor emoticons | codesample help',
        image_advtab: true,
        content_css: []
    });
}

// Levelbalken
if ($('#level').length) {
    $('#level').ionRangeSlider({
        min: 1,
        max: 10
    });
}


// Dropdown Person
if ($('#personId').length) {
    let requestData = {
        function: 'Person.getForCombo',
        args: {}
    };

    $.ajax({
        url: '../core/php/RequestHandler.php',
        type: 'POST',
        data: JSON.stringify(requestData)
    }).done(function(data) {
            let rows = JSON.parse(data).rows;
            
            $('#personId').append('<option value="">' + app.getText('Bitte auswählen...') + '</option>');
            
            rows.forEach(function(row) {
                $('#personId').append('<option value=' + row.personId + '>' + row.name + '</option>');
            });
    });
}

// Dropdown Beziehungsart
if ($('#relationshipPerson').length) {
    let requestData = {
        function: 'Person.getRelationshipForCombo',
        args: {}
    };

    $.ajax({
        url: '../core/php/RequestHandler.php',
        type: 'POST',
        data: JSON.stringify(requestData)
    }).done(function(data) {
            let rows = JSON.parse(data).rows;

            $('#relationshipPerson').append('<option value="">' + app.getText('Bitte auswählen...') + '</option>');

            rows.forEach(function(row) {
                $('#relationshipPerson').append('<option value="' + row + '">' + row + '</option>');
            });
    });
}

// Formular übermitteln
$("#biwi-form").submit(function(event) {
    event.preventDefault(); //prevent default action 
    let func = $(this).attr("action"); //get form action url
    let requestMethod = $(this).attr("method"); //get form GET/POST method
    let formData = $(this).serializeArray(); //Encode form elements for submission

    let requestData = {
        function: func,
        args: { formPacket: formData }
    };

    $.ajax({
        url: '../core/php/RequestHandler.php',
        type: requestMethod,
        data: JSON.stringify(requestData)
    }).done(function(data) {
        data = JSON.parse(data);
        if (data.success) {
            'use strict';
            $.toast({
              heading: 'Gespeichert',
              text: 'Erfolgreich gespeichert.',
              showHideTransition: 'slide',
              loader: false,
              hideAfter: 8000,
              icon: 'success',
              position: 'top-right'
            })
        }
    });
});




$(document).ready( function () {
    let params = _getAllUrlParams();

    Object.keys(params).forEach(function(key) {
       if (key.includes('id')) {
           let cls = _capitalizeFLetter(key.replace('id', ''));
           let id = params[key];

           let requestData = {
                function: cls + '.getFormData',
                args: {id: id}
            };

            $.ajax({
                url: '../core/php/RequestHandler.php',
                type: 'POST',
                data: JSON.stringify(requestData),
                //data: formData,
                success:function(data) {
                    _fillFormFromData("#biwi-form", JSON.parse(data).row);
                }
            }).done(function(response) {
                    //console.log(response);
            });
       }
    });
});


/**
 * Private Funktionen
 */

function _capitalizeFLetter(str) { 
    return str[0].toUpperCase() + str.slice(1); 
}
  
function _getAllUrlParams(url) {

  // get query string from url (optional) or window
  let queryString = url ? url.split('?')[1] : window.location.search.slice(1);

  // we'll store the parameters here
  let obj = {};

  // if query string exists
  if (queryString) {

    // stuff after # is not part of query string, so get rid of it
    queryString = queryString.split('#')[0];

    // split our query string into its component parts
    let arr = queryString.split('&');

    for (let i = 0; i < arr.length; i++) {
      // separate the keys and the values
      let a = arr[i].split('=');

      // set parameter name and value (use 'true' if empty)
      let paramName = a[0];
      let paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

      // (optional) keep case consistent
      paramName = paramName.toLowerCase();
      if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();

      // if the paramName ends with square brackets, e.g. colors[] or colors[2]
      if (paramName.match(/\[(\d+)?\]$/)) {

        // create key if it doesn't exist
        let key = paramName.replace(/\[(\d+)?\]/, '');
        if (!obj[key]) obj[key] = [];

        // if it's an indexed array e.g. colors[2]
        if (paramName.match(/\[\d+\]$/)) {
          // get the index value and add the entry at the appropriate position
          let index = /\[(\d+)\]/.exec(paramName)[1];
          obj[key][index] = paramValue;
        } else {
          // otherwise add the value to the end of the array
          obj[key].push(paramValue);
        }
      } else {
        // we're dealing with a string
        if (!obj[paramName]) {
          // if it doesn't exist, create property
          obj[paramName] = paramValue;
        } else if (obj[paramName] && typeof obj[paramName] === 'string'){
          // if property does exist and it's a string, convert it to an array
          obj[paramName] = [obj[paramName]];
          obj[paramName].push(paramValue);
        } else {
          // otherwise add the property
          obj[paramName].push(paramValue);
        }
      }
    }
  }

  return obj;
}

function _fillFormFromData(frm, data) {
    $.each(data, function(key, value) {  
        let ctrl = $('[name='+key+']', frm);  
        switch(ctrl.prop("type")) { 
            case "radio": case "checkbox":   
                ctrl.each(function() {
                    if($(this).attr('value') == value) {$(this).attr("checked",value);}
                });   
                break;
            case 'textarea':
                tinymce.activeEditor.setContent(value);
            default:
                ctrl.val(value); 
        }  
    });  
}
