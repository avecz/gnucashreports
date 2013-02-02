 <?php
 
 	//change this to config the connection
 	
	$connection = mysql_pconnect("localhost","pedro","123456");
	mysql_select_db("pgnucash",$connection);


	//This function will ensure that the enddate is later than the startdate
	function datecompare ($date1, $date2) {
		global $startdate, $enddate;
		
		$date1 = substr($date1,6,4).substr($date1,3,2).substr($date1,0,2);
		$date2 = substr($date2,6,4).substr($date2,3,2).substr($date2,0,2);
		if ($date1 > $date2) {
			$startdate = "01/01/2012";
			$enddate = date('d/m/Y');
			echo "<b>Data final selecionada é maior que a data inicial. Selecionando valores padrões</b>";
		}
	}
	
	// Function to format the date in brazilian format dd/mm/aaaa to the sql format aaaa-mm-dd
	function datesqlformat($datebr) {
		if (!empty($datebr)){
		$p_dt = explode('/',$datebr);
		$date_sql = $p_dt[2].'-'.$p_dt[1].'-'.$p_dt[0];
		return $date_sql;
		}
	}

	//this function returns the column label. It is called from within the functions that create the date arrays, to include in the array the appropriate column names
	function getColumnLabel($period, $currperiod, $year){
		switch($period) {
			case 'monthly':
					return date("Y-m", mktime(0,0,0,$currperiod,1,$year));
				break;

			case 'quarterly':
					return "Q".$currperiod."-".$year;
				break;

			case 'semiannually':
					return "S".$currperiod."-".$year;
				break;

			case 'annually':
					return $year;
				break;
				
			}
	}

	//this function returns the first day or the last day of a given time period
	function getPeriodLastOrFirstDay($period, $currperiod, $year, $lastOrFirst){
		switch($period) {
			case 'monthly':
				if ($lastOrFirst == 'last'){
					return date("Y-m-d", mktime(0,0,0,$currperiod+1,0,$year));
				} else {
					return date("Y-m-d", mktime(0,0,0,$currperiod,1,$year));
					}
				break;

			case 'quarterly':
				switch($currperiod) {
					case '1':
						if ($lastOrFirst == 'last'){
							return $year."-03-31";
							} else {
							return $year."-01-01";
							}
						break;
					case '2':
						if ($lastOrFirst == 'last'){
							return $year."-06-30";
							} else {
							return $year."-04-01";
							}
							break;
					case '3':
						if ($lastOrFirst == 'last'){
							return $year."-09-30";
							} else {
							return $year."-07-01";
							}
							break;
					case '4':
						if ($lastOrFirst == 'last'){
							return $year."-12-31";
							} else {
							return $year."-10-01";
							}
						break;
					}
				break;

			case 'semiannually':
				switch($currperiod) {
					case '1':
						if ($lastOrFirst == 'last'){
							return $year."-06-30";
							} else {
							return $year."-01-01";
							}
						break;
					case '2':
						if ($lastOrFirst == 'last'){
							return $year."-12-31";
							} else {
							return $year."-07-01";
							}
						break;
					}
				break;

			case 'annually':
					if ($lastOrFirst == 'last'){
						return $year."-12-31";
						} else {
						return $year."-01-01";
						}
				break;
				
			}
	}
	
	// this function will create an array, named dateToQuery, with the periods between the startdate and the enddate  
	function DateIntervalArray($period)
	{
		global $startdate, $enddate, $dateToQuery ;
		
		if (!isset($period)){
			$period = 'monthly';
			}

		switch($period) {
			case 'monthly':
				$currperiod = date("m", strtotime($startdate));
				$year = date("Y", strtotime($startdate));
				$maxperiod = 12;				
				break;

			case 'quarterly':
					$month = date("M", strtotime($startdate));
					switch ($month) {
						case ($month=='Jan' || $month=='Feb' || $month=='Mar'):
							$currperiod = 1;
							break;
						case ($month=='Apr' || $month=='May' || $month=='Jun'):
							$currperiod = 2;
							break;
						case ($month=='Jul' || $month=='Aug' || $month=='Sep'):
							$currperiod = 3;
							break;
						case ($month=='Oct' || $month=='Nov' || $month=='Dec'):
							$currperiod = 4;
							break;
					}
				$year = date("Y", strtotime($startdate));
				$maxperiod = 4;				
				break;
				
			case 'semiannually':
					$month = date("M", strtotime($startdate));
					switch ($month) {
						case ($month=='Jan' || $month=='Feb' || $month=='Mar' || $month=='Apr' || $month=='May' || $month=='Jun'):
							$currperiod = 1;
							break;
						case ($month=='Jul' || $month=='Aug' || $month=='Sep' || $month=='Oct' || $month=='Nov' || $month=='Dec'):
							$currperiod = 2;
							break;
					}
				$year = date("Y", strtotime($startdate));
				$maxperiod = 2;				
				break;
				
			case 'annually':
				$year = date("Y", strtotime($startdate));
				$currperiod = 1;
				$maxperiod = 1;				
				break;
			}
		
		$firstperiodlastday = getPeriodLastOrFirstDay($period, $currperiod, $year, "last");
			if($firstperiodlastday > $enddate){
			$firstperiodlastday = $enddate;
			}

		$columnlabel = getColumnLabel($period, $currperiod, $year);

		$dateToQuery[$columnlabel] = array($startdate, $firstperiodlastday);

		$periodlastday = $firstperiodlastday;
		while($periodlastday != $enddate) {
			$currperiod = $currperiod+1;
				if($currperiod	> $maxperiod){
				$year = $year+1;
				$currperiod = 1;}
			$periodfirstday = getPeriodLastOrFirstDay($period, $currperiod, $year, "first"); 
			$periodlastday = getPeriodLastOrFirstDay($period, $currperiod, $year, "last");
				if($periodlastday > $enddate){
					$periodlastday = $enddate;
					}
			$columnlabel = getColumnLabel($period, $currperiod, $year);
			$dateToQuery[$columnlabel] = array($periodfirstday, $periodlastday);
		}
	}

	// this function will create an array, named dateToQuery, with the selected months
	//FIXME: it is only an example, to see if the other parts are working. It is necessary to create a function to select the dates and include this option in the report page
	function BalanceAsOfDateArray()
	{
		global $dateToQuery;
		$a = "2008-12-31";		
		$b = "2009-12-31";		
		$c = "2010-12-31";
		$d = "2011-12-31";
		$e = "2012-12-31";
		$f = date('Y-m-d');
		
		$dateToQuery[$a] = array($a); 
		$dateToQuery[$b] = array($b);
		$dateToQuery[$c] = array($c);
		$dateToQuery[$d] = array($d);
		$dateToQuery[$e] = array($e);
		$dateToQuery[$f] = array($f);
		}

	// This function returns 2 things:
	// - an array with the months we want to query in the sql query: $dateToQuery
	// - 2 variables with the date range we want query in the sql query: $startdate and $enddate
	function budget_date_range($date, $num_periods)
	{
		global $dateToQuery, $startdate, $enddate;

		$startdate = $date;
	
		$num = 1;
		$dateToQuery[date("Y-m", strtotime($date))]=$num-1;

		while ($num < $num_periods) {
			$date = date("Y-m-d", mktime(0,0,0,date("m", strtotime($date))+1,date("d", strtotime($date)),date("Y", strtotime($date))));
			$dateToQuery[date("Y-m", strtotime($date))]=$num;
			$num++;
			}
	
		$lastmonth = date("m", strtotime($date));
		if ($lastmonth === 12){
			$year = date("Y", strtotime($date))+1;}
			else {
			$year = date("Y", strtotime($date));}
		$enddate = getPeriodLastOrFirstDay("monthly", $lastmonth, $year, "last");
	} 


	// build the main query for those reports that need the balance as of some date
	// if no account is specified through the SelectedAccounts array, the query will return all the accounts
	function BalanceAsOfQuery($groupby)
	{
		global $query, $dateToQuery, $SelectedAccounts;

		$accArrSize = count($SelectedAccounts);
	
		$query = "SELECT parent.name AS parentname, a.name AS accname, parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid, a.account_type as acctype";
			foreach ($dateToQuery as $eachdate => $firstandlastday) {
			$query .= ", sum(case when date_format(post_date, '%Y-%m-%d') <= '".$firstandlastday[0]."' then (value_num/value_denom) else '0' end) AS '".$eachdate."'";
			}
		$query .= " FROM transactions AS t";
   	$query .= " INNER JOIN splits AS s ON s.tx_guid = t.guid";
   	$query .= " INNER JOIN accounts AS a ON a.guid = s.account_guid";
   	$query .= " INNER JOIN accounts AS parent ON parent.guid = a.parent_guid WHERE";
   		if(isset ($SelectedAccounts)){
	   		foreach ($SelectedAccounts as $eachaccount) {
   			$query .= " a.name = '".$eachaccount."'";
   			$accArrSize -= 1;
	   			if($accArrSize>0){
   				$query .= " OR";
   				}
				}
				$query .= " AND";
   		}
		$query .= " parent.name <>''";
			if(isset ($groupby)){
			   $query .= " GROUP by ".$groupby." ORDER by acccode";
		   } else {
		   	$query .= " GROUP by accname, parentname ORDER by acccode";
		   }
	}
	
	// pretty similar to the BalanceAsOfQuery. The diference here is that the expenses and income accounts should not be shown but the sum result of it should appear in the equity sum
	//FIXME: It is working for me, but it is not good, as it is necessary to indicate the guid of the account that will hold the EXPENSES and INCOME sum
	function TrialBalanceQuery()
	{
		global $query, $dateToQuery;
	
		$query = "select (case when a.account_type = 'INCOME' or a.account_type = 'EXPENSE' then 'Resultado' else parent.name end) as parentname,";
		$query .= " (case when a.account_type = 'INCOME' THEN 'Receitas' WHEN a.account_type = 'EXPENSE' then 'Despesas' else a.name end) as accname,";
		$query .= " (case when a.account_type = 'INCOME' or a.account_type = 'EXPENSE' then '5' else parent.code end) as parentcode,";
		$query .= " (case when a.account_type = 'INCOME' or a.account_type = 'EXPENSE' then '5.2.2' else a.code end) as acccode,";
		$query .= " (case when a.account_type = 'INCOME' or a.account_type = 'EXPENSE' then 'e3b8c16ad8f479948b1c5d01a677e6a8' else parent.guid end) as parentguid,";
		$query .= " (case when a.account_type = 'INCOME' THEN '5b555555' WHEN a.account_type = 'EXPENSE' then '5a555555' else a.guid end) as accguid";

			foreach ($dateToQuery as $eachdate => $firstandlastday) {
			$query .= ", sum(case when date_format(post_date, '%Y-%m-%d') <= '".$firstandlastday[0]."' then (value_num/value_denom) else '0' end) as '".$eachdate."'";		
			}
		$query .= " from transactions as t";
   	$query .= " inner join splits as s on s.tx_guid = t.guid";
   	$query .= " inner join accounts as a on a.guid = s.account_guid";
   	$query .= " inner join accounts as parent on parent.guid = a.parent_guid";
   	$query .= " where parent.name <>''";
   	$query .= " group by (case when a.account_type = 'INCOME' or a.account_type = 'EXPENSE' then a.account_type else accname end), parentname order by acccode";
	}

	// Buid the main query for the cash flow statement
	function CashFlowQuery()
	{
		global $startdate, $enddate, $query, $dateToQuery, $MoneyFlow, $SelectedAccounts;
		
	// create an array that will be used to modify the DateToQuery array, inside the BuildTable function		
	$MoneyFlow[] = "moneyin";
	$MoneyFlow[] = "moneyout";
	
	$accArrSize = count($SelectedAccounts);

  	$query = " select a.name AS selectedaccount, counteraccount.name AS accname, counteraccount.code AS acccode, counteraccount.guid AS accguid, parent.name AS parentname, parent.code AS parentcode, parent.guid AS parentguid, countersplit.guid AS contrguid";
  	
		foreach ($dateToQuery as $eachdate => $firstandlastday) {
		$query .= ", sum(case when date_format(post_date, '%Y-%m-%d') BETWEEN '".$firstandlastday[0]."' AND '".$firstandlastday[1]."' AND (countersplit.value_num/countersplit.value_denom) > 0";
			foreach ($SelectedAccounts as $eachaccount) {
			$query .= " AND counteraccount.name <> '".$eachaccount."'";
			}
		$query .= " then (countersplit.value_num/countersplit.value_denom) else '0' end) as 'moneyout_".$eachdate."'";

		$query .= ", sum(case when date_format(post_date, '%Y-%m') = '".$eachdate."' AND (countersplit.value_num/countersplit.value_denom) < 0";
			foreach ($SelectedAccounts as $eachaccount) {
			$query .= " AND counteraccount.name <> '".$eachaccount."'";
			}
		$query .= " then (countersplit.value_num/countersplit.value_denom) else '0' end) as 'moneyin_".$eachdate."'";
		}

	$query .= " FROM transactions AS t INNER JOIN splits AS s ON s.tx_guid = t.guid";
	$query .= " INNER JOIN accounts AS a ON a.guid = s.account_guid";
	$query .= " INNER JOIN splits AS countersplit ON countersplit.tx_guid = s.tx_guid AND countersplit.guid != s.guid";
	
	$query .= " INNER JOIN accounts AS counteraccount ON counteraccount.guid = countersplit.account_guid";
	$query .= " inner join accounts as parent on parent.guid = counteraccount.parent_guid WHERE";

		foreach ($SelectedAccounts as $eachaccount) {
   	$query .= " a.name = '".$eachaccount."'";
   	$accArrSize -= 1;
	   	if($accArrSize>0){
   		$query .= " OR";
   		}
		}
	
	$query .= " AND date_format(post_date, '%Y-%m-%d') BETWEEN '";
	$query .= $startdate ."' and '" .$enddate. "' GROUP BY accname order by acccode";
	
	}

	// build the main query for the income statement report
	function DateIntervalQuery()
	{
		global $startdate, $enddate, $query, $dateToQuery;
	
		$query = "select parent.name AS parentname, a.name as accname, parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid";
			
			foreach ($dateToQuery as $eachdate => $firstandlastday) {
			$query .= ", sum(case when date_format(post_date, '%Y-%m-%d') BETWEEN '".$firstandlastday[0]."' AND '".$firstandlastday[1]."' then (value_num/value_denom) else '0' end) as '".$eachdate."'";
			}

		$query .= " from transactions as t";
   	$query .= " inner join splits as s on s.tx_guid = t.guid";
   	$query .= " inner join accounts as a on a.guid = s.account_guid";
   	$query .= " inner join accounts as parent on parent.guid = a.parent_guid";
   	$query .= " where (a.account_type ='expense' OR a.account_type ='income') AND t.description !='Closing Entries' AND date_format(post_date, '%Y-%m-%d') BETWEEN '";
   	$query .= $startdate ."' and '" .$enddate;
   	$query .= "' group by accname, parentname order by acccode";
	}
	
	
