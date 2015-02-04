<?
require_once ("./header.php");
require_once ('./tcpdf/tcpdf.php');
require_once ('./tcpdf/config/lang/eng.php');
require_once ("./database_functions.php");

$pagename = "create_invoice";
$selected = array ();

// TODO: make footer/subfooter, accent color, into preferences for each company and fetch from db 

$get_prefs_sql = "SELECT * FROM company LIMIT 1";  // There should only be one company per database anyway
$get_prefs_result = pg_query ( $get_prefs_sql );
$prefs_row =  pg_fetch_assoc ( $get_prefs_result, 0 );

foreach ( $prefs_row as $lt_header => $lt_row_item ) {
  ${$lt_header . "_company"} = $lt_row_item;  // this puts the variable from the database in a variable with the column name
}

$footer_string = "$company_name_company $address1_company $address2_company Phone $phone_company $email_company";
$red_footer_color = 120;
$green_footer_color = 160;
$blue_footer_color = 120;

// assign post parameters to variables

foreach ($_POST as $param_name => $param_val) $$param_name = $param_val;  // assign all posts to varible names for ease of reference

if ( $_POST['create_invoice'] == "Create Invoice" ) {  // Creating Invoice From Selections
  define ('K_PATH_IMAGES', './logos/' );
  $selected = explode ( ",", rtrim ( $_POST['invoice_ids'], "," ) ); // strip end comma if any then turn into array separated by commas
  
  echo "<p><b>Created invoice for " . sizeof ( $selected ) . " entries.</b>"; //  Please download it <a href=\"./invoice.pdf\" target=\"_new\">here</a><p>.";
  
  $timeentry_sql = "SELECT * from timeentry WHERE ";
  foreach ($selected as $entry ) $timeentry_sql .= "entryid = $entry OR ";
  $timeentry_sql = substr ( $timeentry_sql, 0, strlen ($timeentry_sql) - 4);  // remove the last four chars: " OR "
  $timeentry_sql .= " ORDER BY date";
  // echo "<p>Your SQL is <pre>$timeentry_sql</pre>";
  
  $row_entries_result = pg_query ( $timeentry_sql );  // TODO: Check this data to ensure it is all one client
  
  $my_pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, "LETTER", true, 'UTF-8', false);
  $invoice_number = getMaxInvoiceNumber() + 1;

  // set document information
  $my_pdf->SetCreator(PDF_CREATOR);
  $my_pdf->SetAuthor($company_name_company);  
  $my_pdf->SetTitle('Invoice' . $invoice_number); 
  $my_pdf->SetSubject('Invoice' . $invoice_number);
  
  // set default header data
  $logo_header = $_SESSION['company_id'] . ".png";  
  $logo_header_info = getimagesize( K_PATH_IMAGES . $logo_header );
  $logo_width = $logo_header_info[0];  

  $my_pdf->SetHeaderData($logo_header, 50, "Invoice #$invoice_number                                                                                    " . date ("F j, Y") , "\n$address1_company \n$address2_company"); 
  
  // set header and footer fonts
  $my_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 9));
  $my_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
  
  // set default monospaced font
  $my_pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
  
  //set margins
  $my_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  $my_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
  $my_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
  
  //set auto page breaks
  $my_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
  
  //set image scale factor
  $my_pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

  //set some language-dependent strings
  $my_pdf->setLanguageArray($l);
  
  // set font
  $my_pdf->SetFont('times', '', 10);
  
  // add a page
  $my_pdf->AddPage();
  
  // time entries table
  
  $my_pdf->SetFillColor(255, 0, 0);
  $my_pdf->SetTextColor(255);
  $my_pdf->SetDrawColor(0, 0, 0);  // cell borders
  $my_pdf->SetLineWidth(0.3);
  $my_pdf->SetFont('', 'B');
  // Header
  $w = array(40, 35, 40, 45);
  $num_headers = count($header);
  for($i = 0; $i < $num_headers; ++$i) {
    $my_pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
  }
  $my_pdf->Ln();
  // Color and font restoration
  $my_pdf->SetFillColor(224, 235, 255);
  $my_pdf->SetTextColor(0);
  $my_pdf->SetFont('');
  // Data
  $fill = 0;
  
  // Cell (width, height, text, border, line return (?), align, fill, link, stretch, ignore_min_height, calign, valign)
  //MultiCell  MultiCell($ w, $ h, $ txt,  $ border = 0,  $ align = 'J',  $ fill = false,  $ ln = 1,  $ x = '',  $ y = '',  $ reseth = true,  $ stretch = 0,  $ ishtml = false,  $ autopadding = true,  $ maxh = 0,  $ valign = 'T',  $ fitcell = false   )
  
  $my_pdf->MultiCell(0, 10, "", '', 'L', false, 1);  // spacer after header
  
  $default_height = 7;
  $date_width = 20; 
  
  $description_width = 55;
  $time_width = 13;
  $timekeeper_width = 24;
  $rate_width = 15;
  $billed_total_width = $rate_width;
  $fee_detail_width = $rate_width;
  $notes_width = 28; // $notes_width is only used for calucations of width; the value passed to tcpdf is always zero to ensure even right margin (i.e., notes takes up exactly remaining width. 28 is like two pixels shorter than the reality  
  
  
  $spacer_width = $date_width + $time_width + $description_width; // may need to adjust as more rows added
  
  $bill_subtotal = 0;
  
  $fill=true;
  
  $my_pdf->SetFont('helvetica','B',10);
  
  $contact_height = 32;
  
  $contact_string = "$client_name";
  if ( $contact_first != "" && ( $contact_first . " " . $contact_last != $client_name ) ) $contact_string .= "\nAttn: $contact_first $contact_last";
  $contact_string .= "\n$address1";
  if ( ! empty ($address2 ) ) { $contact_string .= "\n$address2"; $contact_height = 38; }
  $contact_string .= "\n$city, $state $zipcode \n$email";
  
  $my_pdf->MultiCell (12, $contact_height, "To:", 0, 'L', false, 0 );
  $my_pdf->SetFont('helvetica','',10);
  $my_pdf->MultiCell (70, $contact_height, $contact_string, 0, 'L', false, 1 );
  $my_pdf->SetFont('helvetica','B',9);
  
