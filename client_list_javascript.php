<script>
$(function() {
    var availableTags = [
<?

$get_client_name_sql = "SELECT client_name FROM client WHERE current_client='t'";
$client_name_result = pg_query ($get_client_name_sql);
for ($lt = 0; $lt < pg_numrows($client_name_result); $lt++) {
  $client_name_js = pg_result ( $client_name_result, $lt, 0 );
    echo "\"$client_name_js\"";
    if ($lt + 1 != pg_numrows($client_name_result)) echo ",";
}

?>


 ];
    $( "#tags" ).autocomplete({
      source: availableTags
    });
  });
  </script>
