import {h} from "preact";
import {useState, useRef, useCallback} from 'preact/hooks'


export default function ChoiceDate(props) {



    const handleNow = function(e){
       e.preventDefault()
       props.onChoicenow(true)
    }

    const handleother =function(e){
        e.preventDefault()
        props.onChoicenow(false)
    }

    return (
        <div className="list-incub">
            <div className="pos-incub-i">
                <div className="pos-incub-l">
                    <div className="mod-incub">
                        <button className='btn-send-log' onClick={handleNow}> J 'y suis </button>
                        <button className='btn-send-log' onClick={handleother}> J 'y serais </button>
                    </div>
                </div>
            </div>
        </div>
    )
}
