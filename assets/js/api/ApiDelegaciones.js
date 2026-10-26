import AbstracApi from "./AbstracApi";
import {getSchemeAndHttpHost} from "../utils";
class ApiDelegaciones extends AbstracApi {
	constructor() {
		super();
	}

	getDelegaciones(){
		return fetch(`${getSchemeAndHttpHost()}/api/delegaciones`, {...this._headers()}).then(response => this.toJson(response))
	}
}

const apiDelegacion = new ApiDelegaciones();
export default apiDelegacion;