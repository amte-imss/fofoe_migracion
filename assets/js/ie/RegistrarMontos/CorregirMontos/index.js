import React, {Fragment} from 'react'
import RegistrarDescuentos from "../Descuentos";

const CorregirMontos = (props) => {
  return (
    props.montos.map((monto, index) =>
      <Fragment key={index}>
        <tr key={index}>
          <td>{monto.carrera.nivelAcademico.nombre}</td>
          <td>{monto.carrera.nombre}</td>
          <td className='hidden'>
            <input
              className='form-control'
              type="number"
              min={1}
              step={0.01}
              defaultValue={monto.carrera.id}
              name={`solicitud_validacion_montos[montosCarreras][${index}][carrera]`}
            />
          </td>
          <td className="form-inline">
            <div className="form-group">
              <div className="input-group">
                <div className="input-group-addon">$</div>
                <input
                  className='form-control'
                  type="text"
                  min={1}
                  step={0.01}
                  name={`solicitud_validacion_montos[montosCarreras][${index}][montoInscripcion]`}
                  className="form-control solicitud_validacion_montos_inscripcion"
                  defaultValue={monto.montoInscripcion}
                  required={true}
                  onBlur={e => props.handleCurrency(e.target)}
                />
              </div>
            </div>
          </td>
          <td className="form-inline">
            <div className="form-group">
              <div className="input-group">
                <div className="input-group-addon">$</div>
                <input
                  className='form-control'
                  type="text"
                  name={`solicitud_validacion_montos[montosCarreras][${index}][montoColegiatura]`}
                  id="solicitud_validacion_montos_inscripcion"
                  className="form-control solicitud_validacion_montos_inscripcion"
                  defaultValue={monto.montoColegiatura}
                  required={true}
                  onBlur={e => props.handleCurrency(e.target)}
                />
              </div>
            </div>
          </td>
        </tr>
        <tr className={'desc'}>
          <td colSpan={5}>
            <p className='background' > {props.observaciones} </p>
          </td>
        </tr>
        <tr className={'desc'}>
          <td colSpan={5}>
            <RegistrarDescuentos
              prefixName={`solicitud_validacion_montos[montosCarreras][${index}][descuentos]`}
              carrera={monto.carrera}
              descuentos={monto.descuentos}
            />
          </td>
        </tr>
      </Fragment>
    )

  );
}

export default CorregirMontos;

