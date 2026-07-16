<?php require_once 'auth_check.php'; ?>
<?php $pageTitle = 'Nx Mx — Statuses'; ?>
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
    <title><?php echo $pageTitle; ?></title>
</head>
<body>

<?php include 'nxmenu.php'; ?>

<div class="nx-admin-nav">
    <a href="mx.php">[Categories]</a>
    <strong>[Statuses]</strong>
    <a href="backup.php">[Backups]</a>
</div>

<div class="nx-page">

    <div class="nx-page-header">
        <div>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="nx-timestamp"><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        <div class="nx-header-actions">
            <img src="img/icons8-refresh-48.png" class="nx-refresh" width="20" height="20" alt="Reload"
                 onclick="location.href = location.pathname;">
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#addrecord">Add Status</button>
        </div>
    </div>

    <?php if (isset($_GET['message'])): ?>
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:8px 14px;margin-bottom:12px;color:#856404;font-size:13px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
    <?php endif; ?>

    <div class="nx-table-card" style="max-width:700px;">
        <table id="myTable" class="display" style="width:100%" data-order='[[1,"asc"]]'>
            <thead>
                <tr>
                    <th>Index</th><th>Status</th><th>BG Color</th><th>Font Color</th><th>&nbsp;</th>
                </tr>
                <tr>
                    <th><input type="text" placeholder="Search"></th>
                    <th><input type="text" placeholder="Status"></th>
                    <th><input type="text" placeholder="BG color"></th>
                    <th><input type="text" placeholder="Font color"></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
        </table>
    </div>

</div><!-- /.nx-page -->


<!-- Add Status Modal -->
<form action="mx2recordadd.php" method="post">
<div class="modal fade" id="addrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:480px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Status</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" style="display:grid;grid-template-columns:180px 1fr;gap:6px 10px;align-items:center;">
            <div style="grid-column:span 2;font-size:12px;color:#999;margin-bottom:4px;">All fields required.</div>

            <label>Name:</label>
            <input type="text" name="name" id="name" style="width:100%;" required>

            <label>Background Color (hex):</label>
            <input type="text" name="color" id="color" style="width:90px;" placeholder="e.g. ff0000" required>

            <label>Font Color (hex):</label>
            <input type="text" name="fcolor" id="fcolor" style="width:90px;" placeholder="e.g. ffffff" required>

            <div style="grid-column:span 2;margin-top:8px;">
                <div id="preview" style="padding:8px 14px;border:1px solid #ddd;display:inline-block;border-radius:6px;">
                    <span id="previewText" style="font-weight:600;">Preview</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="submit" class="btn btn-primary" name="AddRecord" value="Add Status">
        </div>
    </div>
</div>
</div>
</form>


<form action="mx2action.php" method="post" name="actionForm" id="actionForm">
    <input type="hidden" id="valpass" name="valpass">
    <input type="hidden" id="vact"    name="vact">
</form>

<script>
const nameInput   = document.getElementById('name');
const colorInput  = document.getElementById('color');
const fcolorInput = document.getElementById('fcolor');
const previewBox  = document.getElementById('preview');
const previewText = document.getElementById('previewText');

function updatePreview() {
    const bg    = colorInput.value  || 'ffffff';
    const fg    = fcolorInput.value || '000000';
    const label = nameInput.value.trim() || 'Preview';
    previewBox.style.backgroundColor = '#' + bg;
    previewText.style.color = '#' + fg;
    previewText.textContent = label;
}
colorInput.addEventListener('input', updatePreview);
fcolorInput.addEventListener('input', updatePreview);
nameInput.addEventListener('input', updatePreview);
updatePreview();

$(document).ready(function () {
    $('#myTable').DataTable({
        ajax: "mx2data-fetcher.php",
        ordering: true,
        columns: [
            { data: "indx" },
            { data: "name" },
            { data: "color" },
            { data: "fcolor" },
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
            { width: "20px",  targets: 0 },
            { width: "260px", targets: 1 },
            { width: "80px",  targets: 2 },
            { width: "90px",  targets: 3 },
            { width: "48px",  targets: 4, orderable: false }
        ],
        dom: 'lrti',
        pageLength: 50,
        lengthMenu: [[10,25,50,100],[10,25,50,100]],
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
