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
        return (
            <div className="event-potins-form__feedback">
                <Alert>Votre événement a bien été créé/modifié</Alert>
            </div>
        );
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
            className="form-elastic event-potins-form"
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
            <header className="event-potins-form__header">
                <h2 className="event-potins-form__title">Programmer un évènement potin</h2>
                <p className="event-potins-form__lead">Complétez les informations ci-dessous pour publier votre animation dans la programmation.</p>
            </header>

            <section className="event-potins-form__section">
                <div className="event-potins-form__card">
                    <div className="event-potins-form__section-header">
                        <span className="event-potins-form__step">1</span>
                        <div>
                            <h3>Décrivez l'évènement</h3>
                            <p className="event-potins-form__hint">Partagez les informations essentielles et le ton de votre potin.</p>
                        </div>
                    </div>
                    <div className="event-potins-form__field-group">
                        <label className="event-potins-form__label" htmlFor='description'>Description</label>
                        <FormField
                            name='description'
                            type='event'
                            value={description}
                            onChange={handleDescriptionChange}
                            placeholder="Décrivez brièvement le déroulé de l'évènement..."
                            required
                            wrapperClass='event-potins-form__textarea'
                        >
                        </FormField>
                        <p className="event-potins-form__support">La description sera reprise telle quelle dans la fiche publique de l'évènement.</p>
                    </div>
                </div>
            </section>

            <section className="event-potins-form__section">
                <div className="event-potins-form__card">
                    <div className="event-potins-form__section-header">
                        <span className="event-potins-form__step">2</span>
                        <div>
                            <h3>Planifiez les dates</h3>
                            <p className="event-potins-form__hint">Sélectionnez une ou plusieurs sessions dans le calendrier.</p>
                        </div>
                    </div>
                    <div className="event-potins-form__calendar">
                        <CalendarEvent parse={tabunique} edit={edit} onTabchange={handleTabChange}/>
                    </div>
                </div>
            </section>

            <section className="event-potins-form__section">
                <div className="event-potins-form__card">
                    <div className="event-potins-form__section-header">
                        <span className="event-potins-form__step">3</span>
                        <div>
                            <h3>Choisissez la médiathèque</h3>
                            <p className="event-potins-form__hint">Associez l'évènement au lieu qui l'accueille.</p>
                        </div>
                    </div>
                    <div className="event-potins-form__field-group">
                        <label className="event-potins-form__label" htmlFor="postevent-mediatheque">Médiathèque</label>
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
                            <p className="event-potins-form__support">Localisation : {selectedMedia.city}</p>
                        )}
                    </div>
                </div>
            </section>

            <div className='event-potins-form__actions'>
                {stateform &&
                    <FormPrimaryButton>Enregistrer l'évènement</FormPrimaryButton>
                }
                <FormSecondaryButton onClick={handleLoadPrevious}>Annuler</FormSecondaryButton>
            </div>

        </FetchFormEvent>
    )
}
