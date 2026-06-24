import * as React from 'react'
import SelectSearch from "react-select-search";
import {validaMatriculaGet} from "../../api/validaMatricula";

const DescuentosTrabajadoresForm = ({delegaciones, becasAutorizadas,
                                      trabajadoresValidados, setTrabajadoresValidados,
                                      trabajadoresBecados, setTrabajadoresBecados}) => {

  const [opcionesOOAD, setOpcionesOOAD] = React.useState([])
  const [enValidacion, setEnValidacion] = React.useState(false)

  React.useEffect(() => {
    setTrabajadoresValidados(false)
    setTrabajadoresBecados(
      Array.from(Array(becasAutorizadas).keys()).map( (i) => {
        return makeObjTrabajadorImss('', { value:'0', nombre:'' });
      })
    )
    setOpcionesOOAD(getDelegaciones())
  },[becasAutorizadas]);

  const makeObjTrabajadorImss = (matricula_ini='', delegacion_ini = null) => {
    return {
      pendienteValidacion: true,
      validado: null,
      matricula: matricula_ini,
      delegacion: delegacion_ini,
      nombre: '',
      aPaterno: '',
      aMaterno: '',
      curp:'',
      correo:'',
      adscripcion:'',
      adscripcionId:null,
      unidad:'',
      message:'',
      errorMatricula:'',
      errorDelegacion:'',
    }
  }

  const getDelegaciones = () => {
    const result = [{value: '0', name: 'Seleccionar OOAD...'}];
    delegaciones.map(item => {
      result.push({value: item.id.toString(),
        name: item.id + '-' + item.nombre   });
    })
    return result;
  }

  const findDelegacion = (value) => {
    let delegacion = null;
    for (const i of opcionesOOAD) {
      if ((i.value).toString() === value.toString()) {
        delegacion = i;
        break;
      }
    }
    return delegacion;
  }

  const MarkCheck = () => {
    return (
      <p className='right'>&#10004;</p>
    )
  }

  const MarkWrong = () => {
    return (
      <p className='wrong'>&#10006;</p>
    )
  }

  const cleanErrors = () => {
    const list = [...trabajadoresBecados];
    list.forEach((item, index) => {
      list[index]['errorMatricula'] = ''
      list[index]['errorDelegacion'] = ''
    })
    setTrabajadoresBecados(list)
  }

  const validateFields = () => {
    cleanErrors();
    let validate = true;
    const list = [...trabajadoresBecados];
    list.forEach((item, index) => {
      validate = validate && ( '' !== item.matricula && '0' !== item.delegacion.value );
      list[index]['errorMatricula'] = item.matricula ? '' : 'Este campo es obligatorio. Ingrese la matricula'
      list[index]['errorDelegacion'] = '0' !== item.delegacion.value ? '' : 'Este campo es obligatorio. Selecciona una OOAD'
    })
    setTrabajadoresBecados(list)
    return validate;
  }

  const handleValidationMatriculas = (e) => {
    //e.preventDefault();
    if (!validateFields()) return;
    setEnValidacion(true)

    const list = [...trabajadoresBecados]
    list.forEach( (item, i) => {
      //if (!item.pendienteValidacion) return item
      validaMatriculaGet(item.matricula, item.delegacion.value)
        .then((res) =>   {
            list[i]['pendienteValidacion'] = false;

            if (res.hasOwnProperty('data')) {
              list[i].nombre = res.data.nombre;
              list[i].aPaterno = res.data.apaterno;
              list[i].aMaterno = res.data.amaterno;
              list[i].curp = res.data.curp;
              list[i].correo = res.data.correo;
              list[i].adscripcion = res.data.adscripcion;
              list[i].adscripcionId = res.data.adscripcionId;
              list[i].unidad = res.data.unidad;
              list[i].validado = true
            } else {
              list[i].message = res.message
              list[i].validado = false
            }
          setTrabajadoresBecados([...list])
          }
        ).catch(() => {
        list[i].message = 'Ocurrió un error al intentar hacer la validación.'
        list[i].validado = false
      })
      return list[i]
    })
    setTimeout(() => {
      //setTrabajadoresBecados([...list])
      setEnValidacion(false)
      setTrabajadoresValidados(true)
    })
  }

  const onChangeMatricula = (e, index) => {
    const list = [...trabajadoresBecados];
    list[index] = makeObjTrabajadorImss(e.target.value, list[index]['delegacion'] )
    setTrabajadoresBecados(list)
    setTrabajadoresValidados(false)
  }

  const onChangeDelegacion = (value, index) => {
    const list = [...trabajadoresBecados];
    list[index] = makeObjTrabajadorImss(list[index]['matricula'], findDelegacion(value))
    setTrabajadoresBecados(list)
    setTrabajadoresValidados(false)
  }

  return (
    <>
      {
        trabajadoresBecados.map( (item, i) => {
          return (
            <div className='row' key={i}>
              <div className="col-md-3 ">
                <div className={`form-group ${item.errorMatricula ? 'has-error has-feedback' : ''}`}>
                  <input
                    type="number"
                    placeholder={`ingrese la matrícula- ${i+1}`}
                    min={0}
                    form="campo-clinico-form"
                    className={'form-control'}
                    value={item.matricula}
                    onChange={e => onChangeMatricula(e, i)}
                    required={true}/>
                  <span
                    className="help-block">{item.errorMatricula}</span>
                </div>
              </div>

              <div className="col-md-3">
                <div className={`form-group ${item.errorDelegacion ? 'has-error has-feedback' : ''}`}>
                  <SelectSearch id={'delegacion'}
                                search
                                options={opcionesOOAD}
                                required={true}
                                value={item.delegacion.value}
                                onChange={value => onChangeDelegacion(value, i)}/>
                  <span
                    className="help-block">{item.errorDelegacion}</span>
                </div>
              </div>

              <div className="col-md-1 ">
                <div className={`form-group`}>
                  {
                    !item.pendienteValidacion ?
                      ( item.validado ? <MarkCheck /> : <MarkWrong />)
                      : null
                  }
                </div>
              </div>

              <div className="col-md-5 ">
                {
                  !item.pendienteValidacion ?
                    <div className={`form-group`}>
                      {
                        item.validado ?
                          <span>{item.nombre} {item.aPaterno} {item.aMaterno} - {item.unidad} </span>
                          :
                          <span className='has-error has-feedback'>{item.message}</span>
                      }
                    </div>
                    : <span>Pendiente de Validación</span>
                }
              </div>

            </div>
          )}
        )
      }

      {
        !enValidacion && becasAutorizadas > 0 && !trabajadoresValidados ?
          <div className="row">
            <div className="col-md-3"> </div>
            <div className="col-md-6">
              <button
                className={'form-control btn btn-primary'}
                onClick={(e) => handleValidationMatriculas(e) }
              >Validar Matrícula(s)
              </button>
            </div>
          </div>
          : null
      }
    </>
  )
}

export default DescuentosTrabajadoresForm;