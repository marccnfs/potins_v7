import {FetchFormEvent, FormField, FormPrimaryButton, FormSecondaryButton} from '../components/Form.jsx'
import {useState, useEffect, useCallback} from 'preact/hooks'
import {Alert} from '../components/Alert.jsx'
import { h } from "preact";
import CalendarEvent from "../functions/calendar";
import {SearchPartnerEvent} from "./search/SearchPartnerEvent";

let init=true;


function updateTextareaHeight(input) {
    if (input !== null) {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    }
}

updateTextareaHeight(document.getElementById('contentOne'));


export default function Formeventpotin(props){

    // old : let redirect="/board/Events/"+props.board
    let redirect='/board-office/programmation-potins'

    let options={"pict":"","partner":[],"potin":"","titre":"","description":"","adresse":""}
    if(props.event){
        options.id=props.event.id;
        options.partner=props.event['locatemedia']??[];
        //options.sector=props.event.sector;
        //options.adresse=props.event.sector.adresse[0].label ??"";
        options.appointments=props.event.appointment;
        //options.titre=props.event.titre ??"";
        options.description=props.event.description ??"";
        options.mediatheque=props.event.mediatheque ??"";
        options.board=props.board;
        options.potin=props.potin;
        }

    const [success, setSuccess] = useState(false)
    const [namepartner, setNamePartner] = useState("")
    const [changPartner, setChangPartner]=useState(false)
    const [partner, setPartner] = useState(false)
    const [tabpartners, setTabpartners]=useState(options.partner)
    const [description, setDescription] = useState(options.description)
    const [changDate, setChangDate]=useState(false)
    const [tabunique, setTabunique]=useState({'Dim':[],'Lun':[],'Mar':[],'Mer':[],'Jeu':[],'Ven':[],'Sam':[]})
    const [dateselect, setDateselect]=useState(false)
    const [edit, setEdit]=useState(false)
    const [stateform, setStateform]=useState(false)
    const [statedescription, setStatedescription]=useState(false)
    const [mediatheque, setMediatheque] = useState(options.mediatheque)
    const [statemediatheque, setStatemediatheque]=useState(false)


    if(props.event && init){
        setEdit(true)
        setTabunique(options.appointments.tabdate['tabdatejso'])
        setDateselect(true)
        if(tabpartners){
            setPartner(true)
        }
        init=!init;
    }

    if (success) {
        return <Alert>Votre evenement à bien été crée/modifié</Alert>
    }

    function handleChange(e){
       setDescription(e.target.value)
       setStatedescription(true)
       valueForm()
    }

    function handleChangeMedia(e){
        setMediatheque(e.target.value)
        setStatemediatheque(true)
        valueForm()
    }

    const handleTabChange = function(tabtemp){
        setTabunique(tabtemp)
        setDateselect(true)
        setChangDate(true)
        valueForm()
    }
    function handleChangePartner(select){
        setTabpartners(select)
        setNamePartner(select.id)
        setChangPartner(true)
        valueForm()
    }

    function valueForm(){
        if(statedescription && changPartner && dateselect)setStateform(true)
    }

    function onSuccess(response){
        setSuccess(response.success)
        AsyncRetrun()
    }
    function handleloadpagebefore(e){
        e.preventDefault()
        window.location.replace(redirect);
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
        <FetchFormEvent action='/potins/event/add-event-potin-ajx' className="form-elastic" onSuccess={onSuccess}
                        edit={edit}
                        board={props.board}
                        potin={props.potin}
                        changeTabDate={changDate}
                        changePartner={{changPartner}}
                        tabdate={tabunique}
                        tabpartners={tabpartners}
                        event={props.event.id}>

                    <FormField name='description' type='event' value={description}  onChange={handleChange} required wrapperClass=''>
                    </FormField>

                    <div className="form-group">
                        <h3>quand ?</h3>
                        <CalendarEvent parse={tabunique} edit={edit} onTabchange={handleTabChange}/>
                    </div>

                    <div className="form-group">
                    <h3>avec :</h3>
                        <div className="bk-fix-zoneform">
                           <SearchPartnerEvent tabpartner={tabpartners} onChangePartner={handleChangePartner}  defaultValue={namepartner}/>
                        </div>
                    </div>

                    <div className='full'>
                       {stateform &&
                        <FormPrimaryButton>Envoyer</FormPrimaryButton>
                       }
                       <FormSecondaryButton onClick={handleloadpagebefore}>annuler</FormSecondaryButton>
                    </div>

        </FetchFormEvent>
    )
}
