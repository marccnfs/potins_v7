import {FetchFormEvent, FormField, FormPrimaryButton, FormSecondaryButton} from '../components/Form.jsx'
import {useState, useEffect, useMemo} from 'preact/hooks'
import {Alert} from '../components/Alert.jsx'
import { h } from "preact";
import CalendarEvent from "../functions/calendar";

const createEmptySchedule = () => ({
    'Dim': [],
    'Lun': [],
    'Mar': [],
    'Mer': [],
    'Jeu': [],
    'Ven': [],
    'Sam': [],
});

const normalizePartner = (partner) => {
    if (!partner) {
        return [];
    }

    if (Array.isArray(partner)) {
        if (partner.length === 0) {
            return [];
        }

        return normalizePartner(partner[0]);
    }

    if (typeof partner === 'object') {
        const identifier = partner.id ?? partner.ID ?? null;

        if (identifier === null || identifier === undefined) {
            return [];
        }

        const locality = partner.locality ?? partner.locatemedia ?? {};
        return [{
            id: String(identifier),
            name: partner.name ?? partner.nameboard ?? partner.title ?? '',
            city: locality.city ?? locality.nameloc ?? partner.city ?? null,
        }];
    }

    if (typeof partner === 'number' || typeof partner === 'string') {
        return [{
            id: String(partner),
            name: '',
            city: null,
        }];
    }

    return [];
};



function updateTextareaHeight(input) {
    if (input !== null) {
        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    }
}

updateTextareaHeight(document.getElementById('contentOne'));


export default function Formeventpotin(props){

    const redirect = '/board-office/programmation-potins';
    const mediatheques = Array.isArray(props.mediatheques) ? props.mediatheques : [];
    const eventData = props.event && typeof props.event === 'object' ? props.event : null;

    const baseDescription = eventData?.description ?? '';
    const basePartner = useMemo(() => {
        if (!eventData) {
            return [];
        }

        return normalizePartner(eventData.locatemedia ?? eventData.mediatheque ?? null);
    }, [eventData]);

    const baseTabunique = useMemo(() => {
        const appointment = eventData?.appointment;
        if (appointment && appointment.tabdate && appointment.tabdate['tabdatejso']) {
            return appointment.tabdate['tabdatejso'];
        }

        return createEmptySchedule();
    }, [eventData]);

    const [success, setSuccess] = useState(false);
    const [description, setDescription] = useState(baseDescription);
    const [tabunique, setTabunique] = useState(baseTabunique);
    const [dateselect, setDateselect] = useState(Boolean(eventData));
    const [changDate, setChangDate] = useState(false);
    const [edit, setEdit] = useState(Boolean(eventData));
    const [tabpartners, setTabpartners] = useState(basePartner);
    const [selectedMediaId, setSelectedMediaId] = useState(basePartner[0]?.id ?? '');
    const [changPartner, setChangPartner] = useState(false);
    const [stateform, setStateform] = useState(false);

    useEffect(() => {
        setEdit(Boolean(eventData));
        setDescription(baseDescription);
        setTabunique(baseTabunique);
        setDateselect(Boolean(eventData));
        setChangDate(false);
        setTabpartners(basePartner);
        setSelectedMediaId(basePartner[0]?.id ?? '');
        setChangPartner(false);
    }, [eventData, baseDescription, baseTabunique, basePartner]);

    useEffect(() => {
        const hasDescription = description.trim().length > 0;
        const hasMedia = selectedMediaId !== '';
        const hasDates = dateselect;

        setStateform(hasDescription && hasMedia && hasDates);
    }, [description, selectedMediaId, dateselect]);

    const selectedMedia = useMemo(() => {
        if (!selectedMediaId) {
            return null;
        }
        return mediatheques.find(item => String(item.id) === String(selectedMediaId)) ?? null;
    }, [mediatheques, selectedMediaId]);

    if (success) {
        return <Alert>Votre evenement à bien été crée/modifié</Alert>;
    }

    const handleDescriptionChange = (e) => {
        setDescription(e.target.value);
    };

    const handleTabChange = (tabtemp) => {
        setTabunique(tabtemp);
        setDateselect(true);
        setChangDate(true);
    };

    const handleMediaChange = (e) => {
        const value = e.target.value;
        setSelectedMediaId(value);

        if (!value) {
            setTabpartners([]);
            setChangPartner(false);
            return;
        }

        const choice = mediatheques.find(item => String(item.id) === value);

        if (choice) {
            setTabpartners([{ id: String(choice.id), name: choice.name ?? '', city: choice.city ?? null }]);
            setChangPartner(true);
        } else {
            setTabpartners([]);
            setChangPartner(false);
        }
    };

    const onSuccess = (response) => {
        setSuccess(response.success);
        AsyncReturn();
    };

    const handleLoadPrevious = (e) => {
        e.preventDefault();
        window.location.replace(redirect);
    };

    const AsyncReturn = async () => {
        await delay(1);
        window.location.replace(redirect);

    };

    const delay = (n) => {
        return new Promise((resolve) => {
            setTimeout(resolve, n * 1000);
        });
    };

    return (
        <FetchFormEvent
            action='/potins/event/add-event-potin-ajx'
            className="form-elastic"
            onSuccess={onSuccess}
            edit={edit}
            board={props.board}
            potin={props.potin}
            changeTabDate={changDate}
            changePartner={{changPartner}}
            tabdate={tabunique}
            tabpartners={tabpartners}
            event={props.event.id}
        >
            <FormField
                name='description'
                type='event'
                value={description}
                onChange={handleDescriptionChange}
                required
                wrapperClass=''
            >
            </FormField>

            <div className="form-group">
                <h3>quand ?</h3>
                <CalendarEvent parse={tabunique} edit={edit} onTabchange={handleTabChange}/>
            </div>

            <div className="form-group">
                <h3>où ?</h3>
                <div className="form-field">
                    <label htmlFor="postevent-mediatheque">Choisir la médiathèque</label>
                    <select
                        id="postevent-mediatheque"
                        value={selectedMediaId}
                        onChange={handleMediaChange}
                        required
                    >
                        <option value="">Sélectionnez une médiathèque</option>
                        {mediatheques.map((media) => (
                            <option key={media.id} value={media.id}>
                                {media.name}{media.city ? ` — ${media.city}` : ''}
                            </option>
                        ))}
                    </select>
                    {selectedMedia?.city && (
                        <p className="form-hint">Localisation : {selectedMedia.city}</p>
                    )}
                </div>
            </div>

            <div className='full'>
                {stateform &&
                    <FormPrimaryButton>Envoyer</FormPrimaryButton>
                }
                <FormSecondaryButton onClick={handleLoadPrevious}>annuler</FormSecondaryButton>
            </div>

        </FetchFormEvent>
    )
}
