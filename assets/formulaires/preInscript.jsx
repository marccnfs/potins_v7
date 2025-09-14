import { h } from "preact";
import {FetchFormControl, FormField, FormPrimaryButton} from '../components/Form.jsx'
import { useState } from 'preact/hooks'
import { useForm } from 'react-hook-form';


let email=localStorage.getItem('email' );

console.log(email)

export default function PreInscriptform() {
    const { register, handleSubmit, formState: { errors } } = useForm();
    const onSubmit = data => console.log(data);
    console.log(errors);

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <input type="text" placeholder="Last name" {...register("Last name", {required: true, maxLength: 100})} />
            <input type="text" placeholder="Email" {...register("Email", {required: true, pattern: /^\S+@\S+$/i})} />
            <input type="tel" placeholder="Mobile number" {...register("Mobile number", {required: true, minLength: 6, maxLength: 12})} />
            <select {...register("Title", { required: true })}>
                <option value="Mr">Mr</option>
                <option value="Mrs">Mrs</option>
                <option value="Miss">Miss</option>
                <option value="Dr">Dr</option>
            </select>
            <input type="text" placeholder="First name" {...register("First name", {required: true, maxLength: 80})} />

            <input {...register("Developer", { required: true })} type="radio" value="Yes" />
            <input {...register("Developer", { required: true })} type="radio" value="No" />

            <input type="submit" />
        </form>
    );
}