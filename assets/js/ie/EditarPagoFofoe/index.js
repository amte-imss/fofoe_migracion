import React from 'react';
import ReactDOM from 'react-dom'
import camelcaseKeys from 'camelcase-keys'
import { SOLICITUD } from "../../constants";
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

        if (amount < parseInt(solicitud.monto) - parseInt(solicitud.pagos.reduce(function(a,b){ return parseInt(a) + parseInt(b.monto); }, 0))) {
            errors.push("El monto ingresado no puede ser menor al faltante");
        }

        return errors;
    }

    const formSubmit = (e) =>{

        try{
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
        }catch(error){
            e.preventDefault();
        }
    };

  return (
    <form
      action={`${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/correccion-de-pago-fofoe`}
      method="post"
      encType='multipart/form-data'
      onSubmit= {formSubmit}
    >
    <div>
        <div className="row mt-10">
            <div className="col-md-6">
                <p className="mt-10">No de referencia:&nbsp; {solicitud.referenciaBancaria }</p>
                <p className="mt-10">Monto total del campo clínico:&nbsp; {solicitud.monto }</p>
            </div>
            <div className="col-md-6">
                <p>Comprobantes registrados:</p>
                <table className='table table-bordered'>
                    <thead className='headers'>
                    <tr>
                        <th>Comprobante Registrado</th>
                        <th>Fecha</th>
                        <th>Monto Validado</th>
                    </tr>
                    </thead>
                    <tbody>
                    {
                    solicitud.pagos.map((item) => (
                        <tr>
                            <td>{ item.comprobantePago }</td>
                            <td>{ item.fechaPago } </td>
                            <td>{ item.monto }</td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </div>
      </div>

      <div className="row mt-10">
        <div className="col-md-12">
            <p><strong>Observaciones:</strong></p>
            <p className="mt-10 mb-10">{ solicitud.pagos.observaciones }</p>
        </div>

        <div className="row">
            <div className="col-md-8">
                <p className="mt-10 mb-10 col-md-12"><strong>Registrar nuevo comprobante de pago</strong></p>
                <p className="mt-10 mb-10 col-md-12">Monto total del campo clínico a pagar: { parseInt(solicitud.monto) - parseInt(solicitud.pagos.reduce(function(a,b){ return parseInt(a) + parseInt(b.monto); }, 0)) }</p>
                <div className='hidden'>
                    <div className="form-group">
                    <input
                    className='form-control'
                    defaultValue={solicitud.id}
                    required={true}
                    name={`solicitud_comprobante_pago[pagos][${solicitud.pagos.length}][solicitud]`}
                    />
                    </div>
                </div>
                <div className='hidden'>
                    <div className="form-group">
                    <input
                    className='form-control'
                    defaultValue={solicitud.referenciaBancaria}
                    required={true}
                    name={`solicitud_comprobante_pago[pagos][${solicitud.pagos.length}][referenciaBancaria]`}
                    />
                    </div>
                </div>
                <div className="form-horizontal col-md-12">
                    <div className="form-group col-md-12">
                        <label className="forma-label">Fecha en la que se realizó el nuevo pago</label>
                        <input
                        className='form-control col-md-12'
                        type="date"
                        required={true}
                        name={`solicitud_comprobante_pago[pagos][${solicitud.pagos.length}][fechaPago]`}
                        />

                    </div>
                </div>

                <div className="form-horizontal col-md-12">
                    <div className="form-group col-md-12">
                        <label className="forma-label">Monto del comprobante nuevo de pago</label>
                        <input
                        className='form-control'
                        type="number"
                        required={true}
                        name={`solicitud_comprobante_pago[pagos][${solicitud.pagos.length}][monto]`}
                        onChange={e => setUserAmount(e.target.value)}
                        />
                        <span className="error-message">NOTA: El monto debe coincidir con el comprobante de pago.</span><br/>
                        <span className="error-message">{errores[0] ? errores[0] : ''}</span>
                    </div>
                </div>

                <p className="col-md-12">
                    ¿Requiere factura? &nbsp;
                    <label htmlFor="solicitud_comprobante_pago">
                    <input
                        type="checkbox"
                        id='solicitud_comprobante_pago'
                        name={`solicitud_comprobante_pago[pagos][${solicitud.pagos.length}][requiereFactura]`}
                    />
                    </label>
                </p>


                <div className='col-md-12 mt-10'>
                    <input
                    type="file"
                    name={`solicitud_comprobante_pago[pagos][${solicitud.pagos.length}][comprobantePagoFile]`}
                    required={true}
                    />
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
  document.getElementById('registrar-component')
);
