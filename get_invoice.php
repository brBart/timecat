<?

include_once ( "./login_check.php" );
include_once ( "./database_functions.php" );
$id = $_GET['id'];
$action = $_GET['action'];
$invoice_no = $_GET['invoice_no'];

if ( $id == "" ) {
  include_once ("./header.php");
  echo "<b>ERROR: No id specified";
  include_once ("./footer.php");
  exit();
}

$file = "./invoices/$id" . ".pdf";

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($file));

$client_name = getClientForInvoiceNumber ( $invoice_no );

$client_name = str_replace(' ', '_', $client_name);

$filename = Slug ( $_SESSION['company'] . "_invoice_" . $invoice_no . " (" . $client_name . ")" );

if ( $action == "dl" ) {
  header('Content-Disposition: attachment; filename=' . basename($filename));
} else {
  header('Content-Disposition: inline; filename=' . basename($filename));
}

readfile($file);

function Slug($string, $slug = '-', $extra = null)
{
  return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, $string), $slug));
}  

?>