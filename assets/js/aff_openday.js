import {useState} from "preact/hooks";
import { ApiError, jsonFetch } from '../functions/api.js'
import {flash} from "../elements/Alert";

document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.calendar-section');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Désactiver tous les onglets
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Activer l'onglet sélectionné
            button.classList.add('active');
            document.getElementById(`calendar-${button.dataset.tab}`).classList.add('active');
        });
    });
});

document.getElementById('submitclokAll').addEventListener('click', () => {
    alert('Horaires mis à jour avec succès !');
});

document.querySelectorAll('.thisday-block').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.classList.toggle('active');
    });
});

/*
document.addEventListener('DOMContentLoaded', () => {
    // Récupérer les données JSON depuis la div cachée
    const providerElement = document.getElementById('nameprovider');
    if (!providerElement) return;

    const joursOuverts = JSON.parse(providerElement.dataset.twigtbunique || '{}'); // Liste des jours ouverts
    const joursFermes = JSON.parse(providerElement.dataset.twigconges || '[]');   // Liste des jours fermés

    console.log("Jours ouverts:", joursOuverts);
    console.log("Jours fermés:", joursFermes);

    // Sélectionner tous les boutons de jours d'ouverture
    const joursButtons = document.querySelectorAll('.thisday-block');

    joursButtons.forEach(button => {
        const day = button.getAttribute('data-dateday'); // Ex: "Lun", "Mar"
        const parentLi = button.closest('li'); // Récupérer l'élément parent <li>

        // Vérifier si le jour est dans la liste des jours ouverts
        if (joursOuverts.hasOwnProperty(day)) {
            button.classList.add('active', 'activeday');
            parentLi.classList.add('selectedday'); // Ajouter la classe à <li>
        } else {
            button.classList.remove('active', 'activeday');
            parentLi.classList.remove('selectedday');
        }

        // Vérifier si le jour est fermé (congé)
        if (joursFermes.includes(day)) {
            button.classList.add('closed');
        } else {
            button.classList.remove('closed');
        }
    });
});


 */



