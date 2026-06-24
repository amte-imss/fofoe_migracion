import * as React from 'react'
import './TablaCamposClinicos.scss';
const SpecialRow = ({cc, handleDelete, canDelete}) => {
    const [collapse, setCollapse] = React.useState(true);
    return (
        <>
            <tr>
                <td>{cc.convenio.cicloAcademico.nombre}</td>
                <td>{cc.convenio.carrera.nivelAcademico.nombre}</td>
                <td>{cc.convenio.carrera.nombre}</td>
                <td>{cc.fechaInicialFormatted} - {cc.fechaFinalFormatted}</td>
                <td>{cc.unidad.nombre}</td>
                <td>{cc.lugaresSolicitados}</td>
                <td>{cc.lugaresAutorizados}</td>
                <td>{cc.trabajadoresBecados.length}</td>
                <td>
                    <a className={'cc'} onClick={e => {setCollapse(!collapse)}}>Detalles</a><br/>
                    <a style={{display: (canDelete ? 'block': 'none')}} className={'cc'} onClick={e => {canDelete? handleDelete(cc.id): ''}}>Eliminar</a>
                </td>
            </tr>
            <tr className={`cc_tr ${(collapse ? 'tr_hide' : 'tr_show')}`}>
                <td colSpan={8} id={`cc_collapse_${cc.id}`} >
                    <ul>
                        <li><strong>Horario del campo clínico</strong>: {(cc.horario? cc.horario : 'Sin Asignar')}</li>
                        <li style={{display: (cc.convenio.cicloAcademico.id === 2 ? 'list-item': 'none')}}><strong>Promoción de Inicio</strong>: {(cc.promocion? cc.promocion : 'Sin Asignar')}</li>
                        <li><strong>Asignatura</strong>: {(cc.asignatura? cc.asignatura : 'Sin Asignar')}</li>
                    </ul>
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
                                <th>Ciclo Académico</th>
                                <th>Nivel</th>
                                <th>Carrera</th>
                                <th>Periodo</th>
                                <th>Sede</th>
                                <th>No. de Lugares Solicitados</th>
                                <th>No. de Lugares Autorizados</th>
                                <th>No. de Trabajadores Becados</th>
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