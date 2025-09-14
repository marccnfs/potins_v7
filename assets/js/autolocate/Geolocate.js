
function geoFindMe() {

    const status = document.querySelector('#status');

    function success(position) {
        const latitude  = position.coords.latitude;
        const longitude = position.coords.longitude;
        window.location.href="/potins/?lon="+longitude+"&&lat="+latitude;
    }

    function error() {
        status.textContent = 'Unable to retrieve your location';
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(success, error);
    }
}

export default class Geolocate extends HTMLElement {

    static get observedAttributes() {return ['prefix']; }

    constructor() {
        super();
        this.lattitude=0
        this.longitude=0
        this.redirect="/potins/?lon="+this.longitude+"&&lat="+this.lattitude;
        this.button=document.createElement('button')
        this.button.classList.add("bt-loc")
        this.incone =document.createElement('i')
        this.incone.classList.add("fa")
        this.incone.classList.add("fa-map-marker")
        this.appendChild(this.button)
        this.button.appendChild(this.incone)
    }

    connectedCallbach(){
        console.log('init geolocate');
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if(oldValue!== null ){
            geoFindMe();
        }
    }

    disconnectCalback(){
        this.destroy
    }
}