$(document).ready(function() {

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



    let idopenday=$('#nameprovider');

    if(idopenday.length> 0){

    let conges={}, tabunique={}, lidays="",tdmth="",dayselect = [],optionhstart = 480,optionhend = 600, hydrate = [],timeday ="", calgrid=$('.lig-grid_day');
    let selected=false, dayOn, calendar={id:0, calendar:{}};

    let website=$('.wb-inf-'),
        post=$('.form-elastic').data('post'),
        id= website.attr('data-idwb'),
        slug= website.attr('data-slugwb'),
        agent=website.attr('data-agent');
    // creation de la date en string humain ex: "Lundi 1er mai 2019"

    /*------------------route en prod --------------------------------*/
    //let routeopenday = Routing.generate('init-opendays-ajx',{},true);
   // let redirect =Routing.generate('parameters',{id:id},true)

    /* route en dev ----------------------------------------------*/
    let routeopenday ="/admin-website/param/init-opendays/jx"
    let redirect = "/admin-website/param/parameters"

    class FtDates {
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
            return this.humadate(FtDates.newdate(dt));
        }
    }

    let formatdate=new FtDates();

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
        delteconge(){

            for(let cong in conges){
                if(conges.hasOwnProperty(cong)) {
                   formatdate.daysmob.indexOf(this.day)
                    if (conges[cong].nbdays === formatdate.daysmob.indexOf(this.day)){
                       delete conges[cong]
                    }
                }
            }
            this.selected=false;
            this.tab=[];
        }

    }

    class MonthCalendar{
        constructor(mois,annee){
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
            if(endweek === 1) {
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
                return this.debutmois.getMonth() !== date.getMonth();
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
    }

    class Datesconge{
        constructor(cg,sort) {
            if(sort===1){
                let datet =FtDates.newdate(cg);
                this.datedeb = datet.getTime();
                this.dateend = datet.getTime()+24*60*60*1000;
                this.dateft= formatdate.humadate(datet);
                this.nbdays = datet.getUTCDay();
            }
            if(sort===2){
                this.monthcg = calendar.calendar["months"].indexOf(cg);
            }
            this.type = sort;
        }

    }

    initpage({month:(new Date()).getMonth(),year:new Date().getFullYear()});

  /*  for(let day in calendar.calendar.daysmob){
    lidays+=$("<li class=\"listweek-block\"><button class=\"thisday-block\" data-dateday=\""+calendar.calendar.daysmob[day]+"\">"+calendar.calendar.daysmob[day]+"</button></li>"
    }
    */

    let tabdayhtml="";

  if(agent==="mobile/"){
      for(let mth in formatdate.monthsmob){
          tdmth+="<td class=\"tab-calendar-thead calendar__month";
          tdmth+=""+(typeof conges[calendar.calendar.monthsmob[mth]]==="undefined" ? '' :' dayoff')+"\">";
          tdmth+=""+calendar.calendar.monthsmob[mth]+"</td>"
      }
      tabdayhtml=$('.tab-selectdate-day_mob')
      timeday = $('.tab_mob_select-day')
  }else{
    for(let mth in formatdate.months){
        tdmth+="<td class=\"tab-calendar-thead calendar__month";
        tdmth+=""+(typeof conges[calendar.calendar.months[mth]]==="undefined" ? '' :' dayoff')+"\">";
        tdmth+=""+calendar.calendar.months[mth]+"</td>"
    }
      tabdayhtml=$('.tab-selectdate-day')
      timeday = $('.tab-selectdate_timeday')
  }
    initdivtime();

    tabdayhtml.append(lidays);
    $('.calendar__table--month').append(tdmth);

    $("[data-tab]").click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        toogleonglet($(this));
    });

    $('#post-opendays').on('click', function(){
        goajax(linearajax)
    });

    calgrid.on('click', '#previoustmonth', function(e){
        e.stopPropagation();
        initpage(calendar.calendar.previousDate())
    });

    calgrid.on('click', '#nextmonth', function(e){
        e.stopPropagation();
        initpage(calendar.calendar.nextDate())
    });

    // ajout/suppression d'une date de fermeture conges et tabunique
    calgrid.on('click','.calendar__day', function(e) {
        e.stopPropagation();
        e.preventDefault();
        let dateclic=$(this).attr('data-date');
        let parentdiv=$(this).parent();
        if (parentdiv.hasClass('dayoff')) {
            if (parentdiv.hasClass('dayon')) {
                parentdiv.removeClass('dayon');
                tabunique[formatdate.daysmob[parentdiv.index()]].deleteexcept(dateclic)
            }else{
                $(this).parent().addClass('dayon');
                tabunique[formatdate.daysmob[parentdiv.index()]].adexept(dateclic)
            }
            recapopendays()
            return
        }

        if (parentdiv.hasClass('dayopen')){
            if (parentdiv.hasClass('dayselect')) {
                parentdiv.removeClass('dayselect');
                delete conges[dateclic]
            }else{
                $(this).parent().addClass('dayselect');
                conges[dateclic]=new Datesconge(dateclic,1);
            }
        }
        recapopendays()
    });

    // ajout/suppression d'un mois de fermeture
    $('.calendar__month').on('click',function(e) {
        e.stopPropagation();
        e.preventDefault();
        let monthclic=$(this).text();
        if ($(this).hasClass('dayoff')) {
            $(this).removeClass('dayoff');
            delete conges[monthclic]
        } else {
            conges[monthclic]=new Datesconge(monthclic,2);
            $(this).addClass('dayoff');
        }
        recapopendays()
    });

        if(agent==="mobile/") {
            //evenement sur div addtime avec delegation
            timeday.on('click', '.addtime_mob', function () {
                let dayselect = $(this).attr('data-addtime');
                addTimeOpen(dayselect);
            });
        }else{
            //evenement sur div addtime avec delegation
            timeday.on('click', '.addtime', function () {
                let dayselect = $(this).attr('data-addtime');
                addTimeOpen(dayselect);
            });

        }

    //evenement sur div deletetime avec delegation
    timeday.on('click','.deletetime', function(){
        deleteTimeOpen($(this).attr('data-deletetime'));
        $(this).siblings('[data-order="1"]').remove();
    });

    // ajout/suppression d'un jour de la semaine Tabunique
    $('.thisday-block').on('click',function(e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).toggleClass('activeday');
        dayselect = $(this).attr('data-dateday');
        if ($(this).parent().hasClass('selectedday')) {
            $(this).parent().removeClass('selectedday');
            $('[data-dateslect="' + dayselect + '"]').remove(); //suprime le <li> class eventday
            tabunique[dayselect].delteconge()

        } else {
            $(this).parent().addClass('selectedday');
            tabunique[dayselect].reinitopenday(dayselect)
            addTimeOpen(dayselect);
        }
        afficalendar(calendar.calendar)
    });

    //modification de l'heure individuellement  // todo fonction desactive avec les imput time
    timeday.on('change','.hitime', function(e){
        e.stopPropagation();
        let frangin=$(this).siblings(), valchg=$(this).val(),
            dateselect=$(this).parents('li').attr('data-dateslect'),
            order=$(this).parents('.defaultday').attr('data-order');
        if($(this).is('.start')){
            hydrate=hydrattabdayunique(valchg, frangin.val());
            $(this).val(hydrate.tlet[0].ht+':'+hydrate.tlet[0].it);
            frangin.val(hydrate.tlet[1].ht+':'+hydrate.tlet[1].it)
        }else{
            hydrate=hydrattabdayunique( frangin.val(), valchg,);
            frangin.val(hydrate.tlet[0].ht+':'+hydrate.tlet[0].it);
            $(this).val(hydrate.tlet[1].ht+':'+hydrate.tlet[1].it)
        }
        tabunique[dateselect].tab[order].startminutes=hydrate.tsatrt;
        tabunique[dateselect].tab[order].alongminute=hydrate.talong
    });

    //modification de toutes les heures (à la meme heure)
    $('#submitclokAll').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        /*if(confirm('cette fonction reinitilaise toutes les dates sélectionnées')) {  */// todo mettre une alerte plus belle "cette fonction reinitilaise toutes les dates sélectionnées"
        let hydratall = hydrattabdayunique($('#appt-time-deb').val(), $('#appt-time-end').val());
        for(let day in tabunique){
            if(tabunique.hasOwnProperty(day)) {
                let thisday = tabunique[day]
                if (thisday.selected) {
                    thisday.tab.forEach(function (tim, indice, tab) {
                        tab[indice].startminutes = hydratall.tsatrt;
                        tab[indice].alongminute = hydratall.talong;
                    })
                }
            }
        }
        optionhstart=hydratall.tsatrt;
        optionhend=hydratall.talong;
        reinitinputTime(hydratall.tlet);
    });

    function toogleonglet(onglet) {
        let tab = onglet.data("tab");
        $(".tab").removeClass("tab-active");
        $("#" + tab).addClass("tab-active");
        $("#tab-nav a").removeClass("tab-nav-active");
        $(onglet).addClass("tab-nav-active");
    }

    function initpage(now){
        if(calendar.id===1){
            delete calendar.calendar;
            calendar.calendar=new MonthCalendar(now.month, now.year)
        }else{
            calendar.id=1;
            calendar.calendar=new MonthCalendar(now.month, now.year);
            for(let day in calendar.calendar.daytime){
                tabunique[calendar.calendar.daytime[day]]=new TabWeekcg(calendar.calendar.daytime[day])
            }

            let tabparse = JSON.parse(idopenday.attr('data-twigtbunique'));
            if(!!tabparse) {
                    for(let d in tabunique){
                        if(tabunique.hasOwnProperty(d)) {
                            tabunique[d].day = tabparse[d].day;
                            tabunique[d].tab = tabparse[d].tab;
                            tabunique[d].execpt = tabparse[d].execpt;
                            tabunique[d].selected = tabparse[d].selected;
                        }
                }
            idopenday.attr('data-twigtbunique',"")
            }

            let conges=JSON.parse(idopenday.attr('data-twigconges'));
            if(!!conges){
                idopenday.attr('data-twigconges',"");
            }
         }

        afficalendar(calendar.calendar)
    }

    function afficalendar(calendar) {
        let datenow=new Date(new Date().getFullYear(),new Date().getMonth(),new Date().getDate(),0,0,0);
        let html="<div class='direct-month'>";
        html+= "<button class='bt_affi _wl-5 bt_affi-primary' id='previoustmonth'><i class='fa fa-chevron-circle-left' aria-hidden='true'></i></button>";
        html+= "<div class=\"col-date\"><h1>"+calendar.month_letter+" "+ calendar.year+"</h1></div>";
        html+= "<button class='bt_affi _wl-5 bt_affi-primary' id='nextmonth'><i class='fa fa-chevron-circle-right' aria-hidden='true'></i></button>";
        html+= "</div>";
        html+= "<table class=\"calendar__table calendar__table--"+calendar.week+"weeks\" >";
        html+= "<thead>";

        for (var day in formatdate.daysmob) {
            html+= "<td class=\"tab-calendar-thead calendar__weekday\">" + formatdate.daysmob[day] + "</td>"
        }
        html+= "</thead>";
        for (let incre1 = 0; incre1 < calendar.week; incre1++) {
            html+="<tr>";
            for (let incre2 = 0; incre2 <= 6; incre2++) {
                dayOn=undefined;
                let datestart = new Date(calendar.day.getFullYear(),calendar.day.getMonth(),calendar.day.getDate(),0,0,0);

                let dateok=new Date(datestart.setUTCMilliseconds((incre2 + incre1 * 7)*24*60*60*1000));

                let beforday=datenow.getTime() > dateok.getTime();
                let istoday = datenow.getTime() === dateok.getTime();
                let affidate=dateok.getFullYear()+","+dateok.getMonth()+","+dateok.getDate();
                let day=formatdate.daytime[dateok.getDay()];
                let openday=tabunique[day].selected;
                if(tabunique[day].execpt.length>0) dayOn=tabunique[day].execpt.find(ele => ele === affidate);
                for(let dtcg in conges){
                    selected=dtcg === affidate;
                    if(selected)break;
                }
                html+="<td class=\""+(istoday ? ' is__today':'')+" "+(selected  ? ' dayselect calendar__othermonth':'')+" "+(openday  ? 'dayopen':'dayoff calendar__othermonth')+" "+(dayOn  ? 'dayon':'')+"\">"
                html+= "<div class=\"calendar__day"+(calendar.withinMonth(dateok) || beforday ? ' calendar__othermonth' : '')+"\" data-date=\""+affidate+"\">"+dateok.getDate()+"</div>";
                html+= "</td>"
            }
            html+= "</tr>"
        }
        html+= "</table>";
        calgrid.html(html);
        recapopendays();
    }

    function recapopendays(){
        let closedmonth="",closedday="",dayweekopen="",dateexceptopen="",hrecap="";
        for(let item in conges) {
            if (conges.hasOwnProperty(item)) {
                if (conges[item].type === 1) closedday += "" + conges[item].dateft + " ";
                if (conges[item].type === 2) closedmonth += "" + item + " ";
            }
        }
        for(let d in tabunique){
            if(tabunique.hasOwnProperty(d)){
                if(tabunique[d].selected) {
                    dayweekopen += "" + d + " ";
                }
                if (tabunique[d].execpt.length > 0) {
                        tabunique[d].execpt.forEach(function (value, index) {
                            dateexceptopen += "" + formatdate.newdatehuman(value) + "" + (index === tabunique[d].execpt.length - 1 ? "." : ",") + ""
                        })
                }
            }
        }

        hrecap+="<div class=\"recpday\">jours ouverts :"+(dayweekopen !==""?dayweekopen: "aucun")+"</div>";
        hrecap+="<div class=\"recpday\">et le(s) :"+(dateexceptopen !==""?dateexceptopen: "")+"</div>";
        hrecap+="<div class=\"recpday\">Fermeture mensuel :"+(closedmonth !==""?closedmonth: "")+"</div>";
        hrecap+="<div class=\"recpday\">jours fermés (pendant période ouverture) : "+(closedday !==""?closedday: "")+"</div>";
        $('#recapopendays').html(hrecap)
    }

    function buttonadd(dayselect){
        if(agent==="mobile/") {
            return '<button type="button" class="btn btn-primary addtime_mob" data-addtime="' + dayselect + '"><i class="fa fa-plus-square"></i><span>ajouter</span></button>'
        }else{
            return '<button type="button" class="btn btn-primary addtime" data-addtime="' + dayselect + '"><i class="fa fa-plus-square"></i><span>ajouter</span></button>'
        }
    }

    function buttondelete(dayselect){
        return'<button type="button" class="btn btn-primary deletetime" data-deletetime="'+dayselect+'"><i class="fa fa-trash"></i></button>'
    }

    function addTimeOpen(dayselect) {  //append <li> + lance la fonction(adddivtime) de creation des imput times et ajout au tab de tabunique
        tabunique[dayselect].tab.push({
            startminutes: optionhstart,
            alongminute: optionhend
        });
        initdivtime()
    }

    function deleteTimeOpen(infodelete) {
        tabunique[infodelete].tab.pop()
        $('[data-addtime="'+infodelete+'"]').show()
        initdivtime()
    }

    function initdivtime(){
        if(agent){
            timeday.html("");
            for(let day in tabunique) {
                if (tabunique.hasOwnProperty(day)) {
                    let h = "";
                    if (tabunique[day].selected) {
                        //let liday=$('.tab-selectdate_timeday_'+day+'')

                        let sel = $('[data-dateday="' + day + '"]');
                        sel.addClass('activeday');
                        sel.parent().addClass('selectedday');
                        h = "<div class=\"eventday_mob " + day.toLowerCase() + "\" data-dateslect=\"" + day + "\">";
                        console.log(tabunique[day])
                        tabunique[day].tab.forEach(function (time, order) {

                            h += "<div class=\"defaultday\" data-order=\"" + order + "\">";
                                h += "<div class=\"ttim\">";
                                let timelet = calhour(
                                    [tabunique[day]['tab'][order].startminutes,
                                        tabunique[day]['tab'][order].alongminute + tabunique[day]['tab'][order].startminutes]); // valeur arbitraire 8h00 -18h00  ou idem au options du formulaire
                                h += "<input type=\"time\" class=\"hitime start\" name=\"hitime\" value=\"" + timelet[0].ht + ":" + timelet[0].it + "\">";
                                h += "<input type=\"time\" class=\"hitime end\" name=\"hitime\" value=\"" + timelet[1].ht + ":" + timelet[1].it + "\">";
                                h += "</div>";
                            h += "</div>";

                        });
                        let sizetab = tabunique[day]['tab'].length;
                      //  if (sizetab > 1) $('[data-addtime="' + day + '"]').hide();
                        h += "" + (sizetab <= 1 ? buttonadd(day) : buttondelete(day)) + "</div>";
                        timeday.append(h);
                    }
                }
            }
        }else{
            timeday.html("");
            for(let day in tabunique) {
                if (tabunique.hasOwnProperty(day)) {
                    let h = "";
                    if (tabunique[day].selected) {
                        let sel = $('[data-dateday="' + day + '"]');
                        sel.addClass('activeday');
                        sel.parent().addClass('selectedday');
                        h = "<li class=\"eventday " + day.toLowerCase() + "\" data-dateslect=\"" + day + "\">";
                        tabunique[day].tab.forEach(function (time, order) {
                            h += "<div class=\"defaultday\" data-order=\"" + order + "\">";
                            h += "<div class=\"ttim\">";
                            let timelet = calhour(
                                [tabunique[day]['tab'][order].startminutes,
                                    tabunique[day]['tab'][order].alongminute + tabunique[day]['tab'][order].startminutes]); // valeur arbitraire 8h00 -18h00  ou idem au options du formulaire
                            h += "<input type=\"time\" class=\"hitime start\" name=\"hitime\" value=\"" + timelet[0].ht + ":" + timelet[0].it + "\">";
                            h += "<input type=\"time\" class=\"hitime end\" name=\"hitime\" value=\"" + timelet[1].ht + ":" + timelet[1].it + "\">";
                            h += "</div>";
                            h += "</div>";
                        });
                        let sizetab = tabunique[day]['tab'].length;
                        if (sizetab > 1) $('[data-addtime="' + day + '"]').hide();
                        h += "" + (sizetab <= 1 ? buttonadd(day) : buttondelete(day)) + "</li>";
                        timeday.append(h);
                    }
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

    function reinitinputTime(tlet){
        $('.start').val(tlet[0].ht+':'+tlet[0].it);
        $('.end').val(tlet[1].ht+':'+tlet[1].it)
    }

    function hydrattabdayunique(t1,t2) {
        let h1,i1,h2,i2,t1bis,t2bis;
        t1bis= t1.split(':');
        t2bis=t2.split(':');
        h1 = parseInt(t1bis[0]);
        i1 = parseInt(t1bis[1]);
        h2 = parseInt(t2bis[0]);
        i2 = parseInt(t2bis[1]);
        let timesart = (h1 * 60) + i1;
        let timealong = ((h2 * 60) + i2) - timesart;
        return {
            tsatrt: timesart,
            talong: timealong,
            tlet: calhour([timesart, timesart + timealong])
        };
    }

    function linearajax(){
        let fd = new FormData();
        fd.append('slug',slug);
        fd.append('tabunique', JSON.stringify(tabunique));
        fd.append('conges', Object.keys(conges).length===0 ? "": JSON.stringify(conges));
        fd.append('spaceweb', "");
        fd.append('order', "1");
        fd.append('notify', "");
        return fd
    }


    async function goajax($data) {
            console.log('click', $data)
        const data =$data();
        console.log(data)
            try {
                $('.progress').show();
                const response = await jsonFetch(routeopenday, {method:'POST', body: data})
                if(response.success) window.location.replace(redirect);
            } catch (e) {
                if (e instanceof ApiError) {
                   console.log(s => ({...s, errors: e.violations}))
                } else if (e.detail) {
                    flash(e.detail, 'danger', null)
                } else {
                    flash(e, 'danger', null)
                    throw e
                }
            }
        }
    } //fin du if de controle si c'est la bonne page chargé (en fonciton du data openday)
});