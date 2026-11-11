import AbstracApi from "../api/AbstracApi";
import {getSchemeAndHttpHost} from "../utils";
class Api extends AbstracApi {

	getTipoSolicitudes(){
		return fetch(getSchemeAndHttpHost(`/api/edu-per/tipo-solicitud`)).then(response => this.toJson(response));
	}

	getDuraciones(value) {
		return fetch(getSchemeAndHttpHost(`/api/edu-per/duracion?modalidad=${value}`)).then(response => this.toJson(response));
	}

	getCursos(){
		return fetch(getSchemeAndHttpHost(`/api/edu-per/curso`)).then(response => this.toJson(response));
	}

	getRequests(){
		return fetch(getSchemeAndHttpHost(`/edu-per/request`)).then(response => this.toJson(response));
	}

	getModalidades(tipo){
		return fetch(getSchemeAndHttpHost(`/api/edu-per/modalidad?type=${tipo}`)).then(response => this.toJson(response));
	}

	storeCurso(value) {
		const formData = new FormData();
		formData.append('curso[name]', value);
		return fetch(`${getSchemeAndHttpHost()}/api/edu-per/curso`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	storeRequest(formData) {
		return fetch(`${getSchemeAndHttpHost()}/edu-per/request/store`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	storePago(solicitud, formData){
		return fetch(`${getSchemeAndHttpHost(`/edu-per/participant/carga-de-comprobante-de-pago`)}`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

	getFofoeSolicitudes(statusPago, type) {
		return fetch(getSchemeAndHttpHost(`/fofoe/edu-per/solicitud/${type}?estado=${statusPago}`)).then(response => this.toJson(response));
	}

	setStatusPago(pago, status, formData) {
		return fetch(`${getSchemeAndHttpHost()}/fofoe/pagos/edu-per/solicitud/${pago}/${status}`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

	uploadFactura(pago, formData) {
		return fetch(`${getSchemeAndHttpHost()}/fofoe/pagos/edu-per/solicitud/factura/${pago}/store`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

}

const api = new Api();

export default api;