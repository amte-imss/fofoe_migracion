import { createRoot } from "react-dom/client";
import React from 'react';
import {dateFormat, getSchemeAndHttpHost, moneyFormat} from "../../utils";
import Cleave from 'cleave.js/react';


const Registrar = (
  {
    institucion,
    solicitud,
    pagos,
    errors
  }) => {

  let monto = 0;
  let factura = false;

  const [fileName, setFileName] = React.useState('');

  pagos.forEach(element => {
    if(element.facturaGenerada !== true || !element.factura.id)
      monto += parseFloat(element.monto);

    if(element.factura)
      factura = true;
  });

  const [total, setTotal] = React.useState(monto);

  const handleChecked = (event) => {
    let subtotal = total;
    if (event.checked) subtotal += parseFloat(pagos[event.id].monto);
    else subtotal -= parseFloat(pagos[event.id].monto);
    setTotal(subtotal);
  }

  const handleChangeFile = (event) => {
    setFileName(event.target.files[0].name);
  }

  return (
    <form
      action={`${getSchemeAndHttpHost()}/fofoe/pagos/${pagos[0].id}/registrar-factura`}
      method="post"
      encType='multipart/form-data'
    >
      <div>
        <div className="row mt-10">
          <div className="col-md-8">
            <p className="mt-10 mb-10">Pago por solicitud de Campo(s) Clínico(s)</p>
            <p className="mt-10 mb-10">Institución educativa: {institucion.nombre}</p>
            <p className="mt-10 mb-10">Razón Social: {institucion.razonSocial}</p>
            <p className="mt-10 mb-10">RFC: {institucion.rfc}</p>
            <p className="mt-10 mb-10">Cédula de identificación fiscal: {
                institucion.cedulaIdentificacion &&
                <a
                  href={`${getSchemeAndHttpHost()}/fofoe/instituciones/${institucion.id}/descargar-cedula-de-identificacion`}
                  download
                >
                  Descargar cédula
                </a>
            }</p>
            <p className="mt-10 mb-10">OOAD: {solicitud.delegacion.nombre}</p>
            {
              solicitud.unidad && solicitud.unidad.esUmae ?
                <p className='mb-5'>UMAE: <strong>{solicitud.unidad.nombre}</strong></p>
                : null
            }
            <p className="mt-10 mb-10">Referencia de pago: {pagos[0].referenciaBancaria}</p>
            <div className="form-inline mt-10 mb-10">
              <div className="form-group">
                <label>Monto total a pagar &nbsp;</label>
                  <div className={`input-group`}>
                    <div className="input-group-addon">$</div>
                    <Cleave
                      options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                      className='mb-10 form-control col-md-1'
                      value={"$ " + pagos[0].camposPagados.campos.map(campo =>  campo.monto)
                        .reduce((acc, monto) => acc + monto , 0) }
                      disabled={true}
                    />
                  </div>
            </div>
          </div>


          </div>
          <div className="col-md-4">
            <p className="mt-10 mb-10">No. de solicitud: {solicitud.noSolicitud}</p>
            <p className="mt-10 mb-10">Fecha de solicitud: {solicitud.fecha}</p>
          </div>
        </div>
        <div className="row mt-10">
          <div className="col-md-12">
            <p className="mt-10 mb-10"><strong>Comprobantes pendientes de facturación</strong></p>
            <p className="mt-10 mb-10">Seleccione los comprobantes de pago que estarán asociados a la factura a
              emitir.</p>
            <div className="form-inline mt-10 mb-10">
              <div className="form-group">
                <label>Monto total a facturar &nbsp;</label>
                <div className="input-group">
                  <div className="input-group-addon">$</div>
                  <Cleave
                      options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                      className='mb-10 form-control col-md-1'
                      value={total}
                      disabled={true}
                    />
                  <input
                    className='form-control'
                    type="hidden"
                    readOnly={true}
                    value={total}
                    name={`pago_factura[factura][monto]`}
                  />
                </div>
              </div>
            </div>

            <div className='hidden'>
              <div className="form-group">
                <input
                  className='form-control'
                  defaultValue={solicitud.id}
                  required={true}
                  name={`pago_factura[factura][aux]`}
                />
              </div>
            </div>
            <div className="col-md-6">
              <table className='table'>
                <thead className='headers'>
                <tr>
                  <th></th>
                  <th>Comprobante de pago</th>
                  <th>Fecha</th>
                  <th>Monto validado</th>
                  <th>Referencia Bancaria</th>
                </tr>
                </thead>
                <tbody>
                {
                  pagos.map((item, index) =>
                    //(item.facturaGenerada != true && item.validado == true) ?
                    (item.facturaGenerada !== true || !item.factura.id ) ?
                      <tr key={index}>
                        <td>
                          <input
                            type="checkbox"
                            //onChange={e => handleChecked(e.target)}
                            id={index}
                            checked={true}
                            readOnly={true}
                            name={`pago_factura[facturaGenerada]`}
                          />
                        </td>
                        <td><a href={`${getSchemeAndHttpHost()}/fofoe/pagos/${item.id}/descargar-comprobante-de-pago`} download>{item.comprobantePago}</a></td>
                        <td>{item.fechaPagoFormatted}</td>
                        <td>{ moneyFormat(item.monto) }</td>
                        <td>{item.referenciaBancaria}</td>
                      </tr>
                      :
                      <div></div>
                  )

                }
                </tbody>
              </table>
            </div>
            <div className="col-md-1"/>
            <div className="col-md-2">
              <p className="mb-10">Subir factura</p>
              <input
                type="file"
                accept=".zip"
                onChange={handleChangeFile}
                name={`pago_factura[factura][zipFile]`}
                required={true}
              />
              <span className="text-danger ">{errors.pago_factura ? errors.pago_factura[0] : ''}</span>
            </div>
          </div>
        </div>

        <div className="row">
          <div className="col-md-6">
            <div className="form-group col-md-12">
              <label>Folio de la factura &nbsp;</label>
                <input
                  className='form-control'
                  type="text"
                  required
                  defaultValue={fileName ? fileName.toString().replace('.zip', '').replace('.ZIP', '') : ''}
                  readOnly={true}
                  name={`pago_factura[factura][folio]`}
                />
            </div>
          </div>

          <div className="col-md-6">
            <div className="form-group col-md-12">
                <label className="forma-label">Fecha de factura</label>
                <input
                className='form-control col-md-12'
                type="date"
                required={true}
                name={`pago_factura[factura][fechaFacturacion]`}
                />
            </div>
          </div>
        </div>

        <div className="row">
          <div className="col-md-12">
            <p className="mt-10 mb-10"><strong>Facturas generadas</strong></p>
            <table className='table'>
                <thead className='headers'>
                <tr>
                  <th>Fecha Facturación</th>
                  <th>Monto Facturado</th>
                  <th>Comprobante</th>
                  <th>Archivo Factura</th>
                  <th>Folio Factura</th>
                </tr>
                </thead>
                <tbody>
                {
                  factura ?

                    pagos.map((item, index) =>
                    (item.factura) && (item.factura.id > 0) ?
                        <tr key={index}>
                          <td> {dateFormat(item.factura.fechaFacturacion)}</td>
                          <td> {item.factura.monto}</td>
                          <td>{item.comprobantePago && <a href={`${getSchemeAndHttpHost()}/fofoe/pagos/${item.id}/descargar-comprobante-de-pago`} download>{item.comprobantePago}</a>}</td>
                          <td>{item.factura.zip && <a href={item.factura.zip} download>{item.factura.zip}</a>}</td>
                          <td>{item.factura.folio}</td>
                        </tr>
                        :
                        ''
                    )
                    :
                    <tr>
                      <td className='text-center text-info' colSpan={5} ><strong>No hay registros disponibles</strong></td>
                    </tr>
                }
                </tbody>
              </table>
          </div>
        </div>
        <div className='row'>
          <div className='col-md-10'></div>
          <div className='col-md-2'>
              <button
                type="submit"
                className='btn btn-success btn-block'
              >
                Guardar
              </button>
          </div>
        </div>
      </div>
    </form>
  )
}

const rootElement = document.getElementById('registrar-factura-component');
if (rootElement) {
  const root = createRoot(rootElement);
    root.render(
        <Registrar
            institucion={window.INSTITUCION_PROP}
            solicitud={window.SOLICITUD_PROP}
            pagos={window.PAGOS_PROP}
            errors={window.ERRORS_PROPS}
        />
    );
}