/* Function to build the main query for the budget report.
 * This SELECT should return both the current values and budgeted values for each account.
 * Thats why there are 2 SELECTS combined into another SELECT one level above.
 * This is the best way I found to do the task, as subqueries demonstrated to be very slow in this task */
 // FIXME: THIS FUNCTION IS RETURNING ONLY THE BUDGET AND ACCOUNTS IF THERE IS ACTUAL EXPENSES/REVENUE. IT SHOULD RETURN ALL ACCOUNTS IN EXPENSES AND INCOME

	function BudgetQuery()
	{
	global $startdate, $enddate, $query, $dateToQuery, $budget;
	
	$query = "SELECT parentname, accname, parentcode, acccode, parentguid, accguid";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			$query .= ", `".$eachmonth."` as '".$eachmonth."', `bgt_".$eachmonth."` as 'bgt_".$eachmonth."', (`bgt_".$eachmonth."` - `".$eachmonth."`) as `dif_".$eachmonth."`";
			} 
	$query .= " FROM (";
		// First select, with actual values
		$query .= "SELECT parent.name AS parentname, a.name as accname, parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			$query .= ", sum(case when (date_format(post_date, '%Y-%m') = '".$eachmonth."') then (value_num/value_denom) else '0' end) as '".$eachmonth."'";
			} 
		$query .= " from transactions as t";
		$query .= " inner join splits as s on s.tx_guid = t.guid";
		$query .= " inner join accounts as a on a.guid = s.account_guid";
		$query .= " inner join accounts as parent on parent.guid = a.parent_guid";
		$query .= " where (a.account_type ='expense' OR a.account_type ='income') AND t.description !='Closing Entries' AND date_format(post_date, '%Y-%m-%d') BETWEEN '";
		$query .= $startdate."' and '".$enddate;
		$query .= "' group by accname, parentname order by acccode) AS TEMP1";
	$query .= " INNER JOIN (";
		// Second select, with budget values
		$query .= "SELECT account_guid, name, guid";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			$query .= ", sum(case when bgt_amt.period_num = '".$period_num."' then (case when a.account_type = 'income' then -(bgt_amt.amount_num/bgt_amt.amount_denom) else (bgt_amt.amount_num/bgt_amt.amount_denom) end) else '0' end) as 'bgt_".$eachmonth."'";
			} 
		$query .= " FROM accounts as a INNER JOIN budget_amounts AS bgt_amt on bgt_amt.account_guid = a.guid";
		$query .= " where (bgt_amt.budget_guid = '".$budget."' AND (a.account_type ='expense' OR a.account_type ='income'))";
		$query .= " GROUP BY account_guid ORDER BY a.code) AS TEMP2";
	$query .= " ON TEMP2.account_guid = TEMP1.accguid";
	$query .= " group by accname, parentname order by acccode";
	}

	
	
	
	//this function will return an array named $accounttree with the parent accounts for some row. It is called from the functions that print the rows.
	function getparent()
	{
		global $connection, $currentparent, $currentparentguid, $currentparentcode, $accounttree, $linha;
		
		//these variables will be used in the next while loop to get the parent account of each account, until finding the root account
		$currentparent = $linha['parentname'];
		$currentparentguid = $linha['parentguid'];
		$currentparentcode = $linha['parentcode'];
		$accounttree = array();
				
		//Here is the loop. FIXME: The problem here is that at each loop the program send another sql query to the server. Probably there is other better way to accomplish that
		//FIXME: I couldn't notice any slow due to this function has been called many times, but it will be better to find out a way to put the account tree in the main query
		while($currentparent <> 'Root Account'){
			$a = $currentparentguid;
			$b = $currentparent;
			$c = $currentparentcode;
				
			$getparentquery = "select parent.name AS parentname, a.name as accname, parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid";
			$getparentquery .= " from accounts as a";
	  		$getparentquery .= " inner join accounts as parent on parent.guid = a.parent_guid";
	  		$getparentquery .= " where a.guid ='".$a."'";
	  		$getparentquery .= " order by parentname, accname";
	
			$getparentresultado = mysql_query($getparentquery,$connection);
			$getparentlinha = mysql_fetch_array($getparentresultado);
			$currentparent = $getparentlinha['parentname'];
			$currentparentguid = $getparentlinha['parentguid'];
			$currentparentcode = $getparentlinha['parentcode'];
				
			$accounttree[$a] = array($b,$c,$currentparentguid);
		}
	}
	

	//function to print each row according to the account tree structure
	function printrow_nestedtree($total)
		{
			global $currentparent, $currentparentguid, $currentparentcode, $parentvalues, $dateToQuery, $linha, $totalcolumn, $totalflow, $accounttree, $printedtree, $level;

				// call the function that will bring the parent account names
				// FIXME: include an option to call this function only when the user wants to show the parent accounts
				getparent();
				$accounttree = array_reverse($accounttree);
		
					//this loop will verify the array filled before and will print the tree structure and the account value as well
					//FIXME: it does not work well on accounts without a code, as the code is used to sort
					foreach ($accounttree as $nodeguid => $nodeaccount) {

						//this array will store the values of each row linked to the guid of each node. This will be accessed later, to populate the table cells of the parent accounts with the proper values
						foreach ($dateToQuery as $eachdate => $firstandlastday) {			
							$aux = "".$eachdate."-".$nodeguid ."";
							$parentvalues[$aux] = $parentvalues[$aux] + $linha[$eachdate];
						}
						//This switch will build the table
						//The switch will use the variable called $printedtree to verify if the parent account has been already shown in the screen. If so, it will only increase the level.
						switch($nodeaccount[0]) {
							default:
								if ($printedtree[$nodeguid] <> $nodeguid) {
									$level = $level + 1;
									echo "<tr id=\"".$nodeguid."\" class=\"level".$level."\" onclick=\"ShowHideChild(this.id)\">\r\n";
									echo "<td id=\"col-".$nodeguid."\" class=\"col-expanded\">". $nodeaccount[1]." ". $nodeaccount[0]."</td>";
										foreach ($dateToQuery as $eachdate => $firstandlastday) {
										echo "<td id=\"".$eachdate."-".$nodeguid ."\"></td>";
										}
									echo "</tr>\r\n";
									$printedtree[$nodeguid] = $nodeguid;
									echo "<script>TreeStructure.push(childparent=[\"".$nodeguid."\",\"".$nodeaccount[2]."\"]);</script>\r\n";
									}
									else {
									$level = $level + 1;
									}
								
							break;
							case $linha['parentname']:
								if(!isset($printedtree[$linha['parentguid']])){
									$level = $level + 1;
									echo "<tr id=\"".$nodeguid."\" class=\"level".$level."\" onclick=\"ShowHideChild(this.id)\">\r\n";
									echo "<td id=\"col-".$nodeguid."\" class=\"col-expanded\">". $linha['parentcode'] ." ". $linha['parentname']."</td>";
										foreach ($dateToQuery as $eachdate => $firstandlastday) {
										echo "<td id=\"".$eachdate."-".$nodeguid."\"></td>";
										}
									echo "</tr>\r\n";
									$printedtree[$linha['parentguid']] = $linha['parentguid'];
									echo "<script>TreeStructure.push(childparent=[\"".$nodeguid."\",\"".$nodeaccount[2]."\"]);</script>\r\n";
									} else {
									$level = $level + 1;
									}
								//at the end, it will show the account with the values
								$level = $level + 1;
								echo "<tr id=\"".$linha['accguid']."\" class=\"level".$level."\" onclick=\"ShowHideChild(this.id)\">\r\n";
								echo "<td class=\"col\">". $linha['acccode']." ". $linha['accname']."</td>";
									foreach ($dateToQuery as $eachdate => $firstandlastday) {
										echo "<td id=\"".$eachdate."-".$linha['accguid']."\">". number_format($linha[$eachdate], 2, ',', '.')."</td>";
									}
									echo "</tr>\r\n";
								$printedtree[$linha['accguid']] = $linha['accguid'];						
								echo "<script>TreeStructure.push(childparent=[\"".$linha['accguid']."\",\"".$linha['parentguid']."\"]);</script>\r\n";						
							break;
							}
					}
				//this other array will be used in case we need to get the sum of the column. It is the case with the income statement		
				if(isset ($total)){
					foreach ($dateToQuery as $eachdate => $firstandlastday) {
					$totalcolumn[$eachdate] = $totalcolumn[$eachdate] + $linha[$eachdate];
					}
				}
			echo "</tr>\r\n";
		}

	/* FIXME: DUPLICATE CODE. This function is  almost the same function above, named printrow_nestedtree().
	 * Right now I decided to keep these 2 functions, even if those 2 has almost the same code, because that one needs to bring
	 * only one column per month,  while the budget report needs 2 or 3 columns. Fix it in the future */

	function BGT_printrow_nestedtree($total)
		{
			global $currentparent, $currentparentguid, $currentparentcode, $parentvalues, $dateToQuery, $linha, $totalcolumn, $totalflow, $accounttree, $printedtree, $level;
			/* FIXME. These variables seems to be necessary. And  I  do  not know why.
			 * It is used only to put the "bgt_" or "dif_" in front of the variables related to budget values.
			 * I couldnt put these strings right in front of the variables. It didnt work. WHY?
			 * (note: I  used  the  prefix "bgt_" and "dif_" to separate  the actual values from the budget values) */
			$bgt  = "bgt_";
			$dif  = "dif_";
			
			$invsignal = "-";

				// call the function that will bring the parent account names
				// FIXME: include an option to call this function only when the user wants to show the parent accounts
				getparent();
				$accounttree = array_reverse($accounttree);
		
					//this loop will verify the array filled before and will print the tree structure and the account value as well
					//FIXME: it does not work well on accounts without a code, as the code is used to sort
					foreach ($accounttree as $nodeguid => $nodeaccount) {

						//this array will store the values of each row linked to the guid of each node. This will be accessed later, to populate the table cells of the parent accounts with the proper values
						foreach ($dateToQuery as $eachmonth => $period_num) {
							$aux = "".$bgt."-".$eachmonth."-".$nodeguid ."";
							$parentvalues[$aux] = $parentvalues[$aux] + $linha[$bgt.$eachmonth];
							$aux = "".$eachmonth."-".$nodeguid ."";
							$parentvalues[$aux] = $parentvalues[$aux] + $linha[$eachmonth];
							$aux = "".$dif."-".$eachmonth."-".$nodeguid ."";
							$parentvalues[$aux] = $parentvalues[$aux] + $linha[$dif.$eachmonth];
						}
						//This switch will build the table
						//The switch will use the variable called $printedtree to verify if the parent account has been already shown in the screen. If so, it will only increase the level.
						switch($nodeaccount[0]) {
							default:
								if ($printedtree[$nodeguid] <> $nodeguid) {
									$level = $level + 1;
									echo "<tr id=\"".$nodeguid."\" class=\"level".$level."\" onclick=\"ShowHideChild(this.id)\">\r\n";
									echo "<td id=\"col-".$nodeguid."\" class=\"col-expanded\">". $nodeaccount[1]." ". $nodeaccount[0]."</td>";
										foreach ($dateToQuery as $eachmonth => $period_num) {
										echo "<td id=\"".$bgt."-".$eachmonth."-".$nodeguid ."\" class=\"bgt\"></td>";
										echo "<td id=\"".$eachmonth."-".$nodeguid ."\" class=\" act\"></td>";
										echo "<td id=\"".$dif."-".$eachmonth."-".$nodeguid ."\" class=\"diff\"></td>";
										}
									echo "</tr>\r\n";
									$printedtree[$nodeguid] = $nodeguid;
									echo "<script>TreeStructure.push(childparent=[\"".$nodeguid."\",\"".$nodeaccount[2]."\"]);</script>\r\n";
									}
									else {
									$level = $level + 1;
									}
								
							break;
							case $linha['parentname']:
								if(!isset($printedtree[$linha['parentguid']])){
									$level = $level + 1;
									echo "<tr id=\"".$nodeguid."\" class=\"level".$level."\" onclick=\"ShowHideChild(this.id)\">\r\n";
									echo "<td id=\"col-".$nodeguid."\" class=\"col-expanded\">". $linha['parentcode'] ." ". $linha['parentname']."</td>";
										foreach ($dateToQuery as $eachmonth => $period_num) {
										echo "<td id=\"".$bgt."-".$eachmonth."-".$nodeguid ."\" class=\"bgt\"></td>";
										echo "<td id=\"".$eachmonth."-".$nodeguid."\" class=\"act\"></td>";
										echo "<td id=\"".$dif."-".$eachmonth."-".$nodeguid ."\" class=\"diff\"></td>";
										}
									echo "</tr>\r\n";
									$printedtree[$linha['parentguid']] = $linha['parentguid'];
									echo "<script>TreeStructure.push(childparent=[\"".$nodeguid."\",\"".$nodeaccount[2]."\"]);</script>\r\n";
									} else {
									$level = $level + 1;
									}
								//at the end, it will show the account with the values
								$level = $level + 1;
								echo "<tr id=\"".$linha['accguid']."\" class=\"level".$level."\" onclick=\"ShowHideChild(this.id)\">\r\n";
								echo "<td class=\"col\">". $linha['acccode']." ". $linha['accname']."</td>";
									foreach ($dateToQuery as $eachmonth => $period_num) {
										echo "\r\n<td id=\"".$bgt.$eachmonth."-".$linha['accguid']."\" class=\"bgt\">". number_format($linha[$bgt.$eachmonth], 2, ',', '.')."</td>";
										echo "\r\n<td id=\"".$eachmonth."-".$linha['accguid']."\" class=\"act\">". number_format($linha[$eachmonth], 2, ',', '.')."</td>";
										echo "\r\n<td id=\"".$dif.$eachmonth."-".$linha['accguid']."\" class=\"diff\">". number_format($linha[$dif.$eachmonth], 2, ',', '.')."</td>";
									}
									echo "</tr>\r\n";
								$printedtree[$linha['accguid']] = $linha['accguid'];						
								echo "<script>TreeStructure.push(childparent=[\"".$linha['accguid']."\",\"".$linha['parentguid']."\"]);</script>\r\n";						
							break;
							}
					}
				//this other array will be used in case we need to get the sum of the column. It is the case with the income statement		
				if(isset ($total)){
					foreach ($dateToQuery as $eachmonth => $period_num) {
					$totalcolumn[$bgt.$eachmonth] = $totalcolumn[$bgt.$eachmonth] + $linha[$bgt.$eachmonth];
					$totalcolumn[$eachmonth] = $totalcolumn[$eachmonth] + $linha[$eachmonth];
					$totalcolumn[$dif.$eachmonth] = $totalcolumn[$dif.$eachmonth] + $linha[$dif.$eachmonth];
					}
				}
			echo "</tr>\r\n";
		}

	//function used to print each row without the tree structure. Right now, it is only used by the cashflow report
	function printrow_plain($flow,$total)
		{
				global $currentparent, $currentparentguid, $currentparentcode, $parentvalues, $dateToQuery, $MoneyFlow, $linha, $totalcolumn, $totalflow, $accounttree;

				// call the function that will bring the parent account names
				// FIXME: include an option to call this function only when the user wants to show the parent accounts
				getparent();
				$accounttree = array_reverse($accounttree);
		
				$parents = "";
				foreach ($accounttree as $nodeguid => $nodeaccount) {
				$parents .= $nodeaccount[0].":";
				}

				echo "\r\n\t<tr id=\"".$linha['accguid']."\">";
				echo "\r\n\t\t<td id=\"col_".$linha['accguid']."\" class=\"col\">". $parents."". $linha['accname']."</td>";
					foreach ($dateToQuery as $eachdate => $firstandlastday) {
						echo "\r\n\t\t<td id=\"".$eachdate."_".$linha['accguid']."\">". number_format($linha[$flow."_".$eachdate], 2, ',', '.')."</td>";
						if(isset ($total)){
							$totalflow[$eachdate] = $totalflow[$eachdate] + $linha[$flow."_".$eachdate];							
							$totalcolumn[$eachdate] = $totalcolumn[$eachdate] + $linha[$flow."_".$eachdate];
						}
					}
			echo "\r\n\t</tr>";
		}

	function BuildTable($total)
	{
		global $query, $connection, $currentparent, $currentparentguid, $currentparentcode, $parentvalues, $totalcolumn, $dateToQuery, $MoneyFlow, $accounttree, $linha, $level;
		//this javascript array will be used by the ShowHideChild function, to know which child accounts should be hided/showed
		echo "<script type=\"text/javascript\">var TreeStructure=new Array();</script>";
		
		echo "\r\n<table id=\"data\">\r\n";

		// colgroups to aply the styles accordingly
		echo "\r\n<colgroup class=\"firstcolumn\"><col></col></colgroup>";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			echo "\r\n<colgroup class=\"content\"><col class=\"normalcol\"></col></colgroup>";
			}

		echo "<thead><th></th>";
		foreach ($dateToQuery as $eachdate => $firstandlastday) {
			echo 	"<th>". $eachdate ."</th>";
			}
		echo "</tr></thead>\r\n<tbody>\r\n";		
		
		// This is the main loop
		$resultado = mysql_query($query,$connection);
		while ($linha = mysql_fetch_array($resultado)) {
			if ($level > $maxlevel){
				$maxlevel = $level;
				}
			$level = 0;
			printrow_nestedtree(withtotal);
		}


		//this last part will create the column total or not, depending on what have been called
		if(isset ($total)){
			echo "<tr class=\"total\"><td class=\"col\">TOTAL</td>";
				foreach ($totalcolumn as $eachcollum) {
				echo "<td> ". number_format(-$eachcollum, 2, ',', '.') ."</td>";
				}
			echo "</tr>\r\n";
			}
		
		echo "</tbody></table>\r\n";


		//finally, it will call some javascript functions to create the level selector and to populate the parent accounts with the sum of their respective child accounts
		//FIXME: both things should be selected in the report page
		echo "<script>createLevelSelection('".$maxlevel."');</script>\r\n";	

		foreach ($parentvalues as $eachparentkey => $eachparentvalue) {
			echo "<script>fillparentvalues('".$eachparentkey."', '".$eachparentvalue."')</script>";
		}
	}

	/* FIXME: DUPLICATE CODE. This function is  almost the same function above. The 2 diferences
	 * are: this one calls another function to write each row and this one writes a diferent header, more suitable for the budget report */

	function BGT_BuildTable($total)
	{
		global $query, $connection, $currentparent, $currentparentguid, $currentparentcode, $parentvalues, $totalcolumn, $dateToQuery, $MoneyFlow, $accounttree, $linha, $level;
		//this javascript array will be used by the ShowHideChild function, to know which child accounts should be hided/showed
		echo "<script type=\"text/javascript\">var TreeStructure=new Array();</script>";
		
		echo "\r\n<table id=\"data\">";
		
		// colgroups to aply the styles accordingly
		echo "\r\n<colgroup class=\"firstcolumn\"><col></col></colgroup>";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			echo "\r\n<colgroup span=3 class=\"content\"><col class=\"budget\"></col><col class=\"actual\"></col><col class=\"bgtdiff\"></col></colgroup>";
			}
		// create the column names
		echo "\r\n<thead><tr><th></th>\r\n";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			echo "<th colspan=3>".$eachmonth."</th>";
			}
		echo "\r\n</tr><tr><th></th>";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			echo "\r\n<th>Bgt</th><th>Ato</th><th>Dif</th>";
			}
		echo "\r\n</tr></thead><tbody>";
				
		// This is the main loop
		$resultado = mysql_query($query,$connection);
		while ($linha = mysql_fetch_array($resultado)) {
			if ($level > $maxlevel){
				$maxlevel = $level;
				}
			$level = 0;
			BGT_printrow_nestedtree(withtotal);
		}

		//this last part will create the column total or not, depending on what have been called
		if(isset ($total)){
			echo "<tr class=\"total\"><td class=\"col\">TOTAL</td>";
				foreach ($totalcolumn as $eachcollum) {
				echo "\r\n<td> ". number_format(-$eachcollum, 2, ',', '.') ."</td>";
				}
			echo "\r\n</tr>";
			}
		
		echo "</tbody></table>\r\n";


		//finally, it will call some javascript functions to create the level selector and to populate the parent accounts with the sum of their respective child accounts
		//FIXME: both things should be selected in the report page
		echo "<script>createLevelSelection('".$maxlevel."');</script>\r\n";	

		foreach ($parentvalues as $eachparentkey => $eachparentvalue) {
			echo "<script>fillparentvalues('".$eachparentkey."', '".$eachparentvalue."')</script>";
		}
	}

	function BuildCashFlowTable($total)
	{
		global $query, $connection, $dateToQuery, $MoneyFlow, $linha, $totalcolumn, $totalflow, $period, $parentvalues;

		//first, we call the function to fill the array with the months to query
		DateIntervalArray($period);

		echo "\r\n<table id=\"data\">\r\n\t<thead>\r\n\t\t<th></th>";
		foreach ($dateToQuery as $eachdate => $firstandlastday) {
			echo 	"\r\n\t\t<th>". $eachdate ."</th>";
			}
		echo "\r\n\t</thead>";
		
		//then we call the function to get the initial balance of the selected accounts
		//FIXME: it is not bringing the initial balance, but the balance as of the end of the first day
		BalanceAsOfQuery("acctype");

			$resultado = mysql_query($query,$connection);
			while ($linha = mysql_fetch_array($resultado)) {
	// instead of using the following lines, it is possible to call the printrow_nestedtree function. In this case is important to not group the query by acctype	
	//			printrow_nestedtree();
	// these following lines could be used instead of calling the printrow_nestedtree function
			echo "\r\n\t<tr>\r\n\t\t<td class=\"col\">".$linha['acctype']." - Saldo inicial</td>";
				foreach ($dateToQuery as $eachdate => $firstandlastday) {
					echo 	"\r\n\t\t<td>".number_format($linha[$eachdate], 2, ',', '.')."</td>";
				}
			}
			
			//this foreach will call the fillparentvalues javascript function, to fill the values in the parent accounts 
			//foreach ($parentvalues as $eachparentkey => $eachparentvalue) {
			//	echo "<script>fillparentvalues('".$eachparentkey."', '".$eachparentvalue."')</script>";
			//}

		//then we call the function to create the main query
		CashFlowQuery();
		
		echo "\r\n\t</tr>\r\n\t</thead>\r\n<tbody>";	
		echo "\r\n\t<tr class=\"header\">\r\n\t\t<td class=\"col\">Dinheiro entrando nas contas selecionadas vem de</td>";		
		foreach ($dateToQuery as $eachdate => $firstandlastday) {
			echo 	"\r\n\t\t<td></td>";
			}
		echo "\r\n\t</tr>";		

			$resultado = mysql_query($query,$connection);
			while ($linha = mysql_fetch_array($resultado)) {
				foreach ($dateToQuery as $eachdate => $firstandlastday) {
					if($linha[$MoneyFlow[0]."_".$eachdate] < 0){
						printrow_plain($MoneyFlow[0], withtotal);
						break;
					}
				}
			}
			
		if(isset ($total)){
			echo "\r\n\t<tr class=\"total\">\r\n\t\t<td class=\"col\">Dinheiro Entrando</td>";
				foreach ($totalflow as $eachcollum) {
				echo "\r\n\t\t<td> ". number_format(-$eachcollum, 2, ',', '.') ."</td>";
				}
			echo "\r\n\t</tr>";
			}
			$totalflow = array();
			
		echo "\r\n</tbody>\r\n<tbody>";	
			
		echo "\r\n\t<tr class=\"header\">\r\n\t\t<td class=\"col\">Dinheiro saindo das contas selecionadas vai para</td>";		
		foreach ($dateToQuery as $eachdate => $firstandlastday) {
			echo 	"\r\n\t\t<td></td>";
			}
		echo "\r\n\t</tr>\r\n";		
			mysql_data_seek($resultado, 0);
			while ($linha = mysql_fetch_array($resultado)) {
				foreach ($dateToQuery as $eachdate => $firstandlastday) {
					if($linha[$MoneyFlow[1]."_".$eachdate] > 0){
						printrow_plain($MoneyFlow[1],withtotal);
						break;
					}
				}
			}

		if(isset ($total)){
			echo "\r\n\t<tr class=\"total\">\r\n\t\t<td class=\"col\">Dinheiro Saindo</td>";
				foreach ($totalflow as $eachcollum) {
				echo "\r\n\t\t<td> ". number_format(-$eachcollum, 2, ',', '.') ."</td>";
				}
			echo "\r\n\t</tr>\r\n";
			}

		if(isset ($total)){
			echo "\r\n\t<tr class=\"total\">\r\n\t\t<td class=\"col\">Diferença</td>";
				foreach ($totalcolumn as $eachcollum) {
				echo "\r\n\t\t<td> ". number_format(-$eachcollum, 2, ',', '.') ."</td>";
				}
			echo "\r\n\t</tr>";
			}			

		echo "\r\n</tbody>\r\n</table>\r\n";

	}
	
?>
