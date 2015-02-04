<? $pagename = "view_timekeeper"; 
include_once ("./header.php");

foreach ($_POST as $param_name => $param_val) {
  $$param_name = pg_escape_string ($param_val);
} 

//echo "<p><b>Timekeeper email is $timekeeper_email</b></p>";

if ( $timekeeper_email == "" ) $timekeeper_email=$_SESSION['email'];

?>
<p>
<div class="RoundTableNoHeader" style="display:table;">

<table>
<form action="./view_timekeeper.php" method=POST>
  <tr><td>View Timekeeper: </td><td> <input type="text" name="timekeeper_email" value="<? echo ($timekeeper_email); ?>" >
  </td><td><input type=submit name="ViewTimekeeper" Value="View">
  </tr>
</form>
</table>
</div>
<?

  // For viewing timekeeper info

if ( $_POST['ViewTimekeeper'] == "View" || $Update == "Update" ) {

  if ( $active == "on" ) $active = 't'; else $active = 'f';
  if ( $administrator == "on" ) $administrator = 't'; else $administrator = 'f';

  if ( $Update == "Update" ) {
    if ( ! $_SESSION['administrator'] ) {
      echo "<p><b>Non administrators may not edit timekeepers.</b>";
      include_once ("footer.php");
      exit();
    }
    $update_sql = "UPDATE timekeeper SET first_name = '$first_name', last_name = '$last_name', rate = $rate, active = '$active', administrator = '$administrator' WHERE email = '$timekeeper_email'";
    // echo "<p>$update_sql<p>";  // DEBUG
    $update_result = pg_query ($update_sql);

    // TODO: check that person changing password is an administrator

    if ( $update_result != false ) echo "<p><b>Timekeeper updated.</b>";

    if ( $password != "" ) {
        if ( ctype_alnum ( $_POST['password'] ) && ( strlen ($_POST['password']) > 6  ) ) {
	  $auth_db_connection = pg_connect ($auth_connection_string);
	  
	  $hash = create_hash ( $password );
	  $key = base64_encode(mcrypt_create_iv(36, MCRYPT_DEV_URANDOM));
	  $update_pwd_sql = "UPDATE users SET hash='$hash', key='$key' WHERE email='$timekeeper_email'";
	  pg_query ( $auth_db_connection, $update_pwd_sql );
	  pg_close ($auth_db_connection);
	  echo "<p><b>Updating password.</b></p>";

	} else echo "<p><b>Password not changed.  Must be at least 6 characters and alphanumeric.</b>";
    }
    
  }
  
  $select_timekeeper_sql = "SELECT * FROM timekeeper WHERE email = '" . $timekeeper_email ."'";
  $timekeeper_result = pg_query ($connection, $select_timekeeper_sql );
  if ( $timekeeper_result == FALSE || pg_num_rows ( $timekeeper_result ) == 0 ) {
    echo "<p><b>Timekeeper not found in database.</b><p>";
  } else {
    $row = pg_fetch_assoc ( $timekeeper_result, 0 ); // row headers
    
    foreach ( $row as $lt_header => $lt_row_item ) {
      $$lt_header = $lt_row_item;  // this puts the variable from the database in a variable with the row name
    }
    


  ?> <p>
<div class="RoundTableNoHeader" style="display:table;">
<table><tr><td colspan=2><b><center>
    <? if ($_SESSION['administrator']) echo "Edit "; else echo "View ";  ?>

TimeKeeper</center></b></td></tr>
<form action="./view_timekeeper.php" method=POST>
  <input type = "hidden" name = "timekeeper_email" value="<? echo $timekeeper_email ?>"
  <tr><td>Email</td><td><? echo $timekeeper_email; ?></td></tr>
    <tr><td>First Name</td><td><input type = "text" name ="first_name" value="<? echo $first_name; ?>"</td></tr>
    <tr><td>Last Name</td><td><input type = "text" name ="last_name" value="<? echo $last_name; ?>"</td></tr>
    <tr><td>Rate</td><td><input type = "text" name ="rate" value="<? echo $rate; ?>"</td></tr>

    <? if ( $_SESSION['administrator']) {  ?>

    <tr><td>Password <? echo (help_link ("password")); ?></td><td><input type = "password" name ="password"</td></tr>
    
    <tr><td>Active</td><td><input type = "checkbox" name ="active"  <? if ($active == "t") echo "checked"; ?>></td></tr>
  <tr><td>Administrator</td><td><input type = "checkbox" name ="administrator" <? if ($administrator == "t") echo "checked"; ?>></td></tr>
  <tr><td colspan=2><center><input type="submit" name="Update" value="Update"</td></tr>
    </form>
    <? } ?>

    </table>
</div>
<?
  } // TODO: add in submit button and processing of timekeeper
}

include_once ("footer.php");

?>