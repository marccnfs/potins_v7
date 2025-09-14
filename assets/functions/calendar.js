import { h } from "preact";
import {useState, useRef, useCallback} from 'preact/hooks'
import {classNames} from "./dom";

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

/// ici info props a transmettre depuis calendar

let optionhstart = 480,optionhend = 600,timeday = $('.tab-selectdate_timeday'), selected=false, dayOn;


class FtDatesAff {
    constructor(){
        this.jours=["Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
        this.mois=["Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre"];
        this.days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        this.daysdate=['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        this.daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        this.daytime=['Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        this.months=['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' ];
        this.monthsmob=['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
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
    newdatehuman(dt){
        return this.humadate(FtDatesAff.newdate(dt));
    }
}

let formatdate=new FtDatesAff();

class TabWeekcgAff{

    constructor(day){
        this.day=day;
        this.tab=[];
        this.execpt=[];
        this.adexept =function(dt){this.execpt.push(dt)};
        this.deleteexcept=function(dt){this.execpt.splice(this.execpt.indexOf(dt),1)};
        this.reinitopenday=function(){
            this.execpt=[]
        }
    }
}

class MonthCalendarAff{
    constructor(){
        this.dt=new Date();
        this.days=['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        this.daysdate=['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        this.daysmob=['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        this.daytime=['Dim','Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        this.months=['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' ];
        this.monthsmob=['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        this.month="";
        this.month_letter="";
        this.year="";
        this.week="";
        this.day="";
        this.debutmois="";
        this.finmois="";
        this.html="";
        this.end="";
        this.code="";
        this.start="";
    }

    initcalendar() {
        this.month= this.dt.getMonth();
        this.year = this.dt.getFullYear();
        this.loadincalendar()
    }

    changemonth(mois,annee) {
        this.month = parseInt(mois);
        this.year = parseInt(annee);
        if (this.month < 0 || this.month > 11) {
            alert("le mois " + this.month + " n'est pas valide");
        }
        if (this.year < 1970) {
            alert("l'année " + this.year + " est inferieure à 1970");
        }
        this.loadincalendar()
    }

    loadincalendar(){
        this.month_letter=formatdate.months[this.month];
        this.debutmois = new Date(Date.UTC(this.year, this.month, 1));// datetime du premier jour du mois
        let calnext=this.nextDate();
        let nextmois = new Date(Date.UTC(calnext.year, parseInt(calnext.month), 1));
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
        this.day=this.start;
        this.week =6;
        //this.day=this.debutmois; //????

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

    withinMonth(date){
        return this.debutmois.getMonth() !== date.getMonth();
    };
}


function initpageAff(calendar, parse, calinit){
    return inittabparse(calendar,parse,calinit)
}

function initnow(calendar,parse, calinit){
        let nowselect=  new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate(), 0, 0, 0);
        let stringdate=nowselect.getFullYear() + "," + nowselect.getMonth() + "," + nowselect.getDate()
        let thisday=nowselect.getDay();
        parse={'Dim':[],'Lun':[],'Mar':[],'Mer':[],'Jeu':[],'Ven':[],'Sam':[]};
        parse[formatdate.daytime[thisday]].push(stringdate)
        return inittabparse(calendar,parse,calinit)
    }

function inittabparse(calendar,parse,calinit){
        if(!calinit){
            calendar = new MonthCalendarAff()
            calendar.initcalendar()
        }
        let tab=[];
        for (let day in calendar.daytime) {
            tab[calendar.daytime[day]] = new TabWeekcgAff(calendar.daytime[day])
        }
        for (const property in parse) {
            if (parse.hasOwnProperty(property)) {
                if(parse[property].length > 0){
                    tab[`${property}`].execpt=parse[property]
                    tab[`${property}`].day=`${property}`
                }
            }
        }
        return {cal:calendar,tab:tab, tabtemp:parse}
    }

function reinitparse(tab, parse){
        for(let d in tab){
            if(tab.hasOwnProperty(d)){
                if (tab[d].execpt.length > 0) {
                    parse[tab[d].day]=tab[d].execpt
                }
            }
        }
        return parse
}

function changeMonth(calendar,tab,now){
        calendar.changemonth(now.month, now.year)
        return calendar
    }
function changedate(r,tab){
        let dateclic = r.affidate;
        if (r.dayOn) {
            tab[r.day].deleteexcept(dateclic)
        } else {
            tab[r.day].adexept(dateclic)
        }
        return tab
    }
function ftnewdatehuman(dt){
        return formatdate.humadate(FtDatesAff.newdate(dt));
    }

function buttonadd(dayselect){
    return'<button type="button" class="btn btn-primary addtime" data-addtime="'+dayselect+'"><i class="fa fa-plus-square"></i><span>ajouter</span></button>'
}

function buttondelete(dayselect){
    return'<button type="button" class="btn btn-primary deletetime" data-deletetime="'+dayselect+'"><i class="fa fa-trash"></i></button>'
}

function addTimeOpen(dayselect) {  //append <li> + lance la fonction(adddivtime) de creation des imput times et ajout au tab de tab
    tab[dayselect].tab.push({
        startminutes: optionhstart,
        alongminute: optionhend
    });
    initdivtime()
}

function deleteTimeOpen(infodelete) {
    tab[infodelete].tab.pop()
    $('[data-addtime="'+infodelete+'"]').show()
    initdivtime()
}

function initdivtime(){
    timeday.html("");
    for(let day in tab) {
        if (tab.hasOwnProperty(day)) {
            let h = "";
            if (tab[day].selected) {
                let sel = $('[data-dateday="' + day + '"]');
                sel.addClass('activeday');
                sel.parent().addClass('selectedday');
                h = "<li class=\"eventday " + day.toLowerCase() + "\" data-dateslect=\"" + day + "\">";
                tab[day].tab.forEach(function (time, order) {
                    h += "<div class=\"defaultday\" data-order=\"" + order + "\">";
                    h += "<div class=\"ttim\">";
                    let timelet = calhour(
                        [tab[day]['tab'][order].startminutes,
                            tab[day]['tab'][order].alongminute + tab[day]['tab'][order].startminutes]); // valeur arbitraire 8h00 -18h00  ou idem au options du formulaire
                    h += "<input type=\"time\" class=\"hitime start\" name=\"hitime\" value=\"" + timelet[0].ht + ":" + timelet[0].it + "\">";
                    h += "<input type=\"time\" class=\"hitime end\" name=\"hitime\" value=\"" + timelet[1].ht + ":" + timelet[1].it + "\">";
                    h += "</div>";
                    h += "</div>";
                });
                let sizetab = tab[day]['tab'].length;
                if (sizetab > 1) $('[data-addtime="' + day + '"]').hide();
                h += "" + (sizetab <= 1 ? buttonadd(day) : buttondelete(day)) + "</li>";
                timeday.append(h);
            }
        }
    }
}

function calhour(t){
    t.forEach(function (time, indice, tableau) {
        let h = parseInt(time/60);
        if (h < 10) {h = "0" + h}
        let i = time % 60;
        if (i < 10) {i = "0" + i}
        tableau[indice]={ht:h,it:i};
    });
    return t
}


let calendar={};
let tab=[];
let initcal=true;

export default function CalendarEvent(props) {

    const [html, setHtml] = useState()
    const[togglechioice, setTogglechioice] = useState(false)
    const[calinit, setCalinit] = useState(false)


    if(props.edit && initcal){
        let r = initpageAff(calendar,props.parse,calinit)
        calendar = r.cal
        tab = r.tab
        setHtml(createCalendar(calendar))
        setTogglechioice(false)
        setCalinit(true)
        props.onTabchange(r.tabtemp);
        initcal=!initcal;
    }

    const handleNow = function(e){
        e.preventDefault()
        let r = initnow(calendar,props.parse, calinit)
        calendar = r.cal
        tab = r.tab
        setTogglechioice(true)
        props.onTabchange(r.tabtemp);
        setCalinit(true)
    }

    const handleother =function(e){
        e.preventDefault()
        let r = initpageAff(calendar,props.parse,calinit)
        calendar = r.cal
        tab = r.tab
        setHtml(createCalendar(calendar))
        setTogglechioice(false)
        setCalinit(true)
        props.onTabchange(r.tabtemp);
    }

    // ajout/suppression d'une date a tabunique
    const handleselect = (r, e) => {
        e.stopPropagation();
        e.preventDefault();
        tab = changedate(r, tab)
        setHtml(createCalendar(calendar))
        props.onTabchange(reinitparse(tab,props.parse));
    }

    const handleChangeCalendarprevious = (e) => {
        e.preventDefault()
        calendar = changeMonth(calendar, tab, calendar.previousDate())
        setHtml(createCalendar(calendar))
    }

    const handleChangeCalendarnext = (e) => {
        e.preventDefault()
        calendar = changeMonth(calendar, tab, calendar.nextDate())
        setHtml(createCalendar(calendar))
    }

    function createCalendar(calendar) {
        let nweek = []
        for (let incre1 = 0; incre1 < calendar.week; incre1++) {
            nweek.push(incre1)
        }
        return <table className={classNames("calendar__table calendar__table--" + calendar.week + "weeks")}>
            <thead>
            <tr>
                {calendar.daysmob.map((day, index) => (
                    <td key={day}
                        className={classNames("tab-calendar-thead calendar__weekday")}>{calendar.daysmob[index]}</td>
                ))}
            </tr>
            </thead>
            {nweek.map((day, index) => (
                <Tdpardate ligne={day}>
                    {(index) => <td key={index.affidate} onClick={(e) => handleselect(index, e)}
                                    className={classNames(index.istoday ? ' is__today' : '', index.dayOn ? 'dayon' : '')}>
                        <div
                            className={classNames("calendar__day", calendar.withinMonth(index.dateok) || index.beforday ? ' calendar__othermonth' : '')}
                            data-date={index.affidate}>{index.dateok.getDate()}</div>
                    </td>}
                </Tdpardate>
            ))}
        </table>
    }

    function Tdpardate(props) {

        let datenow = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate(), 0, 0, 0);
        let items = [];

        for (let incre2 = 0; incre2 <= 6; incre2++) {

            let datestart = new Date(calendar.day.getFullYear(), calendar.day.getMonth(), calendar.day.getDate(), 2, 0, 0); // deux herure de plus pour rattarpeer le decallage heure hivers/ete
            let dateok = new Date(datestart.setUTCMilliseconds((incre2 + props.ligne * 7) * (24 * 60 * 60 * 1000)));
            let affidate = dateok.getFullYear() + "," + dateok.getMonth() + "," + dateok.getDate();
            let datetest = datenow.getFullYear() + "," + datenow.getMonth() + "," + datenow.getDate();
            let day = calendar.daytime[dateok.getDay()];

            let mapincre2 = {
                dateok: dateok,
                beforday: datenow.getTime() > dateok.getTime(),
                istoday: affidate === datetest,
                affidate: affidate,
                day: day,
                dayOn: tab[day].execpt.length > 0 ? tab[day].execpt.find(ele => ele === affidate) : false,
            }
            items.push(props.children(mapincre2));
        }
        return <tr key={props.ligne}>{items}</tr>;
    }

    let date1 = new Date();
    let datestring=date1.toISOString().substring(0, 10)

    if(!calinit){
        return (
            <div className="bk-fix-zoneform">
                <button className='btn-send-log' onClick={handleNow}> J 'y suis </button>
                <button className='btn-send-log' onClick={handleother}> J 'y serais </button>
            </div>
        )
     }else {
        return (
            <div>
                <div className="bk-fix-zoneform">
                    <button className='btn-send-log' onClick={handleNow}> J 'y suis</button>
                    <button className='btn-send-log' onClick={handleother}> J 'y serais</button>
                </div>

                {!togglechioice
                    ? <div className="calendar-v8">
                        <div className="direct-month">
                            <button className="bt_affi _wl-5 bt_affi-primary" onClick={handleChangeCalendarprevious}>
                                <i className="fa fa-chevron-circle-left" aria-hidden='true'/>
                            </button>
                            <div className="col-date">
                                <h1>{calendar.month_letter + " " + calendar.year}</h1>
                            </div>
                            <button className='bt_affi _wl-5 bt_affi-primary' onClick={handleChangeCalendarnext}>
                                <i className='fa fa-chevron-circle-right' aria-hidden='true'/>
                            </button>
                        </div>
                        <div className="tab-calendar">{html}</div>
                    </div>
                    : <div className="calendar-v8">
                        <h3>{"Maintenant ! le" + datestring}</h3>
                    </div>
                }
            </div>
        )
    }
}



// <div className="tab-calendar" dangerouslySetInnerHTML={{__html: html}}/>
/*
    if(props.event){
        datarea = <div className="list-incub">
            <div className="pos-incub-i">
                <div className="pos-incub-l">
                    <FormField name='startevent' type='date' value={datestart} required onChange={handleChangeDate}>début</FormField>
                    <FormField name='endevent' type='date' value={dateend} required onChange={handleChangeDate}>fin</FormField>
                </div>
            </div>
        </div>
    }

 */
