import * as React from "react";
import Swal from "sweetalert2";
import Cleave from "cleave.js/react";
import {getSchemeAndHttpHost, moneyFormat} from "../../../utils";
import {TIPO_PAGO} from "../../../constants";
import {validateFormatoFofoe, validateOficioMontos} from "../api";

const SI_ES_PAGO_CORRECTO_DEFAULT = 1
const NO_ES_PAGO_CORRECTO_DEFAULT = 0

function transformStatusFormatoFofoe(campos) {
    const status = {};
    campos.forEach(campos => {
        status['campo_' + campos.id] = campos.validateFormatoFofoe;
    })
    return status;
}

const ValidacionDePagoCC = ({pago, campos}) => {
    const {useState, useRef} = React
    const [monto, setMonto] = useState(pago.monto)
    const [isPagoValidado, setPagoValidado] = useState(true)
    const [showSelectValidateOficioMontos, setShowSelectValidateOficioMontos] = useState(pago && pago.solicitud && pago.solicitud.validateOficioMontos == null)
    const [validateOficioMontosText, setValidateOficioMontosText] = useState(pago && pago.solicitud && pago.solicitud.validateOficioMontos != null ? (pago.solicitud.validateOficioMontos == 0 ? 'NO' : 'SI') : '')
    const [statusFormatoFofoe, setStatusFormatoFofoe] = useState(transformStatusFormatoFofoe(campos));
    const [showInputMonto, setShowInputMonto] = useState(true);
    const [rawMonto, setRawMonto] = useState(moneyFormat(pago.montoPendienteValidar));
    const formRef = useRef(null)


    const updateStatusFormatoFofoe = (campoId, status) => {
        setStatusFormatoFofoe(prevStatus => ({
            ...prevStatus,
            ['campo_' + campoId]: status
        }));
    }

    const formatosFofoeValidados = () => {
        return Object.values(statusFormatoFofoe).every(status => status === 1);
    }

    const formatosFofoeNoValidados = () => {
        return Object.values(statusFormatoFofoe).some(status => status === 0);
    }

    const formatosFofoeSinValidar = () => {
        return Object.values(statusFormatoFofoe).some(status => status === null);
    }

    function isPagoMultiple() {
        return pago.solicitud.tipoPago === TIPO_PAGO.MULTIPLE;
    }

    function handleMonto({target}) {
        setMonto(target.rawValue)
    }

    function getMontoTotalTitle() {
        return isPagoMultiple() ?
            'Monto total del campo clínico:' :
            'Monto total de la solicitud:';
    }

    function handlePagoValidado({target}) {
        setPagoValidado(parseInt(target.value) === SI_ES_PAGO_CORRECTO_DEFAULT)
        if (parseInt(target.value) === SI_ES_PAGO_CORRECTO_DEFAULT) {
            setShowInputMonto(false);
        } else {
            setShowInputMonto(true);
        }
    }

    function handleValidacionDePago(event) {
        event.preventDefault();

        Swal.fire({
            title: '¿Estás seguro de continuar?',
            text: 'El monto debe de coincidir con el del comprobante cargado por la institución educativa',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Si, estoy seguro!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                formRef.current.submit();
            }
        })
    }

    function handleValidateOficioMontos(event) {
        const value = parseInt(event.target.value);
        const selectNode = event.target;

        const configSwal = {
            title: '¿Estás seguro de continuar?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Si, estoy seguro!',
            cancelButtonText: 'Cancelar'
        }
        if (value === 0) {
            configSwal['input'] = 'textarea';
            configSwal['inputPlaceholder'] = 'Escribe el motivo por el cual no es válido el formato FOFOE';
            configSwal['inputValidator'] = (value) => {
                return !value && 'El motivo es requerido';
            };
        }

        if ([0, 1].includes(value)) {
            Swal.fire(configSwal).then(function (result) {
                if (result.isConfirmed) {
                    validateOficioMontos(pago.solicitud.id, value, result.value).then(data => {
                        if (data.status) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'El oficio de montos ha sido validado correctamente',
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            });
                            setShowSelectValidateOficioMontos(false);
                            setValidateOficioMontosText(value === 1 ? 'SI' : 'NO');
                        } else {
                            Swal.fire({
                                title: '¡Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#d33'
                            });
                            selectNode.value = ''
                        }
                    })
                } else {
                    selectNode.value = ''
                }
            })
        }
    }

    return (
        <div className='row mt-20'>
            <div className="col-md-12 mb-20">
                <div className="row">
                    <div className="col-md-4">
                        <p className='mb-5'><strong>Solicitud</strong></p>
                        <p className='mb-5'>No. de Solicitud: <strong>{pago.solicitud.noSolicitud}</strong></p>
                        <p className='mb-5'>OOAD: <strong>{pago.institucion.delegacion}</strong></p>
                        {
                            pago.solicitud.esUMAE ?
                                <p className='mb-5'>UMAE: <strong>{pago.solicitud.nombreUnidad}</strong></p>
                                : null
                        }
                        <p className='mb-5'>Tipo de pago: <strong>{pago.solicitud.tipoPago}</strong></p>
                        <p className='mb-20'>{getMontoTotalTitle()} <strong>{moneyFormat(pago.montoTotal)}</strong></p>
                    </div>
                    {
                        isPagoMultiple() &&
                        <div className="col-md-4">
                            <p className='mb-5'><strong>Campo clínico</strong></p>
                            <p className='mb-5'>Sede: <strong>{pago.solicitud.campoClinico.sede}</strong></p>
                            <p className='mb-5'>Carrera: <strong>{pago.solicitud.campoClinico.carrera}</strong>
                                {pago.solicitud.campoClinico.asignatura ?
                                    <>
                                        <br/>
                                        Asignatura: <strong>{pago.solicitud.campoClinico.asignatura} </strong>
                                    </>
                                    : null
                                }
                                <br/></p>
                        </div>
                    }
                    <div className="col-md-4">
                        <p className='mb-5'><strong>Institución</strong></p>
                        <p className='mb-5'>Nombre: <strong><a
                            href={`${getSchemeAndHttpHost()}/fofoe/detalle-ie/${pago.institucion.id}`}>{pago.institucion.nombre}</a></strong>
                        </p>
                        <p className='mb-5'>Razón Social: <strong>{pago.institucion.razonSocial}</strong></p>
                        <p className='mb-5'>RFC: <strong>{pago.institucion.rfc}</strong></p>
                    </div>
                </div>
            </div>
            <div className="col-md-12 mb-20">
                <div className="row">
                    <div className="col-md-4">
                        <a href={`${getSchemeAndHttpHost()}/fofoe/referencia/${pago.id}/expediente/download`}
                           className='btn btn-success btn-block'
                           target={'_blank'}>Descargar expediente completo</a>
                    </div>
                </div>
            </div>
            <div className="col-md-12 mb-20">
                <div className="row">
                    <div className="col-md-12">
                        <table className='table table-condensed'>
                            <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Valido</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td><strong><a target={'_blank'}
                                               href={`${getSchemeAndHttpHost()}/fofoe/solicitud/${pago.solicitud.id}/oficio`}>Oficio
                                    de Montos de Colegiatura, Inscripción y Listado de Alumnos</a></strong></td>
                                {showSelectValidateOficioMontos ?
                                    <td>
                                        <form>
                                            <select className={'form-control'}
                                                    onChange={handleValidateOficioMontos}
                                                    name="validate_oficio_montos" id="validate_oficio_montos">
                                                <option value="">Selecciona una opción</option>
                                                <option value="1">Si</option>
                                                <option value="0">No</option>
                                            </select>
                                        </form>
                                    </td>
                                    : <td className='text-success'>{validateOficioMontosText}</td>}
                            </tr>
                            {campos.map((campo, index) => {
                                return <FormatoFofoeValidacion
                                    updateStatusFormatoFofoe={updateStatusFormatoFofoe}
                                    key={index}
                                    campo={campo}/>
                            })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div className="col-md-12 mb-20">
                <table className='table table-condensed'>
                    <thead>
                    <tr>
                        <th>No de referencia</th>
                        <th>Comprobante registrado</th>
                        <th>Fecha</th>
                        <th>Monto validado</th>
                    </tr>
                    </thead>
                    <tbody>
                    {
                        pago.historial.length !== 0 ?
                            pago.historial.map((pago, index) =>
                                <tr key={index}>
                                    <td>{pago.referenciaBancaria}</td>
                                    <td><a
                                        href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/descargar-comprobante-de-pago`}
                                        download>Descargar</a></td>
                                    <td>{pago.fechaPago}</td>
                                    <td>{moneyFormat(pago.monto)}</td>
                                </tr>
                            ) :
                            <tr>
                                <td
                                    className='text-center text-info'
                                    colSpan={4}
                                >
                                    Aún no se ha validado ningún comprobante de pago
                                </td>
                            </tr>
                    }
                    </tbody>
                </table>
            </div>
            <div className="col-md-12">
                <h3 className='mb-20'>Validar comprobante de pago</h3>
                <p className='mb-5'>Referencia bancaria: <strong>{pago.referenciaBancaria}</strong></p>
                <p className='mb-5'>Monto pendiente a
                    validar: <strong>{moneyFormat(pago.montoPendienteValidar)}</strong></p>
                <p className='mb-5'>Comprobante de pago a validar:&nbsp;&nbsp;
                    <a
                        href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/descargar-comprobante-de-pago`}
                        download
                    >
                        Descargar
                    </a>
                </p>
                <p className='mb-20'>Factura: <strong>{pago.requiereFactura ? 'Solicitada' : 'No solicitada'}</strong>
                </p>
                {validateOficioMontosText === 'SI' && formatosFofoeValidados() &&
                    <form
                        action={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/validacion-de-pago`}
                        method='post'
                        className='form-horizontal'
                        encType='multipart/form-data'
                        ref={formRef}
                        onSubmit={handleValidacionDePago}
                    >
                        <div className="form-group">
                            <label
                                htmlFor="validacion_pago_fechaPago"
                                className='control-label col-md-4'
                            >
                                Fecha en que se realizó el nuevo pago:
                            </label>
                            <div className="col-md-3">
                                <input
                                    type="date"
                                    id='validacion_pago_fechaPago'
                                    className='form-control'
                                    name='validacion_pago[fechaPago]'
                                    required={true}
                                    defaultValue={pago.fechaPago}
                                />
                            </div>
                        </div>
                        <div className="form-group">
                            <label
                                htmlFor='validacion_pago_requiere_factura'
                                className="control-label col-md-4 text-right"
                            >
                                ¿El pago es correcto?&nbsp;
                            </label>
                            <div className="col-md-3">
                                <label htmlFor='validacion_pago_validado_yes'>Si&nbsp;</label>
                                <input
                                    type="radio"
                                    value={SI_ES_PAGO_CORRECTO_DEFAULT}
                                    id='validacion_pago_validado_yes'
                                    name='validacion_pago[validado]'
                                    required={true}
                                    onChange={handlePagoValidado}
                                />
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <label htmlFor="validacion_pago_validado_no">No&nbsp;</label>
                                <input
                                    type="radio"
                                    value={NO_ES_PAGO_CORRECTO_DEFAULT}
                                    id='validacion_pago_validado_no'
                                    name='validacion_pago[validado]'
                                    required={true}
                                    onChange={handlePagoValidado}
                                />
                            </div>
                        </div>
                        <div className="form-group">
                            <label
                                htmlFor="validacion_pago_monto"
                                className='control-label col-md-4'
                            >
                                Monto del comprobante a registrar:<br/>
                                <span className='text-danger text-sm'>NOTA: El monto debe coincidir con el comprobante registrado</span>
                            </label>
                            <div className="col-md-3">
                                <div className={`input-group`}>
                                    {showInputMonto &&
                                        <>
                                            <div className="input-group-addon">$</div>
                                            <Cleave
                                                options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                                                className='form-control'
                                                required={true}
                                                onChange={handleMonto}
                                            />
                                        </>}
                                    <input
                                        type="hidden"
                                        id='validacion_pago_monto'
                                        name='validacion_pago[monto]'
                                        value={monto}
                                    />
                                </div>
                                {!showInputMonto &&
                                    <div className="input-group">
                                        <div className="input-group-addon">$</div>
                                        <input type="text"
                                               readOnly={true}
                                               className="form-control" required="" value={rawMonto}/>
                                    </div>}
                            </div>
                        </div>
                        {
                            !isPagoValidado &&
                            <div className="form-group">
                                <label
                                    htmlFor="validacion_pago_observaciones"
                                    className='control-label col-md-4'
                                >
                                    Observaciones
                                </label>
                                <div className="col-md-5">
              <textarea
                  rows={7}
                  className='form-control'
                  id='validacion_pago_observaciones'
                  name='validacion_pago[observaciones]'
                  required={true}
              />
                                </div>
                            </div>
                        }
                        <div className="row mt-30">
                            <div className="col-md-4"/>
                            <div className="col-md-2">
                                <a
                                    href={`${getSchemeAndHttpHost()}/fofoe/inicio`}
                                    className='btn btn-default btn-block'
                                >
                                    Cancelar
                                </a>
                            </div>
                            <div className="col-md-2">
                                <button
                                    type='submit'
                                    className='btn btn-success btn-block'>
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                }
                {
                    (validateOficioMontosText === 'NO') &&
                    <p><strong>SI EL OFICIO DE MONTOS NO ES VALIDO NO ES POSIBLE VALIDAR EL PAGO</strong></p>

                }
                {
                    (validateOficioMontosText === '') &&
                    <p><strong>SE REQUIRE VALIDAR EL OFICIO DE MONTOS PARA PODER VALIDAR UN PAGO</strong></p>

                }
                {
                    formatosFofoeSinValidar() &&
                    <p><strong>SE REQUIRE VALIDAR TODOS LOS FORMATOS FOFOE PARA PODER VALIDAR UN PAGO</strong></p>
                }

                {
                    formatosFofoeNoValidados() &&
                    <p><strong>SI ALGUNO DE LOS FORMATOS FOFOE NO ES VALIDO NO ES POSIBLE VALIDAR EL PAGO</strong></p>
                }
            </div>
        </div>
    )
}

const FormatoFofoeValidacion = ({campo, updateStatusFormatoFofoe}) => {

    const {useState, useRef} = React

    const [showSelectValidateFormatoFofoe, setShowSelectValidateFormatoFofoe] = useState(campo.validateFormatoFofoe == null)
    const [validateFormatoFofoeText, setValidateFormatoFofoeText] = useState(campo.validateFormatoFofoe != null ? (campo.validateFormatoFofoe == 0 ? 'NO' : 'SI') : '')

    function handleValidateFormatoFofoe(event) {
        const value = parseInt(event.target.value);
        const selectNode = event.target;
        const configSwal = {
            title: '¿Estás seguro de continuar?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '¡Si, estoy seguro!',
            cancelButtonText: 'Cancelar'
        };
        if (value === 0) {
            configSwal['input'] = 'textarea';
            configSwal['inputPlaceholder'] = 'Escribe el motivo por el cual no es válido el formato FOFOE';
            configSwal['inputValidator'] = (value) => {
                return !value && 'El motivo es requerido';
            };
        }
        if ([0, 1].includes(value)) {
            Swal.fire(configSwal).then(function (result) {
                if (result.isConfirmed) {
                    validateFormatoFofoe(campo.id, value, result.value).then(data => {
                        if (data.status) {
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'El oficio de montos ha sido validado correctamente',
                                icon: 'success',
                                confirmButtonColor: '#3085d6'
                            });
                            setShowSelectValidateFormatoFofoe(false);
                            setValidateFormatoFofoeText(value === 1 ? 'SI' : 'NO');
                            updateStatusFormatoFofoe(campo.id, value);
                        } else {
                            Swal.fire({
                                title: '¡Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#d33'
                            });
                            selectNode.value = ''
                        }
                    })
                } else {
                    selectNode.value = ''
                }
            })
        }
    }

    return (
        <tr>
            <td className='mb-5'><strong><a target={'_blank'}
                                            href={`${getSchemeAndHttpHost()}/fofoe/campo_clinico/${campo.id}/formato_fofoe_firmado/download`}>Formato
                FOFOE <br/> {campo.displayFormatted}</a></strong></td>
            <td>
                {showSelectValidateFormatoFofoe ?
                    <form>
                        <select className={'form-control'}
                                onChange={handleValidateFormatoFofoe}
                                name="validate_formato_fofoe"
                                id="validate_formato_fofoe">
                            <option value="">Selecciona una opción</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </form>
                    : <p className='text-success'>{validateFormatoFofoeText}</p>}
            </td>
        </tr>
    )
}

export default ValidacionDePagoCC;