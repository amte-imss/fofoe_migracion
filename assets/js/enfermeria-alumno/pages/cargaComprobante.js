import * as React from 'react';
import { createRoot } from 'react-dom/client';
import Cleave from 'cleave.js/react';
import { getSchemeAndHttpHost, moneyFormat } from '../../utils';
import api from '../utils/api';

const MAX_FILE_BYTES = 2 * 1024 * 1024;
const fieldError = (errors, name) => {
  const value = errors?.[name] || errors?.[`comprobante_pago[${name}]`];
  return Array.isArray(value) ? value.join(' ') : (value || '');
};
const displayDate = date => date ? date.split('-').reverse().join('/') : '—';

function Field({ label, value }) {
  return <div className="nursing-field"><span>{label}</span><strong>{value || '—'}</strong></div>;
}

function ConfirmModal({ fecha, monto, requiereFactura, comprobante, onClose, onConfirm, busy }) {
  const previewUrl = React.useMemo(() => comprobante ? URL.createObjectURL(comprobante) : null, [comprobante]);
  React.useEffect(() => () => { if (previewUrl) URL.revokeObjectURL(previewUrl); }, [previewUrl]);

  return <div className="nursing-modal-overlay" role="presentation">
    <div className="nursing-modal" role="dialog" aria-modal="true" aria-labelledby="nursing-confirm-title">
      <div className="nursing-modal-header"><i className="ti ti-circle-check"/><div><h2 id="nursing-confirm-title">Confirma los datos del pago</h2><p>Revisa la información antes de enviarla.</p></div></div>
      <div className="nursing-modal-body">
        <div className="nursing-confirm-grid">
          <div><span>Fecha de pago</span><strong>{displayDate(fecha)}</strong></div>
          <div><span>Monto registrado</span><strong>{moneyFormat(Number(monto.replace(/,/g, '')))}</strong></div>
          <div><span>¿Requiere factura?</span><strong>{requiereFactura === '1' ? 'Sí' : 'No'}</strong></div>
          <div><span>Comprobante</span><strong>{comprobante?.name || '—'}</strong></div>
        </div>
        {previewUrl && <iframe className="nursing-pdf-preview" src={previewUrl} title="Vista previa del comprobante"/>}
      </div>
      <div className="nursing-modal-actions">
        <button type="button" className="btn btn-outline-secondary" onClick={onClose} disabled={busy}>Volver a editar</button>
        <button type="button" className="btn nursing-btn-primary" onClick={onConfirm} disabled={busy}><i className="ti ti-upload mr-1"/>Confirmar y enviar</button>
      </div>
    </div>
  </div>;
}

