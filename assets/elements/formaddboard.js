import {useState, useEffect, useCallback} from 'preact/hooks'
import { h } from "preact";
import {Testentityform} from "../formulaires/testentityform";
import {SearchCity} from "./search/SearchCity";
import {Alert} from "../components/Alert";

let fieldform=$('#bonosucess')
let valid={
    'city':false,
    'entity':false
}

export default function FormAdBoard(props){

    const [city, setCity] = useState("")
    const [gps, setGps]=useState("")
    const [namewebsite, setNamewebsite] = useState("")


    function handleValid(){
        if(!valid.city || !valid.entity) fieldform.hide()
        if(valid.city && valid.entity) fieldform.show()
    }

    function handleChangeWebsite(entity, state){
        valid.entity=state
        if(entity) {
            setNamewebsite(entity.name)
            if (props.author === "dispath") {
                $('#new_spw_namewebsite').val(entity.name)
            } else {
                $('#form_namewebsite').val(entity.name)
            }
        }
        handleValid()
    }

    function handleChangeCity(citymap,state){
        valid.city=state
        if(citymap){
            setGps(citymap.result)
            if (props.author === "dispath") {
                $('#new_spw_idcity').val(citymap.result)
            } else {
                console.log(valid)
                $('#form_idcity').val(citymap.result)
            }
        }
        handleValid()
    }

    return (
        <div className="w100">
            <div className="pos-incub-l">
                <p>Choisissez la localité pour ce nouveau panneau :</p>
                <SearchCity onChangeCity={handleChangeCity} defaultValue={city}/>
            </div>
            <div className="pos-incub-l">
                <p>nom du nouveau panneau :</p>
                <Testentityform onChangeWebsite={handleChangeWebsite} defaultValue=""/>
            </div>
        </div>
         )
}

