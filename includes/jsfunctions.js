/*
 
 Pop-Up Calendar Built from Scratch by Marc Grabanski
  
 Original Design
 MarcGrabanski.com 
 
 Br - Version 
 Anselmo Battisti <anselmobattisti@gmail.com>
 battisti.wordpress.com
 1 - If exist a data in the input filed the calendar open in this day
 2 - More one button who set the date of today in the input field
 3 - Mmodify to portuguese

 Anderson Dias <anderzd@gmail.com>
 1 - Add bodyOnReady to automatic create CalendarDiv
*/

function format_currency(num) {
	/* FIXME: it works only for values with 2 cents numbers and under 9 digits
		Probably there is another better way to do that, but I dont have time to figure out right now */

	if (num.search("-") == -1){
		numToReturn = "";		
		len = num.length;		
		}
		else{
		numToReturn = "-";
		len = num.length-1;
		num = num.substr(1,len);}

	/* FIXME: this tentative for numbers above 9 digits is not working right */
	switch (true){
		case ((len<=12) && (len>9)):
			a = len-9;
			b = a+3;
			c = b+3;
			numToReturn += num.substr(0,a)+".";
			numToReturn += num.substr(a,3)+".";
			numToReturn += num.substr(b,3);
			numToReturn += num.substr(c,3);
			return numToReturn;
			break;
		
		case ((len<=9) && (len>6)):
			a = len-6;
			b = a+3;
			numToReturn += num.substr(0,a)+".";
			numToReturn += num.substr(a,3);
			numToReturn += num.substr(b,3);
			return numToReturn;
			break;
	
		default:	
			numToReturn += num;
			return numToReturn;
			break;
}
}

function createLevelSelection(num) {
	var el = document.getElementById('report-nav');
	var a = "";
		for (var i=1; i<=num; i++){
		a += "<a id="+i+" onclick=showhidebyLevels(this.id)>"+i+"</a>";		
		}
	el.innerHTML = a;
	}
	
	
var allHTMLTags = new Array();

function showhidebyLevels(theClass) {
	var allHTMLTags=document.getElementsByTagName("*");
	var Len=allHTMLTags.length;
	for (i=0; i<Len; i++) {
			var e = allHTMLTags[i].className;
			var f = document.getElementById('col-'+allHTMLTags[i].id);
			if (e.length == 6){
				e = parseInt(e.charAt(5));
				
				switch (true){
					case (e>theClass):
						allHTMLTags[i].style.display = 'none';
						if (f != null){
							f.setAttribute('class', 'col-collapsed');
						}
						break;
						
					case (e<theClass):
						allHTMLTags[i].style.display = '';
						if (f != null){
							f.setAttribute('class', 'col-expanded');
						}
						break;

					case (e==theClass):
						allHTMLTags[i].style.display = '';
						if (f != null){
							f.setAttribute('class', 'col-collapsed');
						}
						break;

    			}		
			}	
		}
	}
	
/* as I had problems with sum of negative values, I made this function to sum the values.
	FIXME: right now, the function works with values in the Brazilian format, and return the value in the American format. Make it work with values in the American format (need to change the code where this function is called)
	FIXME: improve the function or find a better way to accomplish that  */
function sumvalues(a, b) {
	a = a.replace(".","");
	a = a.replace(",",".");
	b = b.replace(".","");
	b = b.replace(",",".");
	var check_a = a.search("-");
	var check_b = b.search("-");

	if ((check_a == -1) && (check_b == -1)){
		a = parseFloat(a);
		b = parseFloat(b);
		value = a+b;
		return value;
		}
		else if ((check_a == 0)&&(check_b == 0)) {
		signal = "-";
		len = a.length-1;
		a = a.substr(1,len);
		a = parseFloat(a);
		len = b.length-1;
		b = b.substr(1,len);
		b = parseFloat(b);
		value = signal+(a+b);
		return value;
		}
		else if ((check_a == -1)&&(check_b == 0)) {
		len = b.length-1;
		b = b.substr(1,len);
		a = parseFloat(a);
		b = parseFloat(b);
			if (a > b) {
				value = a-b;
				return value;
				} else {
				signal = "-";
				value = signal+String(b-a);
				return value;
			}
		}
		else if ((check_a == 0)&&(check_b == -1)) {
		len = a.length-1;
		a = a.substr(1,len);
		a = parseFloat(a);
		b = parseFloat(b);
			if (a > b) {
				signal = "-";
				value = signal+String(a-b);
				return value;
				} else {
				value = b-a;
				return value;
			}
		}
	}



function fillparentvalues(guid, value) {
	var el = document.getElementById(guid);
   if (el.innerHTML != 0) {
   	num = el.innerHTML;
  	   num = num.replace(".","");
	   num = num.replace(",",".");

		num = parseFloat(num);
   	} else {
   	num = 0
   	}

   value = parseFloat(value);
   value = value + num;
   value = value.toFixed(2);
   value = value.replace(".",",");
   value = format_currency(value);
   el.innerHTML = value;
	}
	

/* this function is much similar to the fillparentvalues function, but this one is used to sum values of groups separated by tbody elements.
	This should be used in those cases because this function is better than the fillparentvalues (it is easier to implement this one) */