function CargaComprobante({ usuario }) {
  const formRef = React.useRef(null);
  const solicitud = usuario.solicitud || {};
  const [fecha, setFecha] = React.useState('');
  const [monto, setMonto] = React.useState('');
  const [requiereFactura, setRequiereFactura] = React.useState('');
  const [comprobante, setComprobante] = React.useState(null);
  const [showConfirm, setShowConfirm] = React.useState(false);
  const [busy, setBusy] = React.useState(false);
  const [message, setMessage] = React.useState('');
  const [errors, setErrors] = React.useState({});
  const today = new Date();
  const maxDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

  const selectPdf = event => {
    const file = event.target.files?.[0] || null;
    const isPdf = file && (file.type === 'application/pdf' || (!file.type && /\.pdf$/i.test(file.name)));
    if (file && (!isPdf || file.size > MAX_FILE_BYTES)) {
      event.target.value = '';
      setComprobante(null);
      setMessage('El comprobante debe ser un PDF de máximo 2 MB.');
      return;
    }
    setMessage('');
    setComprobante(file);
  };

  const confirm = async () => {
    setShowConfirm(false);
    setBusy(true);
    setMessage('');
    setErrors({});
    const formData = new FormData(formRef.current);
    formData.set('comprobante_pago[montoRegistrado]', monto.replace(/,/g, ''));
    const cedula = formData.get('comprobante_pago[cedulaFile]');
    if (cedula && cedula.size === 0) formData.delete('comprobante_pago[cedulaFile]');

    try {
      const response = await api.storePago(solicitud.id, formData);
      if (response.status === false) throw new Error(response.message || 'No se pudo cargar el comprobante.');
      window.location.href = getSchemeAndHttpHost('/enfermeria-alumno/');
    } catch (error) {
      let detail = error?.data;
      if (detail?.data && typeof detail.data.then === 'function') {
        detail = await detail.data.catch(() => null);
      }
      setErrors(detail?.errors || {});
      setMessage(detail?.message && detail.message !== 'Error' ? detail.message : 'No se pudo guardar el comprobante. Revisa los datos e inténtalo de nuevo.');
      setBusy(false);
    }
  };

  return <>
    <section className="nursing-card mb-4">
      <div className="nursing-card-header"><i className="ti ti-id"/><div><h2>Datos de la solicitud</h2><p>Comprueba que el pago corresponde a tu escuela.</p></div></div>
      <div className="nursing-card-body nursing-info-grid">
        <Field label="Escuela" value={solicitud.unidad?.nombreEnfermeria}/>
        <Field label="Alumno" value={usuario.nombre}/>
        <Field label="CURP" value={usuario.curp}/>
        <Field label="Correo electrónico" value={usuario.email}/>
        <Field label="Período" value={solicitud.periodo}/>
        <Field label="Vigencia" value={[solicitud.fechaInicioFormatted, solicitud.fechaFinFormatted].filter(Boolean).join(' — ')}/>
        <Field label="Monto de referencia" value={usuario.lastPago?.monto != null ? moneyFormat(usuario.lastPago.monto) : '—'}/>
        <Field label="Estado" value={usuario.statusFormatted}/>
      </div>
    </section>

    <section className="nursing-card">
      <div className="nursing-card-header"><i className="ti ti-file-upload"/><div><h2>Registrar comprobante</h2><p>Captura los datos tal como aparecen en tu comprobante.</p></div></div>
      <div className="nursing-card-body">
        {usuario.lastPago?.observaciones && <div className="nursing-notice nursing-notice--warning mb-4"><strong>Observaciones sobre tu pago:</strong> {usuario.lastPago.observaciones}</div>}
        {message && <div className="alert alert-danger" role="alert">{message}</div>}
        <form ref={formRef} method="post" encType="multipart/form-data" onSubmit={event => { event.preventDefault(); setShowConfirm(true); }}>
          <div className="nursing-form-grid">
            <div className="nursing-form-group">
              <label htmlFor="nursing-fecha">Fecha en que realizaste el pago <span className="text-danger">*</span></label>
              <input id="nursing-fecha" type="date" name="comprobante_pago[fechaPagoRegistrada]" className="form-control" value={fecha} onChange={event => setFecha(event.target.value)} max={maxDate} required/>
              {fieldError(errors, 'fechaPagoRegistrada') && <small className="text-danger d-block">{fieldError(errors, 'fechaPagoRegistrada')}</small>}
            </div>
            <div className="nursing-form-group">
              <label htmlFor="nursing-monto">Monto del comprobante <span className="text-danger">*</span></label>
              <Cleave id="nursing-monto" name="comprobante_pago[montoRegistrado]" className="form-control" options={{ numeral: true, numeralThousandsGroupStyle: 'thousand' }} onChange={event => setMonto(event.target.value)} required/>
              <small className="nursing-form-help">Debe coincidir con el monto de tu comprobante.</small>
              {fieldError(errors, 'montoRegistrado') && <small className="text-danger d-block">{fieldError(errors, 'montoRegistrado')}</small>}
            </div>
          </div>
          <div className="nursing-form-group">
            <label htmlFor="nursing-comprobante">Comprobante de pago <span className="text-danger">*</span></label>
            <input id="nursing-comprobante" type="file" name="comprobante_pago[comprobantePagoFile]" className="form-control nursing-file-input" accept="application/pdf,.pdf" onChange={selectPdf} required/>
            <small className="nursing-form-help">Solo archivos PDF de máximo 2 MB.</small>
            {fieldError(errors, 'comprobantePagoFile') && <small className="text-danger d-block">{fieldError(errors, 'comprobantePagoFile')}</small>}
          </div>
          <fieldset className="nursing-form-group">
            <legend className="h6 mb-2">¿Requieres factura? <span className="text-danger">*</span></legend>
            <div className="nursing-options">
              <label className="nursing-option"><input type="radio" name="comprobante_pago[requiereFactura]" value="1" checked={requiereFactura === '1'} onChange={() => setRequiereFactura('1')} required/> Sí</label>
              <label className="nursing-option"><input type="radio" name="comprobante_pago[requiereFactura]" value="0" checked={requiereFactura === '0'} onChange={() => setRequiereFactura('0')} required/> No</label>
            </div>
            {fieldError(errors, 'requiereFactura') && <small className="text-danger d-block">{fieldError(errors, 'requiereFactura')}</small>}
          </fieldset>
          {requiereFactura === '1' && <div className="nursing-form-group">
            <label htmlFor="nursing-cedula">Constancia de Situación Fiscal actualizada <span className="text-danger">*</span></label>
            <input id="nursing-cedula" type="file" name="comprobante_pago[cedulaFile]" className="form-control nursing-file-input" accept="application/pdf,.pdf" required/>
            <small className="nursing-form-help">Adjunta un PDF emitido en los últimos 3 meses (máximo 2 MB).</small>
            {fieldError(errors, 'cedulaFile') && <small className="text-danger d-block">{fieldError(errors, 'cedulaFile')}</small>}
          </div>}
          <div className="nursing-form-actions">
            <a href={getSchemeAndHttpHost('/enfermeria-alumno/')} className="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" className="btn nursing-btn-primary" disabled={busy}><i className="ti ti-arrow-right mr-1"/>Revisar y continuar</button>
          </div>
        </form>
      </div>
    </section>
    {showConfirm && <ConfirmModal fecha={fecha} monto={monto} requiereFactura={requiereFactura} comprobante={comprobante} onClose={() => setShowConfirm(false)} onConfirm={confirm} busy={busy}/>}
  </>;
}

const rootElement = document.getElementById('wrapper-page');
if (rootElement) createRoot(rootElement).render(<CargaComprobante usuario={window.Usuario}/>);
