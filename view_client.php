<? $pagename = "view_client"; // TODO: add this to pagenav.  Need to do row break.
include_once ("./header.php");
require_once "client_list_javascript.php";
include_once "help_files.php";

foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
} 

if ( $current_client == "on") $current_client = "TRUE"; else $current_client = "FALSE";

if ( $login_id != "" ) $username = $login_id; else $username=$_SESSION['email'];
?>
<p>
<div class="RoundTableNoHeader" style="display:table;">

<table>
<form action="./view_client.php" method=POST>
  <tr><td>View Client: </td><td> <input type="text" name="client_name" id="tags">
  </td><td><input type=submit name="ViewClient" Value="View">
  </tr>
</form>
</table>
</div>
<?

if ( $_POST['ViewClient'] == "View" || $_POST['EditClient'] == "Edit" ) {

  if ( ( $_POST['EditClient'] == "Edit" ) &! $_SESSION['administrator'] ) {
    echo "<p><b>Non-administators may not edit clients.";
    include_once ("./footer.php");
    exit();
  }
  if (  $_POST['EditClient'] == "Edit" ) {
    $update_sql = "UPDATE client SET email='$email', address1='$address1', address2='$address2', city='$city', zipcode=$zipcode, contact_first='$contact_first', contact_last='$contact_last', referrer='$referrer', referrer_type='$referrer_type', state='$state', current_client=$current_client, fee_adjust=$fee_adjust WHERE clientid = $clientid";
    $update_result = pg_query ($update_sql);
    if ( $update_result != false ) echo "<p><b>Client updated.</b>";    
    $select_client_sql = "SELECT * FROM client WHERE clientid = '". $clientid . "'";
  } else {
    $select_client_sql = "SELECT * FROM client WHERE client_name = '". $client_name . "'";
  }
  

  $client_result = pg_query ( $select_client_sql );
  if ( $client_result == FALSE || pg_num_rows ( $client_result ) == 0 ) {
    echo "<p><b>Client not found in database.</b><p>";
  } else {
    $row = pg_fetch_assoc ( $client_result, 0 ); // first row as assoc array
    
    foreach ( $row as $lt_header => $lt_row_item ) {
      $$lt_header = $lt_row_item;  // this puts the variable from the database in a variable with the row name
    }
    
    // Form to show data and allow it to be edited




  ?> <p>
<div class="RoundTableNoHeader" style="display:table;">
<table><tr><td colspan=2><b><center>View/Edit Client</center></b></td></tr>
<form action="./view_client.php" method=POST>


<input type="hidden" name="clientid" value="<? echo $clientid ?>">
<tr><td>Client ID</td><td><center><? echo $clientid ?></center></td></tr>
    <tr><td>Client Name</td><td> <!-- this should not be changeable until all other time entries are changed, or else existing time entries will get lost <input type = "text" name ="client_name" value="<? echo $client_name ?>"> --> <? echo $client_name; ?> </td></tr>
<tr><td>Email</td><td><input type = "text" name ="email" value="<? echo $email ?>"></td></tr>
<tr><td>Address1</td><td><input type = "text" name ="address1" value="<? echo $address1 ?>"></td></tr>
<tr><td>Address2</td><td><input type = "text" name ="address2" value="<? echo $address2 ?>"></td></tr>
<tr><td>City</td><td><input type = "text" name ="city" value="<? echo $city ?>"></td></tr>
<tr><td>Zipcode</td><td><input type = "text" name ="zipcode" value="<? echo $zipcode ?>"></td></tr>
<tr><td>Contact First</td><td><input type = "text" name ="contact_first" value="<? echo $contact_first ?>"></td></tr>
<tr><td>Contact Last</td><td><input type = "text" name ="contact_last" value="<? echo $contact_last ?>"></td></tr>
<tr><td>Referrer</td><td><input type = "text" name ="referrer" value="<? echo $referrer ?>"></td></tr>
<tr><td>Referrer Type</td><td><input type = "text" name ="referrer_type" value="<? echo $referrer_type ?>"></td></tr>
  <tr><td>Current Client</td><td><center><input type = "checkbox" name ="current_client" <? if ( $current_client == "t" ) echo "checked"; ?>></center></td></tr>
<tr><td>State</td><td><input type = "text" name ="state" value="<? echo $state ?>"></td></tr>
    <tr><td>Fee Adjustment <? echo (help_link("fee_adjust")); ?></td><td><input type = "text" name ="fee_adjust" value="<? echo $fee_adjust ?>"></td></tr>

    <tr><td>Past Due</td><td> $<? echo (number_format (getPastDueForClient ($client_name))); ?> </td></tr>
  <tr><td colspan=2><center><input type="submit" name="EditClient" value="Edit"></td></tr>
    </form>
    </table>
</div>
<?
  } // TODO: add in submit button and processing of timekeeper
}

include_once ( "footer.php" );

?>