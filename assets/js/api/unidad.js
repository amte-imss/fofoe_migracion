import {getSchemeAndHttpHost} from "../utils";

const unidadesByDelegacionGet = (idDel) => {
	return fetch(`${getSchemeAndHttpHost()}/api/unidad/delegacion/${idDel}`)
		.then(response => {
				return response.json()},
			error => {
				console.error(error)
			})
}

export {unidadesByDelegacionGet}