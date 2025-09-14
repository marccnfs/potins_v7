import {FetchFormEntity, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState, useEffect } from 'preact/hooks'
import { Alert } from '../components/Alert.jsx'
import { h } from "preact";
import {SearchPartner} from "./search/SearchPartner";


export default function Formpartner(props){
    const [success, setSuccess] = useState(false)
    const [partner, setPartner] = useState("")
    const [website, setWebsite]=useState(props.board)
    const [selectPartner, setSelectPartner] = useState(false)
    if (success) {
        return <Alert>Votre action de sponsoring a bien été enregistrée</Alert>
    }
    function handleChangePartner(select){
        setPartner(select.id)
        setSelectPartner(!selectPartner)
    }
    function onSuccess(response){
        setSuccess(response.success)
    }
    return (
        <FetchFormEntity action='/partner/board/apiajax/add-partner-ajx' onSuccess={onSuccess}  board={props.board} class='form-partner'>
                <div>
                    <SearchPartner onChangePartner={handleChangePartner} defaultValue={partner}/>
                </div>
            {selectPartner
                ?
                    <div className='full'>
                        <FormPrimaryButton>soumettre votre sponsoring</FormPrimaryButton>
                    </div>
                : <div/>
            }
            <input name="partner" type='hidden' value={partner}/>
            <input name="board" type='hidden' value={props.board}/>
        </FetchFormEntity>
    )
}