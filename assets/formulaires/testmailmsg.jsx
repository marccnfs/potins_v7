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

    function handletest(response){


        const formStatusProps = {
            success: {
                status: 'nofind',
                message: 'Signed up successfully.',
                type: 'nomail',
            },
            user: {
                status: 'user',
                message: "L'adresse email "+response.email+" est déjà enregistrée. Si cette adresse vous appartient",
                type: 'email',
            },
            error: {
                status: 'false',
                message: 'Something went wrong. Please try again.',
                type: 'error',
            },
            contact: {
                status: 'contact',
                message: "L'adresse email "+response.email+" est déjà enregistrée. Si cette adresse vous appartient",
                type: 'email',
            },
        }

        if(response.ok) {
            localStorage.setItem('email',response.email );

            if (response.success === "user") {
                setFormStatus(formStatusProps.user)
                localStorage.setItem('status',"user" );
            } else {
                if (response.success === "contact"){
                    setFormStatus(formStatusProps.contact)
                    localStorage.setItem('status',"contact" );
                    console.log(response.contact)
                }else{
                    setFormStatus(formStatusProps.success)
                    localStorage.setItem('status',"nomail" );
                    follow();
                }
            }
            setDisplayFormStatus(true)
        }else{
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
                            ) : formStatus.type === 'nomail' ? (
                                <p className=" successMessage">
                                    {formStatus.message}
                                </p>
                            ) : formStatus.type === 'email' ? (
                                <div>
                                    <p className=" successMessage">
                                        {formStatus.message}
                                    </p>
                                    <a href="/security/oderder/identif/login">connectez-vous.</a>
                                </div>
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
                ) : formStatus.type === 'success' ? (
                    <p className=" successMessage">
                        {formStatus.message}
                    </p>
                ) : null}
            </div>
        )}
        </div>
    }
}


/*
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
 */