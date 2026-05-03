<?php
require_once 'auth_check.php';
$pageTitle = 'Nx Lystz';

$valuePass   = $_POST['valpass'] ?? null;
$valueAction = $_POST['vact']    ?? null;
if (isset($_GET['c'])) {
    $valuePass = $_GET['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="DataTables/jquery-3.6.0.min.js"></script>
    <link  rel="stylesheet" href="DataTables/jquery.dataTables.min.css">
    <script src="DataTables/jquery.dataTables.min.js"></script>
    <link  rel="stylesheet" href="DataTables/buttons1.dataTables.min.css">
    <script src="DataTables/dataTables1.buttons.min.js"></script>
    <script src="DataTables/buttons.dataTables.min.js"></script>
    <script src="DataTables/jszip.min.js"></script>
    <script src="DataTables/pdfmake.min.js"></script>
    <script src="DataTables/vfs_fonts.js"></script>
    <script src="DataTables/buttons.html5.min.js"></script>
    <script src="DataTables/buttons.print.min.js"></script>
    <link  rel="stylesheet" href="Bootstrap/bootstrap.min.css">
    <link  rel="stylesheet" href="nxstyle.css">
    <title><?php echo $pageTitle; ?><?php echo $valuePass ? ' — '.htmlspecialchars($valuePass) : ''; ?></title>
</head>
<body>

<?php include 'nxmenu.php'; ?>

<div class="nx-page">

    <div class="nx-page-header">
        <div>
            <h2><?php echo $valuePass ? htmlspecialchars($valuePass) : htmlspecialchars($pageTitle); ?></h2>
            <p class="nx-timestamp"><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        <div class="nx-header-actions">
            <img src="img/icons8-refresh-48.png" class="nx-refresh" width="20" height="20" alt="Reload"
                 onclick="location.href = location.pathname;">
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#addrecord">Add Item</button>
        </div>
    </div>

    <?php if (isset($_GET['message'])): ?>
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:8px 14px;margin-bottom:12px;color:#856404;font-size:13px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
    <?php endif; ?>

    <div class="nx-table-card">
        <table id="myTable" class="display" style="width:100%"
               data-order='[[2,"asc"],[5,"asc"]]'>
            <thead>
                <tr>
                    <th>indx</th><th>lcode</th><th>in1</th><th>in2</th><th>in3</th>
                    <th>Name</th><th>dat1</th><th>dat2</th><th>dat3</th><th>dat4</th><th>&nbsp;</th>
                </tr>
                <tr>
                    <th><input type="text" placeholder="indx"></th>
                    <th><input type="text" placeholder="lcode"></th>
                    <th><input type="text" placeholder="in1"></th>
                    <th><input type="text" placeholder="in2"></th>
                    <th><input type="text" placeholder="in3"></th>
                    <th><input type="text" placeholder="Name"></th>
                    <th><input type="text" placeholder="dat1"></th>
                    <th><input type="text" placeholder="dat2"></th>
                    <th><input type="text" placeholder="dat3"></th>
                    <th><input type="text" placeholder="dat4"></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
        </table>
    </div>

</div><!-- /.nx-page -->


<!-- Add Item Modal -->
<form action="listrecordadd.php" method="post">
<div class="modal fade" id="addrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:860px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add List Item</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" style="display:grid;grid-template-columns:120px 1fr;gap:6px 10px;align-items:start;">

            <label style="padding-top:5px;">List:</label>
            <input type="text" name="lcode" style="width:200px;"
                   value="<?php echo htmlspecialchars($valuePass ?? ''); ?>">

            <label style="padding-top:5px;">Input 1:</label>
            <div><input type="text" name="in1" style="width:60px;"></div>

            <label style="padding-top:5px;">Input 2:</label>
            <div><input type="text" name="in2" style="width:60px;"></div>

            <label style="padding-top:5px;">Input 3:</label>
            <div><input type="text" name="in3" style="width:60px;"></div>

            <label style="padding-top:5px;">Name:</label>
            <input type="text" name="Name" style="width:100%;">

            <label style="padding-top:5px;">Data 1:</label>
            <textarea name="dat1" rows="6" style="width:100%;resize:vertical;"></textarea>

            <label style="padding-top:5px;">Data 2:</label>
            <textarea name="dat2" rows="6" style="width:100%;resize:vertical;"></textarea>

            <label style="padding-top:5px;">Data 3:</label>
            <textarea name="dat3" rows="4" style="width:100%;resize:vertical;"></textarea>

            <label style="padding-top:5px;">Data 4:</label>
            <textarea name="dat4" rows="4" style="width:100%;resize:vertical;"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="submit" class="btn btn-primary" name="AddRecord" value="Add Item">
        </div>
    </div>
</div>
</div>
</form>


<form action="listnxaction.php" method="post" name="actionForm" id="actionForm">
    <input type="hidden" id="valpass" name="valpass">
    <input type="hidden" id="vact"    name="vact">
</form>

<script>
function renderWithWhite999(data, type, row, meta) {
    if (data === 999 || data === "999") {
        const isEven = meta.row % 2 === 0;
        const bg = isEven ? '#ffffff' : '#edf1fb';
        return `<span style="color:${bg};">999</span>`;
    }
    return data;
}

$(document).ready(function () {
    $('#myTable').DataTable({
        ajax: {
            url:  "listlistdata-fetcher.php",
            type: "POST",
            data: function(d) { d.valpass = '<?php echo addslashes($valuePass ?? ''); ?>'; }
        },
        ordering: true,
        columns: [
            { data: "indx" },
            { data: "lcode" },
            {
                data: "in1",
                render: function(data) {
                    if (data != null && (data === 999 || data === "999"))
                        return `<span style="color:#edf1fb;">${data}</span>`;
                    return data;
                }
            },
            {
                data: "in2",
                render: function(data, type, row, meta) {
                    const color   = row.in2_color;
                    const rendered = renderWithWhite999(data, type, row, meta);
                    if (color && color.length === 6)
                        return `<div style="background-color:#${color};padding:2px;">${rendered}</div>`;
                    return rendered;
                }
            },
            {
                data: "in3",
                render: function(data, type, row, meta) {
                    const color   = row.in3_color;
                    const rendered = renderWithWhite999(data, type, row, meta);
                    if (color && color.length === 6)
                        return `<div style="background-color:#${color};padding:2px;">${rendered}</div>`;
                    return rendered;
                }
            },
            { data: "Name" },
            { data: "dat1", render: function(d) { return d && d.match(/^https?:\/\//) ? `<a href="${d}" target="_blank" rel="noopener noreferrer">${d}</a>` : d; } },
            { data: "dat2", render: function(d) { return d && d.match(/^https?:\/\//) ? `<a href="${d}" target="_blank" rel="noopener noreferrer">${d}</a>` : d; } },
            { data: "dat3", render: function(d) { return d && d.match(/^https?:\/\//) ? `<a href="${d}" target="_blank" rel="noopener noreferrer">${d}</a>` : d; } },
            { data: "dat4", render: function(d) { return d && d.match(/^https?:\/\//) ? `<a href="${d}" target="_blank" rel="noopener noreferrer">${d}</a>` : d; } },
            {
                data: null,
                render: function(data, type, row) {
                    return `<div class="action-buttons">
                        <button type="button" class="icon-button"
                            onmouseover="document.getElementById('valpass').value='${row.indx}';document.getElementById('vact').value='recedit';"
                            onclick="document.forms['actionForm'].submit();">
                            <img src="img/edit.png" alt="Edit">
                        </button>
                        <button type="button" class="icon-button"
                            onmouseover="document.getElementById('valpass').value='${row.indx}';document.getElementById('vact').value='recdel';"
                            onclick="document.forms['actionForm'].submit();">
                            <img src="img/del.png" alt="Delete">
                        </button>
                    </div>`;
                }
            }
        ],
        columnDefs: [
            { width: "20px",  targets: 0,  className: "text-center" },
            { width: "120px", targets: 1 },
            { width: "24px",  targets: 2,  className: "text-center" },
            { width: "24px",  targets: 3,  className: "text-center" },
            { width: "24px",  targets: 4,  className: "text-center" },
            { width: "240px", targets: 5 },
            { width: "200px", targets: 6 },
            { width: "200px", targets: 7 },
            { width: "200px", targets: 8 },
            { width: "200px", targets: 9 },
            { width: "48px",  targets: 10, orderable: false }
        ],
        dom: 'Blfrtip',
        pageLength: 50,
        lengthMenu: [[10,25,50,100],[10,25,50,100]],
        buttons: ['csv','excel','print'],
        initComplete: function () {
            this.api().columns().every(function () {
                const col = this;
                $('thead tr:eq(1) th:eq('+col.index()+') input').on('keyup change clear', function () {
                    if (col.search() !== this.value) col.search(this.value).draw();
                });
            });
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>
</html>
