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

function bodyOnReady(func)
{
	/**
	 * Call the function 'func' when DOM loaded
	 * By Micox - www.elmicox.com - elmicox.blogspot.com - webly.com.br
	 * http://www.elmicox.com/2007/evento-body-onready-sem-o-uso-de-libs/
	 */
	if (!(document.body == null))
	{
        document.body.onkeypress = function(e){
            if (e.keyCode == 27) {
				document.getElementById('calendarDiv').style.display = 'none';
			}
        };
  		func();
	} else {		
		var func_rep = func;
  		setTimeout(function(){ bodyOnReady(func_rep) },100);
	}
}

bodyOnReady(function() 
{
	divCal = document.createElement("div");
	divCal.id = "calendarDiv";
	document.body.appendChild(divCal);	
})

var popUpCal = {
    selectedMonth: new Date().getMonth(), // 0-11
    selectedYear: new Date().getFullYear(), // 4-digit year
    selectedDay: new Date().getDate(),
    calendarId: 'calendarDiv',
    inputClass: 'calendarSelectDate',
    
    init: function () {
        var x = getElementsByClass(popUpCal.inputClass, document, 'input');
        var y = document.getElementById(popUpCal.calendarId);
        // set the calendar position based on the input position
        for (var i=0; i<x.length; i++) {
            x[i].onfocus = function () {
                popUpCal.selectedMonth = new Date().getMonth();
                setPos(this, y); // setPos(targetObj,moveObj)
                y.style.display = 'block';
                popUpCal.drawCalendar(this,true); 
                popUpCal.setupLinks(this);
            }
			/*, quando sai fecha o calendario
			x[i].onblur = function(e){			
				if(document.getElementById(e.target.id).value != ""){
					y.style.display = 'none';
				}
			}*/
        }
    },
    
    drawCalendar: function (inputObj,getDate) {

        var expReg = /^(([0-2]\d|[3][0-1])\/([0]\d|[1][0-2])\/[1-2][0-9]\d{2})$/;

	    if (getDate && inputObj.value != "" && inputObj.value.match(expReg)){
			popUpCal.selectedMonth = parseInt(inputObj.value.substr(3, 2)) - 1;
			popUpCal.selectedYear = inputObj.value.substr(6, 4);
			popUpCal.selectedDay = inputObj. value.substr(0, 2);
		}

        var today = new Date().getDate();
        var thisMonth = new Date().getMonth();
        var thisYear = new Date().getFullYear();
		
        var dia = "";
        var mes = "";
        if(today < 9) dia = "0"+today.toString();
        if(thisMonth < 9) mes = "0"+(thisMonth+1);		
		
        /*alert(popUpCal.selectedDay+'/'+popUpCal.selectedMonth+"/"+popUpCal.selectedYear);
        */
        var html = '';
        html = '<a id="closeCalender">[ x ]</a>';
        html += '<table cellpadding="0" cellspacing="0" id="linksTable"><tr>';
        html += '   <td><a id="prevMonth"><< </a></td>';
		html += '   <td align="center"><a id="hoje">HOJE</a></td>';
        html += '   <td><a id="nextMonth"> >></a></td>';
        html += '</tr></table>';
        html += '<table id="calendar" cellpadding="0" cellspacing="0"><tr>';
        html += '<th colspan="7" class="calendarHeader">'+getMonthName(popUpCal.selectedMonth)+' '+popUpCal.selectedYear+'</th>';
        html += '</tr><tr class="weekDaysTitleRow">';
        var weekDays = new Array('D','S','T','Q','Q','S','S');
        for (var j=0; j<weekDays.length; j++) {
            html += '<td>'+weekDays[j]+'</td>';
        }
        
        var daysInMonth = getDaysInMonth(popUpCal.selectedYear, popUpCal.selectedMonth);
        var startDay = getFirstDayofMonth(popUpCal.selectedYear, popUpCal.selectedMonth);
        var numRows = 0;
        var printDate = 1;
        if (startDay != 7) {
            numRows = Math.ceil(((startDay+1)+(daysInMonth))/7); // calculate the number of rows to generate
        }

        // calculate number of days before calendar starts
        if (startDay != 7) {
            var noPrintDays = startDay + 1; 
        } else {
            var noPrintDays = 0; // if sunday print right away  
        }

        // create calendar rows
        for (var e=0; e<numRows; e++) {
            html += '<tr class="weekDaysRow">';
            // create calendar days
            for (var f=0; f<7; f++) {
                if ((printDate == popUpCal.selectedDay) 
                     && (noPrintDays == 0)) {
                    html += '<td id="today" class="weekDaysCell">';
                } else {
                    html += '<td class="weekDaysCell">';
                }
                if (noPrintDays == 0) {
                    if (printDate <= daysInMonth) {
                        html += '<a>'+printDate+'</a>';
                    }
                    printDate++;
                }
                html += '</td>';
                if(noPrintDays > 0) noPrintDays--;
            }
            html += '</tr>';
        }
        html += '</table>';
        
        // add calendar to element to calendar Div
        var calendarDiv = document.getElementById(popUpCal.calendarId);
        calendarDiv.innerHTML = html;
        
        // close button link
        document.getElementById('closeCalender').onclick = function () {
            calendarDiv.style.display = 'none';
        }
        // setup next and previous links
        document.getElementById('prevMonth').onclick = function () {
            popUpCal.selectedMonth--;
            if (popUpCal.selectedMonth < 0) {
                popUpCal.selectedMonth = 11;
                popUpCal.selectedYear--;
            }
            popUpCal.drawCalendar(inputObj,false); 
            popUpCal.setupLinks(inputObj);
        }
        document.getElementById('nextMonth').onclick = function () {
            popUpCal.selectedMonth++;
            if (popUpCal.selectedMonth > 11) {
                popUpCal.selectedMonth = 0;
                popUpCal.selectedYear++;
            }
            popUpCal.drawCalendar(inputObj,false); 
            popUpCal.setupLinks(inputObj);
        }

        document.getElementById('hoje').onclick = function (){
		var mydate= new Date();
		var ano = mydate.getFullYear();
		var mes = mydate.getMonth()+1;
		var dia = mydate.getDate();

		if(dia < 9) dia = "0"+dia;
		if(mes < 9) mes = "0"+mes;

		inputObj.value = dia+'/'+mes+'/'+ano;

		calendarDiv.style.display = 'none';
        }
    }, // end drawCalendar function
    
    setupLinks: function (inputObj) {
        // set up link events on calendar table
        var y = document.getElementById('calendar');
        var x = y.getElementsByTagName('a');
        for (var i=0; i<x.length; i++) {
            x[i].onmouseover = function () {
                this.parentNode.className = 'weekDaysCellOver';
            }
            x[i].onmouseout = function () {
                this.parentNode.className = 'weekDaysCell';
            }
            x[i].onclick = function () {
                document.getElementById(popUpCal.calendarId).style.display = 'none';
                popUpCal.selectedDay = this.innerHTML;
                inputObj.value = formatDate(popUpCal.selectedDay, popUpCal.selectedMonth, popUpCal.selectedYear);       
            }
        }
    }
    
}
// Add calendar event that has wide browser support
if ( typeof window.addEventListener != "undefined" )
    window.addEventListener( "load", popUpCal.init, false );
