
let item=document.getElementById("item")
console.log(item)
let data=item.dataset.microdata

initmicrodataboard(data)

function initmicrodataboard(microdata) {
    console.log(microdata)
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = microdata;
    document.head.appendChild(script);
}

function initmicrodatapost(post) {

    let microdata = {
        "@context": "https://schema.org",
        "@type": ["ItemList", "Event"],
        "name": "les rendez-vous de mademoiselle gingembre",
        "auteur": "mademoiselle gingembre",
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "numberOfItems": tabevents.length,
        "itemListElement": []
    }
    let comp=1
    for (const event in tabevents) {
        if (tabevents.hasOwnProperty(event)) {
            console.log(tabevents[event]);

            let item = {
                "@type": "ListItem",
                "position": comp,
                "item": {
                    "@type": "FoodEvent",
                    "name": tabevents[event].slug,
                    "description": tabevents[event].subject,
                    "event": {
                        "@type": "Event",
                        "about":tabevents[event].id,
                        "name": tabevents[event].titre,
                        "startDate": tabevents[event].createAt,
                        "endDate": "2017-01-22"
                    },
                    "image": tabevents[event].media.imagejpg[0].apiPath,
                    "location": {
                        "@type": "Place",
                        "address": {
                            "@type": "PostalAddress",
                            "addressLocality": "Nantes",
                            "addressRegion": "loire atlantique",
                            "postalCode": "44000",
                            "streetAddress": "3, rue des piquoise"
                        },
                        "name": "The Hi-Dive",
                        "url": "wells-fargo-center.html"
                    },

                    "startDate": tabevents[event].createAt,
                }


            }
            microdata.itemListElement.push(item)
            comp++
        }

    }
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = JSON.stringify(microdata);
    document.head.appendChild(script);

}

function initmicrodataoffre(offre) {

    let microdata = {
        "@context": "https://schema.org",
        "@type": ["ItemList", "Event"],
        "name": "les rendez-vous de mademoiselle gingembre",
        "auteur": "mademoiselle gingembre",
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "numberOfItems": tabevents.length,
        "itemListElement": []
    }
    let comp=1
    for (const event in tabevents) {
        if (tabevents.hasOwnProperty(event)) {
            console.log(tabevents[event]);

            let item = {
                "@type": "ListItem",
                "position": comp,
                "item": {
                    "@type": "FoodEvent",
                    "name": tabevents[event].slug,
                    "description": tabevents[event].subject,
                    "event": {
                        "@type": "Event",
                        "about":tabevents[event].id,
                        "name": tabevents[event].titre,
                        "startDate": tabevents[event].createAt,
                        "endDate": "2017-01-22"
                    },
                    "image": tabevents[event].media.imagejpg[0].apiPath,
                    "location": {
                        "@type": "Place",
                        "address": {
                            "@type": "PostalAddress",
                            "addressLocality": "Nantes",
                            "addressRegion": "loire atlantique",
                            "postalCode": "44000",
                            "streetAddress": "3, rue des piquoise"
                        },
                        "name": "The Hi-Dive",
                        "url": "wells-fargo-center.html"
                    },

                    "startDate": tabevents[event].createAt,
                }


            }
            microdata.itemListElement.push(item)
            comp++
        }

    }
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = JSON.stringify(microdata);
    document.head.appendChild(script);

}

function initmicrodataevent(events) {

    let microdata = {
        "@context": "https://schema.org",
        "@type": ["ItemList", "Event"],
        "name": "les rendez-vous de mademoiselle gingembre",
        "auteur": "mademoiselle gingembre",
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "numberOfItems": tabevents.length,
        "itemListElement": []
    }
    let comp=1
    for (const event in tabevents) {
        if (tabevents.hasOwnProperty(event)) {
            console.log(tabevents[event]);

            let item = {
                "@type": "ListItem",
                "position": comp,
                "item": {
                    "@type": "FoodEvent",
                    "name": tabevents[event].slug,
                    "description": tabevents[event].subject,
                    "event": {
                        "@type": "Event",
                        "about":tabevents[event].id,
                        "name": tabevents[event].titre,
                        "startDate": tabevents[event].createAt,
                        "endDate": "2017-01-22"
                    },
                    "image": tabevents[event].media.imagejpg[0].apiPath,
                    "location": {
                        "@type": "Place",
                        "address": {
                            "@type": "PostalAddress",
                            "addressLocality": "Nantes",
                            "addressRegion": "loire atlantique",
                            "postalCode": "44000",
                            "streetAddress": "3, rue des piquoise"
                        },
                        "name": "The Hi-Dive",
                        "url": "wells-fargo-center.html"
                    },

                    "startDate": tabevents[event].createAt,
                }


            }
            microdata.itemListElement.push(item)
            comp++
        }

    }
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = JSON.stringify(microdata);
    document.head.appendChild(script);

}

