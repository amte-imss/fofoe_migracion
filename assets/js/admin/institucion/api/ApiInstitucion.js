import AbstracApi from "../../../api/AbstracApi";
import {getSchemeAndHttpHost} from "../../../utils";

class ApiInstitucion extends AbstracApi {
	constructor() {
		super();
	}

	storeInstitucion(formData) {
		let url = `${getSchemeAndHttpHost()}/api/admin/institucion`;
		return fetch(url, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	updateInstitucion(institucion, formData) {
		let url = `${getSchemeAndHttpHost()}/api/admin/institucion/${institucion}`;
		return fetch(url, {
			method: 'POST',
			body: formData
		}).then(response => this.toJson(response))
	}

	deleteInstitucion(institucion) {
		let url = `${getSchemeAndHttpHost()}/admin/institucion/${institucion}/deleted`;
		return fetch(url, {
			method: 'POST',
		}).then(response => this.toJson(response))
	}


	paginateInstituciones(filters) {
		let url = `${getSchemeAndHttpHost()}/admin/institucion`;
		const searchParams = new URLSearchParams(filters);
		return fetch(`${url}?${searchParams.toString()}`, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		}).then(response => {
			if (response.redirected && new URL(response.url).pathname === '/login') {
				window.location.assign(response.url);
				throw new Error('La sesión ha expirado');
			}
			return this.toJson(response);
		})
	}
}

const apiInstitucion = new ApiInstitucion();
export default apiInstitucion;
