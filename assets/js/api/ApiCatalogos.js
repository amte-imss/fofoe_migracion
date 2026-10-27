import AbstracApi from "./AbstracApi";
import {getSchemeAndHttpHost} from "../utils";
class ApiCatalogos extends AbstracApi {
	constructor() {
		super();
	}

	getCiclosAcademicos(){
		return fetch(`${getSchemeAndHttpHost()}/api/ciclos-academicos`, {...this._headers()}).then(response => this.toJson(response))
	}

	getGradosAcademicos() {
		return fetch(`${getSchemeAndHttpHost()}/api/nivel-academico`, {...this._headers()}).then(response => this.toJson(response))
	}

	getDisciplinas() {
		return fetch(`${getSchemeAndHttpHost()}/api/disciplinas`, {...this._headers()}).then(response => this.toJson(response))
	}

	getCoordinacionesFirmantes() {
		return fetch(`${getSchemeAndHttpHost()}/api/coordinacion-firmante`, {...this._headers()}).then(response => this.toJson(response))
	}

	getCarreras(nivelAcademico = ''){
		return fetch(`${getSchemeAndHttpHost()}/api/carreras?${nivelAcademico? `nivel_academico=${nivelAcademico}` : ''}`, {...this._headers()}).then(response => this.toJson(response))
	}

	postNivelAcademico(nivelAcademico) {
		const formData = new FormData();
		formData.append('nivel_academico[nombre]', nivelAcademico);
		return fetch(`${getSchemeAndHttpHost()}/api/nivel-academico`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	postDisciplina(disciplina) {
		const formData = new FormData();
		formData.append('disciplina[nombre]', disciplina);
		return fetch(`${getSchemeAndHttpHost()}/api/disciplinas`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	postCarrera(carrera, nivelAcademico = '') {
		const formData = new FormData();
		formData.append('carrera[nombre]', carrera);
	//	formData.append('carrera[nivelAcademico]', nivelAcademico);
		return fetch(`${getSchemeAndHttpHost()}/api/carreras`, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}
}

const apiCatalogos = new ApiCatalogos();
export default apiCatalogos;