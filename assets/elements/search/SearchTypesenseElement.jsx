import {useCallback, useEffect, useRef, useState} from "preact/hooks";
import {useJsonFetchOrFlash,useTypeSenseFetchOrFlash, useToggle} from "../../functions/hooks";
import {Icon} from "../../components/Icon";
import {debounce} from "../../functions/timers";
import {Loader} from "../../components/Loader";
import {classNames} from "../../functions/dom";
import {typesenseFetch} from "../../functions/api";
import { h, render} from "preact";

const SEARCH_IN='/partners/website/search'


export function SearchOneJs() {
    const [isSearchVisible, toggleSearchBar] = useToggle(false)

    // Racourci clavier pour ouvrir la boite de recherche
    useEffect(() => {
        const handler = e => {
            if (['k', ' '].includes(e.key) && e.ctrlKey === true) {
                e.preventDefault()
                toggleSearchBar()
            }
        }
        window.addEventListener('keydown', handler)
        return () => window.removeEventListener('keydown', handler)
    }, [toggleSearchBar]);

    return (
        <div>
            <button onClick={toggleSearchBar} aria-label='Rechercher'>
                <Icon name='search' />
            </button>
            {isSearchVisible && <SearchBar onClose={toggleSearchBar} />}
        </div>
    );
}


export function SearchInput ({ defaultValue }) {
    const input = useRef(null)
    const [query, setQuery] = useState(defaultValue || '')
    const { loading, tpsfetch, data } = useTypeSenseFetchOrFlash()
    const [selectedItem, setSelectedItem] = useState(null)

    let results = data?.items || []
    if (query === '') {
        results = []
    }

    const hits = data?.hits || 0

    if (query !== '' && results.length > 0) {
        results = [
            ...results,
            {
                title: `Voir les <strong>${hits}</strong> résultats`,
                url: `${SEARCH_IN}?q=${encodeURI(query)}&redirect=0`
            }
        ]
    }

    const suggest = useCallback(
        debounce(async e => {
            await tpsfetch(encodeURI(e.target.value))
            setSelectedItem(null)
        }, 300),
        []
    )

    const onInput = e => {
        setQuery(e.target.value)
        suggest(e)
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
            console.log(results)
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

    useEffect(() => {
        input.current.focus()
    }, [])

    return (
        <form action={SEARCH_IN} onSubmit={onSubmit} class='search-input form-group' onClick={e => e.stopPropagation()}>
            <input
                autofocus
                type='text'
                name='q'
                ref={input}
                onInput={onInput}
                autocomplete='off'
                value={query}
                placeholder='Rechercher un contenu...'
            />
            <button type='submit'>
                <Icon name='search' />
            </button>
            {loading && <Loader class='search-input_loader' />}
            {results.length > 0 && (
                <ul class='search-input_suggestions'>
                    {results.map((r, index) => (
                        <li key={r.url}>
                            <a class={classNames(index === selectedItem && 'focused')} href={r.url}>
                                {r.category && <span class='search-input_category'>{r.category}</span>}
                                <span dangerouslySetInnerHTML={{ __html: r.title }} />
                            </a>
                        </li>
                    ))}
                </ul>
            )}
        </form>
    )
}

function SearchBar ({ onClose }) {
    useEffect(() => {
        const handler = e => {
            if (e.key === 'Escape') {
                onClose()
            }
        }
        window.addEventListener('keyup', handler)
        return () => window.removeEventListener('keyup', handler)
    }, [onClose])

    return (
        <div class='search-popup' onClick={onClose}>
            <SearchInput />
        </div>
    )
}


