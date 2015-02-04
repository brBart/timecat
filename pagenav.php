<form method=POST action="./index.php">
<div class="RoundTableNoHeader">
<table border=0 width="100%"><tr><td width="95%" style="vertical-align:center"><CENTER><H3><? echo $timecat_title . " for " . $_SESSION['company']; ?></h3></center><td style="font-size: small; font-weight: normal; text-align: center"><? echo $_SESSION['email'] ?><form method=POST action="./index.php"><input type=submit name="Logout" value="Logout"></table></form></div>

   <div class="RoundTableNoHeader">
<table border = "0" width = "100%">
<tr>

<?

$administrator_only_pages = explode (" ", "view_invoices admin add_client add_timekeeper"); 
$pages_list = "enter_time view_time view_timekeeper view_client add_client add_timekeeper view_invoices admin";  // list all pages here.  Each page should have "$pagename" variable in it so that this pavenav file knows which page it is on.

$all_pages_array = explode ( " " , $pages_list ); // turned into an array. 


if ( $_SESSION['administrator'] ) $pages = $all_pages_array; else {
  $pages = array();
  foreach ( $all_pages_array as $page ) {
    if ( ! in_array ( $page, $administrator_only_pages) ) array_push ( $pages, $page );
  }

}
$width_pct = round ( (1 / count ( $pages ) ), 2 ) * 100;

foreach ( $pages as $page ) {  // $page is a potential page; $pagename is this page
  $page_human = $page;
  $page_human = str_replace ( "_", " ", $page_human );
  $page_human = ucwords ( $page_human );
  if ( $page == $pagename ) {
    echo "<td width=\"$width_pct%\" class=\"selected\">$page_human</td>";
  } else {      
    if ( $page == "enter_time" ) $page = "index";
    echo "<td width=\"$width_pct%\"><a href=\"./$page.php\">$page_human</a></td>";
  }
}

?>
</tr>
</table>
</div>
<p>