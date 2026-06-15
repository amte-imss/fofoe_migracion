import * as React from 'react'
import Modal from 'react-modal';
import {compareDates, getSchemeAndHttpHost} from "../../../utils";
import Select from "react-select";
import Loader from "../../../components/Loader/Loader";

const CampoClinicoForm = ({
                              solicitudPrev,
                              convenios,
                              unidades,
                              callbackSolicitud,
                              callbackCampoClinico}) => {

    const [isLoading, setIsLoading] = React.useState(false);
    const [solicitud, setSolicitud] = React.useState(solicitudPrev ? solicitudPrev : null);
    const [convenio, setConvenio] = React.useState(null);
    const [fechaInicial, setFechaInicial] = React.useState('');
    const [fechaFinal, setFechaFinal] = React.useState('');
    const [horario, setHorario] = React.useState('');
    const [lugaresSolicitados, setLugaresSolicitados] = React.useState(0);
    const [promocion, setPromocion] = React.useState('');
    const [unidad, setUnidad] = React.useState('');
    const [asignatura, setAsignatura] = React.useState('');
    const [cicloAcademico, setCicloAcademico] = React.useState('');

    const [errores, setErrores] = React.useState({});
    const [alert, setAlert] = React.useState({});

    const [showModal, setShowModal] = React.useState(false);

    const ciclosAutorizados = [1, 2];
    const today = new Date();

    const getConveniosActivos = (convenios) => {
        let data = [];
        let today = Date.now();
        for (const i of convenios) {
            const ciclosAcademicosConvenio = i.conveniosCiclosAcademicos.map(item => {
                return item.cicloAcademico;
            })
            const autorizado = ciclosAcademicosConvenio.some(item => ciclosAutorizados.indexOf(item.id) > -1);
            //console.log('autoizado', autorizado, ciclosAcademicosConvenio, ciclosAutorizados);
            if (i.carrera
              //&& i.label.toString() !== 'red'
              && (new Date(i.vigencia)) > today
              && autorizado) {
                data.push(i);
            }
        }
        return data;
    }

    const getMinDateInicio = () => {
        //mala practica pero me obliga el codigo y la DB
        const CICLO_ACADEMICO = 1;
        const INTERNADO_MEDICO = 2;
        const DAYS_INCREMENT = 10;
        if(cicloAcademico == CICLO_ACADEMICO){
            const result = new Date(today);
            result.setDate(result.getDate() + DAYS_INCREMENT);
            return result.toISOString().split('T')[0];
        }
        return today.toISOString().split('T')[0];
    }

    const getCiclosAcademicos = () => {
        if(!convenio) return [];
        //return [];
        return convenio.conveniosCiclosAcademicos.map(item => {
            return item.cicloAcademico;
        })
    }

    const handleSubmit = (event) => {
        event.preventDefault();
        setErrores({});
        setAlert({});
        if (!isFormValid()) return;

        handleSubmitCampoClinicoForm()
    }

    const handleSubmitCampoClinicoForm = () => {
        setIsLoading(true)
        if (solicitud) {
            storeCampoClinico(solicitud);
        } else {
            storeSolicitud().then(solicitud => {
                setSolicitud(solicitud)
                callbackSolicitud(solicitud);
                storeCampoClinico(solicitud)
            });
        }
        setIsLoading(false)
    }

    const storeSolicitud = () => {
        return new Promise((resolve, reject) => {
            let data = createFormData()
            fetch(`${getSchemeAndHttpHost()}/ie/api/solicitud`, {
                method: 'post',
                body: data
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
        //console.log('convenio seleccionado', selected);
        let convenio = null;
        for (const i of convenios) {
            if ((i.id).toString() === (selected).id.toString()) {
                convenio = i;
            }
        }
        //console.log('convenio encontrado', convenio);
        setConvenio(convenio);
    }

    const isFormValid = () => {
        let result = true;
        const fechaI = new Date(fechaInicial);
        const fechaF = new Date(fechaFinal);
        let errores = {};
        today.setHours(0,0,0,0);

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
        if (compareDates(fechaI, today) < 0) {
            errores = Object.assign(errores, {
                fechaInicial: ['La fecha de inicio debe ser posterior a la fecha actual'],
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

    const createFormData = () => {
        let data = new FormData();
        const fechaI = fechaInicial.split('-');
        const fechaF = fechaFinal.split('-');
        data.append('campo_clinico[convenio]', convenio.id);
        data.append('campo_clinico[unidad]', unidad.data.id);
        data.append('campo_clinico[fechaInicial][year]', Number.parseInt(fechaI[0]));
        data.append('campo_clinico[fechaInicial][month]', Number.parseInt(fechaI[1]));
        data.append('campo_clinico[fechaInicial][day]', Number.parseInt(fechaI[2]));
        data.append('campo_clinico[horario]', horario);
        data.append('campo_clinico[lugaresSolicitados]', lugaresSolicitados);
        data.append('campo_clinico[fechaFinal][year]', Number.parseInt(fechaF[0]));
        data.append('campo_clinico[fechaFinal][month]', Number.parseInt(fechaF[1]));
        data.append('campo_clinico[fechaFinal][day]', Number.parseInt(fechaF[2]));
        data.append('campo_clinico[asignatura]', asignatura);
        data.append('campo_clinico[promocion]', promocion);
        data.append('campo_clinico[cicloAcademico]', cicloAcademico);
        return data;
    }

    const storeCampoClinico = (solicitud) => {
        setIsLoading(true)
        let data = createFormData();
        data.append('campo_clinico[solicitud]', solicitud.id);

        fetch(`${getSchemeAndHttpHost()}/ie/api/campo_clinico`, {
            method: 'post',
            body: data
        }).then(response => {
            setIsLoading(false);
            return response.json()
        }, error => {
            setIsLoading(false);
        }).then(json => {
            if (json.errors) {
                setErrores(json.errors);
            } else {
                callbackCampoClinico(json.data);
                setConvenio(null);
                setFechaInicial('');
                setFechaFinal('');
                setHorario('');
                setLugaresSolicitados(0);
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

    const getUnidades = () => {
        const result = [{value: '-', name: 'Seleccionar ...'}];
        unidades.forEach(item => {
            result.push(
              {value: item.id.toString(),
               label: item.claveUnidad + '-' + item.nombre,
              data: item});
        })
        if (unidades.length === 1 && unidades[0].isUMAE) {
            setUnidad({value: item.id.toString(),
                label: item.claveUnidad + '-' + item.nombre,
                data: item})
        }
        return result;
    }

    const openModal = () => {
        setAlert(Object.assign(alert, {
            show: false,
            message: ''
        }));
        setShowModal(true);
    }

    const formatConvenioOptionLabel = (c) => (
        <div>
            <div style={{ fontWeight: "bold" }}>{c.nombre}</div>
            <div style={{ fontSize: "12px", color: "gray" }}>
                {c.ciclosAcademicosFormatted} - {c.nivelAcademico?.nombre} - {c.carrera.nombre}. Vigencia : {c.vigenciaFormatted}
            </div>
        </div>
    );

    return (
      isLoading ? <Loader show={isLoading} />
        :
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
                          <Select id={'carrera'}
                                  options={getConveniosActivos(convenios)}
                                  required
                                  formatOptionLabel={formatConvenioOptionLabel}
                                  onChange={value => handleConvenioEvent(value)}>
                          </Select>
                          <span className="help-block">{errores.convenio ? errores.convenio[0] : ''}</span>
                      </div>
                  </div>
              </div>
              <div className="row">
                  <div className="col-md-12">
                      <div className={`form-group ${errores.convenio ? 'has-error has-feedback' : ''}`}>
                          <label htmlFor="ciclo_academico">Ciclo Academico</label>
                          <select name="ciclo_academico" id="ciclo_academico" className={'form-control'}
                                  form="campo-clinico-form"
                                  onChange={e => setCicloAcademico(e.target.value)}
                                  required={true}
                          >
                              <option value="">Seleccionar ...</option>
                              {getCiclosAcademicos().map((item, index) => {
                                  return (
                                    <option value={item.id} key={index}>{item.nombre}</option>
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
                                 min={getMinDateInicio()}
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
                                 min={getMinDateInicio()}
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
                                style={{display: (convenio && cicloAcademico && cicloAcademico.id === 2) ? 'block' : 'none'}}>&#160;</span>
                              <span
                                style={{display: (!convenio || (convenio && cicloAcademico && cicloAcademico.id !== 2)) ? 'block' : 'none'}}>[solo para Internado]</span>
                          </label>
                          <input id="promocion" className={'form-control'}
                                 form="campo-clinico-form"
                                 disabled={!convenio || (convenio && cicloAcademico && cicloAcademico.id !== 2)}
                                 required={convenio && cicloAcademico && cicloAcademico.id === 2}
                                 type="text" value={promocion} onChange={e => setPromocion(e.target.value)}/>
                          <span className="help-block">{errores.promocion ? errores.promocion[0] : ''}</span>
                      </div>
                  </div>
              </div>

              <div className="row">
                  <div className="col-md-12">
                      <div className={`form-group ${errores.unidad ? 'has-error has-feedback' : ''}`}>
                          <label htmlFor="unidad">Unidad Sede</label>
                          <Select id={'unidad'}
                                options={getUnidades()}
                                value={unidad}
                                required
                                onChange={value => setUnidad(value)}
                            />
                          <span className="help-block">{errores.unidad ? errores.unidad[0] : ''}</span>
                      </div>
                  </div>
              </div>

              <div className="row" >
                  <div className="col-md-7">
                      <div className={`form-group ${errores.asignatura ? 'has-error has-feedback' : ''}`}>
                          <label htmlFor="asignatura">Asignatura </label>
                          <input type="text"
                                 form="campo-clinico-form"
                                 maxLength={1500}
                                 id={'asignatura'}
                                 className={'form-control'} value={asignatura}
                                 onChange={e => setAsignatura(e.target.value)}/>
                          <span className="help-block">{errores.asignatura ? errores.asignatura[0] : ''}</span>
                      </div>
                  </div>
                  <div className="col-md-5">
                      <div className={`form-group ${errores.horario ? 'has-error has-feedback' : ''}`}>
                          <label htmlFor="horario">Horario del campo clínico (Opcional) </label>
                          <input id="horario" className={'form-control'}
                                 form="campo-clinico-form"
                                 type="text" value={horario} onChange={e => setHorario(e.target.value)}/>
                          <span className="help-block">{errores.horario ? errores.horario[0] : ''}</span>
                      </div>
                  </div>
              </div>

              <div className="row">
                  <div className="col-md-3">
                      <div className={`form-group ${errores.lugaresSolicitados ? 'has-error has-feedback' : ''}`}>
                          <label htmlFor="lugaresSolicitados">No. de lugares solicitados</label>
                          <input id={'lugaresSolicitados'}
                                 type="number" value={lugaresSolicitados}
                                 min={1}
                                 form="campo-clinico-form"
                                 className={'form-control'}
                                 onChange={e => setLugaresSolicitados(e.target.value)} required={true}/>
                          <span
                            className="help-block">{errores.lugaresSolicitados ? errores.lugaresSolicitados[0] : ''}</span>
                      </div>
                  </div>

              </div>

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
                            <div className="col-md-6">
                                <label htmlFor="btn_campo_clinico">&#160;</label>
                                <button id="btn_campo_clinico"
                                        type={'submit'}
                                        className={'form-control btn btn-success'}>Guardar Campo
                                    clínico
                                </button>
                            </div>
                  </div>
              </form>
          </Modal>
      </>
    );

}

export default CampoClinicoForm;