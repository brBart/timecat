<?
$pagename = "view_time";
include_once ("./header.php");
include_once "client_list_javascript.php";
$unbilled="on"; $email=$_SESSION['email']; // default values if nothing posted
foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
} 
foreach ($_POST as $param_name => $param_val) {
  ${$param_name."_html"} = $param_val;
} 

if ( $login_id != "" ) $username = $login_id; else $username=$_SESSION['email'];

?>
<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form action="./view_time.php" method=POST>
  <tr><td colspan=5><b>View time for the following (leave a field blank to ignore):</td></tr>

  <? 

if ( $_SESSION['administrator']) {

  echo "<tr><td>Timekeeper email is <input type=\"text\" name=\"email\" value=\"". $username . "\" > </td>";

   } else {

  echo "<tr><td>Timekeeper email is <input type=\"hidden\" name=\"email\" value=\"" . $username . "\" > " . $username . "  </td>";
   
    } 

?>
<td>Client name is <input type = "text" name ="client_name" id = "tags" value="<? echo ($client_name_html); ?>"  >
  <td>Begin date <input type = "text" name ="begin_date" value="<? echo ($begin_date_html); ?>" >
  <td>End date <input type = "text" name ="end_date" value="<? echo ($end_date_html); ?>" >
  <td>Only unbilled? <input type = checkbox name="unbilled" checked>
<tr><td colspan=5 align="right"><input type=submit name="ViewTime" Value="View">
</form>
</table>
</div>

<?

  $total_duration = 0;


  if ( ( $email != $_SESSION['email'] ) &! $_SESSION['administrator'] ) {
    echo "<p><b>Non-administrator may only access his/her own time.</b>";
    include_once ("footer.php");
    exit();
  }
  $select_time_sql = "select * from timeentry";
  $first_condition_set = false;
  if ( $email != "" ) {
    if ( $first_condition_set )  $select_time_sql .= " AND"; else $select_time_sql .= " WHERE";
    $select_time_sql .= " timekeeper_email = '$email'";
    $first_condition_set = true;
  }
  if ( $client_name != "" ) {
    if ( $first_condition_set )  $select_time_sql .= " AND"; else  $select_time_sql .= " WHERE";
    $select_time_sql .= " client_name = '$client_name'";
    $first_condition_set = true;

  }
  if ( $begin_date != "" ) {
    if ( $first_condition_set )  $select_time_sql .= " AND"; else  $select_time_sql .= " WHERE";
    $select_time_sql .= " date >= '$begin_date'";
    $first_condition_set = true;
  }
  if ( $end_date != "" ) {
    if ( $first_condition_set )  $select_time_sql .= " AND"; else  $select_time_sql .= " WHERE";
    $select_time_sql .= " date <= '$end_date'";
  }

  if ( $unbilled == "on" ) {
    if ( $first_condition_set )  $select_time_sql .= " AND"; else  $select_time_sql .= " WHERE";
    $select_time_sql .= " invoice_no IS NULL AND writeoff > -1";
  }

  $select_time_sql .= " ORDER BY  date, invoice_no, timekeeper_email, entryid";
  echo "<p>";
// echo "Your SQL is: <xmp>$select_time_sql</xmp>"; // useful for debug
  $viewtime_result = pg_query ($select_time_sql);
  echo "<div class=\"RoundTable\" >";
  echo "<table width=100%><form action = \"./process_timeentries.php\" method=POST>";
  echo "<tr><td colspan=" . ( pg_num_fields ($viewtime_result) + 2 ) . "><select name=\"process_time_entry_action\"><option value=\"create_invoice\">Create Invoice For Selected</option><option value=\"output_csv\">Output to CSV</option></select> <input type=\"submit\" name=\"process_timeentries\" value=\"Do It!\"><tr>";
  if ( pg_num_rows ($viewtime_result) == 0 ) {
    echo "<td><center>No matching entries. Consider changing or removing the timekeeper email address and/or unchecking \"Only Unbilled.\"</center>"; // localize me!
  } else {    
    $row_headers = pg_fetch_assoc ( $viewtime_result, 0 ); // row headers
    foreach ( $row_headers as $lt_header => $lt_row_item ) {
      if ( $lt_header == "entryid" ) echo "<td>"; 
      if ( $lt_header == "start_time" || $lt_header == "end_time" ) continue;
      echo "<td><b>". $database_field_lookup [$lt_header] ."</b></td>";
    }
    echo "<td><b>Actions</b></td></tr>"; 
    for ($lt = pg_numrows($viewtime_result) -1; $lt >= 0 ; $lt--) {  // loop through rows
      $lt_row = pg_fetch_assoc ( $viewtime_result, $lt );
      echo "<tr>";
      $entry_id;
      foreach ( $lt_row as $lt_field => $lt_row_item ) {  // bein loop to show the items in teh row
	if ( $lt_field == "start_time" || $lt_field == "end_time" ) continue;  // removing start/end time to reduce clutter
	if ( $lt_field == "duration" ) $total_duration += $lt_row_item;
	if ( $lt_field == "entryid" ) {
	  echo "<td><input type=\"checkbox\" name=\"$lt_row_item\" checked>"; 
	  $entry_id = $lt_row_item;
	}
	echo "<td class=\"padded\">$lt_row_item</td>";
      }
      echo "<td class=\"padded\"><a href=\"./edit_time.php?id=$entry_id\">edit</a> <a href=\"./delete_time.php?id=$entry_id&confirmed=no\">delete</a></td>";
    }
    // Final line showing total duration
    echo "<tr><td colspan=".(pg_num_fields ($viewtime_result) + 2).">Total time duration: " . number_format ($total_duration, 1) . "</td></tr>";
    echo "</form></td></tr></table>";
    echo "</div>";
  }


include_once ("footer.php");

?>




