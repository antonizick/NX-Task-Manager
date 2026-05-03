<?php
require_once 'auth_check.php';

$valuePass = $_POST['valpass'];
$valueAction = $_POST['vact'];

 // echo $valuePass."<br>";
 // echo $valueAction."<br>";

include 'dbcon.php';
$record = null;
if (($valueAction == "recedit" || $valueAction == "recdel") && !empty($valuePass)) {
    $sql = "SELECT * FROM viewNxlinks WHERE indx = ?";
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

<?php   // ---------------------------    Edit Record requested, present data for editing
if ($valueAction == "recedit") {
    ?>

<!-- Modal Edit Form -->
<div class="form-container">
<form action="lnxaction.php" method="post">
  <div class="modal fade" id="editrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="width: 100%; max-width: 900px;">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title" id="exampleModalLabel">Edit Link</h2>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="display: grid; grid-template-columns: auto 1fr; gap: 4px; width: 100%; max-width: 800px;">
        
          <div style="grid-column: span 2; color: gray; font-size: 12px; margin-bottom: 4px;"><br></div>

          <label for="name">Name:</label>
          <input type="text" name="name" id="name" style="width: 60%;"  value="<?= htmlspecialchars($record['Name'] ?? '') ?>" required>

          <label for="link">Link:</label>
          <input type="url" name="link" id="link" style="width: 100%;" placeholder="https://example.com" value="<?= htmlspecialchars($record['Link'] ?? '') ?>"  required>

          <label for="so">SO:</label>
          <input type="text" name="so" id="so" style="width: 5%;  color: gray; "  value="<?= htmlspecialchars($record['SO'] ?? '') ?>">


          <!-- Divider -->
          <div style="grid-column: span 2; border-top: 1px solid #ccc; margin: 10px 0;"></div>

          <!-- Optional Fields Note -->
          <div style="grid-column: span 2; color: gray; font-size: 12px;">
            Optional Fields
          </div>

          <label for="category">Category:</label>
          <input type="text" name="category" id="category" style="width: 40%;" value="<?= htmlspecialchars($record['Category'] ?? '') ?>">

          <label for="description">Description:</label>
          <input type="text" name="description" id="description" style="width: 100%;" value="<?= htmlspecialchars($record['Description'] ?? '') ?>" >

          <label for="contact">Contact:</label>
          <input type="text" name="contact" id="contact" style="width: 40%;" value="<?= htmlspecialchars($record['contact'] ?? '') ?>">

          <label for="tier">Tier:</label>
          <input type="text" name="tier" id="tier" style="width: 5%; color: gray;" value="<?= htmlspecialchars($record['tier'] ?? '') ?>">

          <label for="lclass">Class:</label>
          <input type="text" name="lclass" id="lclass" style="width: 5%; color: gray;"  value="<?= htmlspecialchars($record['lclass'] ?? '') ?>">

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
<form action="lnxaction.php" method="post">
<div class="modal fade" id="delrecord" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class="modal-title" id="exampleModalLabel">Remove Link</h1>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="close-x"> 
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <p><h2>You are requesting to Remove the </h2><h1><?= htmlspecialchars($record['Name'] ?? '') ?></h1> <h2> link. <br></h2></p><p>&nbsp</p>
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
// --------------------------- Push Edit action on Link Record --------------------------
if ($valueAction == "receditact") {

    if (!empty($_POST['valpass'])) {
        require_once 'dbcon.php';

        // Fetch and sanitize form values
        $indx        = $_POST['valpass']; // Assuming this is the primary key
        $name        = $_POST['name'];
        $link        = $_POST['link'];
        $so          = $_POST['so'];
        $category    = $_POST['category'];
        $description = $_POST['description'];
        $contact     = $_POST['contact'];
        $tier        = $_POST['tier'];
        $lclass      = $_POST['lclass'];

        // Optional: Clean up empty fields
        function resolvNulls(&$var) {
            if (strlen(trim($var)) === 0) {
                $var = null;
            }
        }

        resolvNulls($so);
        resolvNulls($category);
        resolvNulls($contact);
        resolvNulls($tier);
        resolvNulls($lclass);

        // Prepare and execute update statement
        $stmt = $conn->prepare("
            UPDATE `dataNxlinks`
            SET 
                `Name` = ?, 
                `Category` = ?, 
                `SO` = ?, 
                `Description` = ?, 
                `contact` = ?, 
                `Link` = ?, 
                `tier` = ?, 
                `lclass` = ?
            WHERE `indx` = ?
        ");

        $stmt->bind_param(
            "ssisssssi",
            $name,
            $category,
            $so,
            $description,
            $contact,
            $link,
            $tier,
            $lclass,
            $indx
        );

        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Link Record Updated</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'ltask.php'; }, 1000);</script>";
        } else {
            echo "<div class='form-container'><h2>Update Failed</h2><p>Error: " . htmlspecialchars($stmt->error) . "</p></div>";
        }

        $stmt->close();
        $conn->close();

    } else {
        echo "<div class='form-container'><h2>Error</h2><p>No record ID (valpass) provided.</p></div>";
    }
}
// ----------------------------------------------------------------------
?>





<?php   // ----------------------------------    Push Delete action on Record
if ($valueAction == "recdelact") {
    
    if (!empty($valuePass)) {
        $stmt = $conn->prepare("UPDATE `dataNxlinks` SET `lclass`= 88 WHERE `indx`= ?");
        $stmt->bind_param("s", $valuePass);
        if ($stmt->execute()) {
            echo "<div class='form-container'><h2>Link Deleted Successfully</h2></div>";
            echo "<script>setTimeout(() => { window.location.href = 'ltask.php'; }, 2000);</script>";
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