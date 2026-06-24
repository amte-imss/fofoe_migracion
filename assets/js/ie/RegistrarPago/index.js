import React from 'react';
import ReactDOM from 'react-dom'
import {getSchemeAndHttpHost} from "../../utils";

const Registrar = (
  {
    institucion,
    solicitud
  }) => {

  const [errores, setErrores] = React.useState({});
  const [userAmount, setUserAmount] = React.useState(0);

  function validate(amount) {

    const errors = [];

    if (amount < solicitud.monto) {
      errors.push("El monto ingresado no puede ser menor al de la solicitud");
    }

    return errors;
  }

  const handleSubmit = (e) => {

    setErrores({});

    let amount = userAmount;
    let errors = {};

    console.log(errors);
    console.log(amount);

    errors = validate(amount);
    console.log(errors);
    if (errors.length > 0) {
      setErrores(errors);
      e.preventDefault();
      return;
    }
  };


  return (
    <form
      action={`${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/cargar-comprobante`}
      method="post"
      encType='multipart/form-data'
      onSubmit= {handleSubmit}
    >
      <div>

        <div className="row mt-10">
          <div className="col-md-6">
            <p>RFC:&nbsp; {institucion.rfc}</p>
            <p className="mt-10">No de referencia:&nbsp; {solicitud.referenciaBancaria}</p>
          </div>
          <div className="col-md-6">
            <p><strong>Monto total</strong> por pagar: {solicitud.monto ? solicitud.monto : ""}</p>
            <p className="mt-10">No de comprobantes de pago aa registrar</p>
          </div>

          <div className="col-md-12 mb-10">
            <div className="panel panel-default">
              <div className="panel-body">
                <table className='table'>
                  <thead className='headers'>
                  <tr>
                    <th>Monto</th>
                    <th>Fecha de Pago</th>
                    <th>Comprobante</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td className='hidden'>
                      <div className="form-group">
                        <input
                          className='form-control'
                          defaultValue={solicitud.id}
                          name={`solicitud_comprobante_pago[pagos][0][solicitud]`}
                        />
                      </div>
                    </td>
                    <td className='hidden'>
                      <div className="form-group">
                        <input
                          className='form-control'
                          defaultValue={solicitud.referenciaBancaria}
                          name={`solicitud_comprobante_pago[pagos][0][referenciaBancaria]`}
                        />
                      </div>
                    </td>
                    <td>
                      <div className="form-group">
                        <input
                          className='form-control'
                          type="number"
                          required={true}
                          name={`solicitud_comprobante_pago[pagos][0][monto]`}
                          onChange={e => setUserAmount(e.target.value)}
                        />
                        <span className="error-message">{errores[0] ? errores[0] : ''}</span>
                      </div>
                    </td>
                    <td>
                      <div className="form-group">
                        <input
                          className='form-control'
                          type="date"
                          required={true}
                          name={`solicitud_comprobante_pago[pagos][0][fechaPago]`}
                        />
                      </div>
                    </td>
                    <td>
                      <input
                        type="file"
                        name={`solicitud_comprobante_pago[pagos][0][comprobantePagoFile]`}
                        required={true}
                      />
                    </td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <p>
              ¿Requiere factura? &nbsp;
              <label htmlFor="solicitud_comprobante_pago">
                <input
                  type="checkbox"
                  id='solicitud_comprobante_pago'
                  name='solicitud_comprobante_pago[pagos][0][requiereFactura]'
                />
              </label>
            </p>
          </div>

          <div className="col-md-12 mb-10 mt-10 observaciones">
            <p className="observaciones"><strong>Nota:</strong> El monto a registrar debe coincidir con el comprobante
              registrado</p>
            <p className="observaciones">La suma total de los montos registrados debe ser igual al monto total.</p>

          </div>

          <div className="row">
            <div className="col-md-10"/>
            <div className="col-md-2">
              <button
                type="submit"
                className='btn btn-success btn-block'
              >
                Guardar
              </button>
            </div>
          </div>
        </div>

      </div>
    </form>
  )
}


ReactDOM.render(
  <Registrar
    institucion={window.RFC_PROP}
    solicitud={window.SOLICITUD_PROP}
  />,
  document.getElementById('cargar-comprobante-component')
);
