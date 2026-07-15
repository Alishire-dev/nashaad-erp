<!-- Shared DataTables setup: search, sort, pagination, and Copy/Excel/PDF/CSV/Print export.
     Include this once per list page (after the table markup), then call:
         initDataTable('#yourTableId');
     Excel/PDF need JSZip/pdfmake (loaded below) — real weight, but included
     since matching the original's exact export set was explicitly requested. -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.11/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/css/buttons.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<style>
    /* Keep DataTables' default look close to the rest of the app rather than its stock blue theme */
    .dt-buttons .dt-button {
        background:#3a8fd6; color:#fff; border:none; border-radius:6px;
        padding:7px 14px; margin-right:6px; cursor:pointer; font-size:13px; font-weight:500;
        box-shadow:0 1px 3px rgba(0,0,0,.12); transition: background .15s ease;
    }
    .dt-buttons .dt-button:hover { background:#2f7ac0; }
    .dataTables_filter input {
        padding:8px 10px; border:1px solid #dde1e8; border-radius:6px; margin-left:8px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .dataTables_filter input:focus {
        outline:none; border-color:#e88a2e; box-shadow:0 0 0 3px #e88a2e22;
    }
    .dataTables_length select { padding:5px 8px; border:1px solid #dde1e8; border-radius:6px; }
    table.dataTable thead th { background:#e88a2e; color:#fff; }
    table.dataTable tbody td { color:#1a2036; border-bottom:1px solid #e2e5ea; }
    table.dataTable tbody tr:nth-child(even) { background:#f8f9fb; }
    table.dataTable tbody tr:hover td { background:#fdeee0; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background:#e88a2e !important; color:#fff !important; border-radius:5px; border:none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background:#fbeadb !important; border:none !important; color:#1a2036 !important;
    }
</style>

<script>
function initDataTable(selector, options) {
    return $(selector).DataTable(Object.assign({
        dom: 'Bfrtip',
        buttons: ['copy', 'excelHtml5', 'pdfHtml5', 'csv', 'print', 'colvis'],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, 1500, 2000],
                     [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, 1500, 2000]],
        order: [], // don't force-sort by first column; respect server order by default
    }, options || {}));
}
</script>
