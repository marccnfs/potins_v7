import {FetchFormEntity, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState, useEffect } from 'preact/hooks'
import { Alert } from '../components/Alert.jsx'
import { h } from "preact";
import {SearchInput, SearchOne} from "./search/SearchElement";
import { WebsiteForm } from "../formulaires/WebsiteForm"



export default function OldFormwebsite(props){

    const [success, setSuccess] = useState(false)
    const [partner, setPartner] = useState("")
    const [creatingWebsite, setCreatingWebsite]=useState(false)
    const [website, setWebsite]=useState(props.board)

    if (success) {
        return <Alert>Votre evenement à bien été crée</Alert>
    }

    function handleChangePartner(select){
        setPartner(select.id)
    }

    function handleCreated(website){
        setPartner(website.id)
        setWebsite(website.namewebsite)
        setCreatingWebsite(false)
    }

    function handleCreate(e){
        e.preventDefault()
        setCreatingWebsite(!creatingWebsite)
    }

    function onSuccess(response){
        setSuccess(response.success)
    }

    return (
        <FetchFormEntity action='/partner/board/apiajax/add-partner-ajx' onSuccess={onSuccess}  board={props.board} class='form-partner'>
            {!creatingWebsite
                ?<div>
                    <SearchInput onChangePartner={handleChangePartner} handleCreate={handleCreate} defaultValue={partner}/>
                    <button className='btn-send-log' onClick={handleCreate}>Ajoutez un lieu</button>
                </div>
                :<WebsiteForm handleCreated={handleCreated} handleCreate={handleCreate}/>
            }
            <input name="partner" type='hidden' value={partner}/>
            <input name="board" type='hidden' value={props.board}/>
        </FetchFormEntity>
    )
}


/*
 <FormPrimaryButton>Ajouter</FormPrimaryButton>
 */