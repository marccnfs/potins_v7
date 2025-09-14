import {FetchFormOffre, FormField, FormPrimaryButton, FormSecondaryButton} from '../components/Form.jsx'
import {useState, useEffect, useCallback} from 'preact/hooks'
import {Alert} from '../components/Alert.jsx'
import { h } from "preact";
import CalendarEvent from "../functions/calendar";
import {FileUploader} from "../functions/FileUpLoader";


let init=true;


function updateTextareaHeight(input) {
    if (input !== null) {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    }
}

updateTextareaHeight(document.getElementById('contentOne'));


export default function FormOffrepotin(props){

    // old : let redirect="/board/Events/"+props.board
    let redirect='/programmation-potins'

    let options={"pict":"","titre":"","description":""}
    if(props.offre){
        options.id=props.offre.id;
        //options.partner=props.event['locatemedia']??[];
        //options.sector=props.event.sector;
        //options.adresse=props.event.sector.adresse[0].label ??"";
        options.appointments=props.offre.appointment;
        options.titre=props.offre.titre ??"";
        options.description=props.offre.description ??"";
        options.board=props.board;
        options.pict=props.offre.media.imagejpg.length > 0 ? props.offre.media.imagejpg[0].namefile :"";
        //options.potin=props.potin;
        }

    const [success, setSuccess] = useState(false)
    //const [namepartner, setNamePartner] = useState("")
    //const [changPartner, setChangPartner]=useState(false)
    //const [partner, setPartner] = useState(false)
    //const [tabpartners, setTabpartners]=useState(options.partner)
    const [titre, setTitre] = useState(options.titre)
    const [description, setDescription] = useState(options.description)
    const [img, setImg]=useState(false)
    const [imgpath, setImgpath]=useState(options.pict)
    const [changImg, setChangImg]=useState(false)
    const [changDate, setChangDate]=useState(false)
    const [tabunique, setTabunique]=useState({'Dim':[],'Lun':[],'Mar':[],'Mer':[],'Jeu':[],'Ven':[],'Sam':[]})
    const [dateselect, setDateselect]=useState(false)
    const [edit, setEdit]=useState(false)
    const [stateform, setStateform]=useState(false)
    const [statedescription, setStatedescription]=useState(false)
    const [stateTitre, setStateTitre]=useState(false)

    if(props.offre && init){
        setEdit(true)
        setTabunique(options.appointments.tabdate['tabdatejso'])
        setDateselect(true)
      //  if(tabpartners){
        //    setPartner(true)
       // }
        init=!init;
    }

    if (success) {
        return <Alert>Votre offre à bien été crée/modifié</Alert>
    }

    function handleChange(e){
       setDescription(e.target.value)
       setStatedescription(true)
       valueForm()
    }

    function handleChangeTitre(e){
        setTitre(e.target.value)
        setStateTitre(true)
        valueForm()
    }

    function onImgChange(image){
        setImg(image)
        setChangImg(true)
    }

    const handleTabChange = function(tabtemp){
        setTabunique(tabtemp)
        setDateselect(true)
        setChangDate(true)
        valueForm()
    }
    /*
    function handleChangePartner(select){
        setTabpartners(select)
        setNamePartner(select.id)
        setChangPartner(true)
        valueForm()
    }
     */
    function valueForm(){
        if(statedescription &&  stateTitre && dateselect)setStateform(true)
    }

    function onSuccess(response){
        setSuccess(response.success)
        AsyncReturn()
    }
    function handleloadpagebefore(e){
        e.preventDefault()
        window.location.replace(redirect);
    }
    async function AsyncReturn(){
        await delay(1);
        window.location.replace(redirect);
    }
    function delay(n){
        return new Promise(function(resolve){
            setTimeout(resolve,n*1000);
        });
    }

    return (
        <FetchFormOffre action='/member/marketplace/shop/add-offre-ajx' className="form-elastic" onSuccess={onSuccess}
                        edit={edit}
                        board={props.board}
                        //potin={props.potin}
                        changeTabDate={changDate}
                        changeImg={changImg}
                        img={img}
                        //changePartner={{changPartner}}
                        tabdate={tabunique}
                        //tabpartners={tabpartners}
                        offre={props.offre.id}>

                <FormField name='titre' type='offre' value={titre}  onChange={handleChangeTitre} required wrapperClass=''>
                </FormField>

                <FormField name='description' type='offre' value={description}  onChange={handleChange} required wrapperClass=''>
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

                <div className="form-group">
                    <h3>quand ?</h3>
                    <CalendarEvent parse={tabunique} edit={edit} onTabchange={handleTabChange}/>
                </div>

                <div className='full'>
                   {stateform &&
                    <FormPrimaryButton>Envoyer</FormPrimaryButton>
                   }
                   <FormSecondaryButton onClick={handleloadpagebefore}>annuler</FormSecondaryButton>
                </div>

        </FetchFormOffre>
    )
}


/*
  <div className="form-group">
                    /*<h3>avec :</h3>
                        <div className="bk-fix-zoneform">
                             <SearchPartnerEvent tabpartner={tabpartners} onChangePartner={handleChangePartner}  defaultValue={namepartner}/>
                        </div>
                    </div>
 */