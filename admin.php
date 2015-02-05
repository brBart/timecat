<?

$pagename = "admin";

foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
} 


if ( $export_clients  == "Export Clients" ) {
  error_reporting ( 0 );
  require_once ("./settings.php");
  require_once ("./login_check.php");
  $get_clients_sql = "select * from client where current_client=true";
  $row_entries_result = pg_query ( $get_clients_sql ); 
  header("Content-type: text/csv");
  header("Content-Disposition: attachment; filename=timecat_client_list.csv");
  header("Pragma: no-cache");
  header("Expires: 0");
  
  $csv_array = array();
  array_push ($csv_array, $row_titles);
  for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) {
    $db_row = pg_fetch_assoc ( $row_entries_result, $lt );
    $main_timekeeper_res = pg_query ("select timekeeper_email, sum (duration) from timeentry where client_name='". pg_escape_string ($db_row['client_name'] )."' group by timekeeper_email order by sum desc");
    $main_timekeeper = pg_fetch_assoc ($main_timekeeper_res);
    array_push ( $db_row, $main_timekeeper['timekeeper_email'] );
    array_push ( $csv_array, $db_row );
  }


  $output = fopen("php://output", "w");
  foreach ($csv_array as $csv_row) {
    if ( $csv_row == null ) continue;
    fputcsv($output, $csv_row);
  }
  fclose($output);
  exit();
  
}


include_once ("./header.php");


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
<form action="./admin.php" method=POST>

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
<form ENCTYPE="multipart/form-data" method=POST>
<tr><td>Current Logo:</td></tr>
<tr><td><img src="./logos/<? echo $_SESSION['company_id']. ".png"  ?>"></td></tr>
  <tr><td>Upload new logo (png format only):</td></tr>
<tr><td><INPUT TYPE=FILE NAME="image"></td></tr>
<tr><td><input type=submit name="upload_logo" value = "Upload Logo"></td></tr>

</form>
</table>
</div>
<p>
<div class="RoundTableNoHeader" style="display:table;"><table><tr><td>
<form method="POST">
  Click here to export client information as a CSV file:<p>
<input type=submit name="export_clients" value = "Export Clients">
</form>
</td></tr></table></div>
<p>
<div class="RoundTableNoHeader" style="display:table;"><table><tr><td>
  Import from a CSV file<? echo help_link ("upload_clients"); ?>:<p>
<form method="POST" enctype="multipart/form-data">
<input type="file" name="client_import_file">
<input type=submit name="import_clients" value = "Import Clients">
</form>
</td></tr></table></div>

<?

if ( $import_clients  == "Import Clients" ) {
  $row = 1;
  if (($handle = fopen($_FILES['client_import_file']['tmp_name'], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      $client_name =  pg_escape_string ( trim ( $data[0], ", \t\n\r\0\x0B" ) );
      $email =  pg_escape_string ( trim ( $data[1], ", \t\n\r\0\x0B" ) );
      $firstname =  pg_escape_string ( trim ( $data[2], ", \t\n\r\0\x0B" ) );
      $lastname  =  pg_escape_string ( trim ( $data[3], ", \t\n\r\0\x0B" ) );
      $address1 =  pg_escape_string ( trim ( $data[4], ", \t\n\r\0\x0B" ) );
      $address2 =  pg_escape_string ( trim ( $data[5], ", \t\n\r\0\x0B" ) );
      $city =  pg_escape_string ( trim ( $data[6], ", \t\n\r\0\x0B" ) );
      $state =  pg_escape_string ( trim ( $data[7], ", \t\n\r\0\x0B" ) );
      $zip =  pg_escape_string ( trim ( $data[8], ", \t\n\r\0\x0B" ) );
      if ( $zip == null ) $zip = 0;

      $insert_sql = "INSERT INTO client ( client_name, email, contact_first, contact_last, address1, address2, city, state, zipcode, current_client ) VALUES ( '$client_name', '$email', '$firstname', '$lastname', '$address1', '$address2', '$city', '$state', $zip, 't')";
      $ret_val = pg_query ( $insert_sql );
      if ( $ret_val != FALSE ) echo "<br>Imported $client_name";
    }
    fclose($handle);
  } else echo "Could not open file.";

}


 include_once ("footer.php"); ?>
