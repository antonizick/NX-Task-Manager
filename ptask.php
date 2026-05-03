<?php
require_once 'auth_check.php';
$archiveMode = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive']) && $_POST['archive'] === 'archive') {
    $archiveMode = true;
}

$pageTitle = $archiveMode ? 'Nx TaskTracker (Personal) — Archive' : 'Nx TaskTracker (Personal)';
?>
<script>
  const ajaxUrl = "<?php echo $archiveMode ? 'parchive-fetcher.php' : 'pdata-fetcher.php'; ?>";
</script>
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

<?php $active_page = 'ptasks'; include 'nxmenu.php'; ?>

<div class="nx-page">

    <div class="nx-page-header">
        <div>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="nx-timestamp"><?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        <div class="nx-header-actions">
            <img src="img/icons8-refresh-48.png" class="nx-refresh" width="20" height="20" alt="Reload"
                 onclick="location.href = location.pathname;">
            <?php if (!$archiveMode): ?>
            <button class="nx-btn" type="button" data-toggle="modal" data-target="#addrecord">Add Task</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['message'])): ?>
    <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:8px 14px;margin-bottom:12px;color:#856404;font-size:13px;">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
    <?php endif; ?>

    <div class="nx-table-card">
        <table id="myTable" class="display" style="width:100%"
               data-order='[[1,"asc"],[4,"asc"],[5,"desc"],[3,"asc"],[0,"asc"]]'>
            <thead>
                <tr>
                    <th>I</th><th>P</th><th>Description</th><th>P2</th>
                    <th>Stat</th><th>HV</th><th>Links</th><th>&nbsp;</th>
                </tr>
                <tr>
                    <th><input type="text" placeholder="Index"></th>
                    <th><input type="text" placeholder="Priority"></th>
                    <th><input type="text" placeholder="Description"></th>
                    <th><input type="text" placeholder="Urgency"></th>
                    <th><input type="text" placeholder="Status"></th>
                    <th><input type="text" placeholder="HighVis"></th>
                    <th><input type="text" placeholder="Links"></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="nx-footer">
        <form id="archiveForm" action="ptask.php" method="POST" style="display:inline;">
            <input type="hidden" name="archive" value="archive">
            <a href="#" class="nx-footer-link"
               onclick="document.getElementById('archiveForm').submit(); return false;">[Archive]</a>
        </form>
    </div>

</div><!-- /.nx-page -->


<!-- Add Task Modal -->
<form action="precordadd.php" method="post">
<div class="modal fade" id="addrecord" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog" role="document" style="max-width:860px;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Personal Task</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" style="display:grid;grid-template-columns:160px 1fr;gap:6px 10px;align-items:center;">

            <label>Priority 2Day:</label>
            <div><input type="number" name="priority2day" style="width:70px;" value="999"></div>

            <label>Description:</label>
            <input type="text" name="description" style="width:100%;">

            <label>Urgency:</label>
            <div><input type="number" name="urgency" style="width:70px;" value="999"></div>

            <label>Status:</label>
            <select name="status" style="width:180px;color:#d4edbc;background-color:#11734b;">
            <?php
                require_once 'dbcon.php';
                $statusResult = $conn->query("SELECT * FROM datastatus");
                $defaultStatus = 3;
                while ($row = $statusResult->fetch_assoc()) {
                    $sel = ($row['pkindx'] == $defaultStatus) ? ' selected' : '';
                    echo '<option value="'.htmlspecialchars($row['pkindx']).'"'.$sel
                        .' style="background-color:#'.htmlspecialchars($row['color'])
                        .';color:#'.htmlspecialchars($row['fcolor']).';">'
                        .htmlspecialchars($row['status']).'</option>';
                }
            ?>
            </select>

            <label>HighVis:</label>
            <div><input type="number" name="elenav" style="width:70px;" value="0"></div>

            <label>Project:</label>
            <input type="text" name="project" style="width:100%;">

            <div style="grid-column:span 2;"><hr class="modal-divider"></div>

            <label>Narrative:</label>
            <textarea name="narritive" rows="4" style="width:100%;resize:vertical;"></textarea>

            <label>Cryo Date:</label>
            <div><input type="date" name="cryodate" style="width:160px;"></div>

            <label>Deadline:</label>
            <div><input type="date" name="deadline1" style="width:160px;"></div>

            <label>Links:</label>
            <textarea name="links" rows="3" style="width:100%;resize:vertical;"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <input type="submit" class="btn btn-primary" name="AddRecord" value="Add Task">
        </div>
    </div>
