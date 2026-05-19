import {getSchemeAndHttpHost} from "../utils";
import {FetchError} from "../utils";

class Api {


	_headers() {
		return {
			"Content-Type": "application/json",
			"Accept": "application/json",
			"X-Requested-With": "XMLHttpRequest"
		}
	}

	storeSolicitud(formData) {
		return fetch(`${getSchemeAndHttpHost()}/enfermeria/solicitud/api`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

	storeAlumno(formData) {
		return fetch(`${getSchemeAndHttpHost()}/enfermeria/solicitud/alumno/api`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

	getFofoeSolicitudes(statusPago) {
		return fetch(getSchemeAndHttpHost(`/fofoe/enfermeria/solicitud?estado=${statusPago}`)).then(response => this.toJson(response));
	}

	setStatusPago(pago, status, formData) {
		return fetch(`${getSchemeAndHttpHost()}/fofoe/pagos/enfermeria/solicitud/${pago}/${status}`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

	uploadFactura(pago, formData) {
		return fetch(`${getSchemeAndHttpHost()}/fofoe/pagos/enfermeria/solicitud/${pago}/factura`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

	toJson(response){
		if(response.ok){
			return response.json();
		}
		throw new FetchError(response.statusText, {
			status: false,
			message: response.statusText,
			data: response.json()
		});
	}
}

const api = new Api();

export default api;