(function ($) {
    'use strict';

    // -------------------------------------------------------
    // Add new Person
    // -------------------------------------------------------

    /*Tinymce editor*/
    if ($('#flowText').length) {
        tinymce.init({
            selector: '#flowText',
            height: 500,
            theme: 'silver',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code fullscreen'
            ],
            toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            toolbar2: 'print preview media | forecolor backcolor emoticons | codesample help',
            image_advtab: true,
            templates: [{
                    title: 'Test template 1',
                    content: 'Test 1'
                },
                {
                    title: 'Test template 2',
                    content: 'Test 2'
                }
            ],
            content_css: []
        });
    }

    if ($('#level').length) {
        $('#level').ionRangeSlider({
            min: 1,
            max: 10
        });
    }


    // -------------------------------------------------------
    // Load Person Grid
    // Dokumentation : http://js-grid.com/docs/
    // -------------------------------------------------------
    $(function () {
        //basic config
        if ($('#js-grid').length) {
            $('#js-grid').jsGrid({
                height: 'auto',
                width: '100%',
                filtering: true,
                inserting: false,
                editing: false,
                sorting: true,
                paging: true,
                pagePrevText: 'Zurück',
                pageNextText: 'Weiter',
                pageFirstText: 'Erste',
                pageLastText: 'Letzte',
                pagerFormat: '{first} {prev} {pages} {next} {last}    {pageIndex} von {pageCount}',
                noDataContent: 'Keine Einträge',
                autoload: true,
                loadIndication: true,
                loadIndicationDelay: 500,
                loadMessage: 'Daten werden geladen...',
                loadShading: true,
                pageSize: 25,
                pageButtonCount: 5,
                deleteConfirm: 'Do you really want to delete person?',
                controller: {
                    loadData: function(filter) {
                        let requestData = {
                            function: 'Person.getGridData',
                            args: {filter: filter}
                        };
                        let data = $.Deferred();
                        $.ajax({
                            type: 'POST',
                            url: '../core/php/RequestHandler.php',
                            data: JSON.stringify(requestData),
                            dataType: 'json'
                        }).done(function(response) {
                            data.resolve(response.rows);
                        });
                      return  data.promise();
                    }
                },
                rowDoubleClick: function(args) {
                    let getData = args.item;
                    let keys = Object.keys(getData);
                    let text = [];

                    $.each(keys, function(idx, value) {
                      text.push(value + ' : ' + getData[value])
                    });

                    alert(text.join(', '))
                },
                fields: [
                    { name: 'personId', type: 'number', 'visible': false},
                    { name: 'version', type: 'number', 'visible': false},
                    { name: 'name', title: 'Name', type: 'text', width: 40, filtering: true, css:'js-cell'},
                    { name: 'believer', title: 'Christ', type: 'checkbox', width: 40, filtering: true, css:'js-cell'}
                    //{ type: 'control' }
                ]
            });
        }

        if ($('#sort').length) {
            $('#sort').on('click', function () {
                var field = $('#sortingField').val();
                $('#js-grid-sortable').jsGrid('sort', field);
            });
        }
    });
})(jQuery);