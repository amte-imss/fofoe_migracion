import * as React from 'react'
import Loader from "../../components/Loader/Loader";
import './show.scss';
import {checkInputFile, getSchemeAndHttpHost} from "../../utils";
import {SOLICITUD} from "../../constants";
import Swal from "sweetalert2";
import {uploadOficioMontos} from "./api";

const LinkCredenciales = (props) => {
    const status = [4, 7];
    if (props.campoClinico.estatus && status.indexOf(props.campoClinico.estatus.id) > -1) {
        return (
            <a href={`${getSchemeAndHttpHost()}/formato/campo_clinico/${props.campoClinico.id}/credenciales/download`}
               target={'_blank'}>Credenciales</a>);
    }
    return (<></>);
}

const LinkFormatoFofoe = (props) => {
    const status = [2, 3, 4, 5, 6, 7];
    const urlDownload = props.campoClinico.formatoFofoeFileName ?
        `${getSchemeAndHttpHost()}/came/campo_clinico/${props.campoClinico.id}/formato_fofoe_firmado/download`
        : `${getSchemeAndHttpHost()}/formato/campo_clinico/${props.campoClinico.id}/formato_fofoe/download`

    if ((props.campoClinico.lugaresAutorizados > 0) && ((props.campoClinico.estatus && status.indexOf(props.campoClinico.estatus.id) > -1) || props.showFormatoFofoe)) {
        return (<a href={urlDownload} target={'_blank'}>Formato
            FOFOE {props.campoClinico.formatoFofoeFileName ? 'firmado' : 'para firma'} </a>);
    }
    return (<></>);
}

const UploadFormatoFofoe = ({campoClinico, refreshCampos, showForce = false}) => {
    const actionForm = `${getSchemeAndHttpHost()}/came/api/campo_clinico/${campoClinico.id}/formato_fofoe/upload`
    const [selectedFile, setSelectedFile] = React.useState(null);
    const [fileValid, setFileValid] = React.useState(false);
    const [erroresUpload, setErroresUpload] = React.useState({formatoFofoeFile: ''});
    const [isLoading, setIsLoading] = React.useState(false)
    const [fileUploaded, setFileUploadad] = showForce ? React.useState(false) : React.useState(!!campoClinico.formatoFofoeFileName)

    const handleSubmit = (e) => {
        e.preventDefault()
        setIsLoading(true)
        let data = new FormData();
        data.append('formato_fofoe_file[formatoFofoeFile]', selectedFile);
        fetch(actionForm, {
            method: 'POST',
            body: data
        }).then(res => {
            return res.json()
        }).then(json => {
            if (json.errors) {
                setErroresUpload(json.errors)
            } else {
                setFileUploadad(true)
                refreshCampos()
            }
        }).catch(error => {
            console.error(error)
        }).finally(() => {
            setIsLoading(false)
        })
    }

    return (
        !fileUploaded && campoClinico.lugaresAutorizados > 0 ?
            <div className='form-group'>
                {isLoading ? <Loader/> :
                    <form
                        action={actionForm}
                        method='POST'
                        onSubmit={handleSubmit}
                        encType='multipart/form-data'
                    >
                        <input
                            className='form-control-file'
                            name='formato_fofoe_file[formatoFofoeFile]'
                            type='file'
                            onChange={(e) => {
                                setFileValid(false);
                                setSelectedFile(null);
                                checkInputFile(e.target, {size: 2097152 }, () => {
                                    setSelectedFile(e.target.files[0]);
                                    setFileValid(true);
                                });
                            }}
                            required={true}
                        /><br/>
                        <span
                            className="help-block">{erroresUpload.formatoFofoeFile ? erroresUpload.formatoFofoeFile[0] : ''}</span>
                        <span
                            className="help-block">{erroresUpload.formato_fofoe_file ? erroresUpload.formato_fofoe_file[0] : ''}</span>
                        <button
                            type='submit'
                            disabled={!fileValid}
                            className='btn btn-sm btn-success'>
                            Cargar archivo firmado
                        </button>
                    </form>
                }
            </div>
            : null
    );
}

