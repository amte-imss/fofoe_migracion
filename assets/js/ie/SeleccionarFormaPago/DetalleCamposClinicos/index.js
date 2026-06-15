import * as React from 'react'
import {getSchemeAndHttpHost, moneyFormat} from "../../../utils";
import {Fragment} from "react";
import DetalleMontoPago from "../DetalleMontoPago";

const DetalleCamposClinicos = ({ camposClinicos, solicitud }) => {

  function getTotalBySolicitud() {
    let total = 0.0;
    for(let camposClinico of camposClinicos) {
      total += parseFloat(camposClinico.montoPagar)
    }

    return moneyFormat(total)
  }

  function getCampoClinicoFromSolicitud(idCampo) {
    const camposSol = solicitud.camposClinicos.filter(campo => campo.id === idCampo);
    return camposSol.length === 1 ? camposSol[0] : null;
  }

  return(
    <div className="panel panel-default">
      <div className="panel-body">
        <table className='table'>
          <thead className='headers'>
          <tr>
            <th>Campo clínico</th>
            <th>Carrera</th>
            <th>Sede</th>
            <th>Periodo</th>
            <th>No. lugares</th>
            <th>No. de semanas</th>
            <th>Formato de cálculo de cuotas</th>
            <th>Monto a pagar por campo clínico</th>
          </tr>
          </thead>
          <tbody>
          {
            camposClinicos.map((campoClinico, index) =>
              <Fragment key={index}>
                <tr key={index}>
                  <td>{campoClinico.convenio.cicloAcademico.nombre}</td>
                  <td>{campoClinico.convenio.carrera.nivelAcademico.nombre} - {campoClinico.convenio.carrera.nombre}
                    <br />
                    {campoClinico.asignatura ? `Asignatura: ${campoClinico.asignatura}` :  ''}
                  </td>
                  <td>{campoClinico.unidad.nombre}</td>
                  <td>
                    {campoClinico.fechaInicial} - {campoClinico.fechaFinal}
                    <br />
                    Horario: {campoClinico.horario ?? 'Sin asignar'}
                  </td>
                  <td>Solicitados {campoClinico.lugaresSolicitados}
                    <br />
                    Autorizados {campoClinico.lugaresAutorizados}
                    <br />
                    {
                      parseInt(campoClinico.totalTrabajadoresBecados) > 0 ?
                        `Trabajadores Becados ${campoClinico.totalTrabajadoresBecados}`
                        : ''
                    }
                  </td>
                  <td className={"text-center"}>{campoClinico.numeroSemanas}</td>
                  <td>
                    <a
                      href={`${campoClinico.enlaceCalculoCuotas}`}
                      target='_blank'
                      download
                    >
                      Descargar
                    </a>
                  </td>
                  <td className='text-right'>{moneyFormat(campoClinico.montoPagar)}</td>
                </tr>
                <tr>
                  <td colSpan={9}>
                    <DetalleMontoPago
                      campoClinico={getCampoClinicoFromSolicitud(campoClinico.id)}
                    />
                  </td>
                </tr>
              </Fragment>
            )
          }
          </tbody>
        </table>
        <hr/>
        <div className='row'>
          <div className="col-md-12 text-right">
            <p><strong>Monto total de la solicitud:</strong>&nbsp;&nbsp;&nbsp;{getTotalBySolicitud()}</p>
          </div>
        </div>
      </div>
    </div>
  )
}

export default DetalleCamposClinicos
