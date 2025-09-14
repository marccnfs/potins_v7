import { h } from "preact";
import {FetchFormControl, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'
import { Alert } from '../components/Alert.jsx'
let url=""



export function Addcommentwp ({msgid,slug,id,module}) {

    let options={
        website:{
            route:'/messagery/board/new-message-board',
            addroute:'/messagery/board/add-convers',
            redirect:'/messagery/board/msg-read/'+slug+'/'+msgid
        },
        blog:{
            route:'/messagery/publication/new-message-publication',
            addroute:'/messagery/publication/add-convers'
        },
    }

console.log(options.website.redirect)

    const [displayFormStatus, setDisplayFormStatus] = useState(false)
    const [formStatus, setFormStatus] = useState({
        message: '',
        type: '',
    })

    if(module==="_blog" || module==="_shop" ) url=options.blog.route
    if(module==="add_blog" || module==="add_shop" ) url=options.blog.addroute
    if(module==="add_board" ) url=options.website.addroute
    if(module==="_board" ) url=options.website.route

    function handlemsg(response){

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
            if(module==="add_board" ) window.location.replace(options.website.redirect);

        /*else{
                document.location.href=options.blog.redirect;
            }

         */

        }else{
            setFormStatus(msgStatusProps.error)
            setDisplayFormStatus(true)
            console.error(response)
        }
    }

    return (
        <FetchFormControl action={url} onSuccess={handlemsg} className='area_free'>
            <FormField name='comment' type='msgmob' placeholder='votre message' required>
            </FormField>
            <input type='hidden' name="slug" value={slug}/>
            <input type='hidden' name="msgid" value={msgid}/>
            <input type='hidden' name="id" value={id}/>
            <input type='hidden' name="module" value={module}/>
            <div className='full'>
                <FormPrimaryButton>Envoyer</FormPrimaryButton>
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