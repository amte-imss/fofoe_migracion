import * as React from "react";
import './styles.scss'
import {dateFormat} from "../../utils";

const ListConvenios = ({ convenios }) => {

  return (
    <div className='list-convenios'>
      <div className='row flex align-items-center'>
        <div className='col-md-6'>
          <p className="mb-10 text-bold">Convenios de la institución educativa</p>
          <table className='table table-bordered'>
            <thead className='headers'>
            <tr>
              <th>Número</th>
              <th>Tipo</th>
              <th>Ciclo</th>
              <th>Grado</th>
              <th>Carrera</th>
              <th>Vigencia</th>
            </tr>
            </thead>
            <tbody>
            {
              convenios.map((item) => (
                <tr key={item.id} className={`label-${item.label}`}>
                  <td>{item.numero}</td>
                  <td>{item.tipo}</td>
                  <td>{item.ciclosAcademicosFormatted}</td>
                  <td>{item.nivelAcademico ? item.nivelAcademico.nombre : 'No asignado'}</td>
                  <td>{item.carrera ? item.carrera.nombre : 'No asingado'}</td>
                  <td>{dateFormat(item.vigencia)}</td>
                </tr>
              )) }
            </tbody>
          </table>
        </div>
        <div className='col-md-6'>
          <div className='tabla-vigencia'>
            <div className='tabla-vigencia__item'>
              <p className='tabla-vigencia__title'>Convenio está vigente, con fecha de término mayor a un año.</p>
            </div>
            <div className='tabla-vigencia__item'>
              <p className='tabla-vigencia__title'>Convenio vigente, con fecha de término menor a un año pero mayor a 6 meses. El convenio debería estar en trámites de renovación.</p>
            </div>
            <div className='tabla-vigencia__item'>
              <p className='tabla-vigencia__title'>La vigencia del convenio está por terminar, es necesario actualizar el convenio.</p>
            </div>
            <div className='tabla-vigencia__item'>
              <p className='tabla-vigencia__title'>El convenio ya no está vigente.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default ListConvenios
