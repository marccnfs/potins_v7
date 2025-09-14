(function($) {
    'use strict';

    Date.prototype.getWeek = function (dowOffset)
    {
        dowOffset = typeof(dowOffset) === 'number' ? dowOffset : 0; //default dowOffset to zero
        let newYear = new Date(this.getFullYear(),0,1);
        let day = newYear.getDay() - dowOffset; //the day of week the year begins on
        day = (day >= 0 ? day : day + 7);
        let daynum = Math.floor((this.getTime() - newYear.getTime() -
            (this.getTimezoneOffset()-newYear.getTimezoneOffset())*60000)/86400000) + 1;
        let weeknum;
        //if the year starts before the middle of a week
        if(day < 4)
        {
            weeknum = Math.floor((daynum+day-1)/7) + 1;
            if(weeknum > 52)
            {
                let nYear = new Date(this.getFullYear() + 1,0,1);
                let nday = nYear.getDay() - dowOffset;
                nday = nday >= 0 ? nday : nday + 7;
                /*if the next year starts before the middle of
                  the week, it is week #1 of that year*/
                weeknum = nday < 4 ? 1 : 53;
            }
        }
        else
        {
            weeknum = Math.floor((daynum+day-1)/7);
        }
        return weeknum;
    };

    class Calendar{
        constructor(mois,annee){
            this.dt=new Date();
            this.days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            this.daysdate=['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            this.daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            this.daytime=['Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            this.months=['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' ];
            this.month="";
            this.month_letter="";
            this.year="";
            this.week="";
            this.day="";
            this.appointments=[];
            this.debutmois="";
            this.finmois="";
            if(mois === null || annee === null) {
                this.month= this.dt.getMonth();
                this.year = this.dt.getFullYear();
            }else{
                this.month=parseInt(mois);
                this.year=parseInt(annee);
                if (this.month < 0 || this.month> 11) {
                    alert("le mois "+this.month+" n'est pas valide");
                }
                if (this.year < 1970)  {
                    alert("l'année "+this.year+" est inferieure à 1970");
                }
            }
            this.month_letter=formatdate.months[this.month];
            this.debutmois = new Date(Date.UTC(this.year, this.month, 1));// datetime du premier jour du mois
            let nextmois = new Date(Date.UTC(this.nextDate().year, this.nextDate().month, 1));
            this.finmois=new Date(nextmois.getTime()-60*1000);
            let d=true;
            let datetest=new Date(this.debutmois);
            while(d){
                if(datetest.getDay()===1){
                    this.start=datetest;
                    d=false
                }else{
                    datetest.setTime(datetest.getTime()-24*60*60*1000);
                }
            }
            this.end = this.finmois;  	// dernier jour du mois
            let startweek = this.start.getWeek();
            let endweek = this.end.getWeek();
            if(endweek === 0) {
                endweek = (new Date(this.end.getTime()-7*24*60*60*1000)).getWeek()+1;
            }
            let weeks = (endweek - startweek) +1;
            this.week=weeks;
            this.day=this.start;
            if(weeks < 0)
            {
                this.week= this.end.getWeek();
                this.day=this.debutmois;
            }
            this.withinMonth= function (date){
                return this.debutmois.getMonth() === date.getMonth();
            };
        }

        previousDate(){
            let moisprevious=this.month-1;
            let yearprevious=this.year;
            if(moisprevious < 0 ){
                moisprevious =11;
                yearprevious=this.year-1;
            }
            return {month: moisprevious, year: yearprevious};
        };

        nextDate(){
            let moisnext=this.month+1;
            let yearnext=this.year;
            if(moisnext > 11)
            {
                moisnext =0;
                yearnext=this.year+1;
            }
            return {month:moisnext, year:yearnext};
        };

        dayselect(daydate){
            this.appointments.push(daydate)
        };

        daydelete(daydate){
            this.appointments.splice(this.appointments.indexOf(daydate),1)
        }
        isSelect(dateclic){
            return this.appointments.indexOf(dateclic)
        }
    }
    class FtDates {
        constructor(){
            this.jours=["Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
            this.mois=["Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre"];
            this.days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            this.daysdate=['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            this.daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            this.daytime=['Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            this.months=['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' ];
        }

        humadate(dt){
            let j=dt.getDay();
            let d=dt.getDate();
            if(d===1){d+="er";}
            let m=dt.getMonth();
            let y=dt.getFullYear();
            return this.jours[j]+" "+d+" "+this.mois[m]+" "+y;
        }

        static newdate(dt){
            let regSep=/[,;]+/gi;
            let dates=dt.split(regSep);
            return new Date(parseInt(dates[0]),parseInt(dates[1]),parseInt(dates[2]),0,0,0);
        }
        static nowdatezero(){
            let date = new Date();
            date.setHours(0);
            date.setMinutes(0);
            date.setSeconds(0);
            return date;
        }
        newdatehuman(dt){
            return this.humadate(FtDates.newdate(dt));
        }
        testdate(dt){
            return Math.floor(FtDates.newdate(dt)/1000)+1 >= Math.floor(FtDates.nowdatezero()/1000);
        }
    }

    class TabWeekcg{
        constructor(day){
            this.day=day;
            this.tab=[];
            this.execpt=[];
            this.selected=false;
            this.adexept =function(dt){this.execpt.push(dt)};
            this.deleteexcept=function(dt){this.execpt.splice(this.execpt.indexOf(dt),1)};
            this.reinitopenday=function(thisday){
                this.selected=true;
                this.execpt=[]
            }
        }
        delteconge(){ // todo a revoir ??

            for(let cong in conges){
                if(conges.hasOwnProperty(cong)) {
                    if (conges[cong].nbdays === formatdate.daysmob.indexOf(this.day)){
                    }
                }
            }
            this.selected=false;
            this.tab=[];
        }
    }

    let formatdate=new FtDates();

    var AffCalendar = window.AffCalendar || {};
    let selected=false, dayOn, conges={}, tabunique={};
    let idopenday=$('#initoffert');
    let calgrid=$('.lig-grid_day');

    AffCalendar = (function() {
        var instanceUid = 0;
        function AffCalendar(element, settings) {
            var _ = this;
            _.instanceUid = instanceUid++;
            _.now={month:(new Date()).getMonth(),year:new Date().getFullYear()};
            _.initpage();
        }
        return AffCalendar;
    }());

    $.fn.affCalendar = function() {
        var _ = this,
            opt = arguments[0],
            args = Array.prototype.slice.call(arguments, 1),
            l = _.length,
            i,
            ret;

        for (i = 0; i < l; i++) {
            if (typeof opt == 'object' || typeof opt == 'undefined') {
                _[i].affCalendar = new AffCalendar();
            }else{
                ret = _[i].affCalendar[opt].apply(_[i].affCalendar, args);
                if (typeof ret != 'undefined') return ret;
            }
        }
        return _;
    };

    AffCalendar.prototype.nextMonth =function(e) {
        var _=this;
        _.now=_.calendar.nextDate();
        _.initpage()
    };

    AffCalendar.prototype.getdayselected =function() {
        var _=this;
        return _.calendar.appointments.join(';')
    };

    AffCalendar.prototype.previousMonth =function(e) {
        var _=this;
        _.now=_.calendar.previousDate();
        _.initpage()
    };

    AffCalendar.prototype.addday =function(e) {
        let _=this, el=e.target, dateclic=$(el).attr('data-date'), parentdiv=$(el).parent();
        if(formatdate.testdate(dateclic)){
            if(_.calendar.isSelect(dateclic)!==-1){
                parentdiv.removeClass('dayon');
                parentdiv.addClass('dayoff');
                _.calendar.daydelete(dateclic);
            }else{
                parentdiv.addClass('dayon');
                parentdiv.removeClass('dayoff');
                _.calendar.dayselect(dateclic)
            }
            _.recapopendays()
        }
    };

    AffCalendar.prototype.init = function() {
        var _ = this;
        let datenow=new Date(new Date().getFullYear(),new Date().getMonth(),new Date().getDate(),0,0,0);
        let html="<div class=\"col-date\"><h1>"+ _.calendar.month_letter+" "+  _.calendar.year+"</h1>";
        html+= "<div class='direct-month'>";
        html+= "<div class='butt-direct' id='previoustmonth'><i class='fa fa.chevron-right'></i></div>";
        html+= "<div class='butt-direct' id='nextmonth'><i class='fa fa.chevron-left'></i></div>";
        html+= "</div></div>";
        html+= "<table class=\"calendar__table calendar__table--"+ _.calendar.week+"weeks\" >";
        html+= "<thead>";

        for (var day in formatdate.daysmob) {
            html+= "<td class=\"tab-calendar-thead calendar__weekday\">" + formatdate.daysmob[day] + "</td>"
        }
        html+= "</thead>";
        for (let incre1 = 0; incre1 <  _.calendar.week; incre1++) {
            html+="<tr>";
            for (let incre2 = 0; incre2 <= 6; incre2++) {
                dayOn=undefined;
                let datestart = new Date( _.calendar.day.getFullYear(), _.calendar.day.getMonth(), _.calendar.day.getDate(),0,0,0);
                let dateok=new Date(datestart.setUTCMilliseconds((incre2 + incre1 * 7)*24*60*60*1000));
                let beforday=datenow.getTime() > dateok.getTime();
                let istoday = datenow.getTime() === dateok.getTime();
                let affidate=dateok.getFullYear()+","+dateok.getMonth()+","+dateok.getDate();
                let day=formatdate.daytime[dateok.getDay()];
                let openday=false;
                if (typeof _.calendar.appointments.find(el=> el===affidate) !== 'undefined') openday=true;
                html+="<td class=\""+(istoday ? ' is__today':'')+" "+(selected  ? 'dayselect':'')+" "+(openday  ? 'dayopen':'dayoff')+" "+(dayOn  ? 'dayon':'')+"\">"
                html+= "<div class=\"calendar__day"+( _.calendar.withinMonth(dateok) || beforday ? ' calendar__thismonth' : 'calendar__othermonth')+"\" data-date=\""+affidate+"\">"+dateok.getDate()+"</div>";
                html+= "</td>"
            }
            html+= "</tr>"
        }
        html+= "</table>";
        calgrid.html(html);
        _.recapopendays();
    };

    AffCalendar.prototype.initpage =function(){
        var _ = this;
        if( _.instanceUid===1){
            delete _.calendar;
            _.calendar=new Calendar(_.now.month, _.now.year)
        }else{
            _.instanceUid=1;
            _.calendar=new Calendar(_.now.month, _.now.year)
            for(let day in _.calendar.daytime){
                tabunique[_.calendar.daytime[day]]=new TabWeekcg(_.calendar.daytime[day])
            }
            if(idopenday.attr('data-twigtbunique')!=="") {
                let tabparse = JSON.parse(idopenday.attr('data-twigtbunique'));
                tabparse.forEach(el=>_.calendar.dayselect(el))
                idopenday.attr('data-twigtbunique',"")
            }
        }
        _.init()
    };

    AffCalendar.prototype.recapopendays =function(){
        var _ = this;
        let closedmonth="",closedday="",dayweekopen="",dateexceptopen="",hrecap="";
        _.calendar.appointments.forEach(function (value, index) {
                        dateexceptopen += "" + formatdate.newdatehuman(value) + "" + (index === _.calendar.appointments.length - 1 ? "." : ", ") + ""
                    })


        hrecap+="<div class=\"recpday\">jour(s) sélectionné(s) : "+(dateexceptopen !=="" ? dateexceptopen : "")+"</div>";
        $('#recapopendays').html(hrecap)
    }

})(jQuery);

