<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>INDEX</title>
		<meta name="generator" content="Bluefish 2.0.3" >
		<meta name="copyright" content="">
		<meta name="keywords" content="">
		<meta name="description" content="">
		<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
		<script type='text/javascript' src='includes/calendar.js'></script>
		<script type="text/javascript" src="includes/jsfunctions.js"></script>
		<link rel="stylesheet" href="includes/gnucashreports.css" type="text/css" media="screen" />
	</head>
	<?php	include 'functions.php';?>
	<body>
		<div id="headercontainer">
			<div id="topheader">
			<?php include 'includes/topmenu.php'; ?>
				<div id="report-nav">
				</div>

			</div>
		</div>
	
		<div id="content">
		<?php

	/* This show the budgets and the basic information about each.
	 * Not necessary now.
	 * Use this later to enable the budget selection. I think a list with the available budgets would  be nice */
		 
	/*
	$query = "SELECT * FROM budgets AS bgt";
	$query .= " inner join recurrences AS r on r.obj_guid = bgt.guid";

	$resultado = mysql_query($query,$connection);
	while ($linha = mysql_fetch_array($resultado)) {
		echo $linha['guid']." - ".$linha['name']." - ".$linha['num_periods']." - ".$linha['recurrence_period_start']."<br>";
		}
		echo "<br>";
		
	mysql_free_result($resultado);		
	*/

	/* here the budget is selected and it is called a function named budget_date_range()
	 * This function will return the necessary array and variables to query properly */
	$budget = '1364120d28e4046dae48feba0b14f787';
	$query = "SELECT * FROM budgets AS bgt";
	$query .= " inner join recurrences AS r on r.obj_guid = bgt.guid";
	$query .= " where bgt.guid = '".$budget."'";
	
	$resultado = mysql_query($query,$connection);
	while ($linha = mysql_fetch_array($resultado)) {
		budget_date_range($linha['recurrence_period_start'], $linha['num_periods']);
		}
	mysql_free_result($resultado);

	/* call the  function to buid the main query */
	BudgetQuery();

	/* main TABLE*/
	
	BGT_BuildTable(withtotal);

/*
	$resultado = mysql_query($query,$connection);

	echo "\r\n<table id=\"data\"><thead><th></th>\r\n";
		
		// create the collum names
		echo "<tr><th></th>";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			echo "<th colspan=2>".$eachmonth."</th>";
			}
		echo "</tr><tr><th></th>";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			echo "<th>Bgt</th><th>Ato</th>";
			}
		echo "</tr>";

		// populate the table with the information
		$bgt  = "bgt_";
		while ($linha = mysql_fetch_array($resultado)) {
			echo "<tr><td class=\"col\">".$linha['acccode']." ".$linha['accname']."</td>";
				foreach ($dateToQuery as $eachmonth => $period_num) {
				echo "<td class=\"bgt\">".number_format($linha[$bgt.$eachmonth], 2, ',', '.')."</td><td>".number_format($linha[$eachmonth], 2, ',', '.')."</td>";
				}
		echo "</tr><tr>";

			}
*/			
		?>
		</div>         
         
    </body>  
    </html>
