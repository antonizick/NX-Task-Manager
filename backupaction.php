<?php
require_once 'auth_check.php';
require_once 'dbcon.php';
require_once 'lib/BackupManager.php';

$valuePass = $_POST['valpass'] ?? ($_GET['valpass'] ?? '');
$valueAction = $_POST['vact'] ?? ($_GET['vact'] ?? '');

$record = null;
if ($valueAction === 'recrestore' && !empty($valuePass)) {
    $record = BackupManager::find($valuePass);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="nxstyle.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 40px;
        }

        .form-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
            padding: 20px;
            max-width: 600px;
            width: 100%;
        }

        .modal-content { background: #fff; }

        .modal-header,
        .modal-footer {
            display: flex;
            align-items: center;
        }
        .modal-header { justify-content: space-between; }
        .modal-footer { justify-content: flex-end; gap: 10px; }

        .close {
            background: transparent;
            border: 0;
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            transition: filter .12s ease;
        }
        .btn:hover { filter: brightness(1.08); }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-danger {
            background-color: #c0392b;
            color: #fff;
        }
        .btn-danger:disabled { opacity: .5; cursor: not-allowed; filter: none; }

        body[data-theme="dark"] .form-container {
            background: #232730;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }
        body[data-theme="dark"] .modal-content { background: #232730; }
        body[data-theme="dark"] .btn-secondary { background-color: #4a5568; }
    </style>
</head>
<body>

<script>
(function() {
    if (localStorage.getItem('nx-theme') === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
    }
})();
</script>

<?php
// --------------------------- Restore requested, confirm before touching data
if ($valueAction === 'recrestore') {
    if (!$record) {
        echo "<div class='form-container'><h2>Error</h2><p>Backup file not found.</p></div>";
    } else {
?>
<div class="form-container">
<form action="backupaction.php" method="post">
  <div class="modal fade" id="restorerecord" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title">Restore Backup</h1>
        </div>
        <div class="modal-body">
          <p>You are about to restore <strong><?= htmlspecialchars($record['name']) ?></strong>.</p>
          <p><strong>This replaces all current data</strong> (tasks, links, lists, memos, categories, statuses)
             with the contents of this backup. A safety backup of the current data is taken automatically
             first, so this can be undone by restoring that one.</p>
          <p>Type <strong>RESTORE</strong> below to confirm:</p>
          <input type="text" id="confirmText" autocomplete="off" style="width:100%;padding:6px;box-sizing:border-box;">
          <input type="hidden" name="valpass" value="<?= htmlspecialchars($record['name']) ?>">
          <input type="hidden" name="vact" value="restoreact">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="close-footer">Cancel</button>
          <button type="submit" class="btn btn-danger" id="confirmBtn" disabled>Restore</button>
        </div>
      </div>
    </div>
  </div>
</form>
</div>
<script>
    const confirmText = document.getElementById('confirmText');
    const confirmBtn = document.getElementById('confirmBtn');
    confirmText.addEventListener('input', function () {
        confirmBtn.disabled = confirmText.value.trim() !== 'RESTORE';
    });
</script>
<?php
    }
}
?>

<?php
// ---------------------------------- Push restore of an existing backup
if ($valueAction === 'restoreact') {
    $record = BackupManager::find($valuePass);
    if (!$record) {
        echo "<div class='form-container'><h2>Error</h2><p>Backup file not found.</p></div>";
    } else {
        try {
            BackupManager::create($conn, 'pre-restore-safety');
            BackupManager::restore($conn, file_get_contents($record['path']));
            echo "<div class='form-container'><h2>Backup Restored</h2><p>Data restored from " . htmlspecialchars($record['name']) . ".</p></div>";
            echo "<script>setTimeout(() => { window.location.href = 'backup.php'; }, 1500);</script>";
        } catch (Throwable $e) {
            echo "<div class='form-container'><h2>Restore Failed</h2><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
        }
    }
}
?>

<?php
// ---------------------------------- Push restore of an uploaded backup file
if ($valueAction === 'uploadrestore') {
    if (empty($_FILES['backupfile']) || $_FILES['backupfile']['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='form-container'><h2>Error</h2><p>No valid file uploaded.</p></div>";
    } else {
        try {
            $sql = file_get_contents($_FILES['backupfile']['tmp_name']);
            BackupManager::create($conn, 'pre-restore-safety');
            BackupManager::restore($conn, $sql);
            echo "<div class='form-container'><h2>Backup Restored</h2><p>Data restored from uploaded file.</p></div>";
            echo "<script>setTimeout(() => { window.location.href = 'backup.php'; }, 1500);</script>";
        } catch (Throwable $e) {
            echo "<div class='form-container'><h2>Restore Failed</h2><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
        }
    }
}
?>

<script>
    const closeFooter = document.getElementById('close-footer');
    if (closeFooter) {
        closeFooter.addEventListener('click', function () {
            window.history.back();
        });
    }
</script>

</body>
</html>
