import React, {Fragment} from 'react';
import ReactDOM from 'react-dom'
import camelcaseKeys from 'camelcase-keys'
import RegistrarDescuentos from "./Descuentos";
import {checkInputFile, formatNumeroDinero, getSchemeAndHttpHost} from "../../utils";
import './styles.scss';

const Registrar = (
    {
        autorizados,
        solicitudId,
        institucion,
        carreras,
        montos,
        errors,
        route
    }) => {

    const [executing, setExecuting] = React.useState(false);
    const [descValidos, setDescValidos] = React.useState(true);
    const [descValidosCC, setDescValidosCC] = React.useState(
        solicitudId.camposClinicos.reduce((acc, elem) => {
                acc.push({id: elem.id, validate: true});
                return acc;
            },
            [])
    );

    let acceso = false;
    let editar = false;

    const handleCurrency = (e) => {
        let valPrev = e.value.toString().replace(",", "");
        e.value = formatNumeroDinero(valPrev);
    };

    const callbackValidateDescCC = (i, value, idCampo, val) => {
        const indexCC = descValidosCC.findIndex(item => {
            return item.id === idCampo
        });
        descValidosCC[indexCC] = {id: idCampo, validate: val};
        setDescValidosCC(descValidosCC);
        setDescValidos(descValidosCC.reduce((acc, elem) => {
            return acc && elem.validate
        }, true));
    }

    const formSubmit = (e) => {
        if (!descValidos) {
            e.preventDefault();
            return;
        }
        try {
            var tag = document.getElementsByClassName('solicitud_validacion_montos_inscripcion');

            for (var i = 0; i < tag.length; i++) {
                tag[i].value = tag[i].value.replace(/,/g, '');
            }
            setExecuting(true);
        } catch (error) {
            e.preventDefault();
        }
    };

    if (autorizados[0].autorizados !== 0) acceso = true;
    if (route === "ie#corregir_montos") editar = true;
    return (
        <>
            {
                acceso ?
                    <form
                        action={`${getSchemeAndHttpHost()}/ie/solicitudes/${solicitudId.id}/registrar-montos`}
                        method="post"
                        encType='multipart/form-data'
                        onSubmit={formSubmit}
                    >
                        <div className='row'>
                            {
                                editar ?
                                    <div className="col-md-12 bm-10 tm-10">
                                        <span className="error-message mb-10 mt-10"><strong>Por favor, ingrese la información correcta correspondiente a los importes de inscripción, colegiaturas y descuentos</strong></span>
                                    </div>
                                    : ''
                            }
                            <div className="col-md-12 mb-10 mt-10">
                                <div className="row">
                                    <div className="col-md-8">
                                        <p>
                                            Adjunte documento original oficial que contenga los importes <span style={{fontWeight: 'bold', textDecoration: 'underline'}}>ANUALES</span> de
                                            inscripción y colegiaturas de todas las carreras que comprenden su solicitud
                                            de campos clínicos igualmente oficio detallado de los importes de beca que
                                            aplique, así como el listado de alumnos.
                                        </p>
                                    </div>
                                    <div className="col-md-4">
                                        <input
                                            type="file"
                                            onInput={event => checkInputFile(event.target, {size: 2097152 })}
                                            name='solicitud_registro_montos[urlArchivoFile]'
                                            required={true}
                                        />
                                        <span className='error-message ' style={{fontWeight: 'bold'}}>
                                            El documento de montos debe incluir firmas originales de la institución educativa
                                        </span>
                                        {errors && <span className='error-message'>{errors['urlArchivoFile']}</span>}
                                    </div>
                                </div>
                            </div>

                            <div className="col-md-12 mb-10">
                                <p>Ingrese los montos correspondientes a cada carrera de su solicitud</p>
                            </div>

                            <div className="col-md-12 mb-10">
                                <div className="panel panel-default">
                                    <div className="panel-body">
                                        <table className='table'>
                                            <thead className='headers'>
                                            <tr>
                                                <th>Carrera</th>
                                                <th>Período</th>
                                                <th>Sede</th>
                                                <th>Inscripción</th>
                                                <th>Colegiatura</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {
                                                solicitudId.camposClinicos.filter(campo => campo.lugaresAutorizados > 0).map((campo, index) =>
                                                    <Fragment key={index}>
                                                        <tr key={index}>
                                                            <td>{campo.convenio.carrera.nivelAcademico ? campo.convenio.carrera.nivelAcademico.nombre : ''} - {campo.convenio.carrera.nombre}
                                                                <br/>
                                                                {campo.asignatura ? `Asignatura: ${campo.asignatura}` : ''}
                                                            </td>
                                                            <td>
                                                                <div>{campo.displayFechaInicial}-{campo.displayFechaFinal}
                                                                    <br/>
                                                                    Horario: {campo.horario ?? 'Sin asignar'}
                                                                </div>
                                                            </td>
                                                            <td>{campo.unidad.nombre}
                                                                <br/>
                                                                {campo.lugaresAutorizados} lugares autorizados
                                                                <br/>
                                                                {
                                                                    parseInt(campo.totalTrabajadoresBecados) > 0 ?
                                                                        `${campo.totalTrabajadoresBecados} becas trabajadores`
                                                                        : ''
                                                                }
                                                            </td>
                                                            <td className='hidden'>
                                                                <input
                                                                    className='form-control'
                                                                    type="number"
                                                                    min={1}
                                                                    step={0.01}
                                                                    defaultValue={campo.convenio.carrera.id}
                                                                    name={`solicitud_registro_montos[campo_${campo.id}][montoCarrera][carrera]`}
                                                                />
                                                            </td>
                                                            <td className="form-inline">
                                                                <div className="form-group">
                                                                    <div className="input-group">
                                                                        <div className="input-group-addon">$</div>
                                                                        <input
                                                                            type="text"
                                                                            min={1}
                                                                            step={0.01}
                                                                            name={`solicitud_registro_montos[campo_${campo.id}][montoCarrera][montoInscripcion]`}
                                                                            className="form-control solicitud_validacion_montos_inscripcion"
                                                                            defaultValue={campo.montoCarrera ? formatNumeroDinero(campo.montoCarrera.montoInscripcion) : ''}
                                                                            required={true}
                                                                            onBlur={e => handleCurrency(e.target)}
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td className="form-inline">
                                                                <div className="form-group">
                                                                    <div className="input-group">
                                                                        <div className="input-group-addon">$</div>
                                                                        <input
                                                                            type="text"
                                                                            name={`solicitud_registro_montos[campo_${campo.id}][montoCarrera][montoColegiatura]`}
                                                                            id="solicitud_registro_montos_inscripcion"
                                                                            className="form-control solicitud_validacion_montos_inscripcion"
                                                                            defaultValue={campo.montoCarrera ? formatNumeroDinero(campo.montoCarrera.montoColegiatura) : ''}
                                                                            required={true}
                                                                            onBlur={e => handleCurrency(e.target)}
                                                                        />
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        {
                                                            editar ?
                                                                <tr className={'desc'}>
                                                                    <td colSpan={5}>
                                                                        <p className='background'> {campo.observaciones} </p>
                                                                    </td>
                                                                </tr> : null
                                                        }
                                                        <tr className='desc'>
                                                            {
                                                                true &&

                                                            <td colSpan={5}>
                                                                <RegistrarDescuentos
                                                                    prefixName={`solicitud_registro_montos[campo_${campo.id}][montoCarrera][descuentos]`}
                                                                    carrera={campo.carrera}
                                                                    campo={campo}
                                                                    descuentos={campo.montoCarrera ? campo.montoCarrera.descuentos : []}
                                                                    onChange={callbackValidateDescCC}
                                                                    indexMonto={index}
                                                                />
                                                            </td>
                                                            }
                                                        </tr>
                                                    </Fragment>
                                                )
                                            }
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div className='col-md-12 mb-20'>
                                <p>
                                    La institución  &nbsp;
                                    <span className='text-bold'>{institucion} </span>
                                    valida que la información capturada y archivos adjuntos corresponden a su solicitud.
                                    &nbsp;&nbsp;
                                    <label htmlFor="solicitud_registro_montos_confirmacionOficioAdjunto">
                                        <input
                                            type="checkbox"
                                            id='solicitud_registro_montos_confirmacionOficioAdjunto'
                                            name='solicitud_registro_montos[confirmacionOficioAdjunto]'
                                            required={true}
                                        />&nbsp;Acepto
                                    </label>
                                </p>
                            </div>
                        </div>
                        <div className="col-md-12">
                            <div className="row">
                                <div className="col-md-10"/>
                                <div className="col-md-2">
                                    <button
                                        type="submit"
                                        className={`btn btn-success btn-block ${descValidos ? ' ' : 'disabled'}`}
                                        disabled={executing || !descValidos}
                                    >
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    :
                    <div className="mt-20">
                        <h1>
                            <center>Lo sentimos, no tiene campos clínicos autorizados</center>
                        </h1>
                    </div>
            }
        </>
    )
}

ReactDOM.render(
    <Registrar
        autorizados={window.AUTORIZADOS_PROP}
        institucion={window.INSTITUCION_PROP}
        carreras={camelcaseKeys(window.CARRERAS_PROP)}
        campos={camelcaseKeys(window.CAMPOS_PROP)}
        montos={window.MONTOS_PROP}
        solicitudId={window.SOLICITUD_ID_PROP}
        errors={window.ERRORS_PROP}
        route={window.ROUTE_PROP}
    />,
    document.getElementById('registrar-montos-component')
);
