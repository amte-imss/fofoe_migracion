import * as React from 'react';

const errorText = value => Array.isArray(value) ? value.join(' ') : (value || '');

export default function ValidarDatos({ residente, errores, action }) {
  const usuario = residente.usuario || {};

  return <section className="resident-card mb-4">
    <div className="resident-card-header"><i className="ti ti-id"/><div><h2>Datos generales</h2><p>Revisa tu información personal y actualiza tus datos de contacto.</p></div></div>
    <div className="resident-card-body">
      <div className="resident-info-grid mb-4">
        <div className="resident-field"><span>Nombre completo</span><strong>{[usuario.nombre, usuario.apellidoPaterno, usuario.apellidoMaterno].filter(Boolean).join(' ') || '—'}</strong></div>
        <div className="resident-field"><span>CURP</span><strong>{usuario.curp || '—'}</strong></div>
        <div className="resident-field"><span>Nacionalidad</span><strong>{residente.nacionalidad || '—'}</strong></div>
        <div className="resident-field"><span>Folio</span><strong>{residente.folio || '—'}</strong></div>
      </div>
      <form action={action} method="post" id="informacion_personal_residente" className="resident-contact-form">
        <div className="resident-section-label">Datos de contacto</div>
        <div className="row align-items-end">
          <div className="col-md-5 mb-3">
            <label htmlFor="informacion_personal_residente_correo">Correo electrónico</label>
            <input className="form-control" type="email" name="informacion_personal_residente[correo]"
                   id="informacion_personal_residente_correo" defaultValue={usuario.correo || ''} required/>
            {errores.correo && <small className="text-danger">{errorText(errores.correo)}</small>}
          </div>
          <div className="col-md-4 mb-3">
            <label htmlFor="informacion_personal_residente_telefono">Teléfono</label>
            <input className="form-control" type="tel" name="informacion_personal_residente[telefono]"
                   id="informacion_personal_residente_telefono" defaultValue={usuario.telefono || ''} required/>
            {errores.telefono && <small className="text-danger">{errorText(errores.telefono)}</small>}
          </div>
          <div className="col-md-3 mb-3"><button type="submit" className="btn resident-btn-primary btn-block"><i className="ti ti-device-floppy mr-1"/>Guardar cambios</button></div>
        </div>
      </form>
    </div>
  </section>;
}
