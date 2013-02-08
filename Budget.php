<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>INDEX</title>
		<meta name="generator" content="Bluefish 2.2.3" >
		<meta name="copyright" content="">
		<meta name="keywords" content="">
		<meta name="description" content="">
		<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">

		<link type="text/css" href="jquery/css/ui-lightness/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
		<script type="text/javascript" src="jquery/js/jquery-1.7.2.min.js"></script>
		<script type="text/javascript" src="jquery/js/jquery-ui-1.8.21.custom.min.js"></script>

		<script type="text/javascript" src="includes/bgt_jsfunctions.js"></script>		
		<script type="text/javascript" src="includes/jsfunctions.js"></script>
		
		<link rel="stylesheet" href="includes/gnucashreports.css" type="text/css" media="screen" />
	</head>
	<?php
	// functions shared between 2 or more reports
	include 'includes/functions.php';
	// functions not shared with other reports, if any
	include 'includes/bgt_functions.php';
	?>
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
	$budget = 'baedce03cb41c2eee83b55b219ee5971';
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
	
	// use this to track errors in the query
	//echo $query;

	/* main TABLE*/
	
	BGT_BuildTable(withtotal);

		?>
		</div>         
         
    </body>  
    </html>
