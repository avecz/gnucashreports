<div id="accselector-nav">
<?php

	$accquery = "SELECT parent.name AS parentname, a.name AS accname,";
	$accquery .= " parent.code AS parentcode, a.code AS acccode, parent.guid AS parentguid, a.guid AS accguid";
	$accquery .= " FROM accounts AS a INNER JOIN accounts AS parent ON parent.guid = a.parent_guid";
	$accquery .= " WHERE parent.name <>'' GROUP by accname, parentname ORDER by acccode";


	echo "<select name=\"acc[]\" multiple=multiple>";

	$accresult = mysql_query($accquery,$connection);
	while ($acc = mysql_fetch_array($accresult)) {
		echo "<option value=\"".$acc['accname']."\">".$acc['accname']."</option>";
	}

	echo "</select>";
?>
</div>