import React from "react";
import Select from 'react-select'
import {umaesGet, umaesNoSelcsGet, unidadesByDelegacionGet} from "../../api/unidad";
import {delegacionesGet, delegacionesNoSelecsGet} from "../../api/delegacion";
import ListConvenios from "../../../components/ListConvenios";
import {conveniosGet} from "../../api/convenios";
import CampoClinicoForm from "./CampoClinicoForm";
import CamposClinicos from "../../../came/solicitud/components/CamposClinicos";
import Loader from "../../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../../utils";

const FormularioSolicitud = ({solicitudPrev}) => {

  const [errores, setErrores] = React.useState({});
  const [alert, setAlert] = React.useState({});
  const [isLoading, setIsLoading] = React.useState(false);
  const [delegaciones, setDelegaciones] = React.useState([])
  const [umaes, setUmaes] = React.useState([])
  const [unidades, setUnidades] = React.useState([])
  const [convenios, setConvenios] = React.useState([])
  const [solicitud, setSolicitud] = React.useState(solicitudPrev);
  const [camposClinicos, setCamposClinicos] = React.useState([])
  const [ooadsUmaes, setOoadsUmaes] = React.useState([]);
  const DEFAULT_VALUE_SELECT = {value:'-', label: 'Seleccionar ...'}
  const [selectedOoadUmae, setSelectedOoadUmae] = React.useState(DEFAULT_VALUE_SELECT)

  React.useEffect(() => {
    getOadsUmaes().then((vals) => {
      getConvenios()
      if (solicitudPrev) {
        setCamposClinicos(solicitudPrev.campoClinicos)
        handleSelectedOoadUmae(
          {
            value: `${solicitudPrev.esUMAE ? 'UMAE' : 'OOAD'}-${solicitudPrev.idDelUMAE}`,
            label: `${solicitudPrev.displayDelUMAE}`,
            isUMAE: solicitudPrev.esUMAE,
            data: solicitudPrev.esUMAE ?
              vals[1][solicitudPrev.idDelUMAE]
              : vals[0][solicitudPrev.idDelUMAE],
          })
      }
    })
  }, [solicitudPrev])

  const getOadsUmaes = () => {
    setIsLoading(true)
    const result = [DEFAULT_VALUE_SELECT];

    return Promise.all([getDelegaciones(), getUmaes(), delegacionesNoSelecsGet(), umaesNoSelcsGet()]).then((values) => {
      values[0].forEach((item => {
        if (item !== undefined && item.activo) {
          result.push({
            value: `OOAD-${item.id}`,
            label: `[OOAD] ${item.nombre}`,
            isUMAE: false,
            data: item,
            disabled: values[2].filter(selec => item.id === selec.id).length > 0
          })
        }
      }))

      values[1].forEach((item => {
        if (item !== undefined) {
          result.push({
            value: `UMAE-${item.id}`,
            label: `[UMAE] - ${values[0][item.delegacion.id].nombre} / ${item.nombre} `,
            isUMAE: true,
            data: item,
            disabled: values[3].filter(selec => item.id === selec.id).length > 0
          })
        }
      }))

      setOoadsUmaes(result)
      setIsLoading(false)

      return [values[0], values[1]]
    })

  }

  const handleSelectedOoadUmae = (item) => {
    setSelectedOoadUmae(item)
    if (item.value !== '-' && item.isUMAE ) {
      setUnidades([item.data])
    } else if (item.value !== '-' && !item.isUMAE) {
      unidadesByDelegacionGet(item.data.id).then(json => {
        setUnidades(json.data)
      })
    }
  }

  const getDelegaciones = () => {
    return delegacionesGet().then(
      (json) => {
        let dels = []
        json.data.forEach(item => {
          dels[item.id] = item
        });
        setDelegaciones(dels)
        return dels
      }
    )
  }

  const getUmaes = () => {
    return umaesGet().then(
      (json) => {
        let u = []
        json.data.forEach(item => {
          u[item.id] = item
        });
        setUmaes(u)
        return u
      }
    )
  }

  const getConvenios = () => {
    setIsLoading(true)
    conveniosGet().then((json) => {
      setConvenios(json.data)
      setIsLoading(false)
    })
  }

  const handleDeleteEvent = (campo) => {
    setIsLoading(true);
    fetch(`${getSchemeAndHttpHost()}/ie/api/campo_clinico/${campo}`, {
      method: 'delete'
    }).then(response => {
      return response.json()
    }, error => {
      console.error(error);
    }).then(json => {
      if (json.status) {
        removeCampo(campo);
      }
    }).finally(() => {
      setIsLoading(false);
    });
  }

  const removeCampo = (campo) => {

    const nuevos = [];
    camposClinicos.map(item => {
      if (item.id.toString() !== campo.toString()) {
        nuevos.push(item);
      }
    })
    setCamposClinicos(nuevos);
  }

  const callbackCampoClinico = (campo) => {
    const campos = camposClinicos.slice(0);
    campos.push(campo);
    setCamposClinicos(campos);
  }

  const handleSolicitudSubmit = (event) => {
    event.preventDefault();
    setIsLoading(true);

    fetch(`${getSchemeAndHttpHost()}/ie/api/solicitud/terminar/${solicitud.id}` , {
      method: 'post'
    }).then(response => {
      return response.json()
    }, error => {
      console.error(error);
    }).then(json => {
      if(json.status){
        new Promise((resolve, reject) => {
          setTimeout(() => {
            resolve()
          }, 250)
        }).then(() => {
          document.location.href = `${getSchemeAndHttpHost()}/ie/inicio`;
        });
      }
    }).finally(() => {
      setIsLoading(false);
    });
  }

  return (
      isLoading ?
      <Loader show={isLoading}/>
      :
      <div>
        <div className="row">
          <div className="col-md-12 mb-5">
            {
              camposClinicos.length !== 0 ?
                <h3>Seleccione la OOAD o UMAE en donde desea solicitar campo(s) clínico(s)</h3>
                :
                <h3>OOAD / UMAE en donde solicita campo(s) clínico(s)</h3>
            }
          </div>
        </div>
        <div className="row">
          <div className="col-md-12">
            <div className={`form-group ${errores.ooad_umae ? 'has-error has-feedback' : ''}`}>
              <div className={`alert alert-${alert.type} `}
                   style={{display: (alert.show ? 'block' : 'none')}}>
                <a className="close" onClick={e => setAlert({})}>&times;</a>
                {alert.message}
              </div>
              <Select
                id={'ooad_umae'}
                options={ooadsUmaes}
                onChange={value => handleSelectedOoadUmae(value)}
                value={selectedOoadUmae}
                isDisabled={camposClinicos.length !== 0}
                isOptionDisabled={(opt) => opt.disabled}
                required
              />
              <span className="help-block">{errores.ooad_umae ? errores.ooad_umae[0] : ''}</span>
            </div>
          </div>
        </div>
        <div className='row'>
          <ListConvenios
            convenios={convenios}
          />
        </div>
        <div className='row'>
          {
            selectedOoadUmae.value !== '-' ?
              <CampoClinicoForm
                solicitudPrev={solicitudPrev}
                unidades={unidades}
                convenios={convenios}
                callbackCampoClinico={callbackCampoClinico}
                callbackSolicitud = {value => setSolicitud(value)}
              />
              : null
          }
          <CamposClinicos
            campos={camposClinicos}
            handleDelete={handleDeleteEvent}/>

          <form onSubmit={handleSolicitudSubmit}
                style={{display: (camposClinicos.length > 0 ? 'block' : 'none')}}>
            <div className="row">
              <div className="col-md-12">
                <label htmlFor="btn_solicitud">&#160;</label>
                <button id="btn_solicitud" className={'form-control btn btn-success'}>Terminar Solicitud</button>
              </div>
            </div>
          </form>
        </div>
      </div>
  )
}

export default FormularioSolicitud