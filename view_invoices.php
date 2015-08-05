<?
$pagename = "view_invoices";
require_once ("./login_check.php");
$output_csv = false;
if ($_POST['output_csv'] == "on")  $output_csv = true; 
$only_unpaid="on";
if ( !$output_csv ) {
include_once ("./header.php");
include_once "client_list_javascript.php";
}
if ( ! $_SESSION['administrator'] ) exit();

foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
} 
foreach ($_POST as $param_name => $param_val) {
  ${$param_name."_html"} = $param_val;
} 

if ( $login_id != "" ) $username = $login_id; else $username=$_SESSION['user']; 
if ( !$output_csv ) {
?>
<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form action="./view_invoices.php" method=POST>
  <tr><td colspan=5><b>View the following invoices (leave a field blank to ignore):</td></tr>
<td>Client name is <input type = "text" name ="client_name" id = "tags" value="<? echo ($client_name_html); ?>"  >
  <td>Begin date <input type = "text" name ="begin_date" value="<? echo ($begin_date_html); ?>" ></td>
  <td>End date <input type = "text" name ="end_date" value="<? echo ($end_date_html); ?>" ></td>
  <td>Invoice Number <input type = "text" name ="invoice_no" value="<? echo ($invoice_no_html); ?>" ></td></tr>
  <tr><td colspan=1> Only unpaid invoices: <input type = checkbox name = "only_unpaid" <? if ( $only_unpaid == "on" || $_POST['ViewInvoice'] == "" ) echo "checked";  ?>></td><td colspan=1> Only invoices with payments: <input type = checkbox name = "only_w_payments" <? if ( $only_w_payments == "on" ) echo "checked";  ?>></td><td colspan=2>Output CSV <input type = checkbox name = "output_csv"></td></tr>
<tr><td colspan=5 align="right"><input type=submit name="ViewInvoice" Value="View">
</form>
</table>
</div>

<?
}

