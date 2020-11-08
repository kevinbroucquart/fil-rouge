$(document).ready(function () {


  $("#ajout_session_adresse").autocomplete({
    source: function (request, response) {
      $.ajax({
        url: 'https://api-adresse.data.gouv.fr/search/', // on appelle le script JSON
        data: { q: request.term },
        dataType: "json",

        //Recupération des coordonnées pour Open Street Map
        success: function (data) {
          var adresse = [];
          response($.map(data.features, function (item) {
            if ($.inArray(item.properties.name, adresse) == -1) {
              adresse.push(item.properties.label);
              return {
                label: item.properties.label, // on retourne cette forme de suggestion
                city: item.properties.city,
                coordinates: item.geometry.coordinates[0],
                coordinates2: item.geometry.coordinates[1],
                value: item.properties.label,
              };
            }
          }));
        }
      });
    },
    
    // On remplit aussi l'input coordonnees afin d'établir le marqueur sur la carte lors de la validation
    select: function (event, ui) {
      $('#ajout_session_longitude').val(ui.item.coordinates);
      $('#ajout_session_latitude').val(ui.item.coordinates2);
      $('#ajout_session_ville').val(ui.item.city);


    }

  });
});
