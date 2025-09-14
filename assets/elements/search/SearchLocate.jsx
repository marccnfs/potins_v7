import {useCallback, useEffect, useRef, useState} from "preact/hooks";
import {useJsonFetchOrFlash} from "../../functions/hooks";
import {debounce} from "../../functions/timers";
import {Loader} from "../../components/Loader";
import { h } from "preact";

const SEARCH_API =(location.protocol==='http:'?'http:':'https:')+'//geo.api.gouv.fr/communes';

export function SearchLocate (props) {
    const input = useRef(null)
    const [query, setQuery] = useState(props.city || '')
    const { loading, fetch, data } = useJsonFetchOrFlash()
    const [selectedItem, setSelectedItem] = useState(null)
    const [listitem, setListitem] = useState(false)
    const [selecteInput, setSelecteInput] = useState(false)


    const l_class=props.lclass

    window.addEventListener('click', event => {
        setListitem(false)
        setQuery(props.city || '')
    });


    let results =[];
    if(selecteInput){
        results = data || []
    }

    if (query === '') {
        results = []
    }

    const hits = data || 0

    if (query !== '' && results.length === 0) {
        results = [
            ...results,
            {
                code: ``,
                nom: ``
            }
        ]
    }

    const suggest = useCallback(
        debounce(async e => {
            await fetch(`${SEARCH_API}?nom=${encodeURI(e.target.value)}&fields=code,nom,centre,codesPostaux`)
            setSelectedItem(null)
        }, 300),
        []
    )

    const onInput = e => {
        setSelecteInput(true)
        setQuery(e.target.value)
        setListitem(true)
        if(e.target.value.length>3) suggest(e)
    }

    const handleselect=(r,e) =>{
        e.preventDefault()
        setSelecteInput(false)
        setListitem(false)
        setQuery(r.nom)
        okgolocate(r)
    }

    function okgolocate(localitate){
        if (localitate) {
            if(l_class ==="cargo") {
                window.location.href = "/potins/?lon=" + localitate.centre.coordinates[0] + "&&lat=" + localitate.centre.coordinates[1] + "&&nom=" + localitate.nom + "&&code=" + localitate.codesPostaux[0]
            }
            if(l_class ==="customer") {
                window.location.href = "/customer/profil/change-potins-customer/?lon=" + localitate.centre.coordinates[0] + "&&lat=" + localitate.centre.coordinates[1] + "&&nom=" + localitate.nom + "&&code=" + localitate.codesPostaux[0]
            }
            if(l_class ==="init") {
                window.location.href = "/customer/initlocalizer/init-potins-customer/?lon=" + localitate.centre.coordinates[0] + "&&lat=" + localitate.centre.coordinates[1] //+ "&&nom=" + localitate.nom + "&&code=" + localitate.codesPostaux[0]
            }
        }
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
        <div className="space-tag-notop" >
            <div className="search_order_tag active2">
                <div className="ipt_osp">
                    <div className="-stp0-osp">
                        <div className="_i-cL">
                            <div className="hsuHs">
                                 <span className="wFncld z1asCe MZy1Rb">
                                      <i className="fa fa-map-marker" aria-hidden="true"/>
                                 </span>
                            </div>
                        </div>

                        <div className="-stp01">
                            <div className='_in-stp01'/>
                                <input
                                    className='_in-stp02_desk _in-stp02'
                                    type="search" data-lclass="partner" aria-autocomplete="both"
                                    aria-haspopup="false" autoCapitalize="off" autoComplete="off"
                                    role="combobox" spellCheck="false" title="Rechercher" aria-label="Rech."
                                    autofocus
                                    name='ville'
                                    ref={input}
                                    onInput={onInput}
                                    value={query}
                                    placeholder="recherche par ville..."
                                />
                        </div>
                    </div>
                </div>

                {loading && <Loader class='search-input_loader' />}

                {listitem && (
                <div className="_iner-Lst0">
                    <div className="_iner-Lst0-in1"/>
                        <div className="_Lst0">
                            {results.length > 0 && (
                            <ul className='_Ul-l01'>
                                {results.map((r, index) => (
                                    <li key={r.code} className="_Il-l01 resulcp">
                                        <div className="FFI-sugest">
                                            <div className="_ic-02">
                                                <i className="fa fa-search" aria-hidden="true"/>
                                            </div>
                                            <div className="_Op-1" role="option">
                                                <div className="_in_Op-1" onClick={(e)=>handleselect(r,e)}>
                                                    <span dangerouslySetInnerHTML={{ __html: r.nom }} />
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                            )}
                        </div>
                    </div>
                    )}
            </div>
        </div>
    )
}


