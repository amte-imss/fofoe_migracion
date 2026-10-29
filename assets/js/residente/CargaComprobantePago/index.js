import * as React from 'react';
import { createRoot } from 'react-dom/client';
import { dateFormat, getSchemeAndHttpHost, moneyFormat } from '../../utils';
import Cleave from 'cleave.js/react';
import Modal from 'react-modal';
import PreviewDocument from '../../components/PreviewDocument/PreviewDocument';
import Loader from '../../components/Loader/Loader';

const errorText = (errors, name) => {
  const value = errors?.[name] || errors?.[`comprobante_pago[${name}]`];
  return Array.isArray(value) ? value.join(' ') : (value || '');
};

const displayDate = value => {
  if (!value) return '—';
  const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
  return match ? `${match[3]}/${match[2]}/${match[1]}` : dateFormat(value);
};

function DatosResidencia({ residencia }) {
  const residente = residencia.residente || {};
  const usuario = residente.usuario || {};
  const referencia = residencia.pagos?.[0]?.referenciaBancaria || residencia.lastPago?.referencia || '—';

  return <section className="resident-card mb-4">
    <div className="resident-card-header"><i className="ti ti-id"/><div><h2>Datos de la residencia</h2><p>Verifica la información antes de registrar el pago.</p></div></div>
    <div className="resident-card-body resident-info-grid">
      <div className="resident-field"><span>Residente</span><strong>{[usuario.nombre, usuario.apellidoPaterno, usuario.apellidoMaterno].filter(Boolean).join(' ') || '—'}</strong></div>
      <div className="resident-field"><span>CURP</span><strong>{usuario.curp || '—'}</strong></div>
      <div className="resident-field"><span>Nacionalidad</span><strong>{residente.nacionalidad || '—'}</strong></div>
      <div className="resident-field"><span>Ciclo académico</span><strong>{residencia.ciclo || '—'}</strong></div>
      <div className="resident-field"><span>Especialidad</span><strong>{residencia.especialidad || '—'}</strong></div>
      <div className="resident-field"><span>Sede</span><strong>{residencia.sede || '—'}</strong></div>
      <div className="resident-field"><span>Folio / número de oficio</span><strong>{residencia.folio || '—'}</strong></div>
      <div className="resident-field"><span>Referencia bancaria</span><strong>{referencia}</strong></div>
      {residencia.fechaInicio && <div className="resident-field"><span>Período</span><strong>{displayDate(residencia.fechaInicio)} – {displayDate(residencia.fechaTermino)}</strong></div>}
    </div>
  </section>;
}

function ComprobantesCargados({ residencia, lastPago }) {
  const anteriores = (residencia.pagos || []).filter(pago => pago.id !== lastPago.id && pago.comprobantePago);
  if (anteriores.length === 0) return null;

  return <section className="resident-card mb-4">
    <div className="resident-card-header"><i className="ti ti-files"/><div><h2>Comprobantes anteriores</h2><p>Documentos registrados para esta residencia.</p></div></div>
    <div className="table-responsive"><table className="table table-hover resident-table mb-0">
      <thead><tr><th>Comprobante</th><th>Fecha</th><th>Monto validado</th><th>Observaciones</th></tr></thead>
      <tbody>{anteriores.map(pago => <tr key={pago.id}>
        <td><a href={getSchemeAndHttpHost(`/residente/residencia/${residencia.id}/comprobante-pago/${pago.id}/download`)} target="_blank" rel="noopener noreferrer"><i className="ti ti-file-download mr-1"/>Descargar PDF</a></td>
        <td>{displayDate(pago.fechaPago)}</td>
        <td>{moneyFormat(pago.monto)} {pago.tipoMoneda || 'MXN'}</td>
        <td>{pago.observaciones || '—'}</td>
      </tr>)}</tbody>
    </table></div>
  </section>;
}