</div>
</div>
</form>


<form action="pnxaction.php" method="post" name="actionForm" id="actionForm">
    <input type="hidden" id="valpass" name="valpass">
    <input type="hidden" id="vact"    name="vact">
</form>

<script>
function renderWithWhite999(data, type, row, meta) {
    const n = Number(data);
    if (n === 999 || n === 88888) {
        const isEven = meta.row % 2 === 0;
        const bg = isEven ? '#ffffff' : '#edf1fb';
        return `<span style="color:${bg};">${data}</span>`;
    }
    return data;
}

$(document).ready(function () {
    const table = $('#myTable').DataTable({
        ajax: ajaxUrl,
        ordering: true,
        columns: [
            { data: "pkind" },
            {
                data: "priority2day",
                render: function(data, type, row, meta) {
                    const color = row.cc1;
                    const rendered = renderWithWhite999(data, type, row, meta);
                    if (color && color.length === 6)
                        return `<div style="background-color:#${color};padding:2px;">${rendered}</div>`;
                    return rendered;
                }
            },
            { data: "description" },
            {
                data: "urgency",
                render: function(data, type, row, meta) {
                    const color = row.cc2;
                    const rendered = renderWithWhite999(data, type, row, meta);
                    if (color && color.length === 6)
                        return `<div style="background-color:#${color};padding:2px;">${rendered}</div>`;
                    return rendered;
                }
            },
            {
                data: "status_text",
                render: function(data, type, row) {
                    const bg = row.status_color     ? '#'+row.status_color.replace(/^#/,'')     : '#fff';
                    const fc = row.status_font_color ? '#'+row.status_font_color.replace(/^#/,'') : '#000';
                    return `<div style="background-color:${bg};color:${fc};padding:1px 4px;border-radius:3px;">${data}</div>`;
                }
            },
            {
                data: "elenav",
                render: function(data, type, row, meta) {
                    if (data === 1 || data === "1")
                        return '<div style="background-color:red;color:white;padding:1px 4px;border-radius:3px;">'+data+'</div>';
                    const isEven = meta.row % 2 === 0;
                    const bg = isEven ? '#ffffff' : '#edf1fb';
                    return `<span style="color:${bg};">${data}</span>`;
                }
            },
            {
                data: "links",
                render: function(data) {
                    if (typeof data === 'string' && data.match(/^https?:\/\//))
                        return `<a href="${data}" target="_blank" rel="noopener noreferrer">${data}</a>`;
                    return data;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (ajaxUrl !== 'pdata-fetcher.php') return '&nbsp;';
                    return `<div class="action-buttons">
                        <button type="button" class="icon-button"
                            onmouseover="document.getElementById('valpass').value='${row.pkind}';document.getElementById('vact').value='recedit';"
                            onclick="document.forms['actionForm'].submit();">
                            <img src="img/edit.png" alt="Edit">
                        </button>
                        <button type="button" class="icon-button"
                            onmouseover="document.getElementById('valpass').value='${row.pkind}';document.getElementById('vact').value='recdel';"
                            onclick="document.forms['actionForm'].submit();">
                            <img src="img/del.png" alt="Delete">
                        </button>
                    </div>`;
                }
            }
        ],
        columnDefs: [
            { width: "24px",  targets: 0, className: "text-center" },
            { width: "24px",  targets: 1, className: "text-center" },
            { targets: 2 },
            { width: "24px",  targets: 3, className: "text-center" },
            { width: "70px",  targets: 4, className: "text-center" },
            { width: "24px",  targets: 5, className: "text-center" },
            { width: "280px", targets: 6 },
            { width: "48px",  targets: 7, orderable: false }
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

document.addEventListener('DOMContentLoaded', function () {
    const s = document.querySelector('select[name="status"]');
    if (s) s.addEventListener('change', function () {
        const o = this.options[this.selectedIndex];
        this.style.backgroundColor = o.style.backgroundColor || '#fff';
        this.style.color           = o.style.color           || '#000';
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
