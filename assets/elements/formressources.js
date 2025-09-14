import { useState, useEffect } from 'preact/hooks'
import { h } from "preact";
import {SearchRessources} from "./search/SearchRessources";



export default function FormRessources(props){

    const [cat, setCat] = useState("")
    const [nameressource, setNameRessource]=useState("")

    function handleChangeRessources(select){
        setNameRessource(select)
    }

    return (
           <SearchRessources onChangeRessource={handleChangeRessources} defaultValue={nameressource} cat={props.cat} />
    )
}

