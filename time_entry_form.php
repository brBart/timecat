<? require "client_list_javascript.php"; ?>
<tr><td colspan=2><b>Time Entry</b></td></tr>

<tr><td>Username <? echo (help_link ("username" ));  ?><td><input type="text" name="email" value= "<? echo ( $_SESSION['email']); ?>" >
<tr><td>Date<td><input type = "text" name ="date" id="datepicker" value="<? if ( $_POST['timeentry'] == "Enter" ) { echo ( $_POST['date'] ); } else echo ( date ( "m/d/y" ) ); ?>">
   <tr><td>Client name <? echo (help_link ("client_name" ));  ?> <td><input type = "text" name ="client_name" id = "tags" <? if ( $_POST['timeentry'] == "Enter" ) { echo "value=\"".$_POST['client_name']. "\""; }  ?> > 
<tr><td>Duration <? echo (help_link ("duration" ));  ?><td><input type = "text" name ="duration">
<tr><td>Start Time <? echo (help_link ("start_time" ));  ?><td><input type = "text" name ="start_time">
<tr><td>End Time <? echo (help_link ("end_time" ));  ?><td><input type = "text" name ="end_time">
   
   <tr><td>Writeoff <? echo (help_link ("writeoff" ));  ?><td><input type = "text" name ="writeoff" value="1">

<tr><td>Matter <? echo (help_link ("matter" ));  ?><td><input type = "text" name ="matter_id" value="General"> <!-- TODO Need to do some fancy jquery stuff to populate matters when client is selected -->
<tr><td>Description <? echo (help_link ("description" ));  ?><td><textarea name ="description" cols=40 rows=4></textarea>										 
<tr><td>Notes <? echo (help_link ("notes" ));  ?><td><textarea name ="notes" rows=2 cols=40></textarea>
<tr><td>Flat Fee Item <? echo (help_link ("flatfee" ));  ?><td><input name ="flatfee" type="text">

<tr><td colspan=2><input type = "submit" name = "timeentry" value="Enter">
