<!-- javascript functions used only in the budget report.  Functions already in jquery -->

<!-- function to change the negative values in red on diff column of the budget report -->
$(document).ready(

	function() {
			$('td.diff').each(function() {

			var cellvalue = $(this).html();
			if ( cellvalue.substring(0,1) == '-' ) {
				$(this).toggleClass('diff diffnegative');	
			}
		});					   
	}
);

<!-- function to remove the zeros from the act column of the budget report -->
$(document).ready(
function() {
		$('td.act').each(function() {

		var cellvalue = $(this).html();
		if ( cellvalue.substring(0,1) == '0' ) {
			$(this).empty();
		}
	});					   
}

);