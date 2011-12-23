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
		ini = 0;
		numToReturn = "";		
		len = num.length;		
		}
		else{
		ini = 1;
		numToReturn = "-";
		len = num.length-1;
		num = num.substr(1,len);}

	switch (true){
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
			if (e.length == 6){
				e = parseInt(e.charAt(5));
				
				switch (true){
					case (e>theClass):
						allHTMLTags[i].style.display = 'none';
						break;

					case (e<=theClass):
						allHTMLTags[i].style.display = '';
						break;

    			}		
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

function ShowHideChild(val,recur) {
var arLen=TreeStructure.length;
	if (recur == null){
	for (var i=0; i<arLen; i++){
		if (TreeStructure[i][1] == val){
			var e = document.getElementById(TreeStructure[i][0]);
			switch (e.style.display){
				case '':
					e.style.display = 'none';
					ShowHideChild(TreeStructure[i][0],0);
					break;
				case 'none':
          		e.style.display = '';
					break;
    		}
		}
	}
	} else {
	for (var i=0; i<arLen; i++){
		if (TreeStructure[i][1] == val){
			var e = document.getElementById(TreeStructure[i][0]);
			switch (e.style.display){
				case '':
					e.style.display = 'none';
					ShowHideChild(TreeStructure[i][0],0);
					break;
    		}
		}
	}
	}
}