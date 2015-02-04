<?
require_once ("./header.php");
 
$entryid = intval ($_GET['id'] );

if ($_GET['confirmed'] == "no") {

  echo "<b>Please confirm deletion for the following time entry:</b><p>";
  
  $entry_result = pg_query ("SELECT * FROM timeentry WHERE entryid=" . $entryid );
  $entry_row = pg_fetch_assoc ( $entry_result, 0 );
  foreach ( $entry_row as $lt_header => $lt_row_item ) {
  $$lt_header = $lt_row_item;  // this puts the variable from the database in a variable with the column name
}

?>
<div class="RoundTable" style="display:table;">
<table>

<?
  echo "<tr><td>Duration</td><td>Date</td><td>Client</td><td>Timekeeper</td><td>Description</td></tr>";
  echo "<tr><td>$duration</td><td>$date</td><td>$client_name</td><td>$timekeeper_email</td><td>$description</td></tr></table></div>";
  echo "<p><form method=get action=./delete_time.php><input type=hidden name=\"id\" value=\"$entryid\"><input type=hidden name=\"confirmed\" value=\"yes\"><input type=submit name=\"Submit\" value=\"Confirm Deletion\"></td></tr></form>";
} else { // deletion is confirmed
  if ( is_numeric ($entryid) ) {
    $deletion_sql = "DELETE FROM timeentry WHERE entryid = '$entryid'";
    $query_result = pg_query ($deletion_sql);
    if ( $query_result != FALSE ) echo "<b>Time entry $entryid is deleted.</b>"; else echo "Deletion failed.";
  } else echo "Entry id $entryid is not numeric, deletion failed.";
}