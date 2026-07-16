<?php
require_once 'auth_check.php';

$valuePass = $_POST['valpass'];
$valueAction = $_POST['vact'];

 // echo $valuePass."<br>";
 // echo $valueAction."<br>";

include 'dbcon.php';
$record = null;
if (($valueAction == "recedit" || $valueAction == "recdel") && !empty($valuePass)) {
    $sql = "SELECT * FROM viewTasks WHERE pkind = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $valuePass);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $record = $result->fetch_assoc();
    }
    $stmt->close();
    // echo $sql;
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

<?php   // ---------------------------    Edit Record requested, present data for editing
if ($valueAction == "recedit") {
    ?>

<!-- Modal Edit Form -->
<div class="form-container">
<form action="nxaction.php" method="post">
  <div class="modal fade" id="editrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width: 100%; max-width: 900px;">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title" id="exampleModalLabel">Edit Task</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="display: grid; grid-template-columns: auto 1fr; gap: 4px; width: 100%; max-width: 800px;">
        
          <div style="grid-column: span 2; color: gray; font-size: 12px; margin-bottom: 4px;"><br></div>

          <label>Priority 2Day:</label>
          <input type="number" name="priority2day" style="width: 10%;" value="<?= htmlspecialchars($record['priority2day'] ?? '') ?>">

          <label>Description:</label>
          <input type="text" name="description" style="width: 60%;" value="<?= htmlspecialchars($record['description'] ?? '') ?>">

          <label>Urgency:</label>
          <input type="number" name="urgency" style="width: 10%;" value="<?= htmlspecialchars($record['urgency'] ?? '') ?>">

          <label>Status:</label>
<select name="status" id="statusSelect" style="width: 20%; background-color: #505050; color: #ffffff;" onchange="updateSelectStyle(this)">
  <?php
    require_once 'dbcon.php';
    $statusQuery = "SELECT * FROM datastatus";
    $statusResult = $conn->query($statusQuery);

    $currentStatus = $record['status'] ?? null;

   // echo '<option value="" data-bg="#505050" data-fc="#ffffff"' . (is_null($currentStatus) ? ' selected' : '') . '>-- Select Status --</option>';

    if ($statusResult && $statusResult->num_rows > 0) {
      while ($row = $statusResult->fetch_assoc()) {
        $bgColor = htmlspecialchars($row["color"]);
        $fontColor = htmlspecialchars($row["fcolor"]);
        $value = htmlspecialchars($row["pkindx"]);
        $label = htmlspecialchars($row["status"]);
        $selected = ($currentStatus == $row['pkindx']) ? 'selected' : '';
        echo '<option value="' . $value . '" data-bg="#' . $bgColor . '" data-fc="#' . $fontColor . '" style="background-color: #' . $bgColor . '; color: #' . $fontColor . ';" ' . $selected . '>' . $label . '</option>';
      }
    }
  ?>
</select>

<label>Category:</label>
<select name="elenav" id="categorySelect" style="width: 20%; background-color: #505050; color: #ffffff;" onchange="updateSelectStyle(this)">
  <?php
    $catQuery = "SELECT * FROM dataTaskCategories";
    $catResult = $conn->query($catQuery);

    $currentCat = $record['elenav'] ?? null;

    echo '<option value="" data-bg="#909090" data-fc="#ffffff"' . (is_null($currentCat) ? ' selected' : '') . '></option>';

    if ($catResult && $catResult->num_rows > 0) {
      while ($row = $catResult->fetch_assoc()) {
        $bgColor = htmlspecialchars($row["color"]);
        $fontColor = htmlspecialchars($row["fcolor"]);
        $value = htmlspecialchars($row["indx"]);
        $label = htmlspecialchars($row["name"]);
        $selected = ($currentCat == $row['indx']) ? 'selected' : '';
        echo '<option value="' . $value . '" data-bg="#' . $bgColor . '" data-fc="#' . $fontColor . '" style="background-color: #' . $bgColor . '; color: #' . $fontColor . ';" ' . $selected . '>' . $label . '</option>';
      }
    }
  ?>
</select>

<script>
  function updateSelectStyle(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const bg = selectedOption.getAttribute('data-bg') || '#505050';
    const fc = selectedOption.getAttribute('data-fc') || '#ffffff';
    selectElement.style.backgroundColor = bg;
    selectElement.style.color = fc;
  }

  window.addEventListener('DOMContentLoaded', () => {
    updateSelectStyle(document.getElementById('statusSelect'));
    updateSelectStyle(document.getElementById('categorySelect'));
  });
</script>



          <label>Project:</label>
          <input type="text" name="project" style="width: 50%;" value="<?= htmlspecialchars($record['project'] ?? '') ?>">

          <!-- Grey Divider Line -->
          <div style="grid-column: span 2; border-top: 1px solid #ccc; margin: 4px 0;"></div>

          <div style="grid-column: span 2; color: gray; font-size: 12px; margin-top: -4px; margin-bottom: 4px;"><br><br><br></div>

          <label>Narritive:</label>
          <textarea name="narritive" rows="4" style="resize: vertical; width: 100%;"><?= htmlspecialchars($record['narritive'] ?? '') ?></textarea>

          <label>Cryo Date:</label>
          <input type="date" name="cryodate" style="width: 20%;" value="<?= htmlspecialchars($record['cryo date'] ?? '') ?>">

          <label>Deadline:</label>
          <input type="date" name="deadline1" style="width: 20%;" value="<?= htmlspecialchars($record['deadline1'] ?? '') ?>">

          <label>Links:</label>
          <textarea name="links" rows="4" style="resize: vertical; width: 100%;"><?= htmlspecialchars($record['links'] ?? '') ?></textarea>

          <input type="hidden" id="valpass" name="valpass" value="<?= htmlspecialchars($valuePass) ?>">
          <input type="hidden" id="vact" name="vact" value="receditact">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close-footer">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </div>
  </div>
</form>
</div>


<?php // ----------------------------------------------------------------------
}
?>



