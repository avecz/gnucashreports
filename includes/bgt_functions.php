 <?php
 
 /* functions used only by the budget report.
 FIXME: a lot of functions here have a similar function  in the functions.php file.
 Rewrite the code here or there to avoid duplicated code as much as possible.
 */

/* FIXME: there is a strange bug in this report. It does not show the plus and minus images in the lines that have  subaccounts
			I believe that the problem happens when you have a budget value for a account with subaccounts. If the account does not have a value
			the problem does not happen.
			Also, the budget in the child accounts sometimes are not summed in the balance of the mother account.
*/

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


/* Function to build the main query for the budget report.
 * This SELECT should return both the current values and budgeted values for each account.
 * Thats why there are 2 SELECTS combined into another SELECT one level above.
 * This is the best way I found to do the task, as subqueries demonstrated to be very slow in this task */

	function BudgetQuery()
	{
	global $startdate, $enddate, $query, $dateToQuery, $budget;
$query = "SELECT parentname, accname, acccode, parentcode, parentguid, accguid";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			$query .= ", IFNULL(`".$eachmonth."`,0) as '".$eachmonth."', `bgt_".$eachmonth."` as 'bgt_".$eachmonth."', (`bgt_".$eachmonth."` - IFNULL(`".$eachmonth."`,0)) as `dif_".$eachmonth."`";
			} 
	$query .= " FROM (";
		// First select, with actual values
		$query .= "SELECT parent.name AS parentnametemp, parent.code, a.name AS accnametemp, a.code AS acccodetemp, parent.guid AS parentguidtemp, a.guid AS accguidtemp";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			$query .= ", sum(case when (date_format(post_date, '%Y-%m') = '".$eachmonth."') then (value_num/value_denom) else '0' end) as '".$eachmonth."'";
			} 
		$query .= " FROM transactions AS t";
		$query .= " INNER JOIN splits AS s ON s.tx_guid = t.guid";
		$query .= " INNER JOIN accounts AS a ON a.guid = s.account_guid";
		$query .= " INNER JOIN accounts AS parent ON parent.guid = a.parent_guid";
		$query .= " WHERE t.description !='Closing Entries' AND date_format(post_date, '%Y-%m-%d') BETWEEN '";
		$query .= $startdate."' and '".$enddate;
		$query .= "' GROUP BY accnametemp, parentnametemp ORDER BY acccodetemp) AS TEMP1";
	$query .= " RIGHT JOIN (";
		// Second select, with budget values
		
		$query .= "SELECT account_guid AS accguid, a.name AS accname, parent.code AS parentcode, parent.guid AS parentguid, a.guid AS bgtguid, parent.name AS parentname, a.code AS acccode";
			foreach ($dateToQuery as $eachmonth => $period_num) {
			$query .= ", sum(case when bgt_amt.period_num = '".$period_num."' then (case when a.account_type = 'income' then -(bgt_amt.amount_num/bgt_amt.amount_denom) else (bgt_amt.amount_num/bgt_amt.amount_denom) end) else '0' end) as 'bgt_".$eachmonth."'";
			} 
		$query .= " FROM accounts as a INNER JOIN budget_amounts AS bgt_amt ON bgt_amt.account_guid = a.guid";
		$query .= " INNER JOIN accounts AS parent ON parent.guid = a.parent_guid";		
		$query .= " where (bgt_amt.budget_guid = '".$budget."')";
		$query .= " GROUP BY accname, parentname ORDER BY acccode) AS TEMP2";
	$query .= " ON TEMP2.accguid = TEMP1.accguidtemp";
	$query .= " GROUP BY accname, parentname ORDER BY acccode";
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


/* FIXME: DUPLICATE CODE. This function is  almost the same function BuildTable(). The 2 diferences are:
this one calls another function to write each row and this one writes a diferent header, more suitable for the budget report */

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
	
	?>