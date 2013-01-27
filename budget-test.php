    <!DOCTYPE html>  
      
    <html lang="en">  
    <head>  
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
        <title>BUDGET TEST</title> 
 		<script type='text/javascript' src='includes/calendar.js'></script>
		<script type="text/javascript" src="includes/jsfunctions.js"></script>
		<link rel="stylesheet" href="includes/gnucashreports.css" type="text/css" media="screen" />
  
    </head> 
    <body>  
	
		<div id="content">
		<?php


 	//change this to config the connection
		include 'functions.php';


		$query = "SELECT * FROM budgets AS bgt";
		$query .= " inner join recurrences AS r on r.obj_guid = bgt.guid";

		// This is the main loop
		$resultado = mysql_query($query,$connection);
		while ($linha = mysql_fetch_array($resultado)) {
			echo $linha['guid']." - ".$linha['name']." - ".$linha['num_periods']." - ".$linha['recurrence_period_start']."<br>";
			}
			
			echo $query;
			echo "<br>";
			
/* Now we free up the result and continue on with our script */
mysql_free_result($resultado);


		$query = "select parent.name AS parentname, a.name as accname, parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid,";
		$query .= " bgt.name as bgtname, bgt.guid, bgt_amt.amount_num AS bgt_num, bgt_amt.amount_denom AS bgt_denom, recurrence_period_start, period_num";
			
			$query .= ", sum(case when date_format(post_date, '%Y-%m-%d') BETWEEN '2011-01-01' AND '2011-01-31' then (value_num/value_denom) else '0' end) as '2011-01'";

		$query .= " from transactions as t";
   	$query .= " inner join splits as s on s.tx_guid = t.guid";
   	$query .= " inner join accounts as a on a.guid = s.account_guid";
   	$query .= " inner join accounts as parent on parent.guid = a.parent_guid";
   	$query .= " inner join budget_amounts AS bgt_amt on bgt_amt.account_guid = a.guid";
		$query .= " inner join budgets AS bgt on bgt.guid = bgt_amt.budget_guid";
   	$query .= " inner join recurrences as r on r.obj_guid = bgt.guid";
		$query .= " where budget_guid = '1364120d28e4046dae48feba0b14f787' and (a.account_type ='expense' OR a.account_type ='income') AND t.description !='Closing Entries' AND date_format(post_date, '%Y-%m-%d') BETWEEN '";
   	$query .= "2012-01-01' and '2012-01-31";
   	$query .= "' group by accname, parentname order by acccode";
   	
   	echo $query."<br>";
   	

/*A CONSULTA ABAIXO ESTÁ FUNCIONANDO, MAS ELA CONSULTA O PRIMEIRO PERÍODO DO BUDGET SELECIONADO, RELACIONANDO ESSES VALORES COM OS VALORES CORRETOS DA CONSULTA ACIMA.
NO CASO, ESTÁ ERRADO, POIS O PERÍODO DO ORÇAMENTO NÃO É O MESMO DA CONSULTA.
   	
select parent.name AS parentname, a.name as accname, parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid, bgt_num, bgt_denom,
recurrence_period_start, period_num, bgtname, bgt.guid, bgt.account_guid,
sum(case when date_format(post_date, '%Y-%m-%d') BETWEEN '2011-01-01' AND '2011-01-31' then (value_num/value_denom) else '0' end) as '2011-01'
from transactions as t inner join splits as s on s.tx_guid = t.guid
inner join accounts as a on a.guid = s.account_guid
inner join accounts as parent on parent.guid = a.parent_guid

inner join (select bgt_amt.amount_num AS bgt_num, bgt_amt.amount_denom AS bgt_denom,
recurrence_period_start, period_num,  bgt.name as bgtname, bgt.guid, account_guid
from budget_amounts AS bgt_amt
inner join budgets AS bgt on bgt.guid = bgt_amt.budget_guid
inner join recurrences as r on r.obj_guid = bgt.guid
where budget_guid = '1364120d28e4046dae48feba0b14f787' AND period_num = '0')
as bgt on bgt.account_guid = a.guid

WHERE (a.account_type ='expense' OR a.account_type ='income') AND t.description !='Closing Entries' AND date_format(post_date, '%Y-%m-%d') BETWEEN '2011-01-01' and '2011-01-31' group by accname, parentname order by acccode
   	

		*/
//		$query = "SELECT * FROM budgets AS bgt";
//		$query .= " inner join budget_amounts AS bgt_amt on bgt_amt.budget_guid = bgt.guid";
 //  	$query .= " inner join accounts as a on a.guid = bgt_amt.account_guid";
//   	$query .= " inner join recurrences as r on r.obj_guid = bgt.guid";
//		$query .= " WHERE budget_guid = '1364120d28e4046dae48feba0b14f787'";


		// This is the main loop
		$resultado = mysql_query($query,$connection);
		while ($linha = mysql_fetch_array($resultado)) {
			echo $linha['accname']." - (".$linha['recurrence_period_start']."+ ".$linha['period_num'].") | ".($linha['bgt_num']/$linha['bgt_denom'])." | ".($linha['2011-01'])."<br>";
			}


		?>
		</div>         
         
         
         
           
    </body>  
    </html>