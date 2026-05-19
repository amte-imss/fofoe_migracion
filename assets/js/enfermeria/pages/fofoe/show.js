import React from "react";
import ReactDOM from "react-dom";
import Loader from "../../../components/Loader/Loader";
import Swal from 'sweetalert2'
import 'sweetalert2/src/sweetalert2.scss'
import {getSchemeAndHttpHost} from "../../../utils";
import api from '../../api';
import Popup from "../../../components/Popup/Popup";
import "./show.css";


const numberFormat = new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})

function ShowPage({pago}) {

    const [isLoading, setIsLoading] = React.useState(false);
    const [showPopup, setShowPopup] = React.useState(false);
    const [showRechazoPopup, setShowRechazoPopup] = React.useState(false);

    const handleAceptarPago = () => {
        Swal.fire(
            {
                title: '¿Está seguro de validar el pago?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
            }
        ).then((result) => {
            if (result.isConfirmed) {
                setIsLoading(true);
                api.setStatusPago(pago.id, 1).then(() => {
                    Swal.fire(
                        {
                            title: 'Pago actualizado con éxito',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                        }
                    ).then(() => {
                        window.location.href = '/';
                    })
                }).finally(() => {
                    setIsLoading(false)
                })
            }
        })
    }


    const handleUploadFactura = (event) => {
        event.preventDefault();
        setIsLoading(true);
        const formData = new FormData(event.target);
        formData.append('factura[monto]', pago.monto);
        api.uploadFactura(pago.id, formData).then(() => {
            Swal.fire(
                {
                    title: 'Factura guardada con éxito',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                }
            ).then(() => {
                window.location.href = getSchemeAndHttpHost();
            })
        }).finally(() => {
            setIsLoading(false);
        });
    }

    const handleRechazarPago = (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);
        setIsLoading(true);
        api.setStatusPago(pago.id, 0, formData).then(() => {
            Swal.fire(
                {
                    title: 'Pago actualizado con éxito',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                }
            ).then(() => {
                window.location.href = getSchemeAndHttpHost();
            })
        }).finally(() => {
            setIsLoading(false)
        })
    }

    return (
        <>
            <Loader show={isLoading}/>
            <Popup show={showPopup}
                   title={'Subir Factura'}
                   body={<FormFactura
                       onSubmit={handleUploadFactura}
                       onClose={() => setShowPopup(false)}
                   />}
                   footer={<></>}
                   closePopup={() => {
                       setShowPopup(false)
                   }}
            />

            <Popup show={showRechazoPopup}
                   title={'¿Está seguro de no validar el pago?'}
                   footer={<></>}
                   body={<FormRechazo
                       onSubmit={handleRechazarPago}
                       onClose={() => setShowRechazoPopup(false)}
                   />}
                   closePopup={() => {
                       setShowRechazoPopup(false)
                   }}
            />

            <div className="col-md-12">
                <div id="detalle-referencia-component">
                    <div className="row mt-20">
                        <div className="col-md-12 mb-20">
                            <div className="row">
                                <div className="col-md-4"><p className="mb-5"><strong>Solicitud</strong></p><p
                                    className="mb-5">No. de
                                    Solicitud: <strong>{pago.escuelaEnfermeriaSolicitud.idFormatted}</strong></p>
                                    <p className="mb-5">Fecha de registro: <strong>{pago.fechaPagoFormatted}</strong>
                                    </p>
                                    <p className="mb-5">Tipo de pago: <strong>Único</strong></p>
                                    <p className="mb-5">Monto total de la
                                        solicitud: <strong>$ {numberFormat.format(pago.monto)}</strong></p>
                                    <p className="mb-5">Requiere
                                        factura: <strong> {pago.requiereFactura ? 'Si' : 'No'}</strong></p>
                                    {pago.requiereFactura && <p className="mb-20">
                                        <a
                                            target={'_blank'}
                                            href={getSchemeAndHttpHost(`/enfermeria-alumno/cedula-fiscal/${pago.escuelaEnfermeriaSolicitud.id}`)}>
                                            Ver cedula de identificación fiscal
                                        </a>
                                    </p>}
                                </div>
                                <div className="col-md-4">
                                    <p className="mb-5">
                                        <strong>{pago.escuelaEnfermeriaSolicitud.solicitud.unidad.nombreEnfermeria}</strong>
                                    </p>
                                    <p className="mb-5">Nombre: <strong>{pago.escuelaEnfermeriaSolicitud.nombre}</strong>
                                    </p>
                                    <p className="mb-5">Email: <strong>{pago.escuelaEnfermeriaSolicitud?.email}</strong>
                                    </p>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-6 mt-10">
                                    <p>Referencia Bancaria: <strong>{pago.referenciaBancaria}</strong></p>
                                    <p><strong>Estado
                                        de la referencia:</strong> {pago.statusFormatted}</p>
                                    {pago.escuelaEnfermeriaSolicitud.status === 'invoice_pending' &&
                                        <button className={'btn btn-primary mt-20'} onClick={() => {
                                            setShowPopup(true)
                                        }}>Subir Factura</button>
                                    }
                                </div>
                                {pago.validado == null && <div className="col-md-6">
                                    <strong>Acción </strong>
                                    <div className="row">
                                        <button
                                            onClick={handleAceptarPago}
                                            className="btn btn-block btn-primary">Aceptar Pago
                                        </button>
                                    </div>
                                    <br/>
                                    <div className="row">
                                        <button
                                            onClick={() => setShowRechazoPopup(true)}
                                            className="btn btn-block btn-primary"
                                            style={{backgroundColor: 'green'}}>Rechazar Pago
                                        </button>
                                    </div>
                                </div>}

                            </div>
                        </div>
                        <div className="col-md-12 mb-20">
                            <table className="table table-condensed">
                                <thead>
                                <tr>
                                    <th>Comprobante registrado</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Observaciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><a href={getSchemeAndHttpHost(`/fofoe/pagos/${pago.id}/descargar-comprobante-de-pago`)}
                                           download="">Descargar</a>
                                    </td>
                                    <td>{pago.fechaPagoFormatted}</td>
                                    <td>{numberFormat.format(pago.montoRegistrado)} {pago.tipoMoneda}</td>
                                    <td>{pago.observaciones}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div className="row">
                            <div className="col-md-12"><p className="mt-10 mb-10"><strong>Facturas generadas</strong>
                            </p>
                                <table className="table">
                                    <thead className="headers">
                                    <tr>
                                        <th>Fecha Facturación</th>
                                        <th>Monto Facturado</th>
                                        <th>Archivo Factura</th>
                                        <th>Folio Factura</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {!pago.factura &&
                                        <tr>
                                            <td className="text-center text-info" colSpan="4"><strong>No hay registros
                                                disponibles</strong></td>
                                        </tr>
                                    }
                                    {pago.factura &&
                                        <tr>
                                            <td>{pago.factura.fechaFacturacionFormatted}</td>
                                            <td>{pago.factura.monto}</td>
                                            <td><a target={'_blank'}
                                                   href={getSchemeAndHttpHost(`/fofoe/pagos/${pago.factura.id}/factura`)}>Ver
                                                Factura</a></td>
                                            <td>{pago.factura.folio}</td>
                                        </tr>
                                    }
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )

}