const ComprobanteOficio = (props) => {
    if (props.solicitud.fechaComprobante)
        return (<a href={`${getSchemeAndHttpHost()}/came/solicitud/${props.solicitud.id}/oficio`}
                   target={'_blank'}>Descargar</a>);
    return (<></>);
}

const LinkAllFormatosFofoe = (props) => {
    const statusAllowed = [SOLICITUD.MONTOS_VALIDADOS_CAME, SOLICITUD.MONTOS_VALIDADOS,
        SOLICITUD.CARGANDO_COMPROBANTES, SOLICITUD.EN_VALIDACION_FOFOE, SOLICITUD.CREDENCIALES_GENERADAS];
    const urlDownload = `${getSchemeAndHttpHost()}/came/solicitud/${props.solicitud.id}/descargar-formatos-fofoe`


    return (
        statusAllowed.indexOf(props.solicitud.estatus) > -1 ?
            <a href={urlDownload} target={'_blank'}>
                Descargar
            </a>
            : null
    );
}

const LinkPago = (props) => {
    if (props.pago && props.pago.comprobantePago)
        return (
            <div>
                <a href={`${getSchemeAndHttpHost()}/pago/${props.pago.id}/download`} target={'_blank'}>Comprobante de
                    pago</a>
                <p>{props.pago.validado === null ? 'En validación' :
                    (props.showFecha ? props.pago.fechaPagoFormatted : '')}</p>
            </div>
        )
    return (<></>);
}

const LinkFactura = (props) => {
    if (props.factura)
        return (
            <a href={`${getSchemeAndHttpHost()}/factura/${props.factura.id}/download`} target={'_blank'}>Factura</a>)
    return (<></>);
}

const searchPago = (pagos, campo_clinico) => {
    const results = pagos.filter(item => {
        return campo_clinico.referenciaBancaria && campo_clinico.referenciaBancaria.toString() === item.referenciaBancaria.toString();
    });
    if (results.length > 0) {
        return results[0];
    }
    return null;
}

