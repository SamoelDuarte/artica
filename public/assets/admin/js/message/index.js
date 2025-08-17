var url = window.location.origin;
$.fn.dataTableExt.sErrMode = 'none';
$('#table-message').DataTable({
    processing: true,
    serverSide: true,
    "ajax": {
        "url": url + "/mensagem/getMessage",
        "type": "GET"
    },
    "columns": [{
        "data": "device.name"
    }, {
        "data": "number"
    },
    {
        "data": "messagem"
    },
    {
        "data": "display_status"
    },
    {
        "data": "display_created_at"
    },

    {
        "data": "number"
    }
    ],
    'columnDefs': [
        {
            targets: [2],
            className: 'dt-body-center'
        }
    ],
    'rowCallback': function (row, data, index) {
        let btn = 'success';
        if (data['display_status'] == "Pendente") {
            btn = "warning";
        }
        
        // $('td:eq(0)', row).html('<div ><label>' + data['device.name'] + '</label></div>');
         $('td:eq(3)', row).html('<button class="btn btn-' + btn + '">' + data['display_status'] + '</button>');
         $('td:eq(5)', row).html('<a href="javascript:;" data-toggle="modal" onClick="configModalDelete(' + data["id"] + ')" data-target="#modalDelete" class="btn btn-sm btn-danger delete"><i class="far fa-trash-alt"></i></a>');


    }   ,orderCellsTop: true,
});

function configModalDelete(id) {
    $('#id_device').val(id);
}