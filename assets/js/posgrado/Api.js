import {getSchemeAndHttpHost} from "../utils";

const storeResidenciaNoIMSS = (formData) => {
	return fetch(`${getSchemeAndHttpHost()}/posgrado/residentes/no_imss/nuevo`, {
		method: 'post',
		body: formData
	}).then(function (response) {
			return response.json();
	})
}

export {
	storeResidenciaNoIMSS
};
