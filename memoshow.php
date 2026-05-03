<?php
require_once 'auth_check.php';
$pageTitle   = 'Nx Memoz';
$valuePass   = $_POST['valpass'] ?? null;
$valueAction = $_POST['vact']    ?? null;

include 'dbcon.php';

// Handle update before any HTML output so header() works
if ($valueAction === 'receditact') {
    if (!empty($valuePass)) {
        $indx = $valuePass;
        $name = $_POST['Name'] ?? null;
        $memo = $_POST['memo'] ?? null;
        if ($name === '') $name = null;
        if ($memo === '') $memo = null;

        $stmt = $conn->prepare("UPDATE `dataMemo` SET `Name`=?, `memo`=? WHERE `indx`=?");
        $stmt->bind_param("ssi", $name, $memo, $indx);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: memos.php?message=Memo+updated.');
            exit;
        }
        $updateError = $stmt->error;
        $stmt->close();
    } else {
        $updateError = 'No record ID provided.';
    }
}

$record = null;
if ($valueAction === 'memo' && !empty($valuePass)) {
    $stmt = $conn->prepare("SELECT * FROM viewMemo WHERE indx = ?");
    $stmt->bind_param("i", $valuePass);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $record = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="Bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="nxstyle.css">
    <title><?php echo $pageTitle; ?></title>
</head>
<body>

<?php include 'nxmenu.php'; ?>

<div style="padding: 20px 28px;">

<?php if ($valueAction === 'memo'): ?>

    <div class="nx-detail-card">
        <h2><?php echo htmlspecialchars($record['Name'] ?? 'Edit Memo'); ?></h2>
        <form action="memoshow.php" method="post">
            <input type="hidden" name="indx"   value="<?php echo htmlspecialchars($record['indx']   ?? $valuePass); ?>">
            <input type="hidden" name="valpass" id="valpass" value="<?php echo htmlspecialchars($valuePass); ?>">
            <input type="hidden" name="vact"    id="vact"    value="receditact">

            <label>Name</label>
            <input type="text" name="Name"
                   value="<?php echo htmlspecialchars($record['Name'] ?? ''); ?>">

            <label>Memo</label>
            <textarea name="memo" rows="30"
                      style="resize:vertical;"><?php echo htmlspecialchars($record['memo'] ?? ''); ?></textarea>

            <div class="nx-detail-footer">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

<?php elseif ($valueAction === 'receditact'): ?>

    <div class="nx-detail-card">
        <h2>Update failed</h2>
        <p><?php echo htmlspecialchars($updateError ?? 'Unknown error.'); ?></p>
    </div>

<?php endif; ?>

</div>

</body>
</html>
