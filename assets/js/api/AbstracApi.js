import {FetchError} from "../utils";

export default class AbstracApi {
	_headers() {
		return {
			"Content-Type": "application/json",
			"Accept": "application/json",
			"X-Requested-With": "XMLHttpRequest"
		}
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
