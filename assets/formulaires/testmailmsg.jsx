import {Component, h} from "preact";
import {FetchFormControl, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'

let route='/messagery/board/new-message-board';

export function Testmailmsg ({slug,id,module}) {
    if(module==="_blog" || module==="_shop" ) route='/messagery/publication/new-message-publication'
    const [displayFormStatus, setDisplayFormStatus] = useState(false)
    const [send, setSend] = useState(false)
    const [formStatus, setFormStatus] = useState({
        message: '',
        type: '',
    })

    const [emailStatus, setEmailStatus] = useState(true)

    function follow(){
        setEmailStatus(false);
    }

    function handletest(response, submittedData = {}){

        const submittedEmail = submittedData.email ?? submittedData.mail ?? ''
        const successMessage = response.message ?? "Si cette adresse est enregistrée, nous reviendrons vers vous prochainement."

        if(response.ok) {

            if (submittedEmail){
                localStorage.setItem('email',submittedEmail )
            }
            localStorage.setItem('status','unknown' )
            setFormStatus({
                status: 'processed',
                message: successMessage,
                type: 'info',
            })
            follow()
            setDisplayFormStatus(true)
        }else{
            setFormStatus({
                status: 'error',
                message: response.message ?? 'Something went wrong. Please try again.',
                type: 'error',
            })
            setDisplayFormStatus(true)
            console.error(response)
        }
    }

    function handlemsg(response){

        console.log(response)

        const msgStatusProps = {
            success: {
                message: 'votre message à bien été envoyé',
                type: 'success',
            },
            error: {
                message: 'Something went wrong. Please try again.',
                type: 'error',
            },
        }

        if(response.success) {
            localStorage.clear()
            setFormStatus(msgStatusProps.success)
            setDisplayFormStatus(true)
            setSend(true)
        }else{
            setFormStatus(msgStatusProps.error)
            setDisplayFormStatus(true)
            console.error(response)
        }
    }

    if(!send) {

        if (emailStatus) {
            return (

                <FetchFormControl action='/tools/jxrq/testContactMail' className="form-contact" onSuccess={handletest}>
                    <div className="ti-sst"><i className="fa fa-envelope-o"></i></div>
                    <input style="display:none" type="email" name="fakepasswordremembered"/>
                    <FormField name='email' type='email' placeholder="votre email...." autocomplete="off" required/>
                    <div className='full'>
                        <FormPrimaryButton>Envoyer</FormPrimaryButton>
                    </div>
                    {displayFormStatus && (
                        <div className="formStatus">
                            {formStatus.type === 'error' ? (
                                <p className="errorMessage">
                                    {formStatus.message}
                                </p>
                            ) : formStatus.type === 'info' ? (
                                <p className=" successMessage">
                                    {formStatus.message}
                                </p>
                            ) : null}
                        </div>
                    )}
                </FetchFormControl>
            )
        } else {
            return (
                <FetchFormControl action={route} onSuccess={handlemsg} className='cnfs-form'>
                    <FormField name='comment' type='textarea' required>
                        votre message
                    </FormField>
                    <input type='hidden' name="slug" value={slug}/>
                    <input type='hidden' name="email" value={localStorage.getItem('email') ?? ""}/>
                    <input type='hidden' name="status" value={localStorage.getItem('status') ?? ""}/>
                    <input type='hidden' name="id" value={id ?? ""}/>
                    <input type='hidden' name="module" value={module ?? ""}/>
                    <div className='full'>
                        <FormPrimaryButton>Envoyer</FormPrimaryButton>
                    </div>

                </FetchFormControl>
            )
        }
    }else{
        return  <div>
            {displayFormStatus && (
            <div className="formStatus">
                {formStatus.type === 'error' ? (
                    <p className="errorMessage">
                        {formStatus.message}
                    </p>
                ) : formStatus.type === 'success' || formStatus.type === 'info' ? (
                    <p className=" successMessage">
                        {formStatus.message}
                    </p>
                ) : null}
            </div>
        )}
        </div>
    }
}
