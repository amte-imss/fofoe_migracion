import { createRoot } from "react-dom/client";
import * as React from 'react'
import {getSchemeAndHttpHost} from "../../utils";
import {FOFOE_TIPO_PAGOS} from "../constants";

const SeleccionadorAnio = ({years, meta}) => {
    const selectForm = React.useRef(null)

    const handleSubmit = () => {
        selectForm.current.submit()
    }

    return (
        <div className={``}>
            <label htmlFor="year">Año: </label>
            <form
                ref={selectForm}
                method={"get"}
            >
                <select id="year" className="form-control"
                        name="year"
                        defaultValue={meta.year}
                        onChange={handleSubmit}
                >
                    <option value="2021">2021</option>
                    {years.map(item => {
                        return (<option key={`${item.year}`} value={`${item.year}`}>{item.year}</option>)
                    })}
                </select>
            </form>
            <span className="help-block"> </span>
        </div>
    )
}

const LinkComprobantes = ({data, tipo, clave}) => {

    const href = `${getSchemeAndHttpHost()}/fofoe/pagos/${FOFOE_TIPO_PAGOS[tipo]['sufijo_url']}?estado=${FOFOE_TIPO_PAGOS[tipo][clave]}`

    return (
        <a href={href}>
            {data[tipo] ? data[tipo][clave] : ''}
        </a>
    )
}

const Inicio = ({years, metadata, data}) => {

    const areas = [
        {
            'tipo': 'CC',
            'nombre': 'Pregrado / Enfermería',
            'proceso': 'Ciclos Clínicos / Internado Médico',
        },
        {
            'tipo': 'POSGRADO',
            'nombre': 'Posgrado',
            'proceso': 'Alumnos extranjeros en Ciclo académico anual',
        },
        {
            'tipo': 'POSGRADO_RP',
            'nombre': 'Posgrado',
            'proceso': 'Alumnos extranjeros rotación parcial de especialidad',
        },
        {
            'tipo': 'PERMANENTE_PRESENCIAL',
            'nombre': 'Educación permanente',
            'proceso': 'Personal externo que participe en cursos de educación permanente en ' +
                'salud, formación docente o investigación educativa: modalidad ' +
                'presencial'
        },
        {
            'tipo': 'PERMANENTE_DISTANCIA',
            'nombre': 'Educación permanente',
            'proceso': 'Personal externo que participe en cursos de educación permanente en ' +
                'salud, formación docente o investigación educativa: modalidad a distancia'
        },
        {
            'tipo': 'PERMANENTE_SIMULACION',
            'nombre': 'Educación permanente',
            'proceso': 'Personal externo al Instituto Mexicano del Seguro Social, que participe en\n' +
                'cursos basados en simulación, implementados en los Centros de Simulación ' +
                'para la Excelencia Clínica y Quirúrgica'
        },
        {
            'tipo': 'ESCUELA_ENFERMERIA',
            'nombre': 'Enfermería',
            'proceso': 'Licenciatura en Escuelas de Enfermería del Instituto Mexicano del Seguro Social'
        }
    ]

    return (
        <div className={"container"}>
            <div className={"row"}>
                <div className="col-md-2">
                    <SeleccionadorAnio
                        meta={metadata}
                        years={years}/>
                </div>
                <div className="col-md-6"></div>
                <div className="col-md-4">
                    <a href={getSchemeAndHttpHost('/pregrado/reporte/?carrera=null&cicloAcademico=null&delegacion=null&estatus=null&export=1&fechaFin=null&fechaIni=null&search=')}
                       target={"_blank"}
                       className="btn btn-primary pull-right" style={{marginTop: '24px'}}>
                        Descargar reporte de actividades
                    </a>
                </div>
            </div>
            <div>
            </div>
            <div>
                <div className="row">
                    <div className="panel panel-default col-md-12">
                        <div className="panel-body">
                            <table className='table text-center'>
                                <thead className='headers'>
                                <tr>
                                    <td>Área</td>
                                    <td>Proceso</td>
                                    <td>Pendientes de Validación</td>
                                    <td>Pagos No válidos</td>
                                    <td>Pendientes de Facturación</td>
                                    <td>Validados / Facturados</td>
                                </tr>
                                </thead>
                                <tbody>
                                {areas.map((area, index) => {
                                    return (
                                        <tr key={index}>
                                            <td className="text-left">{area.nombre}</td>
                                            <td>
                                                {area.tipo == 'CC' ?
                                                    <a href={getSchemeAndHttpHost('/fofoe/solicitud')}>{area.proceso}</a> : area.proceso}
                                            </td>
                                            <td><LinkComprobantes data={data} tipo={area.tipo}
                                                                  clave={'pendientes_val'}/></td>
                                            <td><LinkComprobantes data={data} tipo={area.tipo} clave={'no_validos'}/>
                                            </td>
                                            <td><LinkComprobantes data={data} tipo={area.tipo}
                                                                  clave={'pendientes_facturacion'}/></td>
                                            <td><LinkComprobantes data={data} tipo={area.tipo}
                                                                  clave={'validados_facturados'}/></td>
                                        </tr>
                                    )
                                })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

document.addEventListener('DOMContentLoaded', () => {
    const indexDom = document.getElementById('fofoe-wrapper-index');
    if (indexDom) {
        createRoot(indexDom).render(
            <React.StrictMode>
                <Inicio
                    metadata={window.META}
                    years={window.YEARS}
                    data={window.DATA}
                />
            </React.StrictMode>
        )
    }
})
