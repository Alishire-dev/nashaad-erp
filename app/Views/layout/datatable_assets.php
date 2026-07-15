<!-- Shared DataTables setup: search, sort, pagination, and Copy/Excel/PDF/CSV/Print export.
     Include this once per list page (after the table markup), then call:
         initDataTable('#yourTableId');

     IMPORTANT: these URLs were previously wrong — "datatables" on cdnjs only
     goes up to v2.3.7, so the v1.13.11 path 404'd silently, jQuery loaded
     fine but DataTables never attached to it, and every DataTables page
     quietly fell back to a plain unenhanced table with no visible error.
     Switched to the OFFICIAL DataTables CDN (cdn.datatables.net) for the
     core + Buttons extension, which is guaranteed to have every version
     path it advertises. jQuery via its own official CDN (code.jquery.com).
     JSZip/pdfmake versions below are a combination independently confirmed
     working together on the DataTables forums, not a guess. -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<style>
    /* Keep DataTables' default look close to the rest of the app rather than its stock blue theme */
    .dt-toolbar-length { margin-bottom: 10px; }
    .dt-toolbar-length select { padding:5px 8px; border:1px solid #dde1e8; border-radius:6px; }
    .dt-toolbar-actions {
        display:flex; justify-content:space-between; align-items:center;
        margin-bottom:12px; flex-wrap:wrap; gap:8px;
    }
    .dt-toolbar-actions .dt-buttons { display:flex; gap:6px; flex-wrap:wrap; }
    .dt-buttons .dt-button {
        background:#3a8fd6; color:#fff; border:none; border-radius:5px;
        padding:5px 10px; cursor:pointer; font-size:12px; font-weight:500;
        box-shadow:0 1px 3px rgba(0,0,0,.12); transition: background .15s ease;
    }
    .dt-buttons .dt-button:hover { background:#2f7ac0; }
    .dataTables_filter input {
        padding:7px 10px; border:1px solid #dde1e8; border-radius:6px; margin-left:8px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .dataTables_filter input:focus {
        outline:none; border-color:#e88a2e; box-shadow:0 0 0 3px #e88a2e22;
    }
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
    // Defensive: if DataTables itself never loaded (e.g. a CDN hiccup), fail
    // loudly in the console but don't throw — leave the plain table visible
    // and usable rather than a half-broken page.
    if (typeof $.fn.DataTable !== 'function') {
        console.error('DataTables failed to load — showing plain table for ' + selector);
        return null;
    }

    // Only offer Excel/PDF buttons if their libraries actually attached.
    // This is the fix for the exact failure mode that broke every page last
    // time: one bad dependency (wrong CDN path) threw inside DataTable()
    // and took the WHOLE table down with it, not just that one button.
    // exportOptions excludes the checkbox column (always first) and Action
    // column (always last) from every export — nobody wants an "Action"
    // column full of buttons/emoji showing up garbled in a PDF or Excel file.
    const exportOptions = { columns: ':not(:first-child):not(:last-child)' };
    const buttons = [
        { extend: 'copy', exportOptions },
        { extend: 'csv', exportOptions },
        { extend: 'print', exportOptions },
        'colvis',
    ];
    if (typeof JSZip !== 'undefined') buttons.splice(1, 0, { extend: 'excelHtml5', exportOptions });
    if (typeof pdfMake !== 'undefined') buttons.splice(2, 0, { extend: 'pdfHtml5', exportOptions });

    return $(selector).DataTable(Object.assign({
        // Custom layout, not the default 'Bfrtip': 'l' (Show X entries) was
        // MISSING entirely before, not just misplaced — this restores it on
        // its own row, then Buttons (left) + Search (right) share the row
        // below, matching the original's two-row toolbar arrangement.
        dom: '<"dt-toolbar-length"l><"dt-toolbar-actions"Bf>rtip',
        buttons: buttons,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, 1500, 2000],
                     [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, 1500, 2000]],
        order: [], // don't force-sort by first column; respect server order by default
    }, options || {}));
}
</script>