function sumChild (container_id) {
	var container = document.getElementById (container_id);

	/* this will get the column names in the th tags, extract the dates and put the dates into an array */
	var headers = container.getElementsByTagName ("th");
	var hdates = new Array();
	for (var i = 0; i < headers.length; i++) {
		var a = headers[i].id;
		var aux = a.substring(0,a.indexOf("_"));
		hdates.push(aux);
	}
	
	/* this will get the account guids in the tr tags and put it all into an array*/
	var guids = container.getElementsByTagName ("tr");
	
	/* this loop will fill an variable named parentvalue with the sum of all the nested rows for each column */
	var parentvalue = "0,00";

	for (var i = 0; i < hdates.length; i++) {
		if (hdates[i] != "col") {
			for (var ii = 0; ii < guids.length; ii++) {
				if (guids[ii].id != container_id+"_"+container_id) {
					var el = document.getElementById(hdates[i]+"_"+guids[ii].id);
				   if (el.innerHTML != 0) {
			   		num = el.innerHTML;
			   		} else {
			   		num = "0,00";
			   		}
					parentvalue = String(parentvalue);
					parentvalue = parseFloat(sumvalues(parentvalue, num));
					parentvalue = parentvalue.toFixed(2);
					parentvalue = parentvalue.replace(".",",");
					parentvalue = format_currency(parentvalue);
					
				}
			}
			document.getElementById(hdates[i]+"_"+container_id).innerHTML = parentvalue;
			parentvalue = "0,00";
		}
	}
}


function ShowHideChild(val,recur) {
var arLen=TreeStructure.length;
	if (recur == null){
	for (var i=0; i<arLen; i++){
		if (TreeStructure[i][1] == val){
			/* the first variable will be used to hide the line
			and the second will be used to change the style of the first column,
			in order to change the class and change the plus and minus img*/
			var e = document.getElementById(TreeStructure[i][0]);
			var f = document.getElementById('col-'+val);
			switch (e.style.display){
				case '':
					e.style.display = 'none';
						if (f != null){
							f.setAttribute('class', 'col-collapsed');
						}
					ShowHideChild(TreeStructure[i][0],0);
					break;
				case 'none':
          		e.style.display = '';
          			if (f != null){
							f.setAttribute('class', 'col-expanded');
						}
					break;
    		}
		}
	}
	} else {
	for (var i=0; i<arLen; i++){
		if (TreeStructure[i][1] == val){
			var e = document.getElementById(TreeStructure[i][0]);
			var f = document.getElementById('col-'+val);
			switch (e.style.display){
				case '':
					e.style.display = 'none';
						if (f != null){
							f.setAttribute('class', 'col-collapsed');
						}
					ShowHideChild(TreeStructure[i][0],0);
					break;
    		}
		}
	}
	}
}


/* this will hide or show the nested rows. This function does almost the same as the ShowHideChild function, but it is cleaner than that.
This function could not be used in all reports, as it needs the tbody tag to do it and the tbody tag is not possible when we have many levels of nested rows */
function ShowHideChildTbody (container_id) {
	var container = document.getElementById (container_id);
	
	/* this will get the account guids in the tr tags and put it all into an array*/
	var guids = container.getElementsByTagName ("tr");	
		

	for (var i = 0; i < guids.length; i++) {
		var a = guids[i].id;
		if (a != container_id+"_"+container_id) {
			switch (document.getElementById(a).style.display){
				case '':
					document.getElementById(a).style.display = 'none';
					break;
				case 'none':
					document.getElementById(a).style.display = '';
					break;
    		}
		}
	}
}



/* TESTING: this function is not used in any place right now. It will be in the future if it works
	I want that this function loop through all the table tags and fill the parent values accordingly */
function sumChildTEST () {
	
/* all the code below this should be changed. I didnt erased it yet because something here should be useful
	I imagine that it will work like this:
		- get the maximun level number (there is a variable with this number in the main code);
		- get all the tr in the table;
		- loop through each higher level, until find the next account with higher level (ex. cursor in level 3, the loop will go through the level 4 and will stop when find the next level equal or lower than 3;
		*/
			
	var container = document.getElementById ("data");

	/* this will get the column names in the th tags, extract the dates and put the dates into an array */
	var headers = container.getElementsByTagName ("th");
	var hdates = new Array();
	for (var i = 0; i < headers.length; i++) {
		var a = headers[i].id;
		var aux = a.substring(0,a.indexOf("_"));
		hdates.push(aux);
	}
	
	/* this will get the account guids in the tr tags and put it all into an array*/
	var guids = container.getElementsByTagName ("tr");
	
	/* this loop will fill an variable named parentvalue with the sum of all the nested rows for each column */
	var parentvalue = "0,00";

	for (var i = 0; i < hdates.length; i++) {
		if (hdates[i] != "col") {
			for (var ii = 0; ii < guids.length; ii++) {
				if (guids[ii].id != container_id+"_"+container_id) {
					var el = document.getElementById(hdates[i]+"_"+guids[ii].id);
				   if (el.innerHTML != 0) {
			   		num = el.innerHTML;
			   		} else {
			   		num = "0,00";
			   		}
					parentvalue = String(parentvalue);
					parentvalue = parseFloat(sumvalues(parentvalue, num));
					parentvalue = parentvalue.toFixed(2);
					parentvalue = parentvalue.replace(".",",");
					parentvalue = format_currency(parentvalue);
					
				}
			}
			document.getElementById(hdates[i]+"_"+container_id).innerHTML = parentvalue;
			parentvalue = "0,00";
		}
	}
}

