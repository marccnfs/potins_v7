import {useCallback, useEffect, useRef, useState} from "preact/hooks";
import {useJsonFetchOrFlash, useToggle} from "../../functions/hooks";
import {Icon} from "../../components/Icon";
import {debounce} from "../../functions/timers";
import {Loader} from "../../components/Loader";
import {classNames} from "../../functions/dom";
import { h, render} from "preact";

const SEARCH_URL = '/recherche'
const SEARCH_API_BY_CITY = '/ajx/method/find/search'
const SEARCH_API = '/ajx/method/find/searchnocity'

export function SearchPartnerEvent ({ defaultValue, onChangePartner, tabpartner }) {
    const input = useRef( null)
    const [query, setQuery] = useState(defaultValue??"")
    const {loading, fetch, data } = useJsonFetchOrFlash()
    const [selectedItem, setSelectedItem] = useState(null)
    const [selecteInput, setSelecteInput] = useState(false)
    const [tabselectpartners, setTabselectpartners]=useState(tabpartner)

    let results =[];

    if(selecteInput){
       results = data?.items || []
    }
    if (query === '') {
        results = []
    }

    const hits = data?.hits || 0

    if (query !== '' && results.length === 0) {
        results = [
            ...results,
            {
                title: ``,
                url: ``,
                pict:''
            }
        ]

    }

    const suggest = useCallback(
        debounce(async e => {
            await fetch(`${SEARCH_API}?q=${encodeURI(e.target.value)}`)
            setSelectedItem(null)
        }, 500),
        []
    )

    const onInput = e => {
        setSelecteInput(true)
        setQuery(e.target.value)
        suggest(e)
    }
    const handleselect=(r,e) =>{
        e.preventDefault()
        console.log(r,tabpartner)
        setSelecteInput(false)
        tabpartner.push(r)
        setTabselectpartners(tabpartner)
        setQuery(r.title)
        onChangePartner(tabpartner)
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

    useEffect(() => {
        input.current.focus()
    }, [])



    return (

        <div className='search-input_board'>
            <input
                autofocus
                type='search'
                name='q'
                ref={input}
                onInput={onInput}
                autocomplete='off'
                value={query}
                placeholder="nom de l'établissement..."
            />

            {loading && <Loader class='search-input_loader' />}

            {(results.length > 0  && selecteInput) &&
                <ul className='search-input_suggestions'>
                    {results.map((r, index) => (
                        <li key={r.url} >
                            <div class={classNames(index === selectedItem && 'focused')}  onClick={(e)=>handleselect(r,e)}>
                                <img class='imgsearch' src={r.pict}/>
                                <span dangerouslySetInnerHTML={{ __html: r.title }} />
                            </div>
                        </li>
                    ))}
                </ul>
            }
        </div>


    )
}



/*
   {r.category && <span class='search-input_category'>{r.category}</span>}
 */