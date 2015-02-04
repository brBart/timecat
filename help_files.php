<?

function help_link ( $link_name ) {
  include ("./localization.php"); 
  //    return "<span title=\"" . $helpfile[$link_name] . "\"><small><a target=\"#\">[?]</a></small></span>";
  return "<small><a title=\"" . $helpfile[$link_name] . "\">[?]</a></small>";
  //  return "<a href=\"#\" title=\"".$helpfile[$link_name]."\"><img align=\"right\" valign=\"middle\" src=\"./images/help3_sm.png\"</a>";
  // return "<a href=\"#\" title=\"".$helpfile[$link_name]."\"><img style=\"vertical-align:middle; float:right\" src=\"./images/help3_sm.png\"</a>"; // GAH! Does not display except lowest one.
}

?>