else if ( typeof window.attachEvent != "undefined" )
    window.attachEvent( "onload", popUpCal.init );
else {
    if ( window.onload != null ) {
        var oldOnload = window.onload;
        window.onload = function ( e ) {
            oldOnload( e );
            popUpCal.init();
        };
    }
    else
        window.onload = popUpCal.init;
}

/* Functions Dealing with Dates */

function formatDate(Day, Month, Year) {
    Month++; // adjust javascript month
    if (Month < 10) Month = '0'+Month; // add a zero if less than 10
    if (Day < 10) Day = '0'+Day; // add a zero if less than 10
    var dateString = Day+'/'+Month+'/'+Year;
    return dateString;
}

function getMonthName(month) {
    var monthNames = new Array('Janeiro','Fevereiro','Mar�o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
    return monthNames[month];
}

function getDayName(day) {
    var dayNames = new Array('Segunda','Ter�a','Quarta','Quinta','Sexta','S�bado','Domingo')
    return dayNames[day];
}

function getDaysInMonth(year, month) {
    return 32 - new Date(year, month, 32).getDate();
}

function getFirstDayofMonth(year, month) {
    var day;
    day = new Date(year, month, 0).getDay();
    return day;
}

/* Common Scripts */

function getElementsByClass(searchClass,node,tag) {
    var classElements = new Array();
    if ( node == null ) node = document;
    if ( tag == null ) tag = '*';
    var els = node.getElementsByTagName(tag);
    var elsLen = els.length;
    var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
    for (i = 0, j = 0; i < elsLen; i++) {
        if ( pattern.test(els[i].className) ) {
            classElements[j] = els[i];
            j++;
        }
    }
    return classElements;
}

/* Position Functions */

function setPos(targetObj,moveObj) {
    var coors = findPos(targetObj);
    moveObj.style.position = 'absolute';
    moveObj.style.top = coors[1]+20 + 'px';
    moveObj.style.left = coors[0] + 'px';
}

function findPos(obj) {
    var curleft = curtop = 0;
    if (obj.offsetParent) {
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    return [curleft,curtop];
}