import * as React from 'react'

const DatosGeneralesResidente = ({residente}) => {
  return (
    <div>
      <div className={"row"}>
        <div className="col-md-12">
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
            <div><span>Correo Electrónico</span></div>
            <div><span>{residente.usuario.correo}</span></div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="form-group">
            <div><span>Teléfono</span></div>
            <div><span>{residente.usuario.telefono}</span></div>
          </div>
        </div>
      </div>

      <div className={"row"}>
        <div className={"col-md-12"} />
      </div>

      <div className="row">
        {
          residente.usuario.curp &&
          <div className="col-md-4">
            <div className="form-group">
              <div><span>CURP</span></div>
              <div><span>{residente.usuario.curp}</span></div>
            </div>
          </div>
        }
        <div className="col-md-4">
          <div className="form-group">
            <div><span>Nacionalidad</span></div>
            <div><span>{residente.nacionalidad}</span></div>
          </div>
        </div>
        <div className="col-md-4"></div>
      </div>
    </div>
  )
}

export default DatosGeneralesResidente