// Matter, etc., sub-header row
$header_height = 8;

$my_pdf->MultiCell(60, $header_height, "Matter", 'LTRB', 'C', true, 0, '', '', true, 0, false, true, $header_height, 'M');
$my_pdf->MultiCell(30, $header_height, "Payment Terms", 'LTRB', 'C', true,0, '', '', true, 0, false, true, $header_height, 'M');
$my_pdf->MultiCell(40, $header_height, "Due Date", 'LTRB', 'C', true,0, '', '', true, 0, false, true, $header_height, 'M');
$my_pdf->MultiCell(30, $header_height, "Past Due", 'LTRB', 'C', true,0, '', '', true, 0, false, true, $header_height, 'M');
$my_pdf->MultiCell(0, $header_height, "Total Due", 'LTRB', 'C', true, 1, '', '', true, 0, false, true, $header_height, 'M');

// Matter, etc., data row

$my_pdf->SetFont('helvetica','',10);
$invoice_amount = 0;

for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) { 
  $lt_row = pg_fetch_assoc ( $row_entries_result, $lt);
  $timekeeper_rate = round (getRateForTimekeeper ($lt_row['timekeeper_email'])  * getMultiplierForClient ($client_name), 2);
  $bill_line_amount = ($timekeeper_rate * $lt_row['duration'] * $lt_row['writeoff']) + $lt_row['flatfee_item'];
  $invoice_amount += $bill_line_amount;
}

$data_height = $my_pdf->getStringHeight ( 60, $matter_description) + 1;
$my_pdf->MultiCell(60, $data_height, "$matter_description", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $data_height, 'M');
$my_pdf->MultiCell(30, $data_height, "$payment_terms", 'LTRB', 'C', false,0, '', '', true, 0, false, true, $data_height, 'M');
$my_pdf->MultiCell(40, $data_height, "$due_date", 'LTRB', 'C', false,0, '', '', true, 0, false, true, $data_height, 'M');
$my_pdf->MultiCell(30, $data_height, "$" . number_format ( floatval ($past_due), 2), 'LTRB', 'C', false,0, '', '', true, 0, false, true, $data_height, 'M');
$my_pdf->MultiCell(0, $data_height, "$" . number_format (  ( $invoice_amount + $past_due) , 2), 'LTRB', 'C', false, 1, '', '', true, 0, false, true, $data_height, 'M');
$my_pdf->MultiCell(0, 5, "", '', 'L', false, 1);  // spacer row