function FormCargaComprobante({ residencia, lastPago, errors, montoAPagar }) {
  const formRef = React.useRef(null);
  const [monto, setMonto] = React.useState('');
  const [fechaPago, setFechaPago] = React.useState('');
  const [tipoMoneda, setTipoMoneda] = React.useState('');
  const [otraMoneda, setOtraMoneda] = React.useState('');
  const [requiereFactura, setRequiereFactura] = React.useState('');
  const [comprobanteFile, setComprobanteFile] = React.useState(null);
  const [showModal, setShowModal] = React.useState(false);
  const [isLoading, setIsLoading] = React.useState(false);

  const handleSubmit = event => {
    event.preventDefault();
    setShowModal(true);
  };

  const confirmar = () => {
    setShowModal(false);
    setIsLoading(true);
    formRef.current.submit();
  };

  return <section className="resident-card resident-upload-card">
    <Loader show={isLoading}/>
    <div className="resident-card-header"><i className="ti ti-file-upload"/><div><h2>Registrar comprobante de pago</h2><p>Completa los datos tal como aparecen en el comprobante.</p></div></div>
    <div className="resident-card-body">
      <div className="resident-payment-summary mb-4">
        <div><span>Monto total a pagar</span><strong>{moneyFormat(residencia.monto)} {residencia.tipoMoneda}</strong></div>
        {residencia.tasaCambio && <div><span>Monto pendiente por pagar</span><strong>{moneyFormat(montoAPagar)} MXN</strong></div>}
      </div>

      {lastPago.observaciones && <div className="alert alert-info mb-4"><strong>Observaciones sobre el pago:</strong> {lastPago.observaciones}</div>}
      <form ref={formRef} action={getSchemeAndHttpHost(`/residente/pagos/${lastPago.id}/carga-de-comprobante-de-pago`)}
            method="post" encType="multipart/form-data" className="resident-upload-form" onSubmit={handleSubmit}>
        <div className="row">
          <div className="col-md-6 mb-3">
            <label htmlFor="comprobante_pago_fechaPago">Fecha en que se realizó el pago <span className="text-danger">*</span></label>
            <input type="date" id="comprobante_pago_fechaPago" className="form-control" name="comprobante_pago[fechaPago]"
                   value={fechaPago} onChange={event => setFechaPago(event.target.value)} required/>
            {errorText(errors, 'fechaPago') && <small className="text-danger">{errorText(errors, 'fechaPago')}</small>}
          </div>
          <div className="col-md-6 mb-3">
            <label htmlFor="comprobante_pago_monto">Monto del comprobante <span className="text-danger">*</span></label>
            <Cleave id="comprobante_pago_monto" options={{ numeral: true, numeralThousandsGroupStyle: 'thousand' }}
                    className="form-control" onChange={event => setMonto(event.target.value)} required/>
            <input type="hidden" name="comprobante_pago[monto]" value={monto.replace(/,/g, '')} readOnly/>
            <small className="resident-help">El monto debe coincidir con el comprobante.</small>
            {errorText(errors, 'monto') && <small className="text-danger d-block">{errorText(errors, 'monto')}</small>}
          </div>
        </div>

        <fieldset className="resident-form-group mb-3">
          <legend>Moneda del pago <span className="text-danger">*</span></legend>
          <div className="resident-options">
            {[['MXN', 'MXN · Pesos mexicanos'], ['USD', 'USD · Dólares'], ['EUR', 'EUR · Euros'], ['other', 'Otra']].map(([value, label]) =>
              <label className="resident-option" key={value}>
                <input type="radio" name="comprobante_pago[tipoMoneda]" value={value} checked={tipoMoneda === value}
                       onChange={() => setTipoMoneda(value)} required/>{label}
              </label>
            )}
          </div>
          {tipoMoneda === 'other' && <div className="resident-extra-input mt-2">
            <label htmlFor="tipoMoneda_otro">Especifica la moneda</label>
            <input className="form-control" type="text" id="tipoMoneda_otro" name="comprobante_pago[tipoMoneda_otro]"
                   value={otraMoneda} onChange={event => setOtraMoneda(event.target.value)} maxLength={10} required/>
          </div>}
          {errorText(errors, 'tipoMoneda') && <small className="text-danger d-block">{errorText(errors, 'tipoMoneda')}</small>}
          {errorText(errors, 'tipoMoneda_otro') && <small className="text-danger d-block">{errorText(errors, 'tipoMoneda_otro')}</small>}
        </fieldset>

        <div className="resident-form-group mb-4">
          <label htmlFor="comprobante_pago_comprobantePagoFile">Comprobante de pago <span className="text-danger">*</span></label>
          <input type="file" accept=".pdf,application/pdf" id="comprobante_pago_comprobantePagoFile"
                 name="comprobante_pago[comprobantePagoFile]" className="form-control-file" required
                 onChange={event => setComprobanteFile(event.target.files?.[0] || null)}/>
          <small className="resident-help">Solo se admiten archivos PDF de máximo 2 MB.</small>
          {errorText(errors, 'comprobantePagoFile') && <small className="text-danger d-block">{errorText(errors, 'comprobantePagoFile')}</small>}
        </div>

        <fieldset className="resident-form-group mb-3">
          <legend>¿Requiere factura? <span className="text-danger">*</span></legend>
          <div className="resident-options">
            {[['1', 'Sí'], ['0', 'No']].map(([value, label]) =>
              <label className="resident-option" key={value}>
                <input type="radio" name="comprobante_pago[requiereFactura]" value={value}
                       checked={requiereFactura === value} onChange={() => setRequiereFactura(value)} required/>{label}
              </label>
            )}
          </div>
          {errorText(errors, 'requiereFactura') && <small className="text-danger d-block">{errorText(errors, 'requiereFactura')}</small>}
          {errorText(errors, 'cedulaFile') && <small className="text-danger d-block">{errorText(errors, 'cedulaFile')}</small>}
        </fieldset>
        {requiereFactura === '1' && <div className="resident-form-group mb-4">
          <label htmlFor="institucion_cedulaFile">Constancia de situación fiscal actualizada {!residencia.residente?.cedulaIdentificacion && <span className="text-danger">*</span>}</label>
          <input type="file" id="institucion_cedulaFile" name="comprobante_pago[cedulaFile]" className="form-control-file"
                 accept=".pdf,application/pdf" required={!residencia.residente?.cedulaIdentificacion}/>
          <small className="resident-help">{residencia.residente?.cedulaIdentificacion ? 'Ya tienes una constancia registrada. Puedes adjuntar una nueva si deseas actualizarla.' : 'Debe tener una antigüedad máxima de 3 meses. Verifica sus datos antes de cargarla.'}</small>
        </div>}

        <div className="resident-form-actions">
          <a href={getSchemeAndHttpHost('/residente/perfil')} className="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" className="btn resident-btn-primary"><i className="ti ti-check mr-1"/>Revisar y continuar</button>
        </div>
      </form>
    </div>

    <Modal isOpen={showModal} onRequestClose={() => setShowModal(false)} ariaHideApp={false}
           className="resident-confirm-modal" overlayClassName="resident-confirm-overlay" contentLabel="Confirmar comprobante de pago">
      <div className="resident-modal-heading"><i className="ti ti-circle-check"/><div><h2>Confirma los datos del pago</h2><p>Revisa la información antes de enviar el comprobante.</p></div></div>
      <div className="resident-modal-body">
        <div className="resident-info-grid mb-3">
          <div className="resident-field"><span>Residente</span><strong>{[residencia.residente?.usuario?.nombre, residencia.residente?.usuario?.apellidoPaterno, residencia.residente?.usuario?.apellidoMaterno].filter(Boolean).join(' ')}</strong></div>
          <div className="resident-field"><span>CURP</span><strong>{residencia.residente?.usuario?.curp || '—'}</strong></div>
          <div className="resident-field"><span>Fecha de pago</span><strong>{displayDate(fechaPago)}</strong></div>
          <div className="resident-field"><span>Monto</span><strong>{monto || '—'} {tipoMoneda === 'other' ? otraMoneda : tipoMoneda}</strong></div>
          <div className="resident-field"><span>Factura</span><strong>{requiereFactura === '1' ? 'Sí' : 'No'}</strong></div>
        </div>
        <div className="resident-preview"><span>Vista previa del comprobante</span><PreviewDocument file={comprobanteFile}/></div>
      </div>
      <div className="resident-modal-actions">
        <button type="button" className="btn btn-outline-secondary" onClick={() => setShowModal(false)}>Volver a editar</button>
        <button type="button" className="btn resident-btn-primary" onClick={confirmar}><i className="ti ti-upload mr-1"/>Confirmar y enviar</button>
      </div>
    </Modal>
  </section>;
}

function CargaComprobantePago({ residencia, errors, montoAPagar }) {
  const lastPago = residencia.lastPago;
  if (!lastPago) return <div className="alert alert-info">No hay un pago pendiente para esta residencia.</div>;

  return <>
    <DatosResidencia residencia={residencia}/>
    <ComprobantesCargados residencia={residencia} lastPago={lastPago}/>
    <FormCargaComprobante residencia={residencia} lastPago={lastPago} errors={errors} montoAPagar={montoAPagar}/>
  </>;
}

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('cargar-de-comprobante-de-pago-component')).render(
    <CargaComprobantePago residencia={window.RESIDENCIA_PROPS} errors={window.ERRORS_PROPS} montoAPagar={window.MONTO_A_PAGAR}/>
  );
});
