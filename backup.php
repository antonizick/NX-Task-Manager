<?php require_once 'auth_check.php'; ?>
<?php $pageTitle = 'Nx Mx — Backups'; ?>
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

<?php $active_page = 'admin'; include 'nxmenu.php'; ?>

<div class="nx-admin-nav">
    <a href="mx.php">[Categories]</a>
    <a href="mx2.php">[Statuses]</a>
    <strong>[Backups]</strong>
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
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#uploadrecord">Restore From Upload</button>
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#addrecord">Create Backup</button>
        </div>
    </div>

    <p style="font-size:12px;color:#999;margin-top:-6px;">
        Keeps the last 10 backups. Older ones drop off automatically when a new one is made.
    </p>

    <div class="nx-table-card">
        <table id="myTable" class="display" style="width:100%" data-order='[[1,"desc"]]'>
            <thead>
                <tr>
                    <th>Name</th><th>Created</th><th>Size</th><th>&nbsp;</th>
                </tr>
            </thead>
        </table>
    </div>

</div><!-- /.nx-page -->


<!-- Create Backup Modal -->
<form action="backuprecordadd.php" method="post">
<div class="modal fade" id="addrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:480px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Create Backup</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <label for="label">Name (optional):</label>
            <input type="text" name="label" id="label" style="width:100%;" placeholder="e.g. before-cleanup" maxlength="60">
            <p style="font-size:12px;color:#999;margin-top:6px;">
                Saved as <code>&lt;timestamp&gt;_&lt;name&gt;.sql</code> — leave blank for a generic name.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="submit" class="btn btn-primary" name="AddRecord" value="Create Backup">
        </div>
    </div>
</div>
</div>
</form>

<!-- Restore From Upload Modal -->
<form action="backupaction.php" method="post" enctype="multipart/form-data" id="uploadForm">
<div class="modal fade" id="uploadrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:480px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Restore From Upload</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="vact" value="uploadrestore">
            <label for="backupfile">Backup file (.sql):</label>
            <input type="file" name="backupfile" id="backupfile" accept=".sql" required style="width:100%;">
            <p style="font-size:12px;color:#999;margin-top:10px;">
                <strong>This replaces all current data</strong> with the contents of the uploaded file.
                A safety backup of the current data is taken automatically first.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger" id="uploadSubmit">Restore</button>
        </div>
    </div>
</div>
</div>
</form>


<form action="backupaction.php" method="get" name="actionForm" id="actionForm">
    <input type="hidden" id="valpass" name="valpass">
    <input type="hidden" id="vact"    name="vact">
</form>

<script>
document.getElementById('uploadForm').addEventListener('submit', function (e) {
    if (!confirm('This will overwrite all current data with the uploaded file. Continue?')) {
        e.preventDefault();
    }
});

function humanSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

$(document).ready(function () {
    $('#myTable').DataTable({
        ajax: "backupdata-fetcher.php",
        ordering: true,
        columns: [
            { data: "name" },
            { data: "mtime" },
            { data: "size", render: function (data) { return humanSize(data); } },
            {
                data: null,
                render: function(data, type, row) {
                    return `<div class="action-buttons">
                        <a class="icon-button" href="backupdownload.php?file=${encodeURIComponent(row.name)}" title="Download">
                            <img src="img/edit.png" alt="Download">
                        </a>
                        <button type="button" class="icon-button" title="Restore"
                            onmouseover="document.getElementById('valpass').value='${row.name}';document.getElementById('vact').value='recrestore';"
                            onclick="document.forms['actionForm'].submit();">
                            <img src="img/del.png" alt="Restore">
                        </button>
                    </div>`;
                }
            }
        ],
        columnDefs: [
            { width: "220px", targets: 0 },
            { width: "140px", targets: 1 },
            { width: "80px",  targets: 2 },
            { width: "60px",  targets: 3, orderable: false }
        ],
        dom: 'lrti',
        pageLength: 50,
        lengthMenu: [[10,25,50,100],[10,25,50,100]]
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