<?php   // ------------------    Delete Recod requested, present data and confirmation for deleting 
if ($valueAction == "recdel") {
    ?>

<div class="form-container">
<form action="nxaction.php" method="post">
<div class="modal fade" id="delrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class="modal-title" id="exampleModalLabel">Sign off / Remove Task</h1>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x"> 
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <p><h2>You are requesting to Sign off (Remove) the <br><?= htmlspecialchars($record['description'] ?? '') ?> task. <br></h2></p><p>&nbsp</p>
                <div class="modal-body">
                    <h2>Are you absolutely sure?</h2>
                    <input type="hidden" id="valpass" name="valpass"  value="<?php echo $valuePass; ?>"><input type="hidden" id="vact" name="vact" value="recdelact">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close-footer">No, Close</button>
                    <button type="submit" class="btn btn-primary">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
</div>




<?php  // ----------------------------------------------------------------------
}
?>



<?php   // ---------------------------------- Push Edit action on Record
if ($valueAction == "receditact") {

    if (!empty($_POST['valpass'])) {
        // Sanitize and fetch form values
        $pkind = $_POST['valpass'];
        $priority2day = $_POST['priority2day'];
        $description = $_POST['description'];
        $urgency = $_POST['urgency'];
        $status = $_POST['status'];
        $elenav = $_POST['elenav'];
        $project = $_POST['project'];
        $narritive = $_POST['narritive'];
        $cryodate = $_POST['cryodate'];
        $deadline1 = $_POST['deadline1'];
        $links = $_POST['links'];

        if (strlen(trim($cryodate)) == 0) { $cryodate = null; }
        if (strlen(trim($deadline1)) == 0) { $deadline1 = null; }

        // Prepare and execute update query
        $stmt = $conn->prepare("
            UPDATE datatasks 
            SET 
                priority2day = ?, 
                description = ?, 
                urgency = ?, 
                status = ?, 
                elenav = ?, 
                project = ?, 
                narritive = ?, 
                `cryo date` = ?, 
                deadline1 = ?, 
                links = ?
            WHERE pkind = ?
        ");

        $stmt->bind_param(
            "isiiisssssi",
            $priority2day,
            $description,
            $urgency,
            $status,
            $elenav,
            $project,
            $narritive,
            $cryodate,
            $deadline1,
            $links,
            $pkind
        );

        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Task Updated</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 1000);</script>";
        } else {
            echo "<div class='form-container'><h2>Update Failed</h2><p>Error: " . $stmt->error . "</p></div>";
        }

        $stmt->close();
    } else {
        echo "<div class='form-container'><h2>Error</h2><p>No record ID (pkind) provided.</p></div>";
    }

}
// ----------------------------------------------------------------------
?>





<?php   // ----------------------------------    Push Delete action on Record
if ($valueAction == "recdelact") {
    
    if (!empty($valuePass)) {
        $stmt = $conn->prepare("UPDATE `datatasks` SET `priority2day`= 88888 WHERE `pkind`= ?");
        $stmt->bind_param("s", $valuePass);
        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Record Deleted Successfully</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 2000);</script>";
        } else {
            echo "<div class='form-container'><h2>Delete Failed</h2><p>MySQL had a meltdown: " . $stmt->error . "</p></div>";
        }
        $stmt->close();
    } else {
        echo "<div class='form-container'><h2>Error</h2><p>No valid CustomerId to delete. Whoopsie.</p></div>";
    }





// ----------------------------------------------------------------------
}
?>




<?php   // ----------------------------------    Template
if ($valueAction == "template") {
    ?>





<?php  // ----------------------------------------------------------------------
}
?>



<script>
    const closeX = document.getElementById('close-x');
    if (closeX) {
        closeX.addEventListener('click', function () {
            window.history.back();
        });
    }

    const closeFooter = document.getElementById('close-footer');
    if (closeFooter) {
        closeFooter.addEventListener('click', function () {
            window.history.back();
        });
    }


    document.addEventListener("DOMContentLoaded", function () {
    const statusSelect = document.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            const bgColor = selectedOption.style.backgroundColor || "#ffffff";
            const textColor = selectedOption.style.color || "#000000";
            this.style.backgroundColor = bgColor;
            this.style.color = textColor;
        });
    }
});

</script>


</body>
</html>