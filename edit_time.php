<?
require_once ("./header.php");
 
$entryid = intval ($_GET['id'] );

if ( $_POST['edit_time'] == "Modify" ) {  // processing data submitted by this form
  $update_sql = "UPDATE timeentry SET ";
  
  foreach ($_POST as $param_name => $param_val) {
    $$param_name = pg_escape_string ($param_val);
    $param_val = pg_escape_string ($param_val);
    if ( $param_name == "entryid" || $param_name == "edit_time" ) continue;
    if ( $param_name == "writeoff" || $param_name == "duration" || $param_name == "flatfee_item"  ) {
      if ( $param_val == "" ) $param_val = "null";
      $update_sql .= "$param_name = $param_val, "; 
    } else $update_sql .= "$param_name = '$param_val', ";  // can't have the single quote around number types for psql
  } 
  $entryid = $_POST['entryid'];
  $update_sql = trim ($update_sql, ", "); // remove trailing comma and space
  $update_sql .= " WHERE entryid = " . $entryid;
  pg_query ( $update_sql );
  //  echo "Your SQL is <p>$update_sql<p>";
  echo "<b>Updated time entry $entry_id</b><p>";
} else {  // got here via GET-based request, check the GET value for id
  if (empty ($entryid) ) {
    echo "<p><b>No time entry specified. Try clicking a link on the <a href=\"./view_time.php\">View Time</a> page.";
    require_once ("./footer.php");
    exit();
  }
  if ( ! is_int ($entryid ) ) {
    echo "<p><b>Time entry id $entryid is not an integer. Try clicking a link on the <a href=\"./view_time.php\">View Time</a> page.";
    require_once ("./footer.php");
    exit();
  }
}

$entry_result = pg_query ("SELECT * FROM timeentry WHERE entryid=" . $entryid );
$entry_row = pg_fetch_assoc ( $entry_result, 0 );

if ( $entry_result == false || $entry_row == false ) {
  echo "<p><b>Time entry not found.";
  require_once ("./footer.php");
  exit();
}

$entry_row = pg_fetch_assoc ( $entry_result, 0 );

foreach ( $entry_row as $lt_header => $lt_row_item ) {
  $$lt_header = $lt_row_item;  // this puts the variable from the database in a variable with the column name
}

?>

<div class="RoundTableNoHeader" style="display:table;">
<table>
<form action = "./edit_time.php" method=POST>
<tr><td colspan=2 class="header"><center>Editing Time Entry #<? echo $entryid; ?>
<tr><td>Duration</td><td><input type = "text" name ="duration" value="<? echo $duration ?>"></td></tr>
<tr><td>Matter  <? echo (help_link ("matter" ));  ?></td><td><input type = "text" name ="matter_id" value="<? echo $matter_id ?>"></td></tr>
<tr><td>Timekeeper</td><td><input type = "text" name ="timekeeper_email" value="<? echo $timekeeper_email ?>"></td></tr>
<tr><td>Writeoff <? echo (help_link ("writeoff" ));  ?></td><td><input type = "text" name ="writeoff" value="<? echo $writeoff ?>"></td></tr>
<tr><td>Date</td><td><input type = "text" name ="date" value="<? echo $date ?>"></td></tr>
<tr><td>Client Name</td><td><input type = "text" name ="client_name" value="<? echo $client_name ?>"></td></tr>
<tr><td>Description</td><td><textarea name ="description" rows=4 cols=40><? echo $description ?></textarea></td></tr>
<tr><td>Notes <? echo (help_link ("notes" ));  ?></td><td><textarea name ="notes" rows=2 cols=40><? echo $notes ?></textarea></td></tr>
<tr><td>Flat Fee Amount</td><td><input type = "text" name ="flatfee_item" value="<? echo $flatfee_item ?>"></td></tr>
<tr><td colspan=2><input type = "submit" name = "edit_time" value="Modify">
<input type="hidden" name = "entryid" value="<? echo $entryid; ?> ">
</form>
</table>
</div>

<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form action = "./edit_time.php" method=GET>
<tr><td colspan=2 class="header">Edit Another Time Entry</td></tr>
  <tr><td>Serial #:<input type = text name="id"><input type ="submit" value="Edit"></td> 
</form>
</table>


  <? require_once ("footer.php"); ?>