import {FetchFormEvent, FormField, FormPrimaryButton, FormSecondaryButton} from '../components/Form.jsx'
import {useState, useEffect, useCallback} from 'preact/hooks'
import {Alert} from '../components/Alert.jsx'
import { h } from "preact";
import {FileUploader} from "../functions/FileUpLoader";
import CalendarEvent from "../functions/calendar";
import ToggleChoice from "../components/ToggleChoice";
import {Searchadress} from "./search/searchadress";
import {SearchPartnerEvent} from "./search/SearchPartnerEvent";

let init=true;


function updateTextareaHeight(input) {
    if (input !== null) {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    }
}

updateTextareaHeight(document.getElementById('contentOne'));


export default function Formevent(props){


    // old version : let redirect="/board/Events/"+props.city+"/"+props.board
    // old version :let redirect="/board/Events/"+props.board
    let redirect='/programmation-potins'

    let options={"pict":"","partner":[],"titre":"","description":"","adresse":""}
    if(props.event){
        options.id=props.event.id;
        options.partner=props.partners;
        options.sector=props.event.sector;
        options.adresse=props.event.sector.adresse[0].label ??"";
        options.appointments=props.event.appointment;
        options.titre=props.event.titre ??"";
        options.description=props.event.description ??"";
        options.pict=props.event.media.imagejpg.length > 0 ? props.event.media.imagejpg[0].namefile :"";
        options.board=props.board;
        }

    const [success, setSuccess] = useState(false)
    const [titre, setTitre] = useState(options.titre)
    const [description, setDescription] = useState(options.description)
    const [partner, setPartner] = useState(false)
    const [imgpath, setImgpath]=useState(options.pict)
    const [img, setImg]=useState(false)
    const [changImg, setChangImg]=useState(false)
    const [changDate, setChangDate]=useState(false)
    const [changPartner, setChangPartner]=useState(false)
    const [changAdress, setChangAdress]=useState(false)
    const [tabunique, setTabunique]=useState({'Dim':[],'Lun':[],'Mar':[],'Mer':[],'Jeu':[],'Ven':[],'Sam':[]})
    const [dateselect, setDateselect]=useState(false)
    const [choiceTgl, setChoiceTgl]=useState(false)
    const [adresse, setAdresse] = useState(options.adresse)
    const [propadresse, setPropadresse] = useState(options.adresse)
    const [edit, setEdit]=useState(false)
    const [tabpartners, setTabpartners]=useState(options.partner)


    if(props.event && init){
        setEdit(true)
        setTabunique(options.appointments.tabdate['tabdatejso'])
        setDateselect(true)
        if(tabpartners.length > 0){
            setPartner(true)
        }
        init=!init;
    }

    if (success) {
        return <Alert>Votre evenement à bien été crée/modifié</Alert>
    }

    const handleTabChange = function(tabtemp){
        setTabunique(tabtemp)
        setDateselect(true)
        setChangDate(true)
    }

    function handleChangePartner(select){
        setTabpartners(select)
        setPartner(true)
        setChangPartner(true)
    }

    function handleChangeAdress(select){
        setPropadresse(select)
        setAdresse(select.label)
        setChangAdress(true)
    }

    function handleToggleChoice(state){
        setChoiceTgl(state)
    }

    function handleChange(e){
        if(e.target.name==='titre'){
            setTitre(e.target.value)
        }else{
            setDescription(e.target.value)
        }
    }
    const handledeselect=(index,e) =>{
        e.preventDefault()
        setChangPartner(true)
        tabpartners.splice(index,1)
        if(tabpartners.length === 0){
            setPartner(false)
        }
        setTabpartners(tabpartners)
        console.log(tabpartners)
    }

    function onSuccess(response){
        setSuccess(response.success)
        AsyncRetrun()

    }

    function handleloadpagebefore(e){
        e.preventDefault()
        window.location.replace(redirect);
    }

    function onImgChange(image){
        setImg(image)
        setChangImg(true)
    }

    async function AsyncRetrun(){
        await delay(1);
        window.location.replace(redirect);
    }

    function delay(n){
        return new Promise(function(resolve){
            setTimeout(resolve,n*1000);
        });
    }

    return (
        <FetchFormEvent action='/member/wb/event/add-event-ajx' className="form-elastic" onSuccess={onSuccess}
                        edit={edit}
                        board={props.board}
                        changeImg={changImg}
                        changeAdress={changAdress}
                        changePartner={changPartner}
                        changeTabDate={changDate}
                        tabdate={tabunique}
                        adress={propadresse}
                        tabpartners={tabpartners}
                        event={props.event.id}
                        img={img}>


                    <FormField name='titre' value={titre} onChange={handleChange} required autofocus>
                            Quoi ?
                    </FormField>

                    <div className="form-pictandcontent">
                        <FileUploader
                            onFileSelectError={({ error }) => alert(error)}
                            onFileSelectSuccess={false}
                            onImgChange={onImgChange}
                            imgpath={imgpath}
                            img={img}
                        />
                    </div>

                    <FormField name='description' type='event' value={description}  onChange={handleChange} required wrapperClass=''>
                    </FormField>

                    <div className="form-group">
                        <h3>quand ?</h3>
                        <CalendarEvent parse={tabunique} edit={edit} onTabchange={handleTabChange}/>
                    </div>

                    <div className="form-group">
                        <h3>où ?</h3>
                        <Searchadress onChangeAdress={handleChangeAdress} defaultValue={adresse}/>
                    </div>

                    <div className="form-group">
                        <h3>avec :</h3>
                        <ToggleChoice etat={choiceTgl} onTogglechoice={handleToggleChoice}>associer un partner</ToggleChoice>
                        <div className="bk-fix-zoneform">
                            {choiceTgl  &&
                                <SearchPartnerEvent tabpartner={tabpartners} onChangePartner={handleChangePartner}  defaultValue=""/>
                            }
                        </div>

                        {(partner) &&
                        <div>
                            <ul className='search-input_suggestions'>
                                {tabpartners.map((r, index) => (
                                    <li key={r.id} className="af_flx li-flex">
                                        <img className='imgsearch' src={r.pict}/>
                                        <span dangerouslySetInnerHTML={{__html: r.title}}/>
                                        <button className="btn btn-danger" onClick={(e) => handledeselect(index, e)}>
                                            <i className="fa fa-trash"/>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        }
                    </div>

                    <div className='full'>
                       {dateselect &&
                        <FormPrimaryButton>Envoyer</FormPrimaryButton>
                       }
                       <FormSecondaryButton onClick={handleloadpagebefore}>annuler</FormSecondaryButton>
                    </div>


        </FetchFormEvent>
    )
}

/*

 function handleCreated(website){
        setPartner(website.id)
        setWebsite(website.namewebsite)
        setCreatingWebsite(false)
    }

    function handleCreate(e){
        e.preventDefault()
        setCreatingWebsite(!creatingWebsite)
    }


    <input name="partner" type='hidden' value={partner}/>
  //  let tabparse = JSON.parse(idopenday.attr('data-twigtbunique'));
  //  idopenday.attr('data-twigtbunique',"")
    // let r= $.initpageAff({month:(new Date()).getMonth(),year:new Date().getFullYear()},tab,parse)

function handleChangeDate(e){
        if(e.target.name==='startevent'){
            setDatestart(e.target.value)
        }else{
            setDateend(e.target.value)
        }
    }

       function inittabparse(tabparse){
        let tabtemp=
        let select=false;
        $.inittabparse()

        for(let d in tabparse){
            if(tabparse.hasOwnProperty(d)){
                if (tabparse[d].execpt.length > 0) {
                    tabparse[d].execpt.forEach(function (dd, index) {
                        tabtemp[tabparse[d].day].push(dd)
                    })
                    select=true
                }
            }
        }
        console.log(tabtemp)
        setTabunique(tabtemp)

    }


<WebsiteForm handleCreated={handleCreated} handleCreate={handleCreate}/>

 */

/*
    const lipartner = function(r) {
        if (edit && !changPartner) {
            return <li key={r.id} className="af_flx li-flex">
                <img className='imgsearch' src={'spaceweb/template' + r.template.logo.namefile}/>
                <span dangerouslySetInnerHTML={{__html: r.namewebsite}}/>
                <button className="btn btn-danger" onClick={(e) => handledeselect(r, e)}>
                    <i className="fa fa-trash"/>
                </button>
            </li>
        } else if(edit && changPartner) {
            return  <li key={r.id} className="af_flx li-flex">
                <img className='imgsearch' src={r.pict}/>
                <span dangerouslySetInnerHTML={{__html: r.title}}/>
                <button className="btn btn-danger" onClick={(e) => handledeselect(r, e)}>
                    <i className="fa fa-trash"/>
                </button>
            </li>
        } else{
            return  <li key={r.id} className="af_flx li-flex">
                <img className='imgsearch' src={r.pict}/>
                <span dangerouslySetInnerHTML={{__html: r.title}}/>
                <button className="btn btn-danger" onClick={(e) => handledeselect(r, e)}>
                    <i className="fa fa-trash"/>
                </button>
            </li>
        }
    }


 */