const DetalleSolicitudDetallado = (props) => {
    const [isLoading, setIsLoading] = React.useState(false)
    const [camposClinicos, setCamposClinicos] = React.useState(props.solicitud.campoClinicos);
    const [query, setQuery] = React.useState({});
    const formatosCargados = camposClinicos.reduce((acc, item) =>
            acc && (item.lugaresAutorizados <= 0 || !!item.formatoFofoeFileName)
        , true);

    const handleSearchEvent = (query) => {
        setIsLoading(true);
        let querystring = '';
        for (const i in query) {
            querystring += `${i}=${query[i]}&`;
        }

        fetch(`${getSchemeAndHttpHost()}/came/api/solicitud/${props.solicitud.id}/campos_clinicos?${querystring}`)
            .then(response => {
                return response.json()
            }, error => {
                console.error(error)
            })
            .then(json => {
                setCamposClinicos(json.data)
            })
            .finally(() => {
                setIsLoading(false);
            });

    }

    const estatusSinCargarFormato = [SOLICITUD.CREADA, SOLICITUD.REGISTRADA,
        SOLICITUD.CONFIRMADA, SOLICITUD.EN_VALIDACION_DE_MONTOS_CAME,
        SOLICITUD.MONTOS_INCORRECTOS_CAME
    ]

    return (
        <><Loader show={isLoading}/>
            <div className="container">
                {!formatosCargados && !estatusSinCargarFormato.includes(props.solicitud.estatus) ?
                    <h4 className='text-danger'>
                        <strong>
                            Recuerda que el formato FOFOE con ambas firmas originales debe ser cargado al sistema.
                        </strong>
                    </h4>
                    : null
                }
            </div>
            <div className="table-responsive">
                <table className="table table-striped">
                    <thead>
                    <tr>
                        <th>Sede <br/> <input type="text" placeholder={'Sede'}
                                              onChange={e => {
                                                  setQuery(Object.assign(query, {unidad: e.target.value}));
                                                  handleSearchEvent()
                                              }}/></th>
                        <th>Campo Clínico <br/> <input type="text" placeholder={'Campo Clínico'}
                                                       onChange={e => {
                                                           setQuery(Object.assign(query, {cicloAcademico: e.target.value}));
                                                           handleSearchEvent()
                                                       }}/></th>
                        <th>Nivel <br/> <input type="text" placeholder={'Nivel'}
                                               onChange={e => {
                                                   setQuery(Object.assign(query, {nivelAcademico: e.target.value}));
                                                   handleSearchEvent()
                                               }}/></th>
                        <th>Carrera <br/> <input type="text" placeholder={'Carrera'}
                                                 onChange={e => {
                                                     setQuery(Object.assign(query, {carrera: e.target.value}));
                                                     handleSearchEvent()
                                                 }}/></th>
                        <th>No. de lugares</th>
                        <th>Período</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {camposClinicos.map(cc => {
                        return (
                            <tr key={cc.id}
                                className={(cc.lugaresAutorizados !== null && cc.lugaresAutorizados <= 0 || cc.validateFormatoFofoe == 0) ? 'bg-danger' : ''}>
                                <td>{cc.unidad.nombre}</td>
                                <td>{cc.convenio.cicloAcademico ? cc.convenio.cicloAcademico.nombre : ''}<br/>
                                    {
                                        !cc.formatoFofoeFileName ?
                                            <LinkFormatoFofoe campoClinico={cc}
                                                              showFormatoFofoe={props.solicitud.estatus === 'Montos validados CAME'}/>
                                            : null
                                    }</td>
                                <td>{cc.convenio.carrera.nivelAcademico ? cc.convenio.carrera.nivelAcademico.nombre : ''}</td>
                                <td>{cc.convenio.carrera.nombre}
                                    <br/>
                                    {cc.asignatura ? `Asignatura: ${cc.asignatura}` : ''}
                                </td>
                                <td>Solicitados {cc.lugaresSolicitados} <br/>
                                    Autorizados {cc.lugaresAutorizados} <br/>
                                    {
                                        cc.trabajadoresBecados.length > 0 ?
                                            `Trabajadores Becados ${cc.trabajadoresBecados.length}`
                                            : null
                                    }
                                </td>
                                <td>Inicio {cc.fechaInicialFormatted} <br/> Final {cc.fechaFinalFormatted}
                                    <br/>
                                    Horario: {cc.horario ?? 'Sin asignar'}
                                </td>
                                <td>
                                    <LinkPago pago={searchPago(props.solicitud.pagos, cc)} showFecha={true}/> <br/>
                                    <LinkFactura
                                        factura={searchPago(props.solicitud.pagos, cc) ? searchPago(props.solicitud.pagos, cc).factura : null}/>
                                    <br/>
                                    {
                                        cc.formatoFofoeFileName ?
                                            <>
                                                <LinkFormatoFofoe campoClinico={cc}
                                                                  showFormatoFofoe={props.solicitud.estatus === 'Montos validados CAME'}/>
                                                <br/>
                                                {false && <LinkCredenciales campoClinico={cc}/>}
                                            </>
                                            :
                                            (props.solicitud.validado ?
                                                <UploadFormatoFofoe
                                                    campoClinico={cc}
                                                    refreshCampos={handleSearchEvent}
                                                />
                                                : null)
                                    }
                                    {
                                        cc.validateFormatoFofoe == 0 &&
                                        <>
                                            <p><strong>Motivo del rechazo: </strong>{cc.motiveFormatoFofoe}</p>
                                            <UploadFormatoFofoe
                                                showForce={true}
                                                campoClinico={cc}
                                                refreshCampos={handleSearchEvent}
                                            />
                                        </>
                                    }
                                </td>
                            </tr>
                        )
                    })}
                    </tbody>
                </table>
            </div>
        </>
    )
}

