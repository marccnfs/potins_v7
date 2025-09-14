import {h} from "preact";
import {useState, useRef, useCallback} from 'preact/hooks'


export default function ToggleChoice(props) {


    const handleChoice = function(e){
       e.preventDefault()
       props.onTogglechoice(!props.etat)
    }

    return (
        <div className="bk-fix-zoneform">
            <button className='btn-send-log' onClick={handleChoice}>{ props.children }</button>
        </div>
    )
}
