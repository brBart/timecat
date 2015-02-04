<?
$export_csv = false;
if ($_POST['process_time_entry_action'] != "output_csv") {
require_once ("./header.php");
require_once ('./tcpdf/tcpdf.php');
require_once ('./tcpdf/config/lang/eng.php');
require_once ("./database_functions.php");
} else{
  require_once ("./settings.php");
 $export_csv = true;
}
include_once ("./login_check.php");
$pagename = "process_timeentries";
$selected = array ();
$row_titles = array();

// assign post parameters to variables
foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
  // echo "<p>$param_name == $param_val";
  if ( $param_val == "on" ) array_push ($selected, $param_name);
} 

if ( $process_time_entry_action == "create_invoice" || $export_csv ) {  // Creating Invoice From Selections
 
  // get address for top client, fill in form

  $timeentry_sql = "SELECT * from timeentry WHERE ";
  foreach ($selected as $entry ) $timeentry_sql .= "entryid = $entry OR ";
  $timeentry_sql = substr ( $timeentry_sql, 0, strlen ($timeentry_sql) - 4);  // remove the last " OR "
  $timeentry_sql .= "ORDER BY date, timekeeper_email, entryid";

  $row_entries_result = pg_query ( $timeentry_sql ); 
  
  // check if all the same client, else give a warning

  $previous_entry_client;  $current_client;

  for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) {
    $lt_row = pg_fetch_assoc ( $row_entries_result, $lt );
    $current_client = $lt_row['client_name'];
    if ( $previous_entry == $entry && $previous_entry != "" &! $export_csv ) {
      echo "<b>Error: not all time for this proposed invoice is for the same client. </b>";
      include_once ("footer.php");
      exit();
    }
  }
  
  $client_info_sql = "SELECT * from client WHERE client_name = '" . pg_escape_string ($current_client) . "'";
  $client_info_result = pg_query ( $client_info_sql );
  
  $row = pg_fetch_assoc ( $client_info_result, 0 ); // first row as assoc array
  
  foreach ( $row as $lt_header => $lt_row_item ) {
    $$lt_header = $lt_row_item;  // this puts the variable from the database in a variable with the row name
  }

  
  // start: processing CSV output option
  if ( $export_csv ) { 

    $headers_row = pg_fetch_assoc ( $row_entries_result, 0 );
    foreach ( $headers_row as $lt_header => $lt_row_item ) {
      array_push ($row_titles, $lt_header); 
    }    

    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=timecat_time_entries.csv");
    header("Pragma: no-cache");
    header("Expires: 0");
    $csv_array = array();
    array_push ($csv_array, $row_titles);
    for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) {
      $db_row = pg_fetch_assoc ( $row_entries_result, $lt );
      array_push ( $csv_array, $db_row );
    }

    $output = fopen("php://output", "w");
    foreach ($csv_array as $csv_row) {
      fputcsv($output, $csv_row);
    }
    fclose($output);
    exit();

  }
  // end: processing CSV output option

  echo "<div class=\"RoundTable\" >";
?>

<table width="100%"><form action = "./create_invoice.php" method=POST>
<table><tr><td colspan=4><b><center>Verify Client Information</center></b></td></tr><tr>
<input type = "hidden" name ="client_name" value="<? echo $client_name ?>">
<td>Client Name</td><td><? echo $client_name ?></td>
<td>Email</td><td><input type = "text" name ="email" value="<? echo $email ?>"></td></tr>
<tr><td>Contact First</td><td><input type = "text" name ="contact_first" value="<? echo $contact_first ?>"></td>
<td>Contact Last</td><td><input type = "text" name ="contact_last" value="<? echo $contact_last ?>"></td></tr>
<tr><td>Address1</td><td><input type = "text" name ="address1" value="<? echo $address1 ?>"></td>
<td>Address2</td><td><input type = "text" name ="address2" value="<? echo $address2 ?>"></td></tr>
<tr><td>City</td><td><input type = "text" name ="city" value="<? echo $city ?>"></td>
<td>State</td><td><input type = "text" name ="state" value="<? echo $state ?>"></td></tr>
<tr><td>Zipcode</td><td><input type = "text" name ="zipcode" value="<? echo $zipcode ?>"></td><td colspan=2></td></tr>
<tr><td colspan=4 class="header"><b><center>Invoice Information</b></td></tr>
   <tr><td width="25%">Matter description</td><td width="25%"><input type="text" name = "matter_description" size="35%"></td><td width="25%">Past Due Amount</td><td width="25%"><input type="text" name="past_due" value="<? echo getPastDueForClient ($client_name); ?>"></tr>
<tr><td>Payment Terms</td><td><input type = "text" name = "payment_terms" value = "30 days">
<td>Due Date</td><td><input type = "text" name = "due_date" value = "<? echo date('F j, Y', strtotime("+30 days")); ?>"> <!-- TODO: make this number of days have a preference for the default value --></td></tr>


</table></div>
<p>
<div class="RoundTable"> <!-- TODO: make this two different table classes w/o rounded top/bottom so it appears as one table -->

<table><tr><td colspan=8>Time Entries For Invoice</td></tr>
<td><b>Date</b></td><td><b>Hours</b></td><td><b>Description</b></td><td><b>Timekeeper</b></td><td><b>Rate</b></td><td><b>Fee Detail</b></td><td><b>Billed total</b></td><td><b>Notes</b></td>
<?
																	     $already_invoiced_error=false;
  for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) {
    $row = pg_fetch_assoc ( $row_entries_result, $lt );
    $rate = round (getRateForTimekeeper ( $row['timekeeper_email']) * getMultiplierForClient ($client_name), 2);
    $fee_detail;
    if ( $row['flatfee_item'] == null ) $fee_detail = round ( ($rate * $row['duration'] ), 2); else $fee_detail = $row['flatfee_item'];
    echo "<tr><td>" . $row['date'] . "</td><td>" . $row['duration']  . "</td><td>" . $row['description']  . "</td><td>" . getFirstNameForTimekeeper ($row['timekeeper_email']) . "</td><td>";
    if ( $row['flatfee_item'] == null ) echo "$" . $rate;
    echo "</td><td>";  // TODO: add option to print whole timekeeper name
    if ( $row['writeoff'] != 1 ) echo "<del>";
    echo "$"  . number_format ($fee_detail, 2);
    if ( $row['writeoff'] != 1 ) echo "</del>";
    $writeoff = $row['writeoff'];
    if ( $writeoff == null ) $writeoff = 1;
    echo "</td><td>$" . number_format (round ( ($writeoff * $fee_detail ), 2 ), 2) ;
    echo "</td><td>" . $row['notes'] . "</td></tr>";
    if ( $row['invoice_no'] != null ) {
      echo "<tr><td colspan=8><center><i>Error, preceding entry cannot be included on another invoice because it is alerady on invoice " . $row['invoice_no'] . ". You will need to delete that invoice before including the time entry on another invoice.";
      $already_invoiced_error=true;
    }
  }
  ?> <tr><td colspan=8><center><input type="submit" name="create_invoice" value = "Create Invoice"

  <? if ($already_invoiced_error) echo " disabled=true"; ?>

> <?

  echo "<input type=\"hidden\" name=\"invoice_ids\" value=\"";
  foreach ($selected as $entry) echo $entry . ",";  // selected time entries to pass through as hidden value in form -- this leaves comma on end need to make sure explode() is ok with that
  echo "\">";
  
  echo "</form></table></div>";
} else echo "<p>No action selected.";

include_once ("footer.php");

?>