const ExpedienteUnico = (props) => {
    return (
        <div className="table-responsive">
            <table className="table">
                <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Archivo</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Oficio de Montos de Colegiatura, Inscripción y Listado de Alumnos</td>
                    <td>{props.solicitud.fechaComprobanteFormatted}</td>
                    <td>
                        <ComprobanteOficio solicitud={props.solicitud}/>
                        {
                            props.solicitud.validateOficioMontos === 0 &&
                            <>
                                <p><strong>Motivo del rechazo: </strong>{props.solicitud.motiveOficioMontos}</p>
                                <button
                                    onClick={props.handleClickOficioMontos}
                                    className="btn btn-primary">Subir Oficio de Montos
                                </button>
                            </>
                        }
                    </td>
                </tr>
                <tr>
                    <td>Formatos FOFOE (zip)</td>
                    <td>-</td>
                    <td><LinkAllFormatosFofoe solicitud={props.solicitud}/></td>
                </tr>
                <tr>
                    <td>Comprobante de Pago</td>
                    <td>{props.solicitud.pago ? props.solicitud.pago.fechaPagoFormatted : ''}</td>
                    <td><LinkPago pago={props.solicitud.pago} showFecha={false}/></td>
                </tr>
                <tr>
                    <td>Factura (CFDI)</td>
                    <td>{props.solicitud.pago && props.solicitud.pago.factura ? props.solicitud.pago.factura.fechaFacturacionFormatted : ''}</td>
                    <td><LinkFactura
                        factura={props.solicitud.pago && props.solicitud.pago.factura ? props.solicitud.pago.factura : null}/>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    )
}

const ExpedienteDetallado = (props) => {
    return (
        <div className="table-responsive">
            <table className="table">
                <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha</th>
                    <th>Archivo</th>
                </tr>
                </thead>
                <tbody>
                <tr className={props.solicitud.validateOficioMontos === 0 ? 'bg-danger' : ''}>
                    <td>Oficio de Montos de Colegiatura, Inscripción y Listado de Alumnos</td>
                    <td>{props.solicitud.fechaComprobanteFormatted}</td>
                    <td>
                        <ComprobanteOficio solicitud={props.solicitud}/>
                        {
                            props.solicitud.validateOficioMontos === 0 &&
                            <>
                                <p><strong>Motivo del rechazo: </strong>{props.solicitud.motiveOficioMontos}</p>
                                <button
                                    onClick={props.handleClickOficioMontos}
                                    className="btn btn-primary">Subir Oficio de Montos
                                </button>
                            </>
                        }
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    )
}

