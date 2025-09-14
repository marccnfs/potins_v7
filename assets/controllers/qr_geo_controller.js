// assets/controllers/qr_geo_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        lat: Number,
        lng: Number,
        radius: { type: Number, default: 100 },
        okMessage: { type: String, default: "Bien joué !" },
        denyMessage: { type: String, default: "Tu n'es pas au bon endroit." },
        needHttpsMessage: { type: String, default: "HTTPS requis pour géolocalisation." }
    }
    static targets = ["msg","button"]

    haversine(a,b){
        const R=6371000, toRad=d=>d*Math.PI/180;
        const dLat=toRad(b.lat-a.lat), dLng=toRad(b.lng-a.lng);
        const A=Math.sin(dLat/2)**2+Math.cos(toRad(a.lat))*Math.cos(toRad(b.lat))*Math.sin(dLng/2)**2;
        return 2*R*Math.asin(Math.sqrt(A));
    }

    check(){
        if (location.protocol!=='https:' && location.hostname!=='localhost'){
            this.msgTarget.textContent = this.needHttpsMessageValue;
            return;
        }
        if (!navigator.geolocation){
            this.msgTarget.textContent = "Géolocalisation indisponible";
            return;
        }
        navigator.geolocation.getCurrentPosition(pos=>{
            const here={lat:pos.coords.latitude,lng:pos.coords.longitude};
            const target={lat:this.latValue,lng:this.lngValue};
            const d=this.haversine(here,target);
            if (d<=this.radiusValue){
                this.msgTarget.textContent = this.okMessageValue;
                document.dispatchEvent(new CustomEvent("puzzle:solved",{bubbles:true, detail:{distance:d}}));
            } else {
                this.msgTarget.textContent = `${this.denyMessageValue} (≈ ${Math.round(d)} m)`;
            }
        }, err=>{
            this.msgTarget.textContent = "Erreur géoloc: "+err.message;
        }, {enableHighAccuracy:true, timeout:8000});
    }
}
