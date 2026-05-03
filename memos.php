<?php require_once 'auth_check.php'; ?>
<?php $pageTitle = 'Nx Memoz'; ?>
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

<?php $active_page = 'memos'; include 'nxmenu.php'; ?>

<div class="nx-page">

    <div class="nx-page-header">
        <div>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="nx-timestamp"><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        <div class="nx-header-actions">
            <img src="img/icons8-refresh-48.png" class="nx-refresh" width="20" height="20" alt="Reload"
                 onclick="location.href = location.pathname;">
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#addrecord">Add Memo</button>
        </div>
    </div>

    <?php if (isset($_GET['message'])): ?>
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:8px 14px;margin-bottom:12px;color:#856404;font-size:13px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
    <?php endif; ?>

    <div class="nx-table-card" style="max-width:700px;">
        <table id="myTable" class="display" style="width:100%" data-order='[[0,"asc"]]'>
            <thead>
                <tr><th>Name</th><th>&nbsp;</th></tr>
                <tr><th><input type="text" placeholder="Search memos"></th><th>&nbsp;</th></tr>
            </thead>
        </table>
    </div>

</div><!-- /.nx-page -->


<!-- Add Memo Modal -->
<form action="memoadd.php" method="post">
<div class="modal fade" id="addrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:860px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Memo</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" style="display:grid;grid-template-columns:120px 1fr;gap:6px 10px;align-items:start;">
            <label style="padding-top:5px;">Name:</label>
            <input type="text" name="Name" style="width:100%;">
            <label style="padding-top:5px;">Memo:</label>
            <textarea name="memo" rows="30" style="width:100%;resize:vertical;"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="submit" class="btn btn-primary" name="AddRecord" value="Add Memo">
        </div>
    </div>
</div>
</div>
</form>


<form action="memoshow.php" method="post" name="actionForm" id="actionForm">
    <input type="hidden" id="valpass" name="valpass">
    <input type="hidden" id="vact"    name="vact">
</form>

<script>
$(document).ready(function () {
    $('#myTable').DataTable({
        ajax: "memodata-fetcher.php",
        ordering: true,
        columns: [
            { data: "Name" },
            { data: null }
        ],
        columnDefs: [{
            targets: 1,
            orderable: false,
            render: function(data, type, row) {
                return `<img src="img/del.png" alt="Delete" style="width:14px;height:14px;cursor:pointer;"
                    onclick="deleteMemo(${row.indx})">`;
            }
        }],
        dom: 'lrti',
        pageLength: 50,
        lengthMenu: [[10,25,50,100],[10,25,50,100]],
        createdRow: function(row, data) {
            row.setAttribute('onmouseover',
                `document.getElementById('valpass').value='${data.indx}';document.getElementById('vact').value='memo';`);
            row.cells[0].style.cursor = 'pointer';
            row.cells[0].onclick = function() { document.forms['actionForm'].submit(); };
        },
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

function deleteMemo(indx) {
    if (!confirm('Delete this memo?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'memodelete.php';
    const i1 = document.createElement('input'); i1.type='hidden'; i1.name='indx';   i1.value=indx;
    const i2 = document.createElement('input'); i2.type='hidden'; i2.name='action'; i2.value='delete';
    form.appendChild(i1); form.appendChild(i2);
    document.body.appendChild(form);
    form.submit();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>
</html>