function initmicrodatamenu(menu) {

    let microdata = {
        "@context": "https://schema.org",
        "@type": ["ItemList", "Event"],
        "name": "les rendez-vous de mademoiselle gingembre",
        "auteur": "mademoiselle gingembre",
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "numberOfItems": tabevents.length,
        "itemListElement": []
    }
    let comp=1
    for (const event in tabevents) {
        if (tabevents.hasOwnProperty(event)) {
            console.log(tabevents[event]);

            let item = {
                "@type": "ListItem",
                "position": comp,
                "item": {
                    "@type": "FoodEvent",
                    "name": tabevents[event].slug,
                    "description": tabevents[event].subject,
                    "event": {
                        "@type": "Event",
                        "about":tabevents[event].id,
                        "name": tabevents[event].titre,
                        "startDate": tabevents[event].createAt,
                        "endDate": "2017-01-22"
                    },
                    "image": tabevents[event].media.imagejpg[0].apiPath,
                    "location": {
                        "@type": "Place",
                        "address": {
                            "@type": "PostalAddress",
                            "addressLocality": "Nantes",
                            "addressRegion": "loire atlantique",
                            "postalCode": "44000",
                            "streetAddress": "3, rue des piquoise"
                        },
                        "name": "The Hi-Dive",
                        "url": "wells-fargo-center.html"
                    },

                    "startDate": tabevents[event].createAt,
                }


            }
            microdata.itemListElement.push(item)
            comp++
        }

    }
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = JSON.stringify(microdata);
    document.head.appendChild(script);

}

function initmicrodatarecipe(recipe) {

    let microdata = {
        "@context": "https://schema.org",
        "@type": ["ItemList", "Event"],
        "name": "les rendez-vous de mademoiselle gingembre",
        "auteur": "mademoiselle gingembre",
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "numberOfItems": tabevents.length,
        "itemListElement": []
    }
    let comp=1
    for (const event in tabevents) {
        if (tabevents.hasOwnProperty(event)) {
            console.log(tabevents[event]);

            let item = {
                "@type": "ListItem",
                "position": comp,
                "item": {
                    "@type": "FoodEvent",
                    "name": tabevents[event].slug,
                    "description": tabevents[event].subject,
                    "event": {
                        "@type": "Event",
                        "about":tabevents[event].id,
                        "name": tabevents[event].titre,
                        "startDate": tabevents[event].createAt,
                        "endDate": "2017-01-22"
                    },
                    "image": tabevents[event].media.imagejpg[0].apiPath,
                    "location": {
                        "@type": "Place",
                        "address": {
                            "@type": "PostalAddress",
                            "addressLocality": "Nantes",
                            "addressRegion": "loire atlantique",
                            "postalCode": "44000",
                            "streetAddress": "3, rue des piquoise"
                        },
                        "name": "The Hi-Dive",
                        "url": "wells-fargo-center.html"
                    },

                    "startDate": tabevents[event].createAt,
                }


            }
            microdata.itemListElement.push(item)
            comp++
        }

    }
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = JSON.stringify(microdata);
    document.head.appendChild(script);

}

function initmicrodataevents(tabevents) {

    let microdata = {
        "@context": "https://schema.org",
        "@type": ["ItemList", "Event"],
        "name": "les rendez-vous de mademoiselle gingembre",
        "auteur": "mademoiselle gingembre",
        "itemListOrder": "https://schema.org/ItemListOrderAscending",
        "numberOfItems": tabevents.length,
        "itemListElement": []
    }
    let comp=1
    for (const event in tabevents) {
        if (tabevents.hasOwnProperty(event)) {
            console.log(tabevents[event]);

            let item = {
                "@type": "ListItem",
                "position": comp,
                "item": {
                    "@type": "FoodEvent",
                    "name": tabevents[event].slug,
                    "description": tabevents[event].subject,
                    "event": {
                        "@type": "Event",
                        "about":tabevents[event].id,
                        "name": tabevents[event].titre,
                        "startDate": tabevents[event].createAt,
                        "endDate": "2017-01-22"
                    },
                    "image": tabevents[event].media.imagejpg[0].apiPath,
                    "location": {
                        "@type": "Place",
                        "address": {
                            "@type": "PostalAddress",
                            "addressLocality": "Nantes",
                            "addressRegion": "loire atlantique",
                            "postalCode": "44000",
                            "streetAddress": "3, rue des piquoise"
                        },
                        "name": "The Hi-Dive",
                        "url": "wells-fargo-center.html"
                    },

                    "startDate": tabevents[event].createAt,
                }


            }
            microdata.itemListElement.push(item)
            comp++
        }

    }
    const script = document.createElement('script');
    script.setAttribute('type', 'application/ld+json');
    script.textContent = JSON.stringify(microdata);
    document.head.appendChild(script);

}
