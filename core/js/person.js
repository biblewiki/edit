/* global app */

(function ($) {

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
                rowClick: function(args) {
                    let getData = args.item;

                    window.location.href = 'entry?personId=' + getData.personId + '&version=' + getData.version;
                },
                fields: [
                    { name: 'personId', type: 'number', 'visible': false },
                    { name: 'version', type: 'number', 'visible': false },
                    { name: 'name', title: app.getText('Name'), type: 'text' },
                    { name: 'description', title: app.getText('Beschreibung'), type: 'text' },
                    { name: 'state', title: app.getText('Status'), type: 'select', valueField: 'state', textField: 'name', items: [
                        { name: '', state: '' },
                        { name: 'In Bearbeitung', state: 10 },
                        { name: 'Zur Bearbeitung freigegeben', state: 20 },
                        { name: 'Freigegeben für Lektor', state: 30 },
                        { name: 'Wird kontrolliert', state: 40 },
                        { name: 'Freigegeben', state: 50 }
                    ]},
                    { name: 'changeId', title: app.getText('Author'), type: 'text', filtering: false },
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