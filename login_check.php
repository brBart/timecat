<?

// LOGIN PROCESSING

include_once ("./settings.php");
include_once ("./database_functions.php");

session_start();
$connection; $connection_string;

if ($_POST['Logout'] == "Logout") {
  session_destroy();
  echo "<center>Logged out.</center><p>";
  include_once ("login_form.php"); 
  exit();
  echo "&nbsp;";
} 

if ( ( ! isset($_SESSION['logged_in']) ) && $login_on ) {  // need to do login check
  if ( $_POST['login']=="") {  // no username, they just got here 
      include_once ("login_form.php"); 
      exit();
    } else { // trying to log in
    $login = $_POST['login'];
    $password = $_POST['password'];
    if  ( filter_var( $login, FILTER_VALIDATE_EMAIL != FALSE ) ) {
      echo "<p><center>Email format incorrect.</center><p>";
      include_once ("login_form.php"); 
      echo "</body></html>";
      exit();
    }
    
    $auth_db_connection = pg_connect ($auth_connection_string);
    
    $hash_fetch_sql = "select hash, company_id, company_name from users where email = '$login'";
    $hash_fetch_result = pg_query ( $auth_db_connection, $hash_fetch_sql );
    if ( pg_numrows ($hash_fetch_result) == 0 ) {
      echo "<p><center>Username or password not found.</center><p>";
      include_once ("login_form.php"); 
      exit();
    }
    $hash = pg_result ( $hash_fetch_result ,0,0 ); 
    pg_close ($auth_db_connection );
    if ( validate_password ( $password, $hash ) ) {
      $company_id = pg_result ($hash_fetch_result, 0, 1);
      
      $connection_string = "dbname=timecat_" . $company_id .  " " . $auth_info;

      // echo "connection string is: <pre>$connection_string</pre>";

      $connection = pg_pconnect($connection_string);
      // let me know if the connection fails
      if (!$connection) {
	print("Database connection failed, TimeCat code 1.");
	exit;
      }
      
      $_SESSION['administrator']=getAdministrator($_POST['login']);
      $_SESSION['logged_in']=1;
      $_SESSION['email']=$_POST['login'];
      $_SESSION['company_id']=$company_id;
      $_SESSION['company']=pg_result ($hash_fetch_result, 0, 2);  // Setting company name here, which is used in display and in inserting some information into the database
      // echo "<P>Company name set to " . $_SESSION['company'] . "<p>";
    } else { // failed login
      echo "<p><center>Wrong password.</center><p>";
      include_once ("login_form.php"); 
      exit();
    }
  }
} else { // already logged in, create database connection

  
  $company_id = $_SESSION['company_id'];
  $connection_string = "dbname=timecat_" . $company_id .  " " . $auth_info;
  $connection = pg_pconnect($connection_string);

  if (!$connection) {
    print("Database connection failed, TimeCat code 2.");
    exit;
  }
  

}
// END LOGIN PROCESSING


?>