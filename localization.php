<? $database_field_lookup = array (
"entryid" => "Serial #",
  "duration" => "Duration",
  "matter_id" => "Matter",
  "invoice_no" => "Invoice No.",
  "timekeeper_email" => "Timekeeper",
  "writeoff" => "Writeoff",
  "date" => "Date",
  "client_name" => "Client Name",
  "description" => "Description",
  "notes" => "Notes",
  "flatfee_item" => "Flat Fee Item",
  "sent" => "Sent",
  "date" => "Date",
  "amount" => "Amount",
  "amount_paid" => "Amount Paid",
  "due_date" => "Due Date",
  "number" => "Invoice No.",
  "address1" => "Address 1",
  "address2" => "Address 2",
  "phone" => "Phone",
  "email" => "email",
  "footer1" => "Footer 1",
  "footer2" => "Footer 2",
  );


// helpfile

$helpfile = array (
		   "client_name" => "Please use the autocomplete name, since spellings other than those in the database will result in an error.",
		   "fee_adjust" => "This is a ratio by which standard billing rate are adjusted.  For example to increase billing rate by 20% above standard rates for this client, enter 1.2.  To give a 20% discount, enter 0.8.",
		   "writeoff" => "Enter a fraction of the time entry to be included.  If the value is 1, the entire time entry is billed.  If the value is 0.8, only 80% of the time entry will be billed (i.e., 20% is written off).  If the value is 0, the entire entry will be written off.  If the value is -1 (negative one), the entire entry will be written off and it will not appear on the invoice.",
		   "description" => "Description of the work as it will show up on the invoice.",
		   "notes" => "Notes on the time entry which will appear on the invoice, such as reasons for a writeoff.",
		   "matter" => "This isn't really used yet, but will be used to separate out different matters (i.e., cases or types of work) for a single client's invoice.",
		   "duration" => "The duration in hours, to the nearest tenth of an hour.",
		   "username" => "The email address of the timekeeper.  The timekeeper's actual name will appear with the time entry on the invoice, not the email address." ,
		   "password" => "Ideally two or more words run together to make a passphrase.  Leave blank to not change it.",
		   "flatfee" => "Put a dollar amount here for a flat fee item.  It cannot also have a duration or start/end time",
		   "duration" => "The duration in hours.  The system rounds to the nearest tenth of an hour. If you are entering the duration, leave start and end time blank.",
		   "start_time" => "The start time, in 24 hour format, colon is optional. Time is rounded up to the next tenth of an hour (i.e., 7 minutes is 0.2 hours).  You cannot also enter a duration, since the system automatically calcluates duration from the start and end time if they are entered.",
		   "end_time" => "The end time, in 24 hour format, colon is optional. Time is rounded up to the next tenth of an hour (i.e., 7 minutes is 0.2 hours). You cannot also enter a duration, since the system automatically calcluates duration from the start and end time if they are entered.",
		   "register_payment" => "This amount is added to the &quot;Amount Paid&quot; above. A negative value can be used to reduce the amount paid, for example to correct an erroneous payment entry.",
		   "upload_clients" => "The uploaded file should be a comma-delimited values (CSV) file, which can be created in software such as Microsoft Excel. It should have no column headers, with each client on a new line, and data in the following columns: The client name, client email, client contact first name, client contact last name, address line one, address line two, city, state, zipcode. Note that this is a different column format than timecat's export function. "
		   );


?>