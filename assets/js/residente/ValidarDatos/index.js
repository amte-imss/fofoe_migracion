import * as React from 'react'
import {getSchemeAndHttpHost} from "../../utils";

const ValidarDatos = ({residente, errores, action}) => {
  return (
    <div className={"mb-20"}>
      <div className={"row"}>
        <div className="col-md-12 mb-20">
          <h2>Datos Generales</h2>
        </div>
      </div>
      <div className="row">
        <div className="col-md-4">
          <div className="form-group">
            <div><span>Nombre</span></div>
            <div><span>{residente.usuario.nombre} {residente.usuario.apellidoPaterno} {residente.usuario.apellidoMaterno}</span></div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="form-group">
            <div><span>CURP</span></div>
            <div><span>{residente.usuario.curp}</span></div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="form-group">
            <div><span>Nacionalidad</span></div>
            <div><span>{residente.nacionalidad}</span></div>
          </div>
        </div>
      </div>

      <div className={"row"}>
        <div className={"col-md-12"} />
      </div>

      <div className="row">
        <div className="col-md-2">
          <div className="form-group">
            <div><span>Folio</span></div>
            <div><span>{residente.folio}</span></div>
          </div>
        </div>
        <form
          action={action}
          method="post"
          id="informacion_personal_residente"
        >
          <div className="col-md-4">
            <div className='form-group'>
              <label htmlFor="usuario_correo">Correo</label>
              <input
                className='form-control'
                type="email"
                name="informacion_personal_residente[correo]"
                id="informacion_personal_residente_correo"
                defaultValue={residente.usuario.correo}
                required={true}
              />
              <span className="help-block bg-danger">{errores.correo ? errores.correo[0] : ''}</span>
            </div>
          </div>
          <div className="col-md-3">
            <div className={`form-group ${errores.telefono ? 'has-error has-feedback' : ''}`}>
              <label htmlFor="informacion_personal_residente_telefono">Teléfono</label>
              <input
                className='form-control'
                type="text"
                name="informacion_personal_residente[telefono]"
                id="informacion_personal_residente_telefono"
                defaultValue={residente.usuario.telefono}
                required={true}
              />
              <span className="help-block bg-danger">{errores.telefono ? errores.telefono[0] : ''}</span>
            </div>
          </div>
        <div className='col-md-2'>
          <div className="form-group">
            <button
              type="submit"
              className="btn btn-primary btn-sm btn-block mt-30"
            >
              Guardar
            </button>
          </div>
        </div>
        <div className='col-md-1' />
        </form>
      </div>
    </div>
  )
}

export default ValidarDatos