import React from 'react';
import { createRoot } from "react-dom/client";
import Loader from "../../components/Loader/Loader";
import InstitucionForm from "./components/InstitucionForm";
import useFormInstitucion from "./hooks/useFormInstitucion";

function CreatePage({}) {
	const [isLoading, setIsLoading] = React.useState(true);
	const {
		formRef,
		formErrors,
		setFormErrors,
		ooads,
		canFormSubmit,
		handleSubmit
	} = useFormInstitucion({setIsLoading})

	return (
		<>
			<Loader show={isLoading}/>
			<InstitucionForm
				formRef={formRef}
				formErrors={formErrors}
				setFormErrors={setFormErrors}
				ooads={ooads}
				canFormSubmit={canFormSubmit}
				handleSubmit={handleSubmit}
			/>
		</>
	);
}


document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.querySelector('.react')).render(
		<CreatePage/>,
	)});