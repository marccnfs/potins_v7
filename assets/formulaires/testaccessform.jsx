import { h } from "preact";
import {FetchFormControl, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'
import { Alert } from '../components/Alert.jsx'
let datacode=$('#code') ,
    popaccess = $('#codeaccess'),
    post=popaccess.data('post');

let redirect = '/potin-history/'+post;

export function Testaccesform () {

    const [displayFormStatus, setDisplayFormStatus] = useState(false)
    const [formStatus, setFormStatus] = useState({
        message: '',
        type: '',
    })

    function handletest(response){

        console.log(response)

        const formStatusProps = {
            success: {
                message: 'accès autorisé',
                type: 'success',
            },
            error: {
                message: "Code d'accès invalide. Recommencez ou contactez votre cnfs.",
                type: 'error',
            },
        }

        if(response.ok) {
                if (response.success === "access"){
                    setFormStatus(formStatusProps.success)
                    window.location.replace(redirect);
                }else{
                    setFormStatus(formStatusProps.error)
                    setDisplayFormStatus(false)
                    datacode.val(response.email)
                }
            setDisplayFormStatus(true)
        }else{
            console.error(response)
        }
    }

    return (
        <FetchFormControl action='/tools/jxrq/testAccess' onSuccess={handletest} className='form-identify'>
            <FormField name='code' type='text' placeholder="votre code d'accès" required/>
            <div className='full-bt'>
                <FormPrimaryButton>valider</FormPrimaryButton>
            </div>
            {displayFormStatus && (
                <div className="formStatus">
                    {formStatus.type === 'error' ? (
                        <p className="errorMessage">
                            {formStatus.message}
                        </p>
                    ) : formStatus.type === 'success' ? (
                        <p className=" successMessage">
                            {formStatus.message}
                        </p>
                    ) : null}
                </div>
            )}
        </FetchFormControl>
    )
}