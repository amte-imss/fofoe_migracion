import React from "react";
import Swal from "sweetalert2";
import {getInputValues, getSchemeAndHttpHost, objectToFormData, showErrors} from "../../../utils";
import apiDelegacion from "../../../api/ApiDelegaciones";
import apiInstitucion from "../api/ApiInstitucion";

export default function useFormInstitucion({setIsLoading, institucion}) {

	const formRef = React.useRef();
	const [ooads, setOoads] = React.useState([]);
	const [formErrors, setFormErrors] = React.useState({});


	React.useEffect(() => {
		getCatalogs();
	}, [])

	const getCatalogs = async () => {
		await apiDelegacion.getDelegaciones().then(({data}) => {
			setOoads(data)
		});
		setIsLoading(false)
	}

	const canFormSubmit = () => {
		let result = true;
		if (!formRef.current) {
			return false;
		}
		for (const i in formRef.current.elements) {
			const input = formRef.current.elements[i];
			if (input.name && input.validity && !input.validity.valid) {
				result = false;
			}
		}
		return result;
	}

	const handleSubmit = (event) => {
		event.preventDefault();
		const form = formRef.current;
		if (!form.checkValidity()) {
			form.reportValidity();
			const errors = {}
			for (const i of formRef.current.elements) {
				if (i.name && !i.validity.valid) {
					errors[i.name] = i.validationMessage;
				}
			}
			setFormErrors(errors);
			return;
		}

		if (institucion) {
			updateInstitucion()
		} else {
			storeInstitucion()
		}
	}

	const updateInstitucion = () => {
		const formData = new FormData(formRef.current)
		if(formData.get('institucion[cedulaFile]').size === 0){
			formData.delete('institucion[cedulaFile]');
		}
		setIsLoading(true);
		apiInstitucion.updateInstitucion(institucion.id, formData).then(result => {
			if (result.status) {
				Swal.fire(
					"Institución actualizada con éxito",
					'',
					'success',
				).then(() => {
					let url = getSchemeAndHttpHost('/admin/institucion');
					window.location.href = url;
				})
			} else {
				//console.log(result.errors);
				showErrors(result.errors);
			}
		}).catch(errors => {
			errors.data.data.then(result => {
				showErrors(result.errors);
			});
		}).finally(() => {
			setIsLoading(false);
		})
	}

	const storeInstitucion = () => {
		const formData = new FormData(formRef.current)
		setIsLoading(true);
		apiInstitucion.storeInstitucion(formData).then(result => {
			if (result.status) {
				Swal.fire(
					"Institución guardada con éxito",
					'',
					'success',
				).then(() => {
					let url = getSchemeAndHttpHost('/admin/institucion');
					window.location.href = url;
				})
			} else {
				//console.log(result.errors);
				showErrors(result.errors);
			}
		}).catch(errors => {
			errors.data.data.then(result => {
				showErrors(result.errors);
			});
		}).finally(() => {
			setIsLoading(false);
		})
	}

	return {formRef, formErrors, setFormErrors, ooads, canFormSubmit, handleSubmit}
}

