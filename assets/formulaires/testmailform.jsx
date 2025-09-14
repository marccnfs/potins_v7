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

    function handletest(response){

       // console.log(response)

        const formStatusProps = {
            success: {
                message: 'Signed up successfully.',
                type: 'success',
            },
            duplicate: {
                message: "Cette adresse mail existe déjà. Merci d'essayer une autre adresse.",
                type: 'error',
            },
            error: {
                message: 'Something went wrong. Please try again.',
                type: 'error',
            },
        }

        if(response.ok) {
            if (response.success === "user") {
                setFormStatus(formStatusProps.duplicate)
            } else {
                if (response.success === "contact"){
                    setFormStatus(formStatusProps.contact)
                    localStorage.setItem('email',response.contact );
                    console.log(response.contact)
                }

                else{
                    setFormStatus(formStatusProps.success)
                    localStorage.setItem('email',response.email );
                    dataemail.val(response.email)
                    $(stape[0]).hide()
                    $(stape[1]).show()

                   // window.location.replace("/security/admin/new-Identify/"+response.email);
                }
            }
            setDisplayFormStatus(true)
        }else{
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

/*
<a className='full-btbtn-send-log' href='/login'>j'ai déjà un compte</a>

 */