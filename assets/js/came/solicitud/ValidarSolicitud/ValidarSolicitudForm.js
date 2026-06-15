import React, {Fragment} from "react";
import {getSchemeAndHttpHost} from "../../../utils";

const ValidarSolicitudForm = ({solicitud, setIsLoading}) => {

  const [camposClinicos, setCamposClinicos] = React.useState(solicitud.campoClinicos)
  const [errores, setErrores] = React.useState({})
  const [alert, setAlert] = React.useState({});

  const handleSolicitudValidaCampos = (event) => {
    event.preventDefault();
    setIsLoading(true)
    let data = new FormData();

    camposClinicos.map((campo, i) => {
      data.append(`solicitud[campo_${campo.id}][lugaresAutorizados]`, campo.lugaresAutorizados);
      data.append(`solicitud[campo_${campo.id}][obsValRegistro]`, campo.obsValRegistro ?? '');
    });

    fetch(`${getSchemeAndHttpHost()}/came/api/solicitud/${solicitud.id}/validar` , {
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
          document.location.href = `${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}`;
        });
      }
    }).finally(() => {
      setIsLoading(false);
    })

  }

  return (
    <div>
      <div className="col-md-12">
        <div className={`alert alert-${alert.type} `}
             style={{display: (alert.show ? 'block' : 'none')}}>
          <a className="close" onClick={e => setAlert({})}>&times;</a>
          {alert.message}
        </div>
      </div>
      <form onSubmit={handleSolicitudValidaCampos}>
        <div className="row">
          <div className='col-md-12'>
            <table className="table">
              <thead>
              <tr>
                <th>Ciclo</th>
                <th>Carrera</th>
                <th>Período</th>
                <th>Sede</th>
                <th>Lugares Solicitados</th>
                <th>Lugares Autorizados</th>
              </tr>
              </thead>
              <tbody>
              {camposClinicos.map((campo, i) =>{
                return (
                  <Fragment key={i}>
                    <tr key={i}>
                      <td>
                        {campo.cicloAcademico.nombre}
                        {
                          campo.promocion ?
                            <div>Promoción: {campo.promocion}</div>
                            : null
                        }
                      </td>
                      <td>
                        <div>
                          {campo.convenio.carrera.nivelAcademico ? campo.convenio.carrera.nivelAcademico.nombre : ''} - {campo.convenio.carrera.nombre}
                        </div>
                        {
                          campo.asignatura ?
                            <div>Asignatura: {campo.asignatura}</div>
                            : null
                        }
                      </td>
                      <td>
                        <div>{campo.fechaInicialFormatted}-{campo.fechaFinalFormatted}</div>
                        {
                          campo.horario ?
                            <div>Horario: {campo.horario}</div>
                            : null
                        }
                      </td>
                      <td>{campo.unidad.nombre}</td>
                      <td className='text-center'>{campo.lugaresSolicitados}</td>
                      <td>
                        <div className={`form-group ${errores['campo_' + campo.id] ? 'has-error has-feedback' : ''}`}>
                          <input className="form-control"
                                 type="number"
                                 value={campo.lugaresAutorizados ?? '' }
                                 min={0}
                                 max={campo.lugaresSolicitados}
                                 step="1"
                                 required={true}
                                 style={{minWidth: '80px'}}
                                 onChange={e => { camposClinicos[i].lugaresAutorizados = e.target.value; setCamposClinicos(Object.assign([], camposClinicos))}}
                          />
                          <span className="help-block">{errores['campo_' + campo.id] ? errores['campo_' + campo.id] : ''}</span>
                        </div>
                      </td>
                    </tr>
                    <tr className={'without-border-top'}>
                      <td colSpan={5}>
                        <div className="col-md-10">
                          <div className={`form-group ${errores.obsValRegistro ? 'has-error has-feedback' : ''}`}>
                            <label htmlFor="obsValRegistro_campo">Observaciones</label>
                            <textarea
                              className={'form-control'}
                              placeholder={'Observaciones'}
                              required={campo.lugaresAutorizados <= 0}
                              onChange={e => { camposClinicos[i].obsValRegistro = e.target.value; setCamposClinicos(Object.assign([], camposClinicos))}}
                            />
                            <span className="help-block">{errores.obsValRegistro ? errores.obsValRegistro[0] : ''}</span>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </Fragment>
                )
              })}
              </tbody>
            </table>
          </div>
        </div>
        <div className='row'>
          <div className="col-md-4 col-md-offset-7">
            <label htmlFor="btn_solicitud">&#160;</label>

            <button id="btn_solicitud"
                    className={`form-control btn btn-primary`}
            >
              Guardar Validación
            </button>
          </div>
        </div>
      </form>
    </div>
  )
}

export default ValidarSolicitudForm;