<?
$pagename = "preferences";
include_once ("./header.php");


foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
} 

foreach ($_POST as $param_name => $param_val) {
  ${$param_name."_html"} = $param_val;
}


if ( $_POST['edit_company'] == "Update Company Settings" ) {
  $update_company_sql = "UPDATE company SET address1 = '$address1', address2 = '$address2', phone = '$phone', email='$email', footer1='$footer1', footer2='$footer2'";
  pg_query ($update_company_sql);
}

if ( $_POST['upload_logo'] == "Upload Logo"   ) {
  if ( isset ($_FILES['image']) ) {
    // TODO check filetype to ensure is png see http://codeaid.net/php/check-if-the-file-is-a-png-image-file-by-reading-its-signature
    move_uploaded_file ( $_FILES['image']["tmp_name"], "./logos/" . $_SESSION['company_id'] . ".png" );
    } else {
    echo "No file uploaded.";
  }
}

$get_prefs_sql = "SELECT * FROM company LIMIT 1";  // There should only be one company per database anyway

$get_prefs_result = pg_query ( $get_prefs_sql );

?>

<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form action="./preferences.php" method=POST>

<?

$row = pg_fetch_assoc ( $get_prefs_result, 0 ); // should be the only row

foreach ( $row as $lt_header => $lt_row_item ) {
  if ( $lt_header == "company_name" || $lt_header == "logo_file" ) continue; // TODO make company name editable, needs to be done in auth DB also
  echo "<tr><td>" . $database_field_lookup[$lt_header] . "</td><td><input name=\"$lt_header\" value=\"$lt_row_item\"></td></tr>";
}


?> 
<tr><td colspan=2><input name="edit_company" type ="submit" value="Update Company Settings"></td></tr>
</form>
</table>
</div>


<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form ENCTYPE="multipart/form-data" action="./preferences.php" method=POST>
<tr><td>Current Logo:</td></tr>
<tr><td><img src="./logos/<? echo $_SESSION['company_id']. ".png"  ?>"></td></tr>
  <tr><td>Upload new logo (png format only):</td></tr>
<tr><td><INPUT TYPE=FILE NAME="image"></td></tr>
<tr><td><input type=submit name="upload_logo" value = "Upload Logo"></td></tr>

</form>
</table>
</div>




<? include_once ("footer.php"); ?>
