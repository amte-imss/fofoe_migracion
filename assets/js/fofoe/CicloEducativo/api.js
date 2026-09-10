import AbstracApi from "../../api/AbstracApi";
import {getSchemeAndHttpHost} from "../../utils";

class Api extends AbstracApi {
    getSolicitudes(meta, query) {
        const page = meta && meta.page ? meta.page : 1;
        const perPage = meta && meta.perPage ? meta.perPage : 1;
        let querystring = '';
        for (const i in query) {
            if(query[i].trim()!== ''){
                querystring += `${i}=${query[i]}&`;
            }
        }
        return fetch(`${getSchemeAndHttpHost(`/fofoe/solicitud?${querystring}page=${page}&perPage=${perPage}`)}`, {
            method: "GET",
            headers: this._headers()
        }).then(response => this.toJson(response));
    }
}

const api  = new Api();
export default api;