const FormFactura = ({onClose, onSubmit}) => {

    const [fileName, setFileName] = React.useState('');

    const handleChangeFile = (event) => {
        setFileName(event.target.files[0].name);
    }

    return (
        <>
            <form onSubmit={onSubmit}>
                <div className="form-group">
                    <label htmlFor="folioFactura">Folio de la factura</label>
                    <input type="text"
                           required
                           defaultValue={fileName}
                           readOnly={true}
                           name={'factura[folio]'}
                           className="form-control"
                           id="folioFactura" placeholder="Folio de la factura"/>
                </div>
                <div className="form-group">
                    <label htmlFor="fechaFactura">Fecha de la factura</label>
                    <input type="date"
                           className="form-control" id="fechaFactura"
                           required
                           name={'factura[fechaFacturacion]'}
                           placeholder="Fecha de la factura"/>
                </div>
                <div className="form-group">
                    <label htmlFor="archivoFactura">Archivo de la factura</label>
                    <input type="file"
                           accept=".zip"
                           required
                           onChange={handleChangeFile}
                           name={'factura[zipFile]'}
                           className="form-control-file" id="archivoFactura"/>
                    <span className='text-danger text-sm'>Solo se permiten archivos .zip</span>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <button type="submit" className="btn btn-primary btn-block">Enviar</button>
                    </div>
                    <div className="col-md-6">
                        <button type="button" className="btn btn-danger btn-block" onClick={onClose}>Cancelar</button>
                    </div>
                </div>

            </form>
        </>
    )
}

const FormRechazo =  ({onClose, onSubmit}) => {

    const [showDiferencia, setShowDiferencia] = React.useState(false);

    return (
        <>
            <form onSubmit={onSubmit}>
                <div className="form-group">
                    <label htmlFor="motivoField">Motivo del Rechazo</label>
                    <textarea
                           required
                           name={'observaciones'}
                           className="form-control"
                           id="motivoField" placeholder="Motivo del Rechazo"></textarea>
                </div>

                <div className="form-group">
                    <label htmlFor="has_diferencia" className="form-check-label">
                        <input type="checkbox"
                               className="form-control" id="has_diferencia"
                               onChange={(e) => setShowDiferencia(e.target.checked)}
                               name={'has_diferencia'}
                               placeholder="Fecha de la factura"/>
                        Indicar monto de diferencia
                    </label>
                </div>

                {showDiferencia &&
                <div className="form-group">
                    <label htmlFor="montoDiferenciaField">Monto Diferencia</label>
                    <input type="number"
                           step="0.01"
                           min={.01}
                           className="form-control" id="montoDiferenciaField"
                           required
                           name={'monto_diferencia'}
                           placeholder="999.99"/>
                </div>}

                <div className="row">
                    <div className="col-md-6">
                        <button type="submit" className="btn btn-primary btn-block">Enviar</button>
                    </div>
                    <div className="col-md-6">
                        <button type="button" className="btn btn-danger btn-block" onClick={onClose}>Cancelar</button>
                    </div>
                </div>

            </form>
        </>
    )
}

document.addEventListener('DOMContentLoaded', () => {
    ReactDOM.render(
        <ShowPage pago={window.Pago}/>,
        document.getElementById('wrapper-page')
    )
})