if ( $_POST['ViewInvoice'] == "View" || $view_invoice_no != null || $output_csv || $only_unpaid=="on" ) {
  if  ( $view_invoice_no != null ) $invoice_no = $view_invoice_no; // this is if this is called after create_invoice.php
  if ( $invoice_no != null ) {
    if ( ! is_numeric ($invoice_no ) ) {  // only processing single values now
      echo "Only one invoice at a time, no ranges or multiple values (yet).";  // TODO: implement values like "x, y" or "x-y"
    }
    $select_invoices_sql = "SELECT * FROM invoices WHERE number = $invoice_no"; 
  } else {
    $first_condition_set = false;
    if ( $client_name != null ) {
      $select_invoices_sql = "SELECT * FROM invoices WHERE client_name = '$client_name'";
      $first_condition_set = true;
    }
    if ( $begin_date != null ) {
      if ( $first_condition_set ) {
	$select_invoices_sql .= " AND date >= '$begin_date'";
      } else {
	$select_invoices_sql = "SELECT * FROM invoices WHERE date >= '$begin_date'";  // fix me for date
	$first_condition_set = true;
      }
    }

    if ( $end_date != null ) {
      if ( $first_condition_set ) {
	$select_invoices_sql .= " AND date <= '$end_date'";
      } else {
	$select_invoices_sql = "SELECT * FROM invoices WHERE date <= '$end_date'";  // fix me for date
	$first_condition_set = true;
      } 
    }

    if ( $only_unpaid == "on" ) {
      if ( $first_condition_set ) {
       	$select_invoices_sql .= " AND amount_paid < amount ";
      } else {
	$select_invoices_sql = "SELECT * FROM invoices WHERE amount_paid < amount ";  // fix me for date
	$first_condition_set = true;
      } 

    }

    if ( $only_w_payments == "on" ) {
      if ( $first_condition_set ) {
       	$select_invoices_sql .= " AND amount_paid > 0 ";
      } else {
	$select_invoices_sql = "SELECT * FROM invoices WHERE amount_paid > 0 ";  // fix me for date
	$first_condition_set = true; 
      } 

    }
    
    if ( $first_condition_set ) $select_invoices_sql .= " AND is_deleted = 'f'"; 
  } // conditions other than invoice number

  
  if ( $select_invoices_sql == "" )  {
    echo "<td>Please select or fill in at least one of the selection criteria."; // localize me!
    echo "</table></div>";
    include_once ("./footer.php");
    exit();
  }
  $select_invoices_sql .= " ORDER BY number";
  $invoices_result = pg_query ( $select_invoices_sql );


  // start: dealing with CSV output option
  if ($output_csv) {

    $row_titles = array();
    
    $row_headers = pg_fetch_assoc ( $invoices_result, 0 ); // row headers
    foreach ( $row_headers as $lt_header => $lt_row_item ) {
      array_push ( $row_titles, $lt_header );
    }
      header("Content-type: text/csv");
      header("Content-Disposition: attachment; filename=timecat_invoice_data.csv");
      header("Pragma: no-cache");
      header("Expires: 0");
      $csv_array = array();
      array_push ($csv_array, $row_titles);
      for ($lt = 0; $lt < pg_numrows($invoices_result); $lt++) {
	$db_row = pg_fetch_assoc ( $invoices_result, $lt );
	array_push ( $csv_array, $db_row );
      }
      
      $output = fopen("php://output", "w");
      foreach ($csv_array as $csv_row) {
	fputcsv($output, $csv_row);
      }
      fclose($output);
    exit();
  }
  // end: dealing with CSV output option


  echo "<p><div class=\"RoundTable\" >";
  echo "<table width=100%>";


  if ( pg_num_rows ($invoices_result) == 0 ) {
    echo "<td>No matching entries."; // localize me!
      echo "</table></div>";
      include_once ("./footer.php");
      exit();
  } else {    
    $row_headers = pg_fetch_assoc ( $invoices_result, 0 ); // row headers
    foreach ( $row_headers as $lt_header => $lt_row_item ) {
      if ( $lt_header == "identifier" || $lt_header =="is_deleted" || $lt_header == "sent" ) continue;
      echo "<td><b>". $database_field_lookup [$lt_header] ."</b></td>";
      if ( $lt_header == "amount_paid" ) echo "<td><b>Balance</b></td>";
    }
    echo "<td><b>Actions</b></td></tr>"; 
    $total_amounts = 0;
    $total_amounts_paid = 0;
    $total_balance = 0;
    $this_amount = 0;
    for ($lt = 0; $lt < pg_numrows($invoices_result); $lt++) {
      $identifer; $invoice_no;
      $lt_row = pg_fetch_assoc ( $invoices_result, $lt );
      $date_diff = date_diff(date_create($lt_row['due_date']), date_create());
      $days_since_invoice = $date_diff->days;
      if ( $days_since_invoice > 0 && $date_diff->invert ==1 ) echo "<tr>";
      else if ( $days_since_invoice < 30 ) echo "<tr style=\"background-color:yellow\">";
      else if ( $days_since_invoice < 60 ) echo "<tr style=\"background-color:orange\">";
      else echo "<tr style=\"background-color:red\">";

      foreach ( $lt_row as $lt_field => $lt_row_item ) {
	if ( $lt_field == "is_deleted" || $lt_field == "sent" ) continue;  // TODO: show when invoice is sent, have an action to mark it as such

	if ( $lt_field == "identifier" ) {
	  $identifier = $lt_row_item;
	  continue;
	}
	if ( $lt_field == "number" ) $invoice_no = $lt_row_item;
	if ( $lt_row_item == "f") $lt_row_item = "No";
	if ( $lt_row_item == "t") $lt_row_item = "No";	
	echo "<td class=\"padded\">";
	if ( $lt_field == "amount" ) {
	  echo "$";
	  $total_amounts += $lt_row_item;
	  $this_amount = $lt_row_item; // to preserve to calculate balance due
	}
	if ( $lt_field == "amount_paid" ) echo "$";

	if ( $lt_field == "date" || $lt_field == "due_date") {
	  echo date ("F j, Y", strtotime ($lt_row_item) );
	} else echo "$lt_row_item</td>";

	if ( $lt_field == "amount_paid" ) {
	  $total_amounts_paid += $lt_row_item;
	  echo "<td>$" . number_format ( ($this_amount - $lt_row_item), 2) . "</td>";
	}

      }
      echo "<td class=\"padded\"><center><a target=\"_new\" href=\"./get_invoice.php?id=$identifier&action=view&invoice_no=$invoice_no\">view</a>&nbsp; <a href=\"./get_invoice.php?id=$identifier&action=dl&invoice_no=$invoice_no\">download</a>&nbsp;  <a href=\"./payment.php?invoice_no=$invoice_no\">payment</a> &nbsp;<a href=\"./delete_invoice.php?invoice_no=$invoice_no\">delete</a></center></td>";
    }
  } 
  echo "<tr><td colspan = 2><b>Totals</b></td><td><b>$" . number_format ($total_amounts, 2) . "</b></td><td><b>$" . number_format ($total_amounts_paid, 2) . "</b></td><td><b>$" . number_format ($total_amounts - $total_amounts_paid, 2) . "</b></td><td colspan=3></td></tr>";
  echo "</table></div>";
}

include_once ("footer.php");


?>
