import { h } from "preact";
import {useRef, useState} from 'preact/hooks'
import {ApiError, jsonFetch} from "../functions/api";
import {flash} from "../elements/Alert";
const TEST_WEBSITE ='/api/jxrq/testwebsite';

export function Testentityform ({onChangeWebsite}) {

   // const inputwb = useRef(null)

    const [displayFormStatus, setDisplayFormStatus] = useState(false)
    const [formStatus, setFormStatus] = useState({
        message: '',
        type: '',
    })

/*
    function onClickCity(e){
        e.preventDefault()
        testCity(inputwb.current.value).then(handleTest)
    }
*/
    const testCity = async r => {
        try {
            return await jsonFetch(TEST_WEBSITE, { method: 'POST', body:[r]  })

        } catch (e) {
            if (e instanceof ApiError) {
                console.log( e.violations)
            } else if (e.detail) {
                flash(e.detail, 'danger', null)
            } else {
                flash(e, 'danger', null)
                throw e
            }
        }
    }
    function novalidate(){
        onChangeWebsite(false, false)
    }

    function onChangeInput(e){
        testCity(e.target.value).then((r) =>{
            e.target.value=r.name
            handleTest(r)
        })
    }

    function handleTest(response){

        const formStatusProps = {
                message: "Ce nom est déjà utilisé. Merci d'essayer une autre nom.",
                type: 'error',
        }

        if (!response.success ) {
            setDisplayFormStatus(false)
            onChangeWebsite(response, true)

        }else{
            setFormStatus(formStatusProps)
            setDisplayFormStatus(true)
            onChangeWebsite(false, false)
        }

    }

    return (
        <div className="search_order_tag active2">
            <div className="ipt_osp">
                <div className="-stp0-osp">
                    <div className="-stp01">
                        <div className='_in-stp01'/>
                        <input id="namewebsite" onChange={onChangeInput} onInput={novalidate}
                               type="search" placeholder="nom du panneau..."
                               className='_in-stp02_desk _in-stp02' />
                    </div>
                </div>
            </div>
            {displayFormStatus && (
                <div className="formStatus">
                    <p className="errorMessage">
                        {formStatus.message}
                    </p>
                </div>
            )}
        </div>
    )
}


/* <div className='full-bt'>
                <button onClick={onClickCity} className='btn-send-log'>soumettre</button>
            </div>

 */