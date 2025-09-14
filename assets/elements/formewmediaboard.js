import {FetchFormEvent, FetchFormMedia, FormField, FormPrimaryButton, FormSecondaryButton} from '../components/Form.jsx'
import {useState, useEffect, useCallback} from 'preact/hooks'
import {Alert} from '../components/Alert.jsx'
import { h } from "preact";
import {SearchCity} from "./search/SearchCity";
import {Searchadress} from "./search/searchadress";


let init=true;


function updateTextareaHeight(input) {
    if (input !== null) {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    }
}

updateTextareaHeight(document.getElementById('contentOne'));


export default function Formewmediaboard(props){

    //let redirect="/board/sucess/wp/show/"+props.customer
    let redirect="/security/mediatheque/registration-mediatheque-end"

    let options={"titre":"","adress":""}
    if(props.customer){
        options.customer=props.customer;
        // options.sector=props.event.sector;
        options.adress="";
        options.titre="";
        }

    const [success, setSuccess] = useState(false)
    const [titre, setTitre] = useState(options.titre)
    const [adress, setAdress] = useState(options.adress)
    const [propadress, setPropadress] = useState(options.adress)



    if (success) {
        return <Alert>Votre étalissement à bien été crée/modifié</Alert>
    }
    function handleChange(e){
       setTitre(e.target.value)
    }

    function handleChangeAdress(select){
            setPropadress(select)
            setAdress(select.label)
    }

    function onSuccess(response){
        setSuccess(response.success)
        AsyncRetrun()
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
        <FetchFormMedia action='/mediatheque/registration/add-new-mediatheque-ajx' className="add-locate" onSuccess={onSuccess}
                        adress={propadress}
                        customer={props.customer}>


                    <FormField name='titre' value={titre} onChange={handleChange} required autofocus>
                        Entrez le nom de votre établissement :
                    </FormField>

                    <div className="form-group">
                        <h5>Entrez votre adresse :</h5>
                        <Searchadress onChangeAdress={handleChangeAdress} defaultValue=""/>
                    </div>

                    <div className='full'>
                       {propadress &&
                        <FormPrimaryButton>Envoyer</FormPrimaryButton>
                       }
                    </div>

        </FetchFormMedia>
    )
}
