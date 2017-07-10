<?
$pagename = "enter_time";

include_once ("./header.php");

?>

<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form name="time_entry" method=POST action="./index.php">
<?  include ("time_entry_form.php"); ?>
</form>
</table>
</div>
<p>

<?

if ( $_POST['timeentry'] == "Enter" ) {

  if ( $_POST['email'] != $_SESSION['email'] &! $_SESSION['administrator'] ) {
    echo "<script type=\"text/javascript\">alert(\"Non-administrators may only enter time for themselves.\")</script><b>Non-administrators may only enter time for themselves.</b>"; // need to make this that non-admins have an authorized time entry list
    include_once ("footer.php");
    exit();
  }

foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
}

if ( $flatfee != null && ( $duration != null || $start_time != null || $end_time !=null ) ) {
  echo "You cannot enter both a flat fee value and a time value for the same entry.";
  echo "</table>";
  include_once ("footer.php");
  exit();
}

if ( $flatfee != null ) {  // entering a flat fee item here
  $flatfee = str_replace (array("$",","),"",$flatfee);  // remove all commas and dollar signs, must be a plain number
  $insert_sql = "INSERT INTO timeentry ( timekeeper_email, client_name, flatfee_item, matter_id, date, description, writeoff, notes ) VALUES ( '$email', '$client_name', $flatfee, '$matter_id', '$date', '$description', $writeoff, '$notes' )";
  $success = pg_query ( $insert_sql );
  if ( $success == FALSE ) echo "<p><b>Flat fee entry failed.</b><p><script type=\"text/javascript\">alert(\"Flat fee entry failed.\")</script>$insert_sql"; else echo "<p><b>Flat fee item entered.</b><p>"; 
  } else if ( $start_time != null || $end_time != null ) {
  if ( $end_time != null xor $start_time != null ) {
    echo "You must enter both a start and an end time. <script type=\"text/javascript\">alert(\"You must enter both a start and an end time.\")</script>"; echo "</table>";  include_once ("footer.php"); exit();
  }
  
  $start_time = str_replace ( ":", "", $start_time );
  $end_time = str_replace ( ":", "", $end_time );

  if ( ( ! is_numeric ( $start_time) ) && ( ! is_numeric ($end_time ) ) ) {
    echo "<p><b>Start and end time must be numeric values.</b><script type=\"text/javascript\">alert(\"Start and end time must be numeric values.\")</script>";
    echo "</table>";  include_once ("footer.php");   exit();    
  } 
  
  if ( (int) $start_time > (int) $end_time ) {     echo "<p><b>Start time must be before end time, and in 24 hour notation.<script type=\"text/javascript\">alert(\"Start time must be before end time, and in 24 hour notation.\")</script>"; echo "</table>";  include_once ("footer.php"); exit();
  }

  $start_time = substr ( $start_time, 0, -2 ) . ":" . substr ( $start_time, -2, 2 );
  $end_time = substr ( $end_time, 0, -2 ) . ":" . substr ( $end_time, -2, 2 );
  
  $duration = ( strtotime ( $end_time ) - strtotime ( $start_time ) ) / 60; // this is now in minutes
  // echo "<p>Length is $duration minutes.";
  $duration = $duration / 60; // now in hours
  // echo "<p>Length is $duration tenths of an hour";
  //  $duration = round ( $duration + .05 , 1, PHP_ROUND_HALF_DOWN ); // rounded up, fractional tenth counts as whole tenth.  // TODO: make this an option
  // echo "<p>Length after rounding is $duration";

}

if ( $flatfee == null ) {
  if ( $duration == null ) {
    echo "<b>You must enter a duration or start/end time.</b><script type=\"text/javascript\">alert(\"You must enter a duration or start/end time.\")</script>";
  } else {
    $insert_sql = "INSERT INTO timeentry ( timekeeper_email, client_name, duration, matter_id, date, description, writeoff, notes, start_time, end_time ) VALUES ( '$email', '$client_name', $duration, '$matter_id', '$date', '$description', $writeoff, '$notes',";
    if ( $start_time != '' ) $insert_sql .= " '$start_time', '$end_time' )"; else $insert_sql .=  " null, null )";
    $success = pg_query ( $insert_sql );
    if ( $success == FALSE ) echo "<p><b>Time entry failed.  Are you sure that the timekeeper and the client both exist in the database?</b><script type=\"text/javascript\">alert(\"Time entry failed.  Are you sure that the timekeeper and the client both exist in the database?\")</script><p>"; else echo "<p><b>Time entry recorded.</p></b>";
  }
}
}


echo "</table>";

include_once ("footer.php");

?>


