<!-- Shared DataTables setup: search, sort, pagination, and Copy/CSV/Print export.
     Include this once per list page (after the table markup), then call:
         initDataTable('#yourTableId');
     Excel/PDF export deliberately omitted — those need JSZip/pdfmake, which is
     extra weight and another thing that can silently fail to load. CSV covers
     the same "get it into a spreadsheet" need without that risk. -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.11/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/css/buttons.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.4.3/buttons.colVis.min.js"></script>

<style>
    /* Keep DataTables' default look close to the rest of the app rather than its stock blue theme */
    .dt-buttons .dt-button {
        background:#2980b9; color:#fff; border:none; border-radius:4px;
        padding:6px 12px; margin-right:6px; cursor:pointer; font-size:13px;
    }
    .dt-buttons .dt-button:hover { background:#21618c; }
    .dataTables_filter input { padding:6px; border:1px solid #ccc; border-radius:4px; margin-left:6px; }
    .dataTables_length select { padding:4px; border:1px solid #ccc; border-radius:4px; }
    table.dataTable thead th { background:#e07b1e; color:#fff; }
</style>

<script>
function initDataTable(selector, options) {
    return $(selector).DataTable(Object.assign({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'print', 'colvis'],
        pageLength: 25,
        order: [], // don't force-sort by first column; respect server order by default
    }, options || {}));
}
</script>
