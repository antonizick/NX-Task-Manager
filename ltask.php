<?php require_once 'auth_check.php'; ?>
<?php $pageTitle = 'NxLynx'; ?>
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

<?php $active_page = 'links'; include 'nxmenu.php'; ?>

<div class="nx-page">

    <div class="nx-page-header">
        <div>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="nx-timestamp"><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        <div class="nx-header-actions">
            <img src="img/icons8-refresh-48.png" class="nx-refresh" width="20" height="20" alt="Reload"
                 onclick="location.href = location.pathname;">
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#addrecord">Add Link</button>
        </div>
    </div>

    <div class="nx-table-card">
        <table id="myTable" class="display" style="width:100%"
               data-order='[[3,"asc"],[8,"asc"],[7,"asc"]]'>
            <thead>
                <tr>
                    <th>indx</th><th>Name</th><th>Category</th><th>SO</th>
                    <th>Description</th><th>Contact</th><th>Link</th><th>Tier</th><th>Class</th><th>&nbsp;</th>
                </tr>
                <tr>
                    <th><input type="text" placeholder="indx"></th>
                    <th><input type="text" placeholder="Name"></th>
                    <th><input type="text" placeholder="Category"></th>
                    <th><input type="text" placeholder="SO"></th>
                    <th><input type="text" placeholder="Description"></th>
                    <th><input type="text" placeholder="Contact"></th>
                    <th><input type="text" placeholder="Link"></th>
                    <th><input type="text" placeholder="Tier"></th>
                    <th><input type="text" placeholder="Class"></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
        </table>
    </div>

</div><!-- /.nx-page -->


<!-- Add Link Modal -->
<form action="lrecordadd.php" method="post">
<div class="modal fade" id="addrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:860px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Link Record</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" style="display:grid;grid-template-columns:160px 1fr;gap:6px 10px;align-items:center;">

            <label>Name:</label>
            <input type="text" name="name" style="width:100%;" required>

            <label>Link:</label>
            <input type="url" name="link" style="width:100%;" placeholder="https://example.com" required>

            <label>SO:</label>
            <div><input type="text" name="so" style="width:60px;color:#666;" value="25"></div>

            <div style="grid-column:span 2;"><hr class="modal-divider"></div>
            <div style="grid-column:span 2;font-size:12px;color:#999;">Optional fields</div>

            <label>Category:</label>
            <input type="text" name="category" style="width:100%;">

            <label>Description:</label>
            <input type="text" name="description" style="width:100%;">

            <label>Contact:</label>
            <input type="text" name="contact" style="width:100%;">

            <label>Tier:</label>
            <div><input type="text" name="tier" style="width:60px;color:#666;" value="1"></div>

            <label>Class:</label>
            <div><input type="text" name="lclass" style="width:60px;color:#666;" value="0"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="submit" class="btn btn-primary" name="AddRecord" value="Add Link">
        </div>
    </div>
</div>
</div>
</form>


<form action="lnxaction.php" method="post" name="actionForm" id="actionForm">
    <input type="hidden" id="valpass" name="valpass">
    <input type="hidden" id="vact"    name="vact">
</form>

<script>
$(document).ready(function () {
    $('#myTable').DataTable({
        ajax: "ldata-fetcher.php",
        columns: [
            { data: "indx" },
            { data: "Name" },
            { data: "Category" },
            { data: "SO" },
            { data: "Description" },
            { data: "contact" },
            {
                data: "Link",
                render: function(data) {
                    if (typeof data === 'string' && data.match(/^https?:\/\//))
                        return `<a href="${data}" target="_blank" rel="noopener noreferrer">${data}</a>`;
                    return data;
                }
            },
            { data: "tier" },
            { data: "lclass" },
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
        scrollX: true,
        columnDefs: [
            { width: "20px",  targets: 0, className: "text-center" },
            { width: "100px", targets: 1 },
            { width: "100px", targets: 2, className: "text-center" },
            { width: "20px",  targets: 3, className: "text-center" },
            { width: "200px", targets: 4 },
            { width: "60px",  targets: 5, className: "text-center" },
            { targets: 6 },
            { width: "30px",  targets: 7, className: "text-center" },
            { width: "30px",  targets: 8, className: "text-center" },
            { width: "48px",  targets: 9, orderable: false }
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
