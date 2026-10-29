import * as React from 'react';
import { createRoot } from 'react-dom/client';
import { getSchemeAndHttpHost, moneyFormat } from '../../utils';

const referenceUrl = getSchemeAndHttpHost('/enfermeria-alumno/referencia');
const uploadUrl = getSchemeAndHttpHost('/enfermeria-alumno/carga-comprobante');

function statusClass(status) {
  if (['validated', 'invoice_pending'].includes(status)) return 'nursing-status--success';
  if (['rejected', 'rejected_doc'].includes(status)) return 'nursing-status--danger';
  if (['wait_payment', 'wait_validation'].includes(status)) return 'nursing-status--warning';
  return 'nursing-status--muted';
}

function Field({ label, value }) {
  return <div className="nursing-field"><span>{label}</span><strong>{value || '—'}</strong></div>;
}

function ReferenceModal({ onClose }) {
  React.useEffect(() => {
    const onKeyDown = event => { if (event.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [onClose]);

  return <div className="nursing-modal-overlay" role="presentation" onMouseDown={event => { if (event.target === event.currentTarget) onClose(); }}>
    <div className="nursing-modal" role="dialog" aria-modal="true" aria-labelledby="nursing-reference-title">
      <div className="nursing-modal-header"><i className="ti ti-file-type-pdf"/><div><h2 id="nursing-reference-title">Referencia de pago</h2><p>Consulta el formato antes de realizar tu pago.</p></div></div>
      <div className="nursing-modal-body"><iframe className="nursing-pdf-preview" src={referenceUrl} title="Referencia de pago"/></div>
      <div className="nursing-modal-actions">
        <a className="btn nursing-btn-outline" href={referenceUrl} target="_blank" rel="noopener noreferrer"><i className="ti ti-external-link mr-1"/>Abrir en otra pestaña</a>
        <button type="button" className="btn nursing-btn-primary" onClick={onClose}>Cerrar</button>
      </div>
    </div>
  </div>;
}

function EnfermeriaAlumnoIndex({ usuario }) {
  const [showReference, setShowReference] = React.useState(false);
  const solicitud = usuario.solicitud || {};
  const canDownload = ['', 'rejected', 'rejected_doc', 'wait_payment'].includes(usuario.status);
  const canUpload = ['rejected_doc', 'wait_payment'].includes(usuario.status);
  const needsReference = ['', 'rejected'].includes(usuario.status);

  const closeReference = React.useCallback(() => {
    setShowReference(false);
    if (usuario.status === '') window.location.reload();
  }, [usuario.status]);

  return <>
    <section className="nursing-card mb-4">
      <div className="nursing-card-header"><i className="ti ti-id"/><div><h2>Datos de la escuela y del alumno</h2><p>Información registrada para este proceso.</p></div></div>
      <div className="nursing-card-body nursing-info-grid">
        <Field label="Escuela" value={solicitud.unidad?.nombreEnfermeria}/>
        <Field label="Alumno" value={usuario.nombre}/>
        <Field label="CURP" value={usuario.curp}/>
        <Field label="Correo electrónico" value={usuario.email}/>
        <Field label="Tipo de período" value={solicitud.periodo}/>
        <Field label="Vigencia" value={[solicitud.fechaInicioFormatted, solicitud.fechaFinFormatted].filter(Boolean).join(' — ')}/>
      </div>
    </section>

    <section className="nursing-card">
      <div className="nursing-card-header"><i className="ti ti-receipt"/><div><h2>Estado del proceso</h2><p>Revisa los siguientes pasos de tu pago.</p></div></div>
      <div className="nursing-card-body">
        <div className="nursing-process-summary">
          <span className={`nursing-status ${statusClass(usuario.status)}`}>{usuario.statusFormatted || 'Inicio'}</span>
          {usuario.lastPago?.monto != null && <p>Monto registrado: <strong>{moneyFormat(usuario.lastPago.monto)}</strong></p>}
        </div>
        {['rejected', 'rejected_doc'].includes(usuario.status) && usuario.lastPago?.observaciones &&
          <div className="nursing-notice nursing-notice--warning mt-3"><strong>Motivo del rechazo:</strong> {usuario.lastPago.observaciones}</div>}
        {needsReference && <div className="nursing-notice mt-3">Para continuar, descarga tu referencia de pago.</div>}
        <div className="nursing-action-list">
          {canDownload && <button type="button" className="btn nursing-btn-primary" onClick={() => setShowReference(true)}><i className="ti ti-download mr-1"/>Descargar referencia de pago</button>}
          {canUpload && <a href={uploadUrl} className="btn nursing-btn-outline"><i className="ti ti-upload mr-1"/>Cargar comprobante de pago</a>}
          {usuario.lastPago?.factura?.id && <a className="btn nursing-btn-outline" href={getSchemeAndHttpHost(`/enfermeria-alumno/${usuario.lastPago.factura.id}/factura`)} target="_blank" rel="noopener noreferrer"><i className="ti ti-file-download mr-1"/>Descargar factura</a>}
        </div>
      </div>
    </section>
    {showReference && <ReferenceModal onClose={closeReference}/>}
  </>;
}

const rootElement = document.getElementById('wrapper-page');
if (rootElement) createRoot(rootElement).render(<EnfermeriaAlumnoIndex usuario={window.Usuario}/>);
