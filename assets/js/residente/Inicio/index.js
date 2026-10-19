import * as React from 'react'
import ReactDOM from 'react-dom'
import ValidarDatos from "../ValidarDatos";
import Residencias from "../Residencias";

const Inicio = ({residente, errores, action}) => {
  return (
    <div>
      <ValidarDatos
        residente={residente}
        errores={errores}
        action={action}
      />
      <Residencias residencias={residente.residencias} />
    </div>
  )
}

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <Inicio
      residente={window.RESIDENTE_PROP}
      errores={window.ERRORS}
      action={window.ACTION}
    />,
    document.getElementById('main-component')
  )
})