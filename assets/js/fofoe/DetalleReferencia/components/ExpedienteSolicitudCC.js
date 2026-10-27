import * as React from 'react'
import {getSchemeAndHttpHost} from "../../../utils";

const ExpedienteSolicitudCC = ({ solicitud, campos, pago }) => {

  const ComprobanteOficio = ({solicitud}) => {
    if(solicitud.fechaComprobanteFormatted)
      return (<a href={`${getSchemeAndHttpHost()}/fofoe/solicitud/${solicitud.id}/oficio`} target={'_blank'}>Descargar</a>);
    return (<></>);
  }

  const ResumenExpediente = ({pago}) => {
    return (
      <a href={`${getSchemeAndHttpHost()}/fofoe/referencia/${pago.id}/formato_referencia/download`}
         target={'_blank'}>Descargar</a>
    );
  }

  const ExpedienteZip = ({pago}) => {
    return (
      <a href={`${getSchemeAndHttpHost()}/fofoe/referencia/${pago.id}/expediente/download`}
         className='btn btn-success'
         target={'_blank'}>Descargar</a>
    );
  }

  const LinkFormatoFofoe = ({campo}) => {
    const urlDownload = campo.formatoFofoeFileName ?
      `${getSchemeAndHttpHost()}/fofoe/campo_clinico/${campo.id}/formato_fofoe_firmado/download`
      : `${getSchemeAndHttpHost()}/fofoe/campo_clinico/${campo.id}/formato_fofoe/download`

    return (<a href={urlDownload} target={'_blank'}>Formato FOFOE</a>);
  }

  const ExpedienteDetallado = ({solicitud, pago}) => {
    return (
      <div className="table-responsive">
        <table className="table">
          <thead>
          <tr>
            <th>Descripción</th>
            <th>Fecha</th>
            <th>Archivo</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>Oficio de Montos de Colegiatura, Inscripción y Listado de Alumnos</td>
            <td>{solicitud.fechaComprobanteFormatted}</td>
            <td><ComprobanteOficio solicitud={solicitud}/></td>
          </tr>
          <tr>
            <td>Resumen del expediente de la referencia de pago </td>
            <td> - </td>
            <td ><ResumenExpediente pago={pago}/></td>
          </tr>
          <tr>
            <td>Expediente completo (zip) </td>
            <td> - </td>
            <td ><ExpedienteZip pago={pago}/></td>
          </tr>
          </tbody>
        </table>
      </div>
    )
  }

  return(
    <div className='container'>
      <div className='row'>
        <div className='col-md-6 mt-20 mb-10'>
          <h2>Campos Clínicos</h2>
        </div>
      </div>
      <div className="table-responsive">
        <table className="table table-striped">
          <thead>
          <tr>
            <th>Campo Clínico </th>
            <th>Carrera </th>
            <th>Sede </th>
            <th>Período</th>
            <th>No. de lugares</th>
            <th> </th>
          </tr>
          </thead>
          <tbody>
          {campos.map(cc => {
            return (
              cc.lugaresAutorizados > 0 ?
              <tr key={cc.id}>
                <td>{cc.cicloAcademico.nombre}</td>
                <td>{`${cc.convenio.carrera.nivelAcademico ? cc.convenio.carrera.nivelAcademico.nombre : ''} - ${cc.convenio.carrera.nombre}`}
                  <br />
                  Asignatura: {cc.asignatura ?? '_'}
                </td>
                <td>{cc.unidad.nombre}</td>
                <td>Inicio {cc.fechaInicialFormatted} <br/> Final {cc.fechaInicialFormatted}
                <br />
                  Horario: {cc.horario ?? 'Sin asignar'}
                </td>
                <td>Autorizados {cc.lugaresAutorizados}
                  <br />
                  {
                    parseInt(cc.totalTrabajadoresBecados) > 0 ?
                      `Trabajadores Becados ${cc.totalTrabajadoresBecados}`
                      : ''
                  }
                </td>
                <td>
                  <LinkFormatoFofoe campo={cc}/> <br/>
                </td>
              </tr>
                : null
            )
          })}
          </tbody>
        </table>
      </div>
      <ExpedienteDetallado
        solicitud={solicitud}
        pago={pago}
      />
    </div>
  )

  {
  }

}

export default ExpedienteSolicitudCC;