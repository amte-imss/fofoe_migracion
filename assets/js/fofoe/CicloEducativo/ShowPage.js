import { createRoot } from "react-dom/client";
import React from "react";
import {getSchemeAndHttpHost} from "../../utils";
import api from "./api";
import {moneyFormat} from "../../utils";
import Swal from "sweetalert2";
import Loader from "../../components/Loader/Loader";
import Popup from "../../components/Popup/Popup";
import './ShowPage.css'


export default function ShowPage({solicitud}) {
    const [loading, setLoading] = React.useState(false);
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
                Swal.fire({
                    icon: "error",
                    title: 'Se presentó un error al procesar la solicitud',
                    text: 'Error al subir el Oficio de Montos: ' + result.message,
                })
            }else{
                Swal.fire('Oficio de Montos reemplazado exitosamente')
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
                Swal.fire({
                    icon: "error",
                    title: 'Se presentó un error al procesar la solicitud',
                    text: 'Error al subir el Formato FOFOE: ' + result.message,
                })
            }else{
                Swal.fire('Formato FOFOE reemplazado exitosamente');
            }
        }).finally(() => {
            setLoading(false);
        })
    }

    const handleCancelSolicitud = () => {
        Swal.fire({
            title: "¿Estás seguro de que deseas cancelar esta solicitud?",
            showCancelButton: true,
            text: "Nota: Esta acción no se puede deshacer",
            confirmButtonText: "Si, cancelar",
            cancelButtonText: "No, mantener solicitud",
        }).then((result) => {
            if (result.isConfirmed) {
                setLoading(true)
                fetch(getSchemeAndHttpHost(`/admin/files/solicitud-ce/${solicitud.id}/cancel`),{
                    method: 'POST',
                    body: new FormData()
                }).then(response => response.json()).then(result => {
                    if(!result.status){
                        Swal.fire({
                            icon: "error",
                            title: 'Se presentó un error al procesar la solicitud',
                            text: result.message,
                        })
                    }else{
                        Swal.fire('Solicitud cancelada exitosamente').then(() => {
                            window.location.reload();
                        });
                    }
                }).finally(() => {
                    setLoading(false);
                })
            }
        });
    }

    const handleChangeStatusValidacionMontos = () => {
        Swal.fire({
            title: "¿Estás seguro de que deseas cambiar el estado de la solicitud?",
            showCancelButton: true,
            text: "Nota: Esta acción no se puede deshacer",
            confirmButtonText: "Si",
            cancelButtonText: "No",
        }).then((result) => {
            if (result.isConfirmed) {
                setLoading(true)
                fetch(getSchemeAndHttpHost(`/admin/files/solicitud-ce/${solicitud.id}/status-validacion-montos`),{
                    method: 'POST',
                    body: new FormData()
                }).then(response => response.json()).then(result => {
                    if(!result.status){
                        Swal.fire({
                            icon: "error",
                            title: 'Se presentó un error al procesar la solicitud',
                            text: result.message,
                        })
                    }else{
                        Swal.fire('Solicitud cambió a Validación de Montos exitosamente').then(() => {
                            window.location.reload();
                        });
                    }
                }).finally(() => {
                    setLoading(false);
                })
            }
        });
    }

    return (
        <>
            <Loader show={loading}/>
            <div id="detalle-referencia-component">
                <div className="row mt-20">
                    <div className="col-md-12 mb-20">
                        <div className="row">
                            <div className="col-md-4">
                                <p className="mb-5"><strong>Solicitud</strong></p>
                                <p className="mb-5">No. de Solicitud: <strong>{solicitud.noSolicitud}</strong></p>
                                <p className="mb-5 solicitud__status"><strong className={'solicitud__status solicitud__status_black'}>Estado de la Solicitud:</strong> {solicitud.status}</p>
                                <p className="mb-5">Fecha de registro: <strong>{solicitud.fecha}</strong></p>
                                <p className="mb-5">Tipo de pago: <strong>{solicitud.tipoPago}</strong></p>
                                <p className="mb-20">Monto total de la
                                    solicitud: <strong>{solicitud.monto ? moneyFormat(solicitud.monto) : 'Montos no registrados'}</strong>
                                </p>
                            </div>
                            <div className="col-md-4">
                                <p className="mb-5"><strong>Institución</strong></p>
                                <p className="mb-5">
                                    Nombre: <strong>{solicitud.institucion.nombre}</strong>
                                </p>
                                <p className="mb-5">RFC: <strong>{solicitud.institucion.rfc}</strong></p>
                                <p className="mb-5">{solicitud.esUmae ? 'UMAE' : 'OOAD'}
                                    : <strong>{solicitud.unidad}</strong></p>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6 mt-10">
                                <p>Referencia Bancaria: <strong>{solicitud.referencia}</strong></p>
                            </div>
                            <div className="col-md-6">
                            </div>
                        </div>
                    </div>
                    <div className="container container_content">
                        <div className="row">
                            <div className="col-md-6 mt-20 mb-10"><h2>Acciones</h2></div>
                        </div>
                        <div className="row">
                            <div className="col-md-3">
                                <button
                                    onClick={handleCancelSolicitud}
                                    className="btn-danger btn-block">Cancelar</button>
                            </div>
                            <div className="col-md-3">
                                <button
                                    onClick={handleChangeStatusValidacionMontos}
                                    className="btn-secondary btn-block">Cambiar estado a validación de montos</button>
                            </div>
                        </div>
                    </div>
                    <div className="container container_content">
                        <div className="row">
                            <div className="col-md-6 mt-20 mb-10"><h2>Campos Clínicos</h2></div>
                        </div>
                        <div className="table-responsive">
                            <table className="table table-striped">
                                <thead>
                                <tr>
                                    <th>Campo Clínico</th>
                                    <th>Carrera</th>
                                    <th>Sede</th>
                                    <th>Período</th>
                                    <th>No. de lugares Solicitados</th>
                                    <th>No. de lugares autorizados</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {
                                    solicitud.camposClinicos.map((cc) => {
                                        return (
                                            <tr key={cc.id}>
                                                <td>{cc.tipo}</td>
                                                <td>
                                                    {cc.carrera}
                                                </td>
                                                <td>{cc.unidad}</td>
                                                <td>Inicio {cc.fechaInicial}
                                                    <br/> Final {cc.fechaFinal}<br/>Horario: {cc.horario}</td>
                                                <td>{cc.lugaresSolicitados}<br/></td>
                                                <td>{cc.lugaresAutorizados}<br/></td>
                                                <td>
                                                    {cc.hasFile ?
                                                    <a
                                                    onClick={() => handleShowFormatoFOFOE(cc.id)}
                                                    target="_blank">Formato FOFOE</a> : <span>No se ha cargado</span>}<br/>
                                                </td>
                                                <td>
                                                    <form
                                                        onSubmit={(event) => handleUploadFormatoFofoe(event, cc.id)}
                                                        encType="multipart/form-data">
                                                        <input type="file" name="comprobante" accept="application/pdf"/>
                                                        <button type="submit">Reemplazar Formato FOFOE</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        )
                                    })
                                }
                                </tbody>
                            </table>
                        </div>
                        <div className="table-responsive">
                            <table className="table">
                                <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>Archivo</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Oficio de Montos de Colegiatura, Inscripción y Listado de Alumnos</td>
                                    <td>30-03-2026</td>
                                    <td>
                                        {solicitud.hasFile ? <a
                                        onClick={handleShowOficioMontos}
                                        target="_blank">Ver</a> : <span>No se ha cargado</span>}
                                        </td>
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
                        </div>
                    </div>
                </div>
            </div>
            <div className="row">
                <div className="col-md-4">
                    <a className="btn btn-default btn-block"
                       href={getSchemeAndHttpHost(`/fofoe/solicitud`)}
                    > Regresar a la lista de
                        pagos recibidos </a>
                </div>
            </div>
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
    const rootElement = document.querySelector('.react');
    if(rootElement){
        const root = createRoot(rootElement);
        root.render(
            <ShowPage solicitud={window.Solicitud}/>
        );
    }
});
