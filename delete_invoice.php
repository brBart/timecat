<?

include_once ("./header.php");

$invoice_no = $_GET['invoice_no'];

if ( intval ($invoice_no ) != 0 ) {

  if ( $_GET['action']=="undo") {  // undelete it
    pg_query ("UPDATE invoices SET is_deleted = FALSE WHERE number=$invoice_no");
    echo "<p><b>Invoice $invoice_no undeleted.</b>";
  } else {  // delete it
    pg_query ("UPDATE invoices SET is_deleted = TRUE WHERE number=$invoice_no");
    pg_query ("UPDATE timeentry SET invoice_no = null WHERE invoice_no=$invoice_no");
    echo "<p><b>Invoice $invoice_no deleted.</b>"; //" <a href=\"./delete_invoice.php?invoice_no=$invoice_no&action=undo\">undelete</a></b>"; // no longer an option now that invoice no is not left associated with old invoice.
  }

} else echo "<p><b>Problem processing invoice number.</b></p>";  // invoice no was not an int

include_once ("./footer.php");



?>