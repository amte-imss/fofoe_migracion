import * as React from 'react'
import './CamposClinicos.scss';
const SpecialRow = ({cc, handleDelete, canDelete}) => {
    const [collapse, setCollapse] = React.useState(true);
    return (
        <>
            <tr>
                <td>{cc.cicloAcademico.nombre}</td>
                <td>{cc.convenio.carrera.nivelAcademico ? cc.convenio.carrera.nivelAcademico.nombre : ''} - {cc.convenio.carrera.nombre}</td>
                <td> {(cc.asignatura? cc.asignatura : '-')}
                </td>
                <td>{cc.fechaInicialFormatted} - {cc.fechaFinalFormatted}<br />
                    Horario: {(cc.horario? cc.horario : 'Sin asignar')}
                </td>
                <td>{cc.unidad.nombre}</td>
                <td>{cc.lugaresSolicitados}</td>
                <td>
                    <a style={{display: (canDelete ? 'block': 'none')}} className={'cc'} onClick={e => {canDelete? handleDelete(cc.id): ''}}>Eliminar</a>
                </td>
            </tr>
        </>
    )
}

const CamposClinicos = (props) => {

    if(props.campos.length>0){
        return (
            <>
                <div className="col-md-12">
                    <h3>Información Solicitud Campos Clínicos</h3>
                    <h4>Campos Clínicos registrados en la Solicitud</h4>
                    <div className="table-responsive">
                        <table className="table table-striped">
                            <thead>
                            <tr>
                                <th className="col-md-1">Ciclo Académico</th>
                                <th className="col-md-2">Carrera</th>
                                <th className="col-md-2">Asignatura</th>
                                <th className="col-md-3">Periodo</th>
                                <th className="col-md-3">Sede</th>
                                <th className="col-md-1">No. de Lugares Solicitados</th>
                                <th className="col-md-1"></th>
                            </tr>
                            </thead>
                            <tbody>
                            {props.campos.map(cc => {
                                return (
                                    <SpecialRow
                                        cc={cc}
                                        key={cc.id}
                                        canDelete={props.campos.length > 1}
                                        handleDelete={props.handleDelete}/>
                                )
                            })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </>
        )
    }
    return <></>

}

export  default CamposClinicos;