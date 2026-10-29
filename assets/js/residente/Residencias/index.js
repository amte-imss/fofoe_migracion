import * as React from 'react';
import { dateFormat, getSchemeAndHttpHost, moneyFormat, getEstatusPago, toOrdinalFormat } from '../../utils';
import { ESTATUS_PAGO } from '../../constants';
import { ESTATUS_RESIDENCIA } from '../constants';

const displayDate = value => {
  if (!value) return 'Pendiente';
  const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
  return match ? `${match[3]}/${match[2]}/${match[1]}` : dateFormat(value);
};

function statusClass(status) {
  if (status === ESTATUS_PAGO.VALIDADO || status === ESTATUS_RESIDENCIA.PAGO_VALIDADO_FOFOE) return 'resident-status--success';
  if (status === ESTATUS_PAGO.INCORRECTO) return 'resident-status--danger';
  if (status === ESTATUS_PAGO.EN_VALIDACION || status === ESTATUS_RESIDENCIA.PENDIENTE_FACTURA_FOFOE) return 'resident-status--warning';
  return 'resident-status--muted';
}

function ResidenciaRow({ residencia }) {
  const [referenciaDescargada, setReferenciaDescargada] = React.useState(false);
  const pagos = residencia.pagos || [];
  const lastPago = pagos.find(pago => pago.id === residencia.lastPago?.id) || pagos[0];
  const estatusPago = lastPago ? getEstatusPago(lastPago, pagos.length) : ESTATUS_PAGO.SIN_REGISTRO;
  const estatus = residencia.estatus === ESTATUS_RESIDENCIA.PENDIENTE_FACTURA_FOFOE
    ? residencia.estatus : estatusPago;
  const puedeDescargarReferencia = lastPago && [ESTATUS_PAGO.SIN_REGISTRO, ESTATUS_PAGO.INCORRECTO].includes(estatusPago);
  const puedeCargar = lastPago &&
    ([ESTATUS_RESIDENCIA.FORMATO_PAGO_DESCARGADO, ESTATUS_PAGO.INCORRECTO].includes(residencia.estatus) || referenciaDescargada) &&
    [ESTATUS_PAGO.SIN_REGISTRO, ESTATUS_PAGO.INCORRECTO].includes(estatusPago);
  const puedeDescargarComprobante = lastPago && [ESTATUS_PAGO.VALIDADO, ESTATUS_PAGO.EN_VALIDACION].includes(estatusPago);

  return <tr>
    <td><strong>{residencia.ciclo || '—'}</strong></td>
    <td>{residencia.especialidad || '—'}</td>
    <td>{toOrdinalFormat(residencia.grado) || '—'}</td>
    <td>{displayDate(lastPago?.fechaPago)}</td>
    <td>{moneyFormat(residencia.monto)} {residencia.tipoMoneda}</td>
    <td><span className={`resident-status ${statusClass(estatus)}`}>{estatus}</span></td>
    <td><div className="resident-row-actions">
      {puedeDescargarReferencia && <a className="btn btn-sm btn-outline-success"
        href={getSchemeAndHttpHost(`/residente/residencia/${residencia.id}/referencia-pago/download`)}
        onClick={() => setReferenciaDescargada(true)} target="_blank" rel="noopener noreferrer">
        <i className="ti ti-download mr-1"/>Referencia
      </a>}
      {puedeCargar && <a className="btn btn-sm resident-btn-primary"
        href={getSchemeAndHttpHost(`/residente/pagos/${lastPago.id}/carga-de-comprobante-de-pago`)}>
        <i className="ti ti-upload mr-1"/>{estatusPago === ESTATUS_PAGO.INCORRECTO ? 'Rectificar' : 'Cargar comprobante'}
      </a>}
      {puedeDescargarComprobante && <a className="btn btn-sm btn-outline-success"
        href={getSchemeAndHttpHost(`/residente/residencia/${residencia.id}/comprobantes-pago/download`)} target="_blank" rel="noopener noreferrer">
        <i className="ti ti-file-download mr-1"/>Comprobantes
      </a>}
      {!puedeDescargarReferencia && !puedeCargar && !puedeDescargarComprobante && <span className="text-muted">—</span>}
    </div></td>
  </tr>;
}

export default function Residencias({ residencias }) {
  return <section className="resident-card">
    <div className="resident-card-header"><i className="ti ti-school"/><div><h2>Mis residencias</h2><p>Revisa tus pagos y descarga tus documentos.</p></div></div>
    <div className="table-responsive">
      <table className="table table-hover resident-table mb-0">
        <thead><tr><th>Ciclo académico</th><th>Especialidad</th><th>Grado</th><th>Fecha de pago</th><th>Monto</th><th>Estado del pago</th><th>Acciones</th></tr></thead>
        <tbody>
          {residencias.length > 0
            ? residencias.map(residencia => <ResidenciaRow key={residencia.id} residencia={residencia}/>)
            : <tr><td className="resident-empty" colSpan={7}><i className="ti ti-school-off"/><strong>No hay residencias registradas</strong></td></tr>}
        </tbody>
      </table>
    </div>
  </section>;
}
