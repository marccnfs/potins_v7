import {useCallback, useEffect, useRef, useState} from "preact/hooks";
import {useJsonFetchOrFlash, useToggle} from "../../functions/hooks";
import {Icon} from "../../components/Icon";
import {debounce} from "../../functions/timers";
import {Loader} from "../../components/Loader";
import {classNames} from "../../functions/dom";
import { h, render} from "preact";


/*
   curl 'https://api-adresse.data.gouv.fr/search/?q=20%20avenue%20de%20S%C3%A9gur%2C%20Paris&type=housenumber&autocomplete=1' adresse avec priorité au numero
   curl 'https://api-adresse.data.gouv.fr/search/?q=20%20avenue%20de%20S%C3%A9gur%2C%20Paris&type=street&autocomplete=1'    adresse avec priorité au nom de rue
   curl 'https://api-adresse.data.gouv.fr/search/?q=20%20avenue%20de%20S%C3%A9gur%2C%20Paris&type=municipality&autocomplete=1' selection de la commune
   curl 'https://api-adresse.data.gouv.fr/search/?q=Saint-Just-sur-Viaur&type=locality&autocomplete=1'  // lieu dit
*/

const SEARCH_API =(location.protocol==='http:'?'http:':'https:')+'//api-adresse.data.gouv.fr/search/';
const SEARCH_NUMBER='&type=housenumber&autocomplete=1';
const SEARCH_STREET='&type=street&autocomplete=1';
const SEARCH_CITY='&type=municipality&autocomplete=1';
const SEARCH_LIEUDIT='&type=locality&autocomplete=1';
let search='';
let callback={};

export function Searchadress ({ defaultValue, onChangeAdress }) {
    const input = useRef(null)
    const [query, setQuery] = useState(defaultValue || '')
    const { loading, fetch, data } = useJsonFetchOrFlash()
    const [selectedItem, setSelectedItem] = useState(null)
    const [selecteInput, setSelecteInput] = useState(false)
    const [typesearch, setTypesearch]=useState(0)

    let results =[];

    if(selecteInput){
        if(data){
            results = data.features
        }else{
            results =[]
        }
    }

    if (query === '') {
        results = []
    }

    function isNumeric(val) {
        return /^-?\d+$/.test(val);
    }

    const hits = data || 0

    if (query !== '' && results.length !== 0) {
      let  results = [
            ...results,
            {
               feature: ``,
            }
        ]
    }

    const suggest = useCallback(
        debounce(async e => {
            await fetch(`${SEARCH_API}?q=${encodeURI(e.target.value)}${search}`)
            setSelectedItem(null)
        }, 300),
        []
    )

    const onInput = e => {
        e.preventDefault()

        if(e.target.value.length > 3) {
         /*   console.log(e.target.value.substr(0, 1));
            console.log(isNumeric(e.target.value.substr(0, 1)));
            console.log("function Number",Number(e.target.value.substr(0, 1)))
            console.log("function parseint",parseInt(e.target.value.substr(0, 1)))
            console.log("function +",+e.target.value.substr(0, 1))
            console.log("function isNaN",isNaN(e.target.value.substr(0, 1)))*/

            if (isNaN(e.target.value.substr(0, 1))){
                setTypesearch(1)
                search=SEARCH_STREET;
            }else{
                setTypesearch(0)
                search=SEARCH_NUMBER;
            }
            setSelecteInput(true)
            setQuery(e.target.value)
            suggest(e)
        }
    }

    const handleselect=(r,e) =>{
        e.preventDefault()
        setSelecteInput(false)
        setQuery(r.properties.label)
        onChangeAdress(r)
    }

    // Déplace le curseur dans la liste
    const moveFocus = useCallback(
        direction => {
            if (results.length === 0) {
                return
            }
            setSelectedItem(i => {
                const newPosition = i + direction
                if (i === null && direction === 1) {
                    return 0
                }
                if (i === null && direction === -1) {
                    return results.length - 1
                }
                if (newPosition < 0 || newPosition >= results.length) {
                    return null
                }
                return newPosition
            })
        },
        [results]
    )

    const onSubmit = e => {
        if (selectedItem !== null && results[selectedItem]) {
            e.preventDefault()
            //window.location.href = results[selectedItem].url
        }
    }

    useEffect(() => {
        const handler = e => {
            switch (e.key) {
                case 'ArrowDown':
                case 'Tab':
                    e.preventDefault()
                    moveFocus(1)
                    return
                case 'ArrowUp':
                    moveFocus(-1)
                    break
                default:
            }
        }
        window.addEventListener('keydown', handler)
        return () => window.removeEventListener('keydown', handler)
    }, [moveFocus])


return (
        <div class='search-input_locate'>
            <input
                type='text'
                name='adresse'
                ref={input}
                onInput={onInput}
                autocomplete='off'
                value={query}
                placeholder="entrez/ajoutez une adresse"
            />

            {loading && <Loader class='search-input_loader' />}
            {results.length > 0 && (
                <ul class='search-input_suggestions'>
                    {results.map((r, index) => (
                        <li key={r.properties.id}>
                            <div class={classNames(index === selectedItem && 'focused')} onClick={(e)=>handleselect(r,e)}>
                                <span dangerouslySetInnerHTML={{ __html: r.properties.label }} />
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    )
}



/*
  useEffect(() => {
        input.current.focus()
    }, [])
 */