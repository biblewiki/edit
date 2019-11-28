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
        
        if ($('#js-grid-relationship').length) {
            
            let persons = [{secondPersonId: '', name: ''}];
            let relationships  = [{relationshipId: '', name: ''}];
            
            let requestData = {
                function: 'Person.getForCombo',
                args: {}
            };

            $.ajax({
                type: 'POST',
                context: this,
                url: '../core/php/RequestHandler.php',
                data: JSON.stringify(requestData),
                dataType: 'json',
                async: false
            }).done(function(response) {
                // Alle Optionen zu Array hinzufügen
                response.rows.forEach(function(row) {
                    persons.push(row);
                });
            });
            
            requestData = {
                function: 'Person.getRelationship',
                args: {}
            };

            $.ajax({
                type: 'POST',
                context: this,
                url: '../core/php/RequestHandler.php',
                data: JSON.stringify(requestData),
                dataType: 'json',
                async: false
            }).done(function(response) {
                // Alle Optionen zu Array hinzufügen
                response.rows.forEach(function(row) {
                    relationships.push(row);
                });
            });

            $('#js-grid-relationship').jsGrid({
                height: '500px',
                width: '1000px',
                filtering: true,
                inserting: true,
                editing: true,
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
                            function: 'Person.getRelationshipGridData',
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
                    { name: 'secondPersonId', title: app.getText('Name'), type: 'select', valueField: 'personId', valueType: 'number', textField: 'name', items: persons },
                    { name: 'description', title: app.getText('Beschreibung'), type: 'text', editing: false, inserting: false },
                    { name: 'relationshipId', title: app.getText('Beziehungsart'), type: 'select', valueField: 'relationshipId', valueType: 'number', textField: 'name', items: relationships },
                    { name: 'fatherAge', title: app.getText('Alter') + ' ' + app.getText('Vater'), type: 'number' },
                    { type: 'control' }
                ]
            });
        }
    });
    
    // -------------------------------------------------------
    // Dropdowns füllen
    // -------------------------------------------------------
    
    // Dropdowns Personen
    $(function () {
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
            let options = [];

            // Bitte auswählen Option hinzufügen
            options.push('<option value="">' + app.getText('Bitte auswählen...') + '</option>');

            // Alle Optionen zu Array hinzufügen
            rows.forEach(function(row) {
                options.push('<option value="' + row.personId + '">' + row.name + '</option>');
            });
    
            // Personen Combos abfüllen
            if ($('#relationshipPerson').length) {
                $('#relationshipPerson').append(options);
            }
            if ($('#father').length) {
                $('#father').append(options);
            }
            if ($('#mother').length) {
                $('#mother').append(options);
            }
        });
    });
  

    // Dropdown Beziehungsart
    $(function () {
        let requestData = {
            function: 'Person.getRelationship',
            args: {}
        };

        $.ajax({
            url: '../core/php/RequestHandler.php',
            type: 'POST',
            data: JSON.stringify(requestData)
        }).done(function(data) {
                let rows = JSON.parse(data).rows;
                let options = [];

                // Bitte auswählen Option hinzufügen
                options.push('<option value="">' + app.getText('Bitte auswählen...') + '</option>');

                // Alle Optionen zu Array hinzufügen
                rows.forEach(function(row) {
                    options.push('<option value="' + row + '">' + row + '</option>');
                });

                // Beziehungs Combo abfüllen
                if ($('#relationshipType').length) {
                    $('#relationshipType').append(options);
                }
        });
    });
    
})(jQuery);