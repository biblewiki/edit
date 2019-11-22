/* global app */

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
                pagePrevText: app.getText('Zurück'),
                pageNextText: app.getText('Weiter'),
                pageFirstText: app.getText('Erste'),
                pageLastText: app.getText('Letzte'),
                pagerFormat: '{first} {prev} {pages} {next} {last}    {pageIndex} ' + app.getText('von') + ' {pageCount}',
                noDataContent: app.getText('Keine Einträge'),
                autoload: true,
                loadIndication: true,
                loadIndicationDelay: 500,
                loadMessage: app.getText('Daten werden geladen...'),
                loadShading: true,
                pageSize: 25,
                pageButtonCount: 5,
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

                    window.location.replace('entry?personId=' + getData.personId + '&version=' + getData.version);
                },
                fields: [
                    { name: 'personId', type: 'number', 'visible': false },
                    { name: 'version', type: 'number', 'visible': false },
                    { name: 'name', title: app.getText('Name'), type: 'text' },
                    { name: 'description', title: app.getText('Beschreibung'), type: 'text' },
                    { name: 'state', title: app.getText('Status'), type: 'select', valueField: 'state', textField: 'name', items: [
                        { name: '', state: 0 },
                        { name: 'In Bearbeitung', state: 10 },
                        { name: 'Zur Bearbeitung freigegeben', state: 20 },
                        { name: 'Freigegeben für Lektor', state: 30 },
                        { name: 'Wird kontrolliert', state: 40 },
                        { name: 'Freigegeben', state: 50 }
                    ]},
                    { name: 'author', title: app.getText('Author'), type: 'text', filtering: false },
                    { name: 'changeDate', title: app.getText('Bearbeitet'), type: 'date' }
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