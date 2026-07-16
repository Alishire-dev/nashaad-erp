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
    /* The actual root cause of the 3-line wrap: this app's global form
       styles (label{display:block}, select/input{width:100%}) bleed into
       DataTables' own internal "Show <select> entries" and search markup,
       since those are real <label>/<select>/<input> elements too. Previous
       attempts added clearfix/flex wrappers around the SYMPTOM without
       neutralizing this at the source — these targeted overrides do that
       directly, with !important since the global rule also has none but
       loads earlier in the cascade. */
    .dataTables_length label, .dataTables_filter label {
        display:inline-block !important; font-weight:normal !important;
        margin:0 !important; color:#2c3038 !important; font-size:14px !important;
    }
    .dataTables_length select { width:auto !important; display:inline-block !important; margin:0 4px; }
    .dataTables_filter input { width:200px !important; display:inline-block !important; margin-left:6px; }

    /* Single row, matching the original: length (left) + buttons (left,
       right after) + search (far right) all share one line via float. */
    .dataTables_wrapper .dataTables_length { float:left; }
    .dataTables_wrapper .dataTables_filter { float:right; }
    .dt-buttons { float:left; margin-left:20px; }
    .dataTables_wrapper::after { content:""; display:table; clear:both; }

    .dt-buttons .dt-button {
        background:#3a8fd6; color:#fff; border:none; border-radius:5px;
        padding:5px 10px; cursor:pointer; font-size:12px; font-weight:500;
        margin-right:6px; box-shadow:0 1px 3px rgba(0,0,0,.12); transition: background .15s ease;
    }
    .dt-buttons .dt-button:hover { background:#2f7ac0; }
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
        // Plain dom, no custom wrapper divs — those added fragility across
        // two prior attempts. DataTables' own float-based default layout
        // for l/B/f works correctly once the global CSS bleed (above) is
        // actually neutralized at the source.
        dom: 'lBfrtip',
        buttons: buttons,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, 1500, 2000],
                     [10, 25, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 1000, 1500, 2000]],
        order: [], // don't force-sort by first column; respect server order by default
    }, options || {}));
}
</script>
