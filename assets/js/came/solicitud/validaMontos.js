import * as React from 'react'
import Loader from "../../components/Loader/Loader";
import {formatNumeroDinero, getSchemeAndHttpHost} from "../../utils";
import RegistrarDescuentos from "../../ie/RegistrarMontos/Descuentos";
import {Fragment} from "react";
import './styles/tables.scss';
import {CAMPO_CLINICO} from "../../constants";

const SolicitudValidaMontos = (props) => {

    const [isLoading, setIsLoading] = React.useState(false);
    const [validos, setValidos] = React.useState(false);
    const [camposClinicos, setCamposClinicos] = React.useState(props.solicitud.camposClinicos);
    const [alert, setAlert] = React.useState({});
    const [errores, setErrores] = React.useState({});
    const [descValidos, setDescValidos] = React.useState(true);
    const [descValidosCC, setDescValidosCC] = React.useState(
      props.solicitud.camposClinicos.reduce((acc, elem) => { acc.push({id: elem.id, validate: true}); return acc; },
        []));
    const formatosDescargados = camposClinicos.reduce(((acc, item) => acc &&
        ( item.lugaresAutorizados <=0 ||
          (item.estatus && item.estatus.nombre === CAMPO_CLINICO.FORMATO_FOFOE_DESCARGADO )
        ) )
        , true )
    const comentariosObligatorios = !validos || validos.toString() === (0).toString()

  const LinkFormatoFofoe = ({campoClinico}) => {

    const handleClick = (e) => {
      const indexCC = camposClinicos.findIndex(item => {return item.id === campoClinico.id});
      camposClinicos[indexCC].estatus = {nombre: CAMPO_CLINICO.FORMATO_FOFOE_DESCARGADO};
      setCamposClinicos(Object.assign([], camposClinicos));
      return true;
    }

    return (<a onClick={handleClick} href={`${getSchemeAndHttpHost()}/formato/campo_clinico/${campoClinico.id}/formato_fofoe/download`} target={'_blank'}>Descargar Formato FOFOE</a>);
  }

    const callbackIsLoading = (value) => {
        setIsLoading(value);
    }

    const callbackDescuentos = (i, value, idCampo, val) => {
      const indexCC = descValidosCC.findIndex(item => {return item.id === idCampo});
      descValidosCC[indexCC] = {id: idCampo, validate: val};
      setDescValidosCC(descValidosCC);
      setDescValidos(descValidosCC.reduce((acc, elem) => {return acc && elem.validate}, true));

      camposClinicos[i].montoCarrera.descuentos = value;
      setCamposClinicos(Object.assign([], camposClinicos));
    }

    const handleSolicitudValidaMontos = (event) => {
        event.preventDefault();
        if(!descValidos) return;
        setIsLoading(true);
        let data = new FormData();
        camposClinicos.map((campo, i) => {
          if (campo.lugaresAutorizados <= 0) return;
           data.append(`solicitud[campo_${campo.id}][observaciones]`, campo.new_observaciones ? campo.new_observaciones : '' );
           data.append(`solicitud[campo_${campo.id}][montoCarrera][montoInscripcion]`, campo.montoCarrera.montoInscripcion);
           data.append(`solicitud[campo_${campo.id}][montoCarrera][montoColegiatura]`, campo.montoCarrera.montoColegiatura);

            campo.montoCarrera.descuentos.map((desc, iDesc) => {
                 data.append(`solicitud[campo_${campo.id}][montoCarrera][descuentos][${iDesc}][numAlumnos]`, desc.numAlumnos);
                 data.append(`solicitud[campo_${campo.id}][montoCarrera][descuentos][${iDesc}][descuentoInscripcion]`, desc.descuentoInscripcion);
                 data.append(`solicitud[campo_${campo.id}][montoCarrera][descuentos][${iDesc}][descuentoColegiatura]`, desc.descuentoColegiatura);
           });
        });

        if(validos.toString() === (1).toString()){
            data.append('solicitud[validado]', validos);
        }

        fetch(`${getSchemeAndHttpHost()}/came/api/solicitud/validar_montos/${props.solicitud.id}` , {
            method: 'post',
            body: data
        }).then(response => {
            return response.json()
        }, error => {console.error(error)
        }).then(json => {
            if (json.errors) {
                setErrores(json.errors);
            }
            setAlert(Object.assign(alert, {
                show: true,
                message: json.message,
                type: (json.status ? 'success' : 'danger')
            }))
            if(json.status){
                new Promise((resolve, reject) => {
                   setTimeout(() => {
                       resolve()
                   }, 250)
                }).then(() => {
                    document.location.href = `${getSchemeAndHttpHost()}/came/solicitud/${props.solicitud.id}`;
                });
            }
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const handleCurrency = (e) => {
      let valPrev = e.value.toString().replace(",", "");
      e.value = formatNumeroDinero(valPrev);
    };

    const onChangeMontoInscripcion = (e, i) => {
      camposClinicos[i].montoCarrera.montoInscripcion = e.target.value.replace(/,/g, '');
      setCamposClinicos(Object.assign([], camposClinicos))
    }

    const onChangeMontoColegiatura = (e, i) => {
      camposClinicos[i].montoCarrera.montoColegiatura = e.target.value.replace(/,/g, '');
      setCamposClinicos(Object.assign([], camposClinicos))
    }

    return (
        <>
            <Loader show={isLoading}/>
            <div className="col-md-12">
                <div className={`alert alert-${alert.type} `}
                     style={{display: (alert.show ? 'block' : 'none')}}>
                    <a className="close" onClick={e => setAlert({})}>&times;</a>
                    {alert.message}
                </div>
            </div>

            <div className="col-md-12">
                <p><strong>No. de Solicitud:</strong> {props.solicitud.noSolicitud}</p>
            </div>
            <div className="col-md-12">
                <p><strong>Estado:</strong> {props.solicitud.estatusCameFormatted}</p>
            </div>
            <div className="col-md-12">
                <p><strong>Insitución Educativa:</strong> {props.solicitud.institucion.nombre}</p>
            </div>
            <div className="col-md-12" style={{display: 'flex', justifyContent: 'center'}}>
                <a className="btn btn-secondary mt-5 mb-5" href={`${getSchemeAndHttpHost()}/came/solicitud/${props.solicitud.id}/oficio`} target={'_blank'} >Descargar Oficio de Montos de Colegiatura, Inscripción y Listado de Alumnos</a>
            </div>
            <div className="col-md-12">
                <p><strong>Por favor valide los montos que se muestran a continuación, deben coincidir con los reportados en el oficio</strong></p>
            </div>
            <form onSubmit={handleSolicitudValidaMontos}>
                <div className="col-md-12">
                    <table className="table">
                        <thead>
                        <tr>
                            <th className={"col-md-2"}>Carrera</th>
                            <th className={"col-md-2"} >Período</th>
                            <th className={"col-md-2"}>Sede</th>
                            <th className={"col-md-2"}>Inscripción</th>
                            <th className={"col-md-2"}>Colegiatura</th>
                            <th className={"col-md-2"}>Formato FOFOE</th>
                        </tr>
                        </thead>
                        <tbody>
                        {camposClinicos.map((campo, i) =>{
                            return (
                              campo.lugaresAutorizados > 0 ?
                              <Fragment key={campo.montoCarrera.id}>
                                <tr key={campo.montoCarrera.id}>
                                    <td>{campo.montoCarrera.carrera.nivelAcademico ? campo.montoCarrera.carrera.nivelAcademico.nombre : ''} {campo.montoCarrera.carrera.nombre}
                                      <br />
                                      {campo.asignatura ? `Asignatura: ${campo.asignatura}` :  ''}
                                    </td>
                                    <td>
                                        <div>{campo.displayFechaInicial}-{campo.displayFechaFinal}
                                          <br />
                                          Horario: {campo.horario ?? 'Sin asignar'}
                                        </div>
                                    </td>
                                    <td>{campo.unidad.nombre}
                                      <br />
                                      {campo.lugaresAutorizados} lugares autorizados
                                      <br />
                                      {
                                        parseInt(campo.totalTrabajadoresBecados) > 0 ?
                                          `${campo.totalTrabajadoresBecados} becas trabajadores`
                                          : ''
                                      }
                                    </td>
                                    <td>
                                        <div className="input-group col-md-10">
                                            <span className="input-sm input-group-addon">$</span>
                                            <input className="form-control"
                                                   type="text"
                                                   defaultValue={camposClinicos[i].montoCarrera.montoInscripcion ? formatNumeroDinero(camposClinicos[i].montoCarrera.montoInscripcion) : ''}
                                                   min={0}
                                                   step="0.01"
                                                   required={true}
                                                   onBlur={e => handleCurrency(e.target)}
                                                   onChange={e => onChangeMontoInscripcion(e, i)}
                                                   />
                                        </div>
                                    </td>
                                    <td>
                                        <div className="input-group col-md-10">
                                            <span className="input-sm input-group-addon">$</span>
                                            <input className="form-control"
                                                   type="text"
                                                   defaultValue={camposClinicos[i].montoCarrera.montoColegiatura ? formatNumeroDinero(camposClinicos[i].montoCarrera.montoColegiatura) : ''}
                                                   min={0}
                                                   step="0.01"
                                                   required={true}
                                                   onBlur={e => handleCurrency(e.target)}
                                                   onChange={e => onChangeMontoColegiatura(e, i)}
                                        />
                                        </div>
                                    </td>
                                  <td>
                                      {false && <><span>Monto a pagar: $ {formatNumeroDinero(campo.monto)}</span>  <br /> </>}
                                    <LinkFormatoFofoe campoClinico={campo} />
                                  </td>
                                </tr>
                                { campo.observaciones ?
                                <tr className={'desc'}>
                                  <td colSpan={5}>
                                    Revisión anterior:
                                    <p className='background' > {campo.observaciones} </p>
                                  </td>
                                </tr> :
                                  null
                                }
                                <tr className={'without-border-top'}>
                                    {true &&
                                    <td colSpan={6}>
                                        <RegistrarDescuentos
                                          prefixName={`solicitud_validacion_montos[montosCarreras][${i}][descuentos]`}
                                          campo={campo}
                                          carrera={campo.montoCarrera.carrera}
                                          campos={props.solicitud.camposClinicos}
                                          descuentos={campo.montoCarrera.descuentos}
                                          onChange={callbackDescuentos}
                                          indexMonto={i}
                                        />
                                    </td>
                                    }
                                </tr>
                                <tr className={'without-border-top'}>
                                  <td colSpan={5}>
                                    <div className="col-md-10">
                                      <div className={`form-group ${errores.observaciones ? 'has-error has-feedback' : ''}`}>
                                        <label htmlFor="observaciones_solicitud">Observaciones</label>
                                        <textarea
                                                  className={'form-control'}
                                                  required = {comentariosObligatorios}
                                                  placeholder={'Observaciones'}
                                                  onChange={e => { camposClinicos[i].new_observaciones = e.target.value; setCamposClinicos(Object.assign([], camposClinicos))}}
                                        />
                                        <span className="help-block">{errores.observaciones ? errores.observaciones[0] : ''}</span>
                                      </div>
                                    </div>
                                  </td>
                                </tr>
                                </Fragment>
                                : null
                            )
                        })}
                        </tbody>
                    </table>
                </div>
                <div className="col-md-12"/>
                <div className="col-md-4">
                    <div className={`form-group ${errores.validado ? 'has-error has-feedback' : ''}`}>
                        <div>
                          <a className="btn btn-secondary mt-5 mb-5" href={`${getSchemeAndHttpHost()}/came/solicitud/${props.solicitud.id}/descargar-formatos-fofoe`} target={'_blank'}>
                            Descargar todos los formatos FOFOE de la solicitud
                          </a>
                        </div>
                        <label htmlFor="validos_solicitud">
                          ¿Los montos de inscripción, colegiatura y listado de alumnos, así como todos los formatos FOFOE de los campos solicitados son correctos?
                        </label>
                        <select id="validos_solicitud" className={'form-control'}
                                required={true} onChange={e => setValidos(e.target.value)}>
                            <option value="">Seleccionar ...</option>
                            <option value={1}>Si</option>
                            <option value={0}>No</option>
                        </select>
                        <span className="help-block">{errores.validado ? errores.validado[0] : ''}</span>
                    </div>
                </div>
                <div className="col-md-2"/>
                <div className="col-md-4">
                    <label htmlFor="btn_solicitud">&#160;</label>

                    <button id="btn_solicitud"
                            className={`form-control btn btn-primary ${descValidos ? ' ' : 'disabled'}`}
                            disabled={(descValidos) ? '' : 'disabled'}>Guardar</button>
                </div>
            </form>
        </>
    )
}

export default SolicitudValidaMontos;