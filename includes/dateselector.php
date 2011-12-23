<div id="dateselector-nav">
<?php

	if(isset($_POST['startdate'])){
		$startdate = $_POST['startdate'];
		$enddate = $_POST['enddate'];
		$period = $_POST['period'];
	}else{
		$startdate = "01/01/2011";
		$enddate = date('d/m/Y');
		$period = "monthly";
	}
	
	if($period <> 'monthly'){
		$options[] = "monthly";
		}
	if($period <> 'quarterly'){
		$options[] = "quarterly";
		}
	if($period <> 'semiannually'){
		$options[] = "semiannually";
		}
	if($period <> 'annually'){
		$options[] = "annually";
		}
		
	datecompare ($startdate, $enddate);

	//calendar
	echo "<form action='".$thisreport."' method='post'>";
	echo "Data inicial: <input type=text name=startdate class='calendarSelectDate' size='10' value=". $startdate ."><br>";
	echo "Data final: <input type=text name=enddate class='calendarSelectDate' size='10' value=". $enddate .">";
	echo "<select name=period><option selected>".$period."</option>";
	
	foreach ($options as $option) {
		echo "<option>".$option."</option>";
		}
	echo "<input type=submit> </form>";
	echo "<div id='calendarDiv'></div>";


	$startdate = datesqlformat($startdate);
	$enddate = datesqlformat($enddate);
?>
</div>