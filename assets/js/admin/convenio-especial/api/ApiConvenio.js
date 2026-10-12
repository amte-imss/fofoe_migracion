import AbstracApi from "../../../api/AbstracApi";
import {getSchemeAndHttpHost} from "../../../utils";
class ApiConvenio extends AbstracApi {
	constructor() {
		super();
	}

	getRecursos(motivo) {
		return fetch(`${getSchemeAndHttpHost('/admin/recurso')}?motivo_colaboracion=${motivo}`, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		}).then(response => this.toJson(response))
	}
	getConvenios(rfc){
		const formData = new FormData();
		formData.append('rfc', rfc);
		return fetch(`${getSchemeAndHttpHost()}/admin/conveniosadmin/getconveniogral`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	getInstituciones(rfc){
		return fetch(`${getSchemeAndHttpHost()}/admin/institucion/search-by-rfc/${rfc}`, {
			method: 'GET'
		}).then(response => this.toJson(response))
	}

	getConveniosGeneralesByInstitucion(institucion){
		return fetch(`${getSchemeAndHttpHost()}/api/admin/conveniosadmin/convenios-generales-by-institucion/${institucion}`, {...this._headers()}).then(response => this.toJson(response))
	}

	calcYearsDate(date, increment){
		const formData = new FormData();
		formData.append('date', date);
		formData.append('increment', increment);
		return fetch(`${getSchemeAndHttpHost()}/api/add-dates-years`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	storeConvenio(isCame, formData) {
		let url = `${getSchemeAndHttpHost()}/admin/convenios-especiales/store`;
		if(isCame){
			url = `${getSchemeAndHttpHost()}/api/came/conveniosadmin`;
		}
		return fetch(url, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	updateConvenio(isCame, convenio, formData) {
		let url = `${getSchemeAndHttpHost()}/admin/convenios-especiales/${convenio}/update`;
		if(isCame){
			url = `${getSchemeAndHttpHost()}/api/came/conveniosadmin/${convenio}`;
		}
		return fetch(url, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	deleteConvenio(isCame, convenio) {
		let url = `${getSchemeAndHttpHost()}/admin/convenios-especiales/${convenio}/delete`;
		if(isCame){
			url = `${getSchemeAndHttpHost()}/admin/convenioscame/convenioscame/${convenio}/delete`;
		}
		return fetch(url, {
			method: 'POST',
		}).then(response => this.toJson(response))
	}

	setTerminacionAnticipada(id, status) {
		let url = `${getSchemeAndHttpHost()}/api/admin/conveniosadmin/${id}/status`;
		const formData = new FormData();
		formData.append('status', status);
		return fetch(url, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	paginateConvenios(filters, isCame = false) {
		let url = `${getSchemeAndHttpHost()}/admin/convenios-especiales`;
		if(isCame){
			url = `${getSchemeAndHttpHost()}/admin/convenios-especiales`;
		}
		const searchParams = new URLSearchParams(filters);
		return fetch(`${url}?${searchParams.toString()}`, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		}).then(response => this.toJson(response))
	}

	postRecurso(recurso, motive) {
		const formData = new FormData();
		formData.append('recurso[nombre]', recurso);
		formData.append('recurso[motivoColaboracion]', motive);
		return fetch(`${getSchemeAndHttpHost()}/admin/recurso/store`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}
}

const apiConvenio = new ApiConvenio();
export default apiConvenio;