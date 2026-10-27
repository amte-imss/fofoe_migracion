import React from 'react';
import { createRoot } from "react-dom/client";
import Loader from "../../components/Loader/Loader";
import InstitucionForm from "./components/InstitucionForm";
import useFormInstitucion from "./hooks/useFormInstitucion";

function EditPage({institucion, hasCedula}) {
	const [isLoading, setIsLoading] = React.useState(true);
	const {
		formRef,
		formErrors,
		setFormErrors,
		ooads,
		canFormSubmit,
		handleSubmit
	} = useFormInstitucion({setIsLoading, institucion})

	return (
		<>
			<Loader show={isLoading}/>
			<InstitucionForm
				formRef={formRef}
				institucion={institucion}
				formErrors={formErrors}
				setFormErrors={setFormErrors}
				ooads={ooads}
				canFormSubmit={canFormSubmit}
				handleSubmit={handleSubmit}
				hasCedula={hasCedula}
			/>
		</>
	);
}


document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.querySelector('.react')).render(
		<EditPage institucion={window.Institucion} hasCedula={window.InstitucionHasCedula}/>,
	)});