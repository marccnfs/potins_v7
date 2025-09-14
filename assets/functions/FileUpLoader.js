import { h } from "preact";
import { useState, useRef } from 'preact/hooks'

export const FileUploader = ({onFileSelectError, onFileSelectSuccess, onImgChange, img, imgpath}) => {

    const fileInput = useRef(null)

    const handleFileInput = (e) => {
        // handle validations
        const file = e.target.files[0]

        if (file.size > 10024) {
            if (!$.testfile(file)) return;
            $.resizor({l: 600, h: 600, f: file, p: true}).then((thetumb) => {
                onFileSelectSuccess=true;
                onImgChange(thetumb.src)
            })
        }else {
            onFileSelectError({ error: "votre fichier image est trop lourd, max 10 M" });
        }
    }

    let preview = <div className="preview"/>;

    if(imgpath !== ""){
        preview = <div className="preview">
                    <img src={"/upload/module/"+imgpath} alt="image event"/>
                    </div>
    }

    return (
        <div className="file-uploader">
            <div className="labels_form">
                <div className="label-upimg">une photo, plus d'info....</div>
                <input id="uploadImage" type="file" onChange={handleFileInput} accept=".jpg, .jpeg, .png" style={{display:"none"}}/>
                <label onClick={e => fileInput.current && fileInput.current.click()} id="media-img" className="fablue fa fa-camera" htmlFor="uploadImage"/>
            </div>
                <div className="tbb-post">
                    {preview}
                </div>
        </div>

    )
}