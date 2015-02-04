<?
// TODO: Put this file outside apache root
//$company_id = "dev";
$auth_info = "user=joe host=localhost password='mypassword'";
$auth_connection_string = "dbname=timecat_auth " . $auth_info;
$login_on = true; // flip to "true" to turn on security after intiating database
$timecat_title = "Timecat Timekeeper";
ini_set ( "display_errors", "1" ); // change to zero once in production
ini_set("session.use_only_cookies", 1); // security setting, do not change
// error_reporting ( 0 );
error_reporting (E_ALL ^ E_NOTICE);

?>