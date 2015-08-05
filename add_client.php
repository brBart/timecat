<? 
$pagename = "add_client";
include_once ("./header.php");

// let me know if the connection fails
if (!$connection) {
  print("Connection Failed.");
  exit;
}

if ( $_POST['AddClient'] == "Add"  ) {
  
  foreach ($_POST as $param_name => $param_val) {
    $$param_name = pg_escape_string ($param_val);
  }  
  
  $hash = create_hash ( $_POST['password'] );
  $key = base64_encode(mcrypt_create_iv(36, MCRYPT_DEV_URANDOM));
  /* Note that "company name" here refers to the *account holders* company name, not the *client* company name
     This is to allow functionality where multiple timecat installs exist on the same server -- keep track of 
     which clients belong to which account holders. */
  $company_name = $_SESSION['company'];
  if ( $current_client == null ) $current_client = "f";  
  $insert_sql = "INSERT INTO client ( client_name, email, address1, address2, city, state, zipcode, contact_first, contact_last, referrer, referrer_type, current_client, fee_adjust ) VALUES ( '$client_name', '$email', '$address1', '$address2', '$city', '$state', $zipcode, '$contact_first', '$contact_last', '$referrer', '$referrer_type', '$current_client', $fee_adjust)";
  
  $client_insert_result = pg_query ( $insert_sql );
  
  if ( !$client_insert_result ) {
    echo "<p><b>Adding client failed.</b> with SQL: </b>$insert_sql<p>";
  } else {
    echo "<p><b>Client " . $_POST['client_name'] . " successfully added.</b><p>";
  }
  
}   
  
?>

<form action="./add_client.php" method=POST>
<div class="RoundTableNoHeader" style="display:table;">
<table border=0><tr><td colspan=2><b>Add Client</b>
<tr><td>Client Name<td><input type = "text" name ="client_name">
<tr><td>Email<td><input type = "text" name ="email">
<tr><td>Address1<td><input type = "text" name ="address1">
<tr><td>Address2<td><input type = "text" name ="address2">
<tr><td>City<td><input type = "text" name ="city">
<tr><td>State<td><input type = "text" name ="state">
<tr><td>Zipcode<td><input type = "text" name ="zipcode">
<tr><td>Contact First<td><input type = "text" name ="contact_first">
<tr><td>Contact Last<td><input type = "text" name ="contact_last">
<tr><td>Referrer<td><input type = "text" name ="referrer">
<tr><td>Fee Adjustment  <? echo (help_link("fee_adjust")); ?> <td><input type = "text" name ="fee_adjust" value="1">
<tr><td>Referrer Type<td><input type = "text" name ="referrer_type"> <!-- needs to prefetch or be a dropdown -->
<tr><td>Current Client<td align=center><input type = checkbox name ="current_client" checked>
<tr><td><td align="right"><input type=submit name="AddClient" Value="Add">
</table>
</div></form>

</body>
</html>