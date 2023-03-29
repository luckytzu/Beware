/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import 'datatables.net';
import 'datatables.net-bs4';

$("#datatable").DataTable();

$('.ajaxVoirMateriel').on('click',function(e) {
    $.ajax({
        method:"POST",
        url:"/materiel/ajaxVoirMateriel/" + $(this).data('id'),
        success: function(resp){
            $('#idMateriel').html(resp['id']);
            $('#nomMateriel').html(resp['nom']);
            $('#prixMateriel').html(resp['prix']);
            $('#quantiteMateriel').html(resp['quantite']);
            $('#dateCreationMateriel').html(resp['DateCreation']);
        },
    })
});