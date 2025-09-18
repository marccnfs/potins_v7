import { h } from "preact";
import {FetchFormControl, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'
import { Alert } from '../components/Alert.jsx'
let machina=$('#machina')
let identify=machina.data('identify')
let choice=machina.data('choice');
let dataemail;
if(choice==="media"){
    dataemail=$('#mediatheque_email')
}else{
    dataemail=$('#user_email')
}

let stape=$('.stape')


if(localStorage.getItem('identify') === identify && localStorage.getItem('email')){
        $(stape[0]).hide()
        $(stape[1]).show()
   // localStorage.clear();
}else{
    localStorage.setItem('identify', identify)
}

export function Testmailform () {

    const [displayFormStatus, setDisplayFormStatus] = useState(false)
    const [formStatus, setFormStatus] = useState({
        message: '',
        type: '',
    })

    function handletest(response, submittedData = {}){

        const submittedEmail = submittedData.email ?? submittedData.mail ?? ''
        const successMessage = response.message ?? 'Votre demande a été enregistrée.'

        if(response.ok) {
            if (submittedEmail) {
                localStorage.setItem('email', submittedEmail )
                dataemail.val(submittedEmail)
            }
            setFormStatus({
                message: successMessage,
                type: 'success',
            })
            $(stape[0]).hide()
            $(stape[1]).show()
            setDisplayFormStatus(true)
        }else{
            setFormStatus({
                message: response.message ?? 'Something went wrong. Please try again.',
                type: 'error',
            })
            setDisplayFormStatus(true)
            console.error(response)
        }
    }

    return (
        <FetchFormControl action='/tools/jxrq/testContactMail' onSuccess={handletest} className='form-identify'>
            <FormField name='email' type='email' placeholder="votre email..." required/>
            <div className='full-bt'>
                <FormPrimaryButton>suivant</FormPrimaryButton>
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

