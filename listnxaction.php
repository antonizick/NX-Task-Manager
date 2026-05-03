<?php
require_once 'auth_check.php';

$valuePass = $_POST['valpass'];
$valueAction = $_POST['vact'];

 // echo $valuePass."<br>";
 // echo $valueAction."<br>";

include 'dbcon.php';
$record = null;
if (($valueAction == "recedit" || $valueAction == "recdel" || $valueAction == "recdelact") && !empty($valuePass)) {

    $sql = "SELECT * FROM dataLists WHERE indx = ?";
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

<!-- Modal Edit Form (Updated with Correct Fields) -->
<div class="form-container">
<form action="listnxaction.php" method="post">
  <div class="modal fade" id="editrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width: 100%; max-width: 900px;">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title" id="exampleModalLabel">Edit List Item</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body" style="display: grid; grid-template-columns: auto 1fr; gap: 4px; width: 100%; max-width: 800px;">
          <div style="grid-column: span 2; color: gray; font-size: 12px; margin-bottom: 4px;"><br></div>

          <label>List:</label>
          <input type="text" name="lcode" style="width: 30%;" value="<?= htmlspecialchars($record['lcode'] ?? $valuePass) ?>">

          <label>Input 1:</label>
          <input type="text" name="in1" style="width: 5%;" value="<?= htmlspecialchars($record['in1'] ?? '999') ?>">

          <label>Input 2:</label>
          <input type="text" name="in2" style="width: 5%;" value="<?= htmlspecialchars($record['in2'] ?? '') ?>">

          <label>Input 3:</label>
          <input type="text" name="in3" style="width: 5%;" value="<?= htmlspecialchars($record['in3'] ?? '') ?>">

          <label>Name:</label>
          <input type="text" name="Name" style="width: 80%;" value="<?= htmlspecialchars($record['Name'] ?? '') ?>">

          <label>Data 1:</label>
          <textarea name="dat1" rows="8" style="width: 100%; resize: vertical;"><?= htmlspecialchars($record['dat1'] ?? '') ?></textarea>

          <label>Data 2:</label>
          <textarea name="dat2" rows="8" style="width: 100%; resize: vertical;"><?= htmlspecialchars($record['dat2'] ?? '') ?></textarea>

          <label>Data 3:</label>
          <textarea name="dat3" rows="4" style="width: 100%; resize: vertical;"><?= htmlspecialchars($record['dat3'] ?? '') ?></textarea>

          <label>Data 4:</label>
          <textarea name="dat4" rows="4" style="width: 100%; resize: vertical;"><?= htmlspecialchars($record['dat4'] ?? '') ?></textarea>

          <!-- Divider -->
          <div style="grid-column: span 2; border-top: 1px solid #ccc; margin: 4px 0;"></div>

          <div style="grid-column: span 2; color: gray; font-size: 12px; margin-top: -4px; margin-bottom: 4px;"><br><br><br></div>

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

<?php
}
?>




<?php   // ------------------    Delete Recod requested, present data and confirmation for deleting 
if ($valueAction == "recdel") {
    ?>

<div class="form-container">
<form action="listnxaction.php" method="post">
<div class="modal fade" id="delrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class="modal-title" id="exampleModalLabel">Sign off / Remove List Item</h1>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x"> 
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <p><h2>You are requesting to Sign off (Remove) the <br><?= htmlspecialchars($record['Name'] ?? '') ?> list item. <br></h2></p><p>&nbsp</p>
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


<?php
// ---------------------------------- Push Edit action on Record
if ($valueAction == "receditact") {

    if (!empty($_POST['valpass'])) {
        // Sanitize and fetch form values
        $pkind = $_POST['valpass'];
        $lcode = $_POST['lcode'];
        $in1 = $_POST['in1'];
        $in2 = $_POST['in2'];
        $in3 = $_POST['in3'];
        $name = $_POST['Name'];
        $dat1 = $_POST['dat1'];
        $dat2 = $_POST['dat2'];
        $dat3 = $_POST['dat3'];
        $dat4 = $_POST['dat4'];

        // Null-check and assign '.' if needed
        if (is_null($dat3)) {
            $dat3 = '.';
        }
        if (is_null($dat4)) {
            $dat4 = '.';
        }

        // Prepare and execute update query
        $stmt = $conn->prepare("
            UPDATE dataLists 
            SET 
                lcode = ?, 
                in1 = ?, 
                in2 = ?, 
                in3 = ?, 
                Name = ?, 
                dat1 = ?, 
                dat2 = ?,
                dat3 = ?, 
                dat4 = ?
            WHERE indx = ?
        ");

        $stmt->bind_param(
            "sssssssssi",
            $lcode,
            $in1,
            $in2,
            $in3,
            $name,
            $dat1,
            $dat2,
            $dat3,
            $dat4,
            $pkind
        );

        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Record Updated</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'listList.php?c=" . $lcode . "'; }, 1000);</script>";

            exit;
        } else {
            echo "<div class='form-container'><h2>Update Failed</h2><p>Error: " . $stmt->error . "</p></div>";
        }

        $stmt->close();
    } else {
        echo "<div class='form-container'><h2>Error</h2><p>No record ID (valpass) provided.</p></div>";
    }

}
// ----------------------------------------------------------------------
?>






<?php   // ----------------------------------    Push Delete action on Record
if ($valueAction == "recdelact") {
    
    if (!empty($valuePass)) {
        $stmt = $conn->prepare("UPDATE `dataLists` SET `in3`= 999 WHERE `indx`= ?");
        $stmt->bind_param("i", $valuePass);
        $lcode = $record['lcode'];
        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Record Deleted Successfully</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'listList.php?c=" . $lcode . "'; }, 1000);</script>";
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