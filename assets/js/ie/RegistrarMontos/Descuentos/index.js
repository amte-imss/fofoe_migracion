import * as React from 'react'

const RegistrarDescuentos = (props) => {
  const [inputList, setInputList] = React.useState(
    props.descuentos ?
      props.descuentos.map((desc, i) => {
        return {
          numAlumnos: desc.numAlumnos,
          descuentoInscripcion: desc.descuentoInscripcion,
          descuentoColegiatura: desc.descuentoColegiatura
        }
      })
      : []);
  const [errorDesc, setErrorDesc] = React.useState(false);

  const maxLugaresAutorizados = props.campo.lugaresAutorizados - props.campo.totalTrabajadoresBecados;

  const validate = (list) => {
    const totalAlumDesc = list.reduce((acc, elem) =>
        {return acc + (elem.numAlumnos ? parseInt(elem.numAlumnos) : 0) }, 0);
    setErrorDesc(totalAlumDesc > maxLugaresAutorizados);
    return totalAlumDesc <= maxLugaresAutorizados;
  }

  // handle click event of the Remove button
  const handleRemoveClick = (e, index) => {
    e.preventDefault();
    const list = [...inputList];
    list.splice(index, 1);
    setInputList(list);
    props.onChange(props.indexMonto, list, props.campo.id, validate(list));
  };

  // handle click event of the Add button
  const handleAddClick = (e) => {
    e.preventDefault();
    setInputList([...inputList,
      { numAlumnos: "",
        descuentoInscripcion: "",
        descuentoColegiatura: "" }]);
  };

  const handleInputChange = (e, index, name) => {
    e.preventDefault();
    const { nameF, value } = e.target;
    const list = [...inputList];
    list[index][name] = value;
    setInputList(list);
    if (props.indexMonto >= 0) {
      props.onChange(props.indexMonto, list, props.campo.id, validate(list));
    }
  };

  return (
    <div className={'form-inline'}>
      {inputList.map((x, i) => {
        const prefixName = `${props.prefixName}[${i}]`
        const descId = `${props.carrera.id}-${i}`;
        return (
          <div className="row mb-5 " key={i}>
            <div className={'form-group col-md-3'}>
                  { i===0 ?
                    <label htmlFor="numAlumnos">Número de Alumnos Becados</label>
                    : null
                  }
                  <div className={"input-group col-sm-10 mr-10"}>
                    <input
                      className={'form-control'}
                      name={`${prefixName}[numAlumnos]`}
                      type="number"
                      required
                      min={1}
                      step={1}
                      placeholder="# de Alumnos "
                      value={x.numAlumnos}
                      onChange={e => handleInputChange(e, i, 'numAlumnos')}
                    />
                  </div>
            </div>
            <div className={'form-group col-md-3'}>
              {
                i===0 ?
                  <label htmlFor="descuentoInscripcion">Porcentaje descuento Inscripción</label>
                  : null
              }
              <div className="input-group mr-10 col-sm-10">
                <div className="input-sm input-group-addon">%</div>
                <input
                  className='form-control'
                  name={`${prefixName}[descuentoInscripcion]`}
                  type="number"
                  required
                  min={0}
                  step={0.01}
                  max={100}
                  placeholder="desc Inscripción"
                  value={x.descuentoInscripcion}
                  onChange={e => handleInputChange(e, i, 'descuentoInscripcion')}
                />
              </div>
            </div>
            <div className="form-group col-md-3">
              {i === 0 ?
                <label htmlFor="descuentoColegiatura">Porcentaje descuento Colegiatura</label>
                : null
              }
              <div className="input-sm input-group mr-10 col-sm-10">
                <div className="input-sm input-group-addon">%</div>
                <input
                  className='form-control '
                  name={`${prefixName}[descuentoColegiatura]`}
                  type="number"
                  required
                  min={0}
                  step={0.01}
                  max={100}
                  placeholder="desc Colegiatura"
                  value={x.descuentoColegiatura}
                  onChange={e => handleInputChange(e, i,'descuentoColegiatura')}
                />
              </div>
            </div>
            <div className="form-group col-md-1">
              <div className={ (i=== 0 ? 'mt-30 ' : '') + "input-group"}>
                {inputList.length !== 0 &&
                <button
                  className="btn btn-link"
                  onClick={(e) => {handleRemoveClick(e, i)}}
                >Eliminar</button>}
              </div>
            </div>
            { errorDesc ?
              <div className='col-md-10'>
              <span className="help-block">El número total de alumnos con descuento
              no puede ser mayor al máximo de lugares disponibles ({maxLugaresAutorizados}) para este campo clínico</span>
              </div>
              : null
            }
          </div>
        );
      })}
      <button
        onClick={handleAddClick}
        className={'btn btn-light'}
        disabled={errorDesc}
      >Agregar descuento</button>
    </div>
  );
}

export default RegistrarDescuentos;