const DetalleSolicitudUnico = (props) => {

    const [isLoading, setIsLoading] = React.useState(false)
    const [camposClinicos, setCamposClinicos] = React.useState(props.solicitud.campoClinicos);
    const [query, setQuery] = React.useState({});
    const formatosCargados = camposClinicos.reduce((acc, item) =>
            acc && (item.lugaresAutorizados <= 0 || !!item.formatoFofoeFileName)
        , true);

    const handleSearchEvent = () => {
        setIsLoading(true);
        let querystring = '';
        for (const i in query) {
            querystring += `${i}=${query[i]}&`;
        }

        fetch(`${getSchemeAndHttpHost()}/came/api/solicitud/${props.solicitud.id}/campos_clinicos?${querystring}`)
            .then(response => {
                return response.json()
            }, error => {
                console.error(error)
            })
            .then(json => {
                setCamposClinicos(json.data)
            })
            .finally(() => {
                setIsLoading(false);
            });

    }
    const estatusSinCargarFormato = [SOLICITUD.CREADA, SOLICITUD.REGISTRADA,
        SOLICITUD.CONFIRMADA, SOLICITUD.EN_VALIDACION_DE_MONTOS_CAME,
        SOLICITUD.MONTOS_INCORRECTOS_CAME
    ]

    return (
        <><Loader show={isLoading}/>
            <div className="container">
                {!formatosCargados && !estatusSinCargarFormato.includes(props.solicitud.estatus) ?
                    <h4 className='text-danger'>
                        <strong>
                            Recuerda que el formato FOFOE con ambas firmas originales debe ser cargado al sistema.
                        </strong>
                    </h4>
                    : null
                }
            </div>
            <div className="table-responsive">
                <table className="table table-striped">
                    <thead>
                    <tr>
                        <th>Sede <br/> <input type="text" placeholder={'Sede'}
                                              onChange={e => {
                                                  setQuery(Object.assign(query, {unidad: e.target.value}));
                                                  handleSearchEvent()
                                              }}/></th>
                        <th>Campo Clínico <br/> <input type="text" placeholder={'Campo Clínico'}
                                                       onChange={e => {
                                                           setQuery(Object.assign(query, {cicloAcademico: e.target.value}));
                                                           handleSearchEvent()
                                                       }}/></th>
                        <th>Nivel <br/> <input type="text" placeholder={'Nivel'}
                                               onChange={e => {
                                                   setQuery(Object.assign(query, {nivelAcademico: e.target.value}));
                                                   handleSearchEvent()
                                               }}/></th>
                        <th>Carrera <br/> <input type="text" placeholder={'Carrera'}
                                                 onChange={e => {
                                                     setQuery(Object.assign(query, {carrera: e.target.value}));
                                                     handleSearchEvent()
                                                 }}/></th>
                        <th>No. de lugares</th>
                        <th>Fechas</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {camposClinicos.map(cc => {
                        return (
                            <tr key={cc.id}>
                                <td>{cc.unidad.nombre}</td>
                                <td>{cc.cicloAcademico.nombre}
                                    <br/>
                                    {
                                        !cc.formatoFofoeFileName ?
                                            <LinkFormatoFofoe campoClinico={cc}
                                                              showFormatoFofoe={props.solicitud.estatus === 'Montos validados CAME'}/>
                                            : null
                                    }
                                </td>
                                <td>{cc.convenio.carrera.nivelAcademico ? cc.convenio.carrera.nivelAcademico.nombre : ''}</td>
                                <td>{cc.convenio.carrera.nombre}
                                    <br/>
                                    {cc.asignatura ? `Asignatura: ${cc.asignatura}` : ''}
                                </td>
                                <td>Solicitados {cc.lugaresSolicitados} <br/>
                                    Autorizados {cc.lugaresAutorizados} <br/>
                                    {
                                        cc.trabajadoresBecados.length > 0 ?
                                            `Trabajadores Becados ${cc.trabajadoresBecados.length}`
                                            : null
                                    }
                                </td>
                                <td>Inicio {cc.fechaInicialFormatted}
                                    <br/> Final {cc.fechaFinalFormatted}
                                    <br/>
                                    Horario: {cc.horario ?? 'Sin asignar'}</td>
                                <td>
                                    {
                                        props.solicitud.estatus === 'En validación FOFOE' ?
                                        <><a href={`${getSchemeAndHttpHost()}/formato/campo_clinico/${cc.id}/formato_fofoe/download`}
                                           target={'_blank'}>
                                            Formato FOFOE para firma
                                        </a><br/></> : null
                                    }
                                    {
                                        cc.formatoFofoeFileName ?
                                            <>
                                                <LinkFormatoFofoe campoClinico={cc}
                                                                  showFormatoFofoe={props.solicitud.estatus === 'Montos validados CAME'}/>
                                                <br/>
                                                {false && <LinkCredenciales campoClinico={cc}/>}
                                            </>
                                            :
                                            (props.solicitud.validado ?
                                                <UploadFormatoFofoe
                                                    campoClinico={cc}
                                                    refreshCampos={handleSearchEvent}
                                                />
                                                : null)
                                    }
                                    {
                                        cc.validateFormatoFofoe == 0 &&
                                        <UploadFormatoFofoe
                                            showForce={true}
                                            campoClinico={cc}
                                            refreshCampos={handleSearchEvent}
                                        />
                                    }
                                </td>
                            </tr>
                        )
                    })}
                    </tbody>
                </table>
            </div>
        </>
    )
}

