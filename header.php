<? 
require_once ("./settings.php");
require_once ("./password_hash.php");
require_once ("./help_files.php" );
require_once ("./localization.php");

session_start();

?>
 
<html><head>
  <META http-equiv="refresh" content="1900; URL=./">

  <link rel="STYLESHEET" HREF="./style.css" type="text/css">
  <link rel="stylesheet" type="text/css" href="./jquery-ui-1.10.4.custom.min.css">  
<script src="./jquery-1.10.2.js"></script>
<script src="./jquery-ui-1.10.4.custom.min.js"></script>
  <script>
  $(function() {
      $( "#datepicker" ).datepicker();
    });
$(function() {
    $( document ).tooltip();
  });
  </script>
<style>
  label {
display: inline-block;
width: 5em;
    }
</style>
<title> 

<? 

  echo $timecat_title;

if ( $_SESSION['company'] != null ) echo ( " for " . $_SESSION['company'] ); ?></title>

<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">

</head>
<body bgcolor = "#dddddd" text="#111111" <? require_once ("./onload.php") ?> > 

<?

include_once ("login_check.php");

include_once ("pagenav.php");


?>