// Date, Time, etc., header row
$my_pdf->SetFillColor( 210, 210, 210);
$fill=false;
$my_pdf->SetFont('helvetica','B',10);
$my_pdf->MultiCell($date_width, $default_height, "Date", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell($time_width, $default_height, "Time", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell($description_width, $default_height, "Description", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell($timekeeper_width, $default_height, "Timekeeper", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell($rate_width, $default_height, "Rate", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell($fee_detail_width, $default_height, "Fee", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell($billed_total_width, $default_height, "Billed", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->MultiCell(0, $default_height, "Notes", 'LTRB', 'C', false, 0, '', '', true, 0, false, true, $default_height, 'M');
$my_pdf->Ln();

$my_pdf->SetFont('','', 8);
$fill=false;

// main body -- showing time entries

$total_time_entries_height = 0;

// first calculate the height of all the rows, which is cumulated in total_time_entries_height
for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) {
  if ( $lt_row['writeoff'] == -1 ) continue;
  $description = $lt_row['descrition'];
  if ( $description == null )  $description = " ";
  $lt_row = pg_fetch_assoc ( $row_entries_result, $lt);
  $desc_height = $my_pdf->getStringHeight ( $description_width, $description) + 1;
  $notes_height = $my_pdf->getStringHeight ( $notes_width, $notes ) + 1; 
  $total_time_entries_height += max ($desc_height, $notes_height);
}

$page_y_position = 0;

$time_entry_height_remaining = $total_time_entries_height;

for ($lt = 0; $lt < pg_numrows($row_entries_result); $lt++) {
  $lt_row = pg_fetch_assoc ( $row_entries_result, $lt);
  if ( $lt_row['writeoff'] == -1 ) continue; // skipping items with writeoff -1, don't go on invoice
  $fill=!$fill;
  $desc_height = $my_pdf->getStringHeight ( $description_width, $lt_row['description']) + 1;
  $notes_height = $my_pdf->getStringHeight ( $notes_width, $lt_row['notes'] ) + 1;
  $row_height = max ($desc_height, $notes_height);

  $time_entry_height_remaining -= $row_height;
  $page_y_position += $row_height;
  if ( ( $page_y_position > 100 && $time_entry_height_remaining < 10 ) || $page_y_position > 160 ) {
    $page_y_position = 10;
    $my_pdf->AddPage();
    $my_pdf->Ln();
  }
  $timekeeper_rate = round (getRateForTimekeeper ($lt_row['timekeeper_email']) * getMultiplierForClient ($client_name), 2);
  $fee_detail_amount; $bill_line_amount;
  if ( $lt_row['flatfee_item'] == null ) {
    $fee_detail_amount = ($timekeeper_rate * $lt_row['duration'] );
    $bill_line_amount = ($timekeeper_rate * $lt_row['duration'] * $lt_row['writeoff'] );
  } else {
    $fee_detail_amount = $lt_row['flatfee_item'];
    $bill_line_amount = $lt_row['flatfee_item'] * $lt_row['writeoff'];
  }
  if ($row_height < $default_height) $row_height = $default_height;
  $my_pdf->MultiCell($date_width, $row_height, $lt_row['date'], 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M');
  $my_pdf->MultiCell($time_width, $row_height, $lt_row['duration'], 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M');
  //  Had to remove extended options after ln because they prevented line wrapping for some reason.
  //  $my_pdf->MultiCell($description_width, $row_height, $lt_row['description'], 'LTRB', '', $fill, 0, null, null, true, 0,     false, false, $default_height, 'M')
  $my_pdf->MultiCell($description_width, $row_height, $lt_row['description'], 'LTRB', '', $fill, 0 );
  $my_pdf->MultiCell($timekeeper_width, $row_height, getFirstNameForTimekeeper ($lt_row['timekeeper_email']), 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
  // rate
  if ( $lt_row['flatfee_item'] == null )    
  $my_pdf->MultiCell($rate_width, $row_height, "$" . $timekeeper_rate, 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
   else   $my_pdf->MultiCell($rate_width, $row_height, "", 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
  // fee detail
  if ( $lt_row['writeoff'] == '1' ) {
    $my_pdf->MultiCell($fee_detail_width, $row_height, "$" . number_format (  $fee_detail_amount , 2 ), 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
  } else {
    // strikeout through reduced amount
    $my_pdf->SetFont('','D');
    
    $my_pdf->MultiCell($fee_detail_width, $row_height, "$" . number_format (  $fee_detail_amount , 2 ), 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
    $my_pdf->SetFont('','');

  }
  // billed total
  $my_pdf->MultiCell($billed_total_width, $row_height, "$" . number_format (  $bill_line_amount , 2 ), 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
  // notes
  $my_pdf->MultiCell(0, $row_height, $lt_row['notes'], 'LTRB', '', $fill, 0 );
  // FOR TESTING, PRINT POSTION INSTEAD OF NOTES $my_pdf->MultiCell($notes_width, $row_height, round ($page_y_position) . " - " . round ($time_entry_height_remaining), 'LTRB', '', $fill, 0 );
  $bill_subtotal += $bill_line_amount;
  $my_pdf->Ln();    
}

$fill=false;

// subtotal
$my_pdf->SetFont('helvetica','B',10);

$my_pdf->MultiCell ( $spacer_width, $default_height, "", "", 'L', false, 0 ); 
$my_pdf->MultiCell ( $timekeeper_width , $default_height, "Subtotal", "LRTB", 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
$my_pdf->MultiCell ( 0, $default_height, "$" . number_format ( $bill_subtotal, 2 ) , "LRTB", 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
$my_pdf->Ln();

// past due
$my_pdf->MultiCell ( $spacer_width, $default_height, "", "", 'L', false, 0 ); 
$my_pdf->MultiCell ( $timekeeper_width, $default_height, "Past Due", 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
$my_pdf->MultiCell ( 0, $default_height, "$" . number_format ( floatval( $past_due ), 2 ) , "LRTB", 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M');  
$my_pdf->Ln();

// total
$my_pdf->MultiCell ( $spacer_width, $default_height, "", "", 'C', false, 0 ); 
$my_pdf->MultiCell ( $timekeeper_width , $default_height, "Total", 'LTRB', 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M'); 
$my_pdf->MultiCell ( 0, $default_height, "$" . number_format ( $past_due + $bill_subtotal, 2 ) , "LRTB", 'C', $fill, 0, '', '', true, 0, false, true, $default_height, 'M');  

$my_pdf->Ln();
$my_pdf->Ln();

$my_pdf->SetTextColor ($red_footer_color, $green_footer_color, $blue_footer_color);

$my_pdf->MultiCell ( 0 , $default_height * 2, $footer1_company, '', 'C', $fill, 0, '', '', true, 0, false, true, $default_height * 2, 'M'); 

$my_pdf->Ln();

$my_pdf->SetFont('','I',12);


$my_pdf->MultiCell ( 0 , $default_height * 2, $footer2_company, '', 'C', $fill, 0, '', '', true, 0, false, true, $default_height * 2, 'M'); 


do {

$identifier=""; // this will be the filename on the server

for ($lt = 0; $lt < 30; $lt++ ) $identifier .= chr(97 + mt_rand(0, 25));

} while ( file_exists ( "./invoices/ " . $identifier . "pdf" ) );

$invoice_insert_sql = "INSERT INTO invoices ( client_name, sent, date, amount, amount_paid, due_date, identifier ) VALUES ( '" . pg_escape_string ( $client_name ) . "', FALSE, '" . date ("F j, Y") ."', $invoice_amount, 0, '$due_date', '$identifier' )";

pg_query ( $invoice_insert_sql );

$update_time_entries_sql = "UPDATE timeentry SET invoice_no=" . getMaxInvoiceNumber() . " WHERE " ;

foreach ($selected as $entry ) $update_time_entries_sql .= "entryid = $entry OR ";
$update_time_entries_sql = substr ( $update_time_entries_sql, 0, strlen ( $update_time_entries_sql ) - 4);  // remove the last four chars: " OR "


pg_query ( $update_time_entries_sql );


$my_pdf->Output ( "./invoices/" . $identifier . ".pdf" , "F" );  // TODO: fancy system for organizing pdfs and invoices and re-fetching them.

$view_invoice_no = getMaxInvoiceNumber();

include_once ( "./view_invoices.php" );

// echo "<p>Invoice SQL: $invoice_insert_sql";

} else echo "<p>No action selected.";

?>