const SolicitudShow = (props) => {
    const Detalle = () => {
        if (props.solicitud.tipoPago === 'Multiple')
            return (<div className={'panel panel-default came-table-detalle'}><DetalleSolicitudDetallado
                solicitud={props.solicitud}/></div>)
        return (<div className={'panel panel-default came-table-detalle'}><DetalleSolicitudUnico
            solicitud={props.solicitud}/></div>)
    }

    const Expediente = () => {
        if (props.solicitud.tipoPago === 'Multiple')
            return (<ExpedienteDetallado
                handleClickOficioMontos={handleClickOficioMontos}
                solicitud={props.solicitud}/>)
        return (<ExpedienteUnico
            handleClickOficioMontos={handleClickOficioMontos}
            solicitud={props.solicitud}/>)
    }

    const handleClickOficioMontos = () => {
        Swal.fire({
            title: 'Sube tu archivo',
            input: 'file',
            inputAttributes: {
                'accept': '.pdf,.doc,.docx,image/*',
                'aria-label': 'Sube tu archivo',
                'name': 'solicitud_registro_montos[urlArchivoFile]'
            },
            showCancelButton: true,
            confirmButtonText: 'Enviar',
            inputValidator: (fileList) => {
                const file = fileList && fileList[0] ? fileList[0] : fileList;
                if (!file) {
                    return 'Debes seleccionar un archivo';
                }
                if (file.size > 2097152) {
                    return 'El archivo no debe ser mayor a 2 MB';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const file = result.value;

                checkInputFile(file, {size: 2097152 }, () => {
                    // Puedes enviarlo con fetch aquí
                    const formData = new FormData();
                    formData.append('solicitud_registro_montos[urlArchivoFile]', file);

                    uploadOficioMontos(props.solicitud.id, formData).then(data => {
                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'El oficio de montos se ha subido correctamente.',
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    })
                });
            }
        });

    }

    return (
        <>
            <div className="col-md-12">
                <p><strong>No. de Solicitud:</strong> {props.solicitud.noSolicitud}</p>
            </div>
            <div className="col-md-12">
                <p><strong>Estado:</strong> {props.solicitud.estatusCameFormatted}</p>
            </div>
            <div className="col-md-12">
                <p><strong>Institución Educativa:</strong> {props.solicitud.institucion.nombre}</p>
            </div>
            <div className="col-md-12">
                <p>Se <strong>autorizaron</strong> {props.solicitud.camposClinicosAutorizados} de {props.solicitud.camposClinicosSolicitados} campos
                    clínicos solicitados.</p>
            </div>
            <div className="row"/>
            <div className="col-md-12">
                <Detalle/>
            </div>
            <div className="col-md-6">
                <h2>Expediente</h2>
            </div>
            <div className="col-md-6">
                <h2>Convenios</h2>
            </div>
            <div className="col-md-6">
                <Expediente/>
            </div>
            <div className="col-md-6">
                <div className="table-responsive">
                    <table className="table">
                        <thead>
                        <tr>
                            <th>Número</th>
                            <th>Grado</th>
                            <th>Ciclo</th>
                            <th>Carrera</th>
                            <th>Vigencia</th>
                        </tr>
                        </thead>
                        <tbody>
                        {props.convenios.map((convenio, i) => {
                            return (
                                <tr key={i}>
                                    <td>{convenio.numero}</td>
                                    <td>{convenio.carrera.nivelAcademico ? convenio.carrera.nivelAcademico.nombre : ''}</td>
                                    <td>{convenio.ciclosAcademicosFormatted}</td>
                                    <td>{convenio.carrera.nombre}</td>
                                    <td>{convenio.vigenciaFormatted}</td>
                                </tr>
                            )
                        })}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    )
}

export default SolicitudShow;
