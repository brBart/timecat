<? 
$pagename = "add_timekeeper";
include_once ("./header.php");

// let me know if the connection fails
if (!$connection) {
  print("Connection Failed.");
  exit;
}


if ( $_POST['AddUser'] == "Add"  ) {
  
  if ( ctype_alnum ( $_POST['password'] ) && ( strlen ($_POST['password']) > 6  ) ) {
    
    foreach ($_POST as $param_name => $param_val) {
      $$param_name = pg_escape_string ($param_val);
    }

    if ( $active == "" ) $active = 'f';
    if ( $administrator == "" ) $administrator = 'f';
    
      
    echo "<b>Added user " . $first_name . " ". $last_name . " with email " . $email . " and password.</b>";
    
    $hash = create_hash ( $_POST['password'] );
    $key = base64_encode(mcrypt_create_iv(36, MCRYPT_DEV_URANDOM));
    $company_name = $_SESSION['company'];
    $company_id = $_SESSION['company_id'];

    // TODO: some data checking, e.g., make sure username is not a dupe and email is formatted

    $insert_sql = "INSERT INTO timekeeper ( first_name, last_name, login_id, rate, active, administrator, company_name, email) VALUES ( '$first_name', '$last_name', '$email', '$rate', '$active', '$administrator', '$company_name', '$email' )";  // TODO: remove hash from here, it only matters in auth db
    
    pg_query ( $connection, $insert_sql );

    $auth_db_connection = pg_connect ($auth_connection_string);
    $insert_auth_sql = "INSERT INTO users ( email, hash, key, company_id, company_name, active ) VALUES ('$email', '$hash', '$key', '$company_id', '$company_name', 't')";

    pg_query ( $auth_db_connection, $insert_auth_sql );
    
    pg_close ($auth_db_connection);
    
  } else if ( ! ctype_alnum( $_POST['password'] ) ) {
    echo "<p><b>Password must be entirely letters and numbers, you gave " . $_POST['password'] . ".</b><p>";
  } else if ( ( strlen ($_POST['password']) <= 6  )) {
    echo "<p><b>Password must be more than six characters.</b><p>";
  }
}    
  
?>

<form action="./add_timekeeper.php" method=POST>
<div class="RoundTableNoHeader" style="display:table;">
<table border=0><tr><td colspan=2><b>Add Timekeeper</b>
  <tr><td>Email address (user ID)<td><input type="text" name="email">
<tr><td>First Name<td><input type="text" name="first_name">
<tr><td>Last Name<td><input type="text" name="last_name">
<tr><td>Rate<td><input type="text" name="rate">
<tr><td>Password<td><input type="password" name="password">
<tr><td>Check if active<td><input type="checkbox" name="active" checked>
<tr><td>Check if administator<td><input type="checkbox" name="administrator" checked>
<tr><td><td align="right"><input type=submit name="AddUser" Value="Add">
</table></form></div>

</body>
</html>

<?
  include_once ( "footer.php" );
?>