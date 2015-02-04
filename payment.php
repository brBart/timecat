<?

include_once ("./header.php");

$invoice_no = $_GET['invoice_no'];

if ($_POST["SubmitPayment"] == "Submit") {
  $amount_paid = $_POST['amount_paid'];
  $invoice_no = $_POST['invoice_no'];
  
  if ( ( floatval ($amount_paid ) == 0 ) ||  ( intval ($invoice_no) == 0 ) ) { // data type checking
    echo "Amount paid must be just a number, no dollar signs, letters, or other symbols. $amount_paid and $invoice_no";
    include_once ("footer.php");
    exit();
  }
  
  $update_paid_amount_sql = "UPDATE invoices SET (amount_paid) = (amount_paid + $amount_paid) WHERE number = $invoice_no";

  pg_query ( $update_paid_amount_sql );
}

if ( intval ($invoice_no ) != 0 ) {

  $get_invoice_info_sql = "SELECT * from invoices WHERE number = $invoice_no";
  $invoice_result = pg_query ($get_invoice_info_sql );
  $row_items = pg_fetch_assoc ( $invoice_result, 0 ); // first and only row as assoc array
  foreach ($row_items as $row_name => $row_val ) {    
    $$row_name = $row_val;
    if ( $$row_name == "t" ) $$row_name = "yes";
    if ( $$row_name == "f" ) $$row_name = "no";
  }  

} else {
  echo "Invoice number not valid.";
  include_once ("footer.php");
  exit();
}
?>

<div class="RoundTableNoHeader" style="display:table;">

<table>
<tr><td class = "header" colspan = 2 style = "text-align: center">Invoice Number <? echo $number ?></td></tr>
<tr><td>Client Name</td><td style="font-weight: normal"><? echo $client_name ?></td></tr>
<tr><td>Sent</td><td style="font-weight: normal"><? echo $sent ?></td></tr>
  <tr><td>Date</td><td style="font-weight: normal"><? echo  date ("F j, Y", strtotime ($date) ) ?></td></tr>
<tr><td>Amount</td><td style = "font-weight: normal">$<? echo $amount ?></td></tr>
<tr><td>Amount Paid</td><td style = "font-weight: normal">$<? echo $amount_paid ?></td></tr>
<tr><td>Amount Due</td><td style = "font-weight: normal">$<? echo $amount - $amount_paid ?></td></tr>
  <tr><td>Due Date</td><td style = "font-weight: normal"><? echo date ("F j, Y", strtotime ($due_date) ) ?></td></tr>
</table>
</div>
<p>
<div class="RoundTableNoHeader" style="display:table;">
<table>
<form action="./payment.php" method=POST>
  <tr><td class = "header" colspan=3>Register New Payment <? echo (help_link ("register_payment"));  ?></td></tr><tr><td>Amount Paid</td><td><input type = "text" name ="amount_paid" value="<? echo  ($amount - $amount_paid); ?>"></td>
<td><input type="submit" name= "SubmitPayment" value = "Submit"></td></tr>
</table>
<input type="hidden" name = "invoice_no" value="<? echo $number ?>">
</form>

<? include_once "footer.php" ?>
