import {FetchFormEntity, FormField, FormPrimaryButton, FormSecondaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'
import { Alert } from '../components/Alert.jsx'
import { h } from "preact";
import {Button} from "../components/Button";
import {SearchCity} from "../elements/search/SearchCity";
import {SearchInput} from "../elements/search/SearchElement";


export function WebsiteForm (props) {

    const [success, setSuccess] = useState(false)
    const [code, setCode]=useState("")

    function handleChangeCity(select){
        setCode(select.code)
    }

    return (
        <div class="add-partner">
            <FetchFormEntity action='/partners/wb/event/suggest-website-ajx' onSuccess={(r)=>props.handleCreated(r)}  className='list-incub'>
            <div className="pos-incub-l">
                <FormField name='name' required type='text'>
                    nom du lieux/partners
                </FormField>
                <FormField name='email' required type='email'>
                    email du contact pour soumision
                </FormField>
                <FormField name='url' type='url'>
                    url site web
                </FormField>

                <div class="adresse">localisation :</div>
                <SearchCity onChangeCity={handleChangeCity}  defaultValue=""/>
                <input name="code" type='hidden' value={code}/>
                <div className='full'>
                    <FormPrimaryButton>soumettre</FormPrimaryButton>
                    <FormSecondaryButton onClick={props.handleCreate}>annuler</FormSecondaryButton>
                </div>
            </div>
        </FetchFormEntity>
        </div>
    )
}

