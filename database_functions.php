<?
// These functions assume a connection to the database.


function getAdministrator ($email) {
  $email = pg_escape_string ($email);
  $get_admin_sql = "SELECT administrator FROM timekeeper WHERE email = '$email'";
  $result = pg_query ($get_admin_sql);
  if ( $result != FALSE ) {
    $result_array = pg_fetch_row ( $result, 0 );
    if ( $result_array[0] == "t" ) return TRUE; else return FALSE;
  } 
  return FALSE;
}

function getRateForTimekeeper ($timekeeper  ) {
  $get_rate_sql = "SELECT rate FROM timekeeper WHERE email = '".$timekeeper."'";
  $result = pg_query ($get_rate_sql);
  $client_multiplier = 1;

  if ( $result != FALSE ) {
    $result_array = pg_fetch_row ( $result, 0 );
    // echo "<p><b>RESULT IS " . $result_array[0] . "</b>"; // For debugging
    return $result_array[0];
  }
  else {
    echo "<p><b>Error fetching billing rate for $timekeeper with </b> <pre>$get_rate_sql</pre>";
    return "0";
  }
}

function getMultiplierForClient ( $client_name ) {
  // if ($client_name == null) return 1;
  $get_multiplier_sql = "SELECT fee_adjust FROM client WHERE client_name='$client_name'";
  $result = pg_query ($get_multiplier_sql);
  if ( $result != FALSE ) {
    $result_array = pg_fetch_row ( $result, 0 );
    // echo "<p><b>RESULT IS " . $result_array[0] . "</b>"; // For debugging
    return $result_array[0];
  }
  else {
    echo "<p><b>Error fetching fee_adjust for $client_name with </b> <pre>$get_multiplier_sql</pre>";
    return 1;
  }
}

function getFirstNameForTimekeeper ($timekeeper) {
  $get_name_sql = "SELECT first_name FROM timekeeper WHERE email = '".$timekeeper."'";
  $result = pg_query ($get_name_sql);
  if ( $result != FALSE ) {
    $result_array = pg_fetch_row ( $result, 0 );
    // echo "<p><b>RESULT IS " . $result_array[0] . "</b>"; // For debugging
    return $result_array[0];
  }
  else {
    echo "<p><b>Error fetching first name for $timekeeper with </b> <pre>$get_rate_sql</pre>";
    return "0";
  }
}

function getLastNameForTimekeeper ($timekeeper) {
  $get_name_sql = "SELECT last_name FROM timekeeper WHERE email = '".$timekeeper."'";
  $result = pg_query ( $get_name_sql);
  if ( $result != FALSE ) {
    $result_array = pg_fetch_row ( $result, 0 );
    // echo "<p><b>RESULT IS " . $result_array[0] . "</b>"; // For debugging
    return $result_array[0];
  }
  else {
    echo "<p><b>Error fetching first name for $timekeeper with </b> <pre>$get_rate_sql</pre>";
    return "0";
  }
}

function getPastDueForClient ($client) { // TODO: implement me
  $past_due = 0;
  $client = pg_escape_string ($client);
  $get_invoices_sql = "SELECT amount, amount_paid FROM invoices WHERE due_date < CURRENT_DATE AND client_name = '$client' AND is_deleted=false";
  $result = pg_query ( $get_invoices_sql);
  for ($lt = 0; $lt < pg_numrows($result); $lt++) {
    $lt_row = pg_fetch_assoc ( $result, $lt );
    $past_due += ( $lt_row['amount'] - $lt_row['amount_paid'] );
  }
  return $past_due;
}

function getMaxInvoiceNumber () {
  
  $get_max_invoice_sql = "SELECT max (number) FROM invoices";
  $result = pg_query ( $get_max_invoice_sql );
  $result_val = pg_fetch_result ( $result, 0, 0 );
  if ( $result_val == 0 ) $result_val = 1000;  // TODO:  Fix this so that the user has a way to set their initial value
  return $result_val;
}

function getClientForInvoiceNumber ( $invoice_number ) {
  $get_client_sql = "SELECT client_name FROM invoices WHERE number=$invoice_number";
  $result = pg_query ($get_client_sql);
  if ( $result != FALSE ) {
    $result_array = pg_fetch_row ( $result, 0 );
    // echo "<p><b>RESULT IS " . $result_array[0] . "</b>"; // For debugging
    return $result_array[0];
  }
  else {
    echo "<p><b>Error fetching client for invoice #$invoice_number with </b> <pre>$get_rate_sql</pre>";
    return "0";
  }
}

?>