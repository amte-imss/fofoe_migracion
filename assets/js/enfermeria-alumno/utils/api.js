import {getSchemeAndHttpHost} from "../../utils";
import AbstracApi from "../../api/AbstracApi";
class Api extends AbstracApi{

	constructor() {
		super();
	}

	storePago(solicitud, formData){
		return fetch(`${getSchemeAndHttpHost(`/enfermeria-alumno/carga-de-comprobante-de-pago`)}`, {
			method: 'post',
			body: formData
		}).then(response => this.toJson(response));
	}

}


const api = new Api();

export default api;