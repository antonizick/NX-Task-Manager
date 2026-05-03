<?php
require_once 'auth_check.php';

$valuePass = $_POST['valpass'];
$valueAction = $_POST['vact'];

/*
 echo $valuePass."<br>";
 echo $valueAction."<br>";
*/

include 'dbcon.php';
$record = null;
if (($valueAction == "recedit" || $valueAction == "recdel") && !empty($valuePass)) {
    $sql = "SELECT * FROM viewTaskCategories WHERE indx = ?";
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
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 40px; /* Add this */
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-header h2 {
            margin: 0 0 10px;
        }

        .form-header .info {
            font-size: 0.9rem;
            color: #555;
        }

        .form-container {
            background: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
            padding: 20px;
            max-width: 600px;
            width: 100%;
        }

        .modal-header,
        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
        }

        .modal-footer {
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .modal-body label {
            margin-top: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>


<?php
// --------------------------- Edit Record requested, present data for editing
if ($valueAction == "recedit") {
?>
<!-- Modal Edit Form -->
<div class="form-container">
<form action="mxaction.php" method="post">
  <div class="modal fade" id="editrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width: 100%; max-width: 500px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Edit Record</h5>
          <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x">
            <span aria-hidden="true">&times;</span>
          </button> -->
        </div>

        <div class="modal-body" style="display: grid; grid-template-columns: auto 1fr; gap: 4px; width: 100%; max-width: 800px;">
          
          <!-- Light Gray Note -->
          <div style="grid-column: span 2; color: gray; font-size: 12px; margin-bottom: 4px;">
            Update the fields below and hit save when you're done pretending to be productive.
          </div>

          <label for="name">Name:</label>
          <input type="text" name="name" id="edit_name" style="width: 30%;" value="<?= htmlspecialchars($record['name'] ?? '') ?>" required>

          <label for="color">Background Color (Hex):</label>
          <input type="text" name="color" id="edit_color" style="width: 20%;" value="<?= htmlspecialchars($record['color'] ?? '') ?>" placeholder="e.g., ff0000" required>

          <label for="fcolor">Font Color (Hex):</label>
          <input type="text" name="fcolor" id="edit_fcolor" style="width: 20%;" value="<?= htmlspecialchars($record['fcolor'] ?? '') ?>" placeholder="e.g., ffffff" required>

          <!-- Live Preview Section -->
          <div style="grid-column: span 2; margin-top: 10px;">
            <div id="edit_preview" style="padding: 10px; border: 1px solid #ccc; display: inline-block; border-radius: 8px; margin-top: 10px;">
              <span id="edit_previewText" style="font-weight: bold;">Preview</span>
            </div>
          </div>

          <script>
            const editNameInput = document.getElementById('edit_name');
            const editColorInput = document.getElementById('edit_color');
            const editFColorInput = document.getElementById('edit_fcolor');
            const editPreviewBox = document.getElementById('edit_preview');
            const editPreviewText = document.getElementById('edit_previewText');

            function updateEditPreview() {
              const bg = editColorInput.value || 'ffffff';
              const fg = editFColorInput.value || '000000';
              const name = editNameInput.value.trim() || 'Preview';

              editPreviewBox.style.backgroundColor = '#' + bg;
              editPreviewText.style.color = '#' + fg;
              editPreviewText.textContent = name;
            }

            // Attach preview update listeners
            editNameInput.addEventListener('input', updateEditPreview);
            editColorInput.addEventListener('input', updateEditPreview);
            editFColorInput.addEventListener('input', updateEditPreview);

            // Initial load
            window.addEventListener('DOMContentLoaded', updateEditPreview);
          </script>

          <input type="hidden" name="valpass" value="<?= htmlspecialchars($record['indx'] ?? '') ?>">
          <input type="hidden" name="vact" value="editrecordact">
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
<form action="mxaction.php" method="post">
<div class="modal fade" id="delrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class="modal-title" id="exampleModalLabel">Remove Category</h1>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x"> 
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <p><h2>You are requesting to Remove the <br><?= htmlspecialchars($record['name'] ?? '') ?> category. <br></h2></p><p>&nbsp</p>
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
if ($valueAction == "editrecordact") {

    if (!empty($_POST['valpass'])) {
        // Grab and sanitize the values
        $indx = $_POST['valpass'];
        $name = trim($_POST['name']);
        $color = trim($_POST['color']);
        $fcolor = trim($_POST['fcolor']);

        // Prepare SQL update
        $stmt = $conn->prepare("
            UPDATE `viewTaskCategories` 
            SET `name` = ?, `color` = ?, `fcolor` = ? 
            WHERE `indx` = ?
        ");

        // Bind the parameters (s = string, i = integer)
        $stmt->bind_param("sssi", $name, $color, $fcolor, $indx);

        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Category Updated</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'mx.php'; }, 1000);</script>";
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
        $stmt = $conn->prepare("UPDATE `dataTaskCategories` SET `rowstat`= 88888 WHERE `indx`= ?");
        $stmt->bind_param("s", $valuePass);
        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Category Deleted Successfully</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'mx.php'; }, 2000);</script>";
        } else {
            echo "<div class='form-container'><h2>Delete Failed</h2><p>MySQL had a meltdown: " . $stmt->error . "</p></div>";
        }
        $stmt->close();
    } else {
        echo "<div class='form-container'><h2>Error</h2><p>No valid Id to delete. Whoopsie.</p></div>";
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