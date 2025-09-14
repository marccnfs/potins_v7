import {FetchData, FormPrimaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'
import {Searchadress} from "./search/searchadress";
import { h } from "preact";

let route

export default function FormAdress(props){

    const [success, setSuccess] = useState(false)
    const [adresse, setAdresse] = useState("")
    const [propadresse, setPropadresse] = useState("")
    const [module, setModule]=useState(props.module)
    const [typesearch, setTypesearch]=useState(0)

    if(props.module==="_dispatch"){
        route='/customer/profil/adress/newadress';
    }else{
        route='/geolocate/op/newadress';
    }

    if (success) {
        return <Alert>adress ajoutée</Alert>
    }

    function handleChangeAdress(select){
        if(select.length > 3){
            console.log(select)
           if(isNaN( select.substr(0,1))) setTypesearch(0)
            setPropadresse(select)
            setAdresse(select.label)
        }
    }

    function onSuccess(response){
        setSuccess(response.success)
    }

    return (
        <div className="add-locate">
            <FetchData action={route} onSuccess={onSuccess} data={propadresse} id={props.id}>
                    <Searchadress onChangeAdress={handleChangeAdress} defaultValue="" typesearch={typesearch}/>
                    <div className='full'>
                        <FormPrimaryButton>soumettre</FormPrimaryButton>
                    </div>
            </FetchData>
        </div>
    )
}
