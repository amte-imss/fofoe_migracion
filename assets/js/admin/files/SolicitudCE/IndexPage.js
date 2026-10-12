import * as React from 'react'
import ReactDOM from "react-dom";
import Loader from "../../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../../utils";
import Popup from "../../../components/Popup/Popup";
import './IndexPage.css';
import {moneyFormat} from "../../../utils";

export default function IndexPage() {
    const [loading, setLoading] = React.useState(false);
    const [solicitud, setSolicitud] = React.useState({});
    const [showPopup, setShowPopup] = React.useState(false);
    const [urlDocument, setUrlDocument] = React.useState('');
    const handleSubmit = (event) => {
        event.preventDefault();
        setLoading(true);
        setSolicitud({})
        fetch(getSchemeAndHttpHost(`/admin/files/solicitud-ce/${event.target.id.value}`))
            .then(response => response.json()).then(({data}) => {
            setSolicitud(data);
        }).catch(error => {
            console.error('Error fetching pago:', error);
        }).finally(() => {
            setLoading(false);
        });
    }

    const handleShowOficioMontos = () => {
        setUrlDocument(getSchemeAndHttpHost(`/admin/files/solicitud-ce/${solicitud.id}/oficio-montos/preview`));
        setShowPopup(true);
    }

    const handleShowFormatoFOFOE = (id) => {
        setUrlDocument(getSchemeAndHttpHost(`/admin/files/campo-clinico/${id}/formato-fofoe/preview`));
        setShowPopup(true);
    }

    const handleUploadOficioMontos = (event) => {
        event.preventDefault();
        setLoading(true)
        fetch(getSchemeAndHttpHost(`/admin/files/solicitud-ce/${solicitud.id}/oficio-montos-upload`),{
            method: 'POST',
            body: new FormData(event.target)
        }).then(response => response.json()).then(result => {
            if(!result.status){
                alert('Error al subir el Oficio de Montos: ' + result.message);
            }else{
                alert('Oficio de Montos reemplazado exitosamente');
            }
        }).finally(() => {
            setLoading(false);
        })
    }

    const handleUploadFormatoFofoe = (event, id) => {
        event.preventDefault();
        setLoading(true)
        fetch(getSchemeAndHttpHost(`/admin/files/campo-clinico/${id}/formato-fofoe-upload`),{
            method: 'POST',
            body: new FormData(event.target)
        }).then(response => response.json()).then(result => {
            if(!result.status){
                alert('Error al subir el Formato FOFOE: ' + result.message);
            }else{
                alert('Formato FOFOE reemplazado exitosamente');
            }
        }).finally(() => {
            setLoading(false);
        })
    }

    return (
        <>
            <Loader show={loading}/>
            <form className={'form'} onSubmit={handleSubmit}>
                <fieldset>
                    <label>
                        ID:
                        <input type="text" name="id"/>
                    </label>
                </fieldset>
                <fieldset>
                    <input type="submit" value={'Buscar'}/>
                </fieldset>
            </form>

            {!!solicitud.id &&
                <div>
                    <table>
                        <tbody>
                        <tr>
                            <th>ID</th>
                            <td>{solicitud.id}</td>
                        </tr>
                        <tr>
                            <th>No Solicitud</th>
                            <td>{solicitud.noSolicitud}</td>
                        </tr>
                        <tr>
                            <th>Referencia Bancaria</th>
                            <td>{solicitud.referenciaBancaria}</td>
                        </tr>
                        <tr>
                            <th>Fecha Solicitud</th>
                            <td>{solicitud.fecha}</td>
                        </tr>
                        <tr>
                            <th>Monto</th>
                            <td>{moneyFormat(solicitud.monto)}</td>
                        </tr>
                        <tr>
                            <th>Validado</th>
                            <td>{solicitud.validado == null ? 'Pendiente' : (solicitud.validado === false ? 'No Valido': 'Validada')}</td>
                        </tr>
                        <tr>
                            <th>Oficio de Montos Validado</th>
                            <td>{solicitud.validateOficioMontos == null ? 'Pendiente' : (solicitud.validateOficioMontos === false ? 'No Valido': 'Validada')}</td>
                        </tr>
                        <tr>
                            <th>Oficio de Montos Observaciones</th>
                            <td>{solicitud.motiveOficioMontos}</td>
                        </tr>
                        <tr>
                            <th>Campus</th>
                            <td>{solicitud.campus?.nombre}</td>
                        </tr>
                        <tr>
                            <th>Oficio de montos</th>
                            <td>
                                <a className={'btn'} onClick={handleShowOficioMontos}>Ver Oficio de Montos</a>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <form
                                    onSubmit={handleUploadOficioMontos}
                                    encType="multipart/form-data">
                                    <input type="file" name="comprobante" accept="application/pdf"/>
                                    <button type="submit">Reemplazar Oficio Montos</button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h2>Campos Clínicos</h2>
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Inicial</th>
                            <th>Fecha Final</th>
                            <th>Unidad</th>
                            <th>Lugares Autorizados</th>
                            <th>ReferenciaBancaria</th>
                            <th>Monto</th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        {solicitud.camposClinicos.map((campoClinico, index) => (
                            <tr key={index}>
                                <td>{campoClinico.id}</td>
                                <td>{campoClinico.fechaInicialFormatted}</td>
                                <td>{campoClinico.fechaFinalFormatted}</td>
                                <td>{campoClinico.unidad?.nombre}</td>
                                <td>{campoClinico.lugaresAutorizados}</td>
                                <td>{campoClinico.referenciaBancaria}</td>
                                <td>{moneyFormat(campoClinico.monto)}</td>
                                <td>
                                    <a className={'btn'} target="_blank" onClick={() => handleShowFormatoFOFOE(campoClinico.id)}>Ver Formato FOFOE Cargado</a>
                                </td>
                                <td>
                                    <form
                                        onSubmit={(event) => handleUploadFormatoFofoe(event, campoClinico.id)}
                                        encType="multipart/form-data">
                                        <input type="file" name="comprobante" accept="application/pdf"/>
                                        <button type="submit">Reemplazar Formato FOFOE</button>
                                    </form>
                                </td>
                            </tr>
                        ) )}
                        </tbody>
                    </table>
                </div>

            }
            <Popup title={'Documento'}
                   body={<>
                       <iframe src={urlDocument} width="100%"
                               height="500px" title="Comprobante de pago"></iframe>
                   </>}
                   closePopup={() => setShowPopup(false)}
                   show={showPopup}
            />
        </>
    )
}


document.addEventListener('DOMContentLoaded', () => {
    ReactDOM.render(
        <IndexPage/>,
        document.querySelector('.react')
    )
});