import * as React from 'react'
import SelectSearch from "react-select-search";
import Modal from 'react-modal';
import {getSchemeAndHttpHost} from "../../../utils";
import DescuentosTrabajadoresForm from "./DescuentosTrabajadoresForm";
import {useEffect} from "react";
import {delegacionesGet} from "../../api/delegaciones";
import Swal from "sweetalert2";

const CampoClinicoForm = (props) => {

    const [isLoading, setIsLoading] = React.useState(true);
    const [solicitud, setSolicitud] = React.useState(props.solicitud ? props.solicitud : null);
    const [convenio, setConvenio] = React.useState(null);
    const [fechaInicial, setFechaInicial] = React.useState('');
    const [fechaFinal, setFechaFinal] = React.useState('');
    const [horario, setHorario] = React.useState('');
    const [lugaresAutorizados, setLugaresAutorizados] = React.useState(0);
    const [lugaresSolicitados, setLugaresSolicitados] = React.useState(0);
    const [becasAutorizadas, setBecasAutorizadas] = React.useState(0);
    const [promocion, setPromocion] = React.useState('');
    const [unidad, setUnidad] = React.useState('');
    const [asignatura, setAsignatura] = React.useState('');
    const [delegaciones, setDelegaciones] = React.useState([])
    const [trabajadoresBecados, setTrabajadoresBecados] = React.useState([])
    const [trabajadoresValidados, setTrabajadoresValidados] = React.useState(true);

    const [errores, setErrores] = React.useState({});
    const [alert, setAlert] = React.useState({});

    const [showModal, setShowModal] = React.useState(false);

    useEffect(  () => {
        delegacionesGet().then((res)  => {
            setDelegaciones(res.data);
        });
    }, []);

    const initConvenios = (convenios) => {
        let data = [];
        for (const i of convenios) {
            if (i.carrera && i.cicloAcademico) {
                data.push(i);
            }
        }
        return data;
    }

    const ciclosAutorizados = [1, 2];

    const getConveniosActivos = (convenios) => {
        let data = [];
        let today = Date.now();
        for (const i of convenios) {
            if (i.carrera && i.cicloAcademico
              //&& i.label.toString() !== 'red'
              && (new Date(i.vigencia)) > today
              && ciclosAutorizados.indexOf(i.cicloAcademico.id) > -1) {
                data.push(i);
            }
        }
        return data;
    }

    const getTotalMatriculasValidadas = () => {
        return trabajadoresBecados.reduce((total, item, index) => {
            return total + (item.validado ? 1 : 0);
        }, 0)
    }

    const handleSubmit = (event) => {
        event.preventDefault();
        setErrores({});
        setAlert({});
        if (!isFormValid()) return;

        const totalMatriculasValidadas = getTotalMatriculasValidadas()

        if (trabajadoresBecados.length > 0) {
            Swal.fire({
                title: 'Validación de matrículas',
                html: 'Se ha realizado la validación de las matrículas ingresadas. <br />' +
                  `${totalMatriculasValidadas} matrículas se verificaron <br />
                   ${trabajadoresBecados.length - totalMatriculasValidadas} matrícula(a) inválida(s). <br />` +
                  '<strong> ¿Confirma que desea continuar con el proceso?, ' +
                    'o presione cancelar para realizar cambios. </strong><br />' +
                  '<br />Recuerde que los alumnos inscritos en este campo deben ser estrictamente ' +
                  'trabajadores del Instituto. Cualquier indicio de mal uso queda bajo su ' +
                  'estricta responsabilidad. De continuar, el usuario acepta que los datos introducidos son correctos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '¡Si, estoy seguro!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    handleSubmitCampoClinicoForm()
                }
            })
        } else {
            handleSubmitCampoClinicoForm()
        }

    }

    const handleSubmitCampoClinicoForm = () => {
        props.callbackIsLoading(true);
        if (solicitud) {
            storeCampoClinico(solicitud);
        } else {
            storeSolicitud().then(solicitud => {
                setSolicitud(solicitud)
                props.callbackSolicitud(solicitud);
                storeCampoClinico(solicitud)
            });
        }
    }

const storeSolicitud = () => {
    return new Promise((resolve, reject) => {
        fetch(`${getSchemeAndHttpHost()}/came/api/solicitud`, {
            method: 'post'
        }).then(response => {
            return response.json();
        }, error => {
            reject(error);
        }).then(json => {
            resolve(json.data);
        });
    });
}

const handleConvenioEvent = (selected) => {
    let convenio = null;
    for (const i of props.convenios) {
        if ((i.id).toString() === (selected).toString()) {
            convenio = i;
        }
    }
    setConvenio(convenio);
}

const isFormValid = () => {
    let result = true;
    const fechaI = new Date(fechaInicial);
    const fechaF = new Date(fechaFinal);
    let errores = {};

    const regEx = /^\d{4}-\d{2}-\d{2}$/;
    if(!fechaInicial.match(regEx)){
        errores = Object.assign(errores, {
            fechaInicial: ['El formato de la fecha debe ser año-mes-día'],
        });
        result = false;
    }
    if(!fechaFinal.match(regEx)){
        errores = Object.assign(errores, {
            fechaFinal: ['El formato de la fecha debe ser año-mes-día'],
        });
        result = false;
    }

    if (fechaI > fechaF) {
        errores = Object.assign(errores, {
            fechaInicial: ['Fecha Inicial debe ser menor a Fecha Final'],
            fechaFinal: ['Fecha Final debe ser mayor a Fecha Inicial']
        });
        result = false;
    }
    if(!unidad){
        errores = Object.assign(errores, {
            unidad: ['Por favor selecciona un objeto de la lista']
        });
        result = false;
    }
    if(!result){
        setAlert({show: true, type: 'danger', message: 'Se presentaron errores al procesar su solicitud'});
        setErrores(errores);
    }
    return result;
}

const resolveDataTrabajadores = (data) => {
    trabajadoresBecados.forEach((item, i) => {
        data.append(`campo_clinico[trabajadoresBecados][${i}][matricula]`, item.matricula);
        data.append(`campo_clinico[trabajadoresBecados][${i}][delegacion]`, item.delegacion.value !== '0' ?  parseInt(item.delegacion.value) : '');
        data.append(`campo_clinico[trabajadoresBecados][${i}][nombre]`, item.nombre);
        data.append(`campo_clinico[trabajadoresBecados][${i}][aPaterno]`, item.aPaterno);
        data.append(`campo_clinico[trabajadoresBecados][${i}][aMaterno]`, item.aMaterno);
        data.append(`campo_clinico[trabajadoresBecados][${i}][correo]`, item.correo);
        data.append(`campo_clinico[trabajadoresBecados][${i}][curp]`, item.curp);
        data.append(`campo_clinico[trabajadoresBecados][${i}][adscripcion]`, item.adscripcionId && item.adscripcionId !== 'null' ? parseInt(item.adscripcionId) : '');
    })
}

const storeCampoClinico = (solicitud) => {

    let data = new FormData();
    const fechaI = fechaInicial.split('-');
    const fechaF = fechaFinal.split('-');
    data.append('campo_clinico[solicitud]', solicitud.id);
    data.append('campo_clinico[convenio]', convenio.id);
    data.append('campo_clinico[unidad]', unidad.id);
    data.append('campo_clinico[fechaInicial][year]', Number.parseInt(fechaI[0]));
    data.append('campo_clinico[fechaInicial][month]', Number.parseInt(fechaI[1]));
    data.append('campo_clinico[fechaInicial][day]', Number.parseInt(fechaI[2]));
    data.append('campo_clinico[horario]', horario);
    data.append('campo_clinico[lugaresSolicitados]', lugaresSolicitados);
    data.append('campo_clinico[lugaresAutorizados]', lugaresAutorizados);
    data.append('campo_clinico[fechaFinal][year]', Number.parseInt(fechaF[0]));
    data.append('campo_clinico[fechaFinal][month]', Number.parseInt(fechaF[1]));
    data.append('campo_clinico[fechaFinal][day]', Number.parseInt(fechaF[2]));
    data.append('campo_clinico[asignatura]', asignatura);
    data.append('campo_clinico[promocion]', promocion);

    if (becasAutorizadas > 0) {
        resolveDataTrabajadores(data)
    }

    fetch(`${getSchemeAndHttpHost()}/came/api/campo_clinico`, {
        method: 'post',
        body: data
    }).then(response => {
        props.callbackIsLoading(false);
        return response.json()
    }, error => {
        props.callbackIsLoading(false);
    }).then(json => {
        if (json.errors) {
            setErrores(json.errors);
        } else {
            props.callbackCampoClinico(json.data);
            setConvenio(null);
            setFechaInicial('');
            setFechaFinal('');
            setHorario('');
            setLugaresSolicitados(0);
            setLugaresAutorizados(0);
            setPromocion('');
            setUnidad('');
            setAsignatura('');
        }
        setAlert(Object.assign(alert, {
            show: true,
            message: json.message,
            type: (json.status ? 'success' : 'danger')
        }));
        setShowModal(!json.status);
    });

}

const findUnidad = (value) => {
    let unidad = null;
    for (const i of props.unidades) {
        if ((i.id).toString() === value.toString()) {
            unidad = i;
        }
    }
    return unidad;
}

const getUnidades = () => {
    const result = [{value: '-', name: 'Seleccionar ...'}];
    props.unidades.map(item => {
        result.push({value: item.id.toString(),
            name: item.claveUnidad + '-' + item.nombre   });
    })
    return result;
}

const openModal = () => {
    setAlert(Object.assign(alert, {
        show: false,
        message: ''
    }));
    setShowModal(true);
}

const handleOnChangeBecas = (e) => {
    if (!e.target.value) {
        setBecasAutorizadas(0);
        setTrabajadoresValidados( true)
        return;
    }
    if (e.target.value && e.target.value > 0 ) {
        setTrabajadoresValidados( false )
    }
    setBecasAutorizadas( parseInt(e.target.value) )
}

return (
  <>
      <div className="col-md-12">
          <div className={`alert alert-${alert.type} `} style={{display: (alert.show ? 'block' : 'none')}}>
              <a className="close" onClick={e => setAlert({})}>&times;</a>
              {alert.message}
          </div>
      </div>

      <div className="row">
          <div className="col-md-3 mt-10 mb-10">
              <label htmlFor="btn_campo_clinico">&#160;</label>
              <button id="btn_campo_clinico"
                      onClick={openModal}
                      className={'form-control btn btn-primary'}>Agregar Campo Clínico
              </button>
          </div>
      </div>

      <Modal
        isOpen={showModal}
        ariaHideApp={false}
        contentLabel="Example Modal"
      >
          <div className="row">
              <div className="col-md-12">
                  <h3>Ingrese la información del campo clínico</h3>
              </div>
          </div>


          <div className="row">
              <div className="col-md-12">
              </div>
          </div>

          <br/><br/>


          <div className="row">
              <div className="col-md-12">
                  <div className={`form-group ${errores.convenio ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="fecha_final">Carrera</label>
                      <select name="convenio" id="campo_convenio" className={'form-control'}
                              value={convenio ? convenio.id : ''}
                              form="campo-clinico-form"
                              required={true} onChange={e => handleConvenioEvent(e.target.value)}>
                          <option value="">Seleccionar ...</option>
                          {getConveniosActivos(props.convenios).map(c => {
                              return (
                                <option key={c.id}
                                        value={c.id}>{c.cicloAcademico.nombre} - {c.carrera.nivelAcademico.nombre} - {c.carrera.nombre}</option>
                              )
                          })}
                      </select>
                      <span className="help-block">{errores.convenio ? errores.convenio[0] : ''}</span>
                  </div>
              </div>
          </div>
          <div className="row">
              <div className="col-md-12">
                  <label htmlFor="fecha_inicial">Periodo</label>
              </div>
          </div>

          <div className="row">
              <div className="col-md-3">
                  <div className={`form-group ${errores.fechaInicial ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="fecha_inicial">Inicio <br/>  &#160;</label>
                      <input id="fecha_inicial" type="date" className={'form-control'}
                             value={fechaInicial}
                             form="campo-clinico-form"
                             placeholder={'año-mes-día'}
                             onChange={e => setFechaInicial(e.target.value)} required={true}/>
                      <span className="help-block">{errores.fechaInicial ? errores.fechaInicial[0] : ''}</span>
                  </div>
              </div>
              <div className="col-md-3 col-md-offset-1">
                  <div className={`form-group ${errores.fechaFinal ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="fecha_final">Fin <br/>&#160;</label>
                      <input id="fecha_final" type="date" className={'form-control'}
                             value={fechaFinal}
                             form="campo-clinico-form"
                             placeholder={'año-mes-día'}
                             onChange={e => setFechaFinal(e.target.value)} required={true}/>
                      <span className="help-block">{errores.fechaFinal ? errores.fechaFinal[0] : ''}</span>
                  </div>
              </div>
              <div className="col-md-4 col-md-offset-1">
                  <div className={`form-group ${errores.promocion ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="promocion">Promoción de Inicio
                          <span
                            style={{display: (convenio && convenio.cicloAcademico.id === 2) ? 'block' : 'none'}}>&#160;</span>
                          <span
                            style={{display: (!convenio || (convenio && convenio.cicloAcademico.id !== 2)) ? 'block' : 'none'}}>[solo para Internado]</span>
                      </label>
                      <input id="promocion" className={'form-control'}
                             form="campo-clinico-form"
                             disabled={!convenio || (convenio && convenio.cicloAcademico.id !== 2)}
                             required={convenio && convenio.cicloAcademico.id === 2}
                             type="text" value={promocion} onChange={e => setPromocion(e.target.value)}/>
                      <span className="help-block">{errores.promocion ? errores.promocion[0] : ''}</span>
                  </div>
              </div>
          </div>

          <div className="row">
              <div className="col-md-12">
                  <div className={`form-group ${errores.unidad ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="unidad">Unidad Sede</label>
                      <SelectSearch id={'unidad'}
                                    search
                                    options={getUnidades()}
                                    value={unidad ? unidad.id.toString() : ''}
                                    required={true}
                                    onChange={value => setUnidad(findUnidad(value))}/>
                      <span className="help-block">{errores.unidad ? errores.unidad[0] : ''}</span>
                  </div>
              </div>
          </div>

          <div className="row" >
              <div className="col-md-5">
                  <div className={`form-group ${errores.horario ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="horario">Horario del campo clínico (Opcional) </label>
                      <input id="horario" className={'form-control'}
                             form="campo-clinico-form"
                             type="text" value={horario} onChange={e => setHorario(e.target.value)}/>
                      <span className="help-block">{errores.horario ? errores.horario[0] : ''}</span>
                  </div>
              </div>
              <div className="col-md-7">
                  <div className={`form-group ${errores.asignatura ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="asignatura">Asignatura (Opcional)</label>
                      <input type="text"
                             form="campo-clinico-form"
                             id={'asignatura'}
                             className={'form-control'} value={asignatura}
                             onChange={e => setAsignatura(e.target.value)}/>
                      <span className="help-block">{errores.asignatura ? errores.asignatura[0] : ''}</span>
                  </div>
              </div>
          </div>

          <div className="row">
              <div className="col-md-3">
                  <div className={`form-group ${errores.lugaresSolicitados ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="lugaresSolicitados">No. de lugares solicitados</label>
                      <input id={'lugaresSolicitados'}
                             type="number" value={lugaresSolicitados}
                             min={0}
                             form="campo-clinico-form"
                             className={'form-control'}
                             onChange={e => setLugaresSolicitados(e.target.value)} required={true}/>
                      <span
                        className="help-block">{errores.lugaresSolicitados ? errores.lugaresSolicitados[0] : ''}</span>
                  </div>
              </div>
              <div className="col-md-3 col-md-offset-1">
                  <div className={`form-group ${errores.lugaresAutorizados ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="lugaresAutorizados">No. de lugares autorizados</label>
                      <input id={'lugaresAutorizados'}
                             type="number" value={lugaresAutorizados}
                             min={0}
                             form="campo-clinico-form"
                             className={'form-control'}
                             onChange={e => setLugaresAutorizados(e.target.value)} required={true}/>
                      <span
                        className="help-block">{errores.lugaresAutorizados ? errores.lugaresAutorizados[0] : ''}</span>
                  </div>
              </div>

              <div className="col-md-3 col-md-offset-2">
                  <div className={`form-group ${errores.becasAutorizadas ? 'has-error has-feedback' : ''}`}>
                      <label htmlFor="becasAutorizadas">No. de becas trabajadores IMSS</label>
                      <input id={'becasAutorizadas'}
                             type="number" defaultValue={becasAutorizadas}
                             min={0}
                             max={lugaresAutorizados}
                             form="campo-clinico-form"
                             className={'form-control'}
                             onChange={handleOnChangeBecas} required={true}/>
                      <span
                        className="help-block">{errores.becasAutorizadas ? errores.becasAutorizadas[0] : ''}</span>
                  </div>
              </div>
          </div>

          {
              becasAutorizadas > 0 ?
                <div className='row'>
                    <div className="col-md-12 form-group">
                        <h3>Ingrese matrícula y OOAD de adscripción de los trabajadores becados</h3>
                    </div>
                </div>
                : null
          }

          <DescuentosTrabajadoresForm
            delegaciones={delegaciones}
            becasAutorizadas={becasAutorizadas}
            trabajadoresValidados={trabajadoresValidados}
            setTrabajadoresValidados={setTrabajadoresValidados}
            trabajadoresBecados={trabajadoresBecados}
            setTrabajadoresBecados={setTrabajadoresBecados}
          />

          <form id="campo-clinico-form" onSubmit={handleSubmit}>
              <div className="row">
                  <div className="col-md-3">
                      <label htmlFor="btn_cancelar_campo">&#160;</label>
                      <button id="btn_cancelar_campo"
                              type={'reset'}
                              onClick={() => setShowModal(false)}
                              className={'form-control btn btn-default'}>Cancelar
                      </button>
                  </div>
                  {
                      0 === becasAutorizadas || trabajadoresValidados ?
                        <div className="col-md-6">
                            <label htmlFor="btn_campo_clinico">&#160;</label>
                            <button id="btn_campo_clinico"
                                    type={'submit'}
                                    className={'form-control btn btn-success'}>Guardar Campo
                                clínico
                            </button>
                        </div>
                        : null
                  }
              </div>
          </form>
      </Modal>
  </>
);

}

export default CampoClinicoForm;