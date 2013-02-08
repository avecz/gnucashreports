	<script>
	$(function() {
		$( "#startdate" ).datepicker({
			dateFormat: "dd/mm/yy",
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#enddate" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		$( "#enddate" ).datepicker({
			dateFormat: "dd/mm/yy",
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 3,
			onSelect: function( selectedDate ) {
				$( "#startdate" ).datepicker( "option", "maxDate", selectedDate );
			}
		});

		$( "#period" ).buttonset();

	});
	</script>
<?php

	if(isset($_POST['startdate'])){
		$startdate = $_POST['startdate'];
		$enddate = $_POST['enddate'];
		$period = $_POST['period'];
		$SelectedAccounts = $_POST['acc'];
	}else{
		$startdate = "01/01/2013";
		$enddate = date('d/m/Y');
		$period = "monthly";
	}
	
	echo "<script>var check = \"".$period."\";</script>";
	
	datecompare ($startdate, $enddate);

	//calendar
	echo "<div id=\"dateselector-nav\">";
	echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
	echo "<input type=text id=\"startdate\" name=startdate class='calendarSelectDate' size='10' value=". $startdate ."> ";
	echo "<input type=text id=\"enddate\" name=enddate class='calendarSelectDate' size='10' value=". $enddate .">";
	
	echo "<div id=\"period\">";
	echo "<input type=\"radio\" id=\"monthly\" name=\"period\" value=\"monthly\"><label for=\"monthly\">monthly</label>";
	echo "<input type=\"radio\" id=\"quarterly\" name=\"period\" value=\"quarterly\"><label for=\"quarterly\">quarterly</label>";
	echo "<input type=\"radio\" id=\"semiannually\" name=\"period\" value=\"semiannually\"><label for=\"semiannually\">semiannually</label>";
	echo "<input type=\"radio\" id=\"annually\" name=\"period\" value=\"annually\"><label for=\"annually\">annually</label>";
	echo "</div>";

	echo "</div>";
	
	include 'includes/accselector.php';
	
	echo "<input type=submit> </form>";

	$startdate = datesqlformat($startdate);
	$enddate = datesqlformat($enddate);
?>
	<script>
	$("#"+check).attr("checked",true);

	</script>
