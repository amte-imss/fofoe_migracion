import * as React from 'react';
import { createRoot } from 'react-dom/client';
import ValidarDatos from '../ValidarDatos';
import Residencias from '../Residencias';

function Inicio({ residente, errores, action }) {
  return <>
    <ValidarDatos residente={residente} errores={errores} action={action}/>
    <Residencias residencias={residente.residencias || []}/>
  </>;
}

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('main-component')).render(
    <Inicio residente={window.RESIDENTE_PROP} errores={window.ERRORS} action={window.ACTION}/>
  );
});
