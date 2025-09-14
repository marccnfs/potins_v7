import { useState, useEffect } from 'preact/hooks'
import { h } from "preact";
import {SearchWebsite} from "./search/SearchWebsite";



export default function Formwebsite(props){

    const [namewebsite, setNamewebsite] = useState("")
    const [website, setWebsite]=useState("")


    function handleChangeWebsite(select){
        setWebsite(select)
    }

    return (
           <SearchWebsite onChangeWebsite={handleChangeWebsite} defaultValue={namewebsite} city={props.city} />
    )
}

