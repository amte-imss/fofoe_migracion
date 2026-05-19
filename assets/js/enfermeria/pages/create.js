import 'regenerator-runtime/runtime';
import React from "react";
import { createRoot } from 'react-dom/client';
import Loader from "../../components/Loader/Loader";
import {FormValidator} from "../../components/FormValidator/FormValidator";
import InputFile from "../../components/InputFile/InputFile";
import Papa from 'papaparse';  //para el csv
import Popup from "../../components/Popup/Popup";
import ProgressBar from "../../components/ProgressBar/ProgressBar";
import {getSchemeAndHttpHost, transformSymfonyDate, objectToFormData} from "../../utils";
import useFetch from "../../hooks/useFetch";
import api from "../api";
import Swal from 'sweetalert2'
import 'sweetalert2/src/sweetalert2.scss';
import readXlsxFile from "read-excel-file";
import './create.css';

const validTypes = ['EXTERNO', 'HIJO DE TRABAJADOR', 'EXTRAORDINARIO'];

function EnfermeriaCreate({asCame, escuelaId, ooad}) {
    const [isLoading, setIsLoading] = React.useState(true);
    const [errors, setErrors] = React.useState({});
    const [showTable, setShowTable] = React.useState(false);
    const [showPopup, setShowPopup] = React.useState(false);
    const [data, setData] = React.useState([]);
    const [current, setCurrent] = React.useState(1);
    const [escuelas, setEscuelas] = React.useState([]);

    const isValidFile = content => {
        return content.length > 0 && content.every(isValidRow)
    }

    const isValidRow = (row) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const curpRegex = /^[A-Z]{4}\d{6}[A-Z]{6}[0-9A-Z]{2}$/; // CURP validation regex
        return row.length >= 4 && row[0] && row[1] && row[2]
            && row[3] && validTypes.includes(row[2].toUpperCase())
            && emailRegex.test(row[1])
            && curpRegex.test(row[3]);
    }

    React.useEffect(() => {
        document.querySelector('[name="solicitud[periodo]"]').value = 'semestral';
        if ((asCame || (!asCame && !escuelaId)) && ooad) {
            // CAME con OOAD, o IE sin escuela asignada pero con OOAD: filtrar por OOAD
            fetch(getSchemeAndHttpHost(`/came/api/unidad_enfermeria/${ooad}`)).then(response => response.json()).then(({data}) => {
                setEscuelas(data);
            }).finally(() => {
                setIsLoading(false);
            });
        } else if (!asCame && !escuelaId && !ooad) {
            // IE sin unidad ni OOAD asignada: cargar todas las escuelas de enfermería
            fetch(getSchemeAndHttpHost('/api/unidad_enfermeria')).then(response => response.json()).then(({data}) => {
                setEscuelas(data);
            }).finally(() => {
                setIsLoading(false);
            });
        } else {
            setIsLoading(false);
        }
    }, [])

    React.useEffect(() => {
        if (data.length > 0) {
            if (!isValidFile(data)) {
                Swal.fire({
                    title: 'Error al procesar el archivo',
                    text: 'Asegúrese de que el archivo cumple con las especificaciones requeridas',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        }
    }, [data])

    const onProcessCsv = (event) => {
        setIsLoading(true);
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = (event) => {
            //console.log(event.target.result);
            setIsLoading(false);
            const json = Papa.parse(event.target.result);
            setShowTable(true);
            //console.log(json.data[1]);
            const content = json.data.splice(1);
            //console.log(content[0][0])
            //console.log('content', content[content.length-1][0])
            try {
                if (content[content.length - 1][0] === "") {
                    content.pop();
                }
            } catch (e) {
            }
            setData(content);
            //console.log(json.data.splice(1));
        };
        reader.readAsText(file, 'ISO-8859-1');
    }

    const onProcessXlsx = (event) => {
        setIsLoading(true);
        const file = event.target.files[0];
        readXlsxFile(file).then((rows) => {
            setShowTable(true);
            //console.log(json.data[1]);
            const content = rows.splice(1);
            //console.log(content[0][0])
            //console.log('content', content[content.length-1][0])
            try {
                if (content[content.length - 1][0] === "") {
                    content.pop();
                }
            } catch (e) {
            }
            setData(content);
        }).catch(e => {
            Swal.fire({
                title: 'Error al procesar el archivo',
                text: 'Asegúrese de que el archivo sea un formato válido',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsLoading(true);
        setShowPopup(true);
        const inputValues = {};
        inputValues['solicitud[fechaInicio]'] = event.target['fechaInicio'].value;
        inputValues['solicitud[fechaFin]']    = event.target['fechaFin'].value;
        inputValues['solicitud[periodo]']     = event.target['solicitud[periodo]'].value;
        inputValues['solicitud[unidad]']      = event.target['solicitud[unidad]'].value;
        api.storeSolicitud(objectToFormData(inputValues)).then(({data}) => {
            return storeAlumnos(data.id);
        }).finally(() => {
            setIsLoading(false);
        });
    }

    const storeAlumnos = async (solicitud) => {
        for (let i = 0; i < data.length; i++) {
            setCurrent(i + 1);
            const item = data[i];
            const formData = new FormData();
            formData.append('alumno[nombre]', item[0]);
            formData.append('alumno[email]', item[1]);
            formData.append('alumno[tipo]', item[2]);
            //formData.append('alumno[promedio]', item[2]);
            //formData.append('alumno[monto]', item[5]);
            formData.append('alumno[curp]', item[3]);
            formData.append('alumno[solicitud]', solicitud);
            if (!!item[3]) {
                await api.storeAlumno(formData);
            }
        }
        setShowPopup(false);
        Swal.fire(
            'Solicitud registrada con éxito',
            '',
            'success',
        ).then((alertResult) => {
            if (alertResult.isConfirmed) {
                if(asCame) {
                    window.location.href = getSchemeAndHttpHost(`/enfermeria`);
                } else{
                    window.location.href = getSchemeAndHttpHost('/');
                }

            }
        })
    }

    const getCurpErrors = (curp) => {
        const errors = [];
        const curpRegex = /^[A-Z]{4}\d{6}[A-Z]{6}[0-9A-Z]{2}$/; // CURP validation regex
        if (!curpRegex.test(curp)) {
            errors.push('El CURP no es válido');
        }
        if (curp.length !== 18) {
            errors.push('El CURP debe tener 18 caracteres');
        }
        if (curp.length === 0) {
            errors.push('El CURP es obligatorio');
        }
        return errors.join(', ');
    }

    const getEmailErrors = (email) => {
        const errors = [];
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errors.push('El email no es válido');
        }
        if (email.length === 0) {
            errors.push('El email es obligatorio');
        }
        return errors.join(', ');
    }

    const getTipoErrors = (tipo) => {
        const errors = [];
        if (!validTypes.includes(tipo.toUpperCase())) {
            errors.push('El tipo de alumno no es válido. Debe ser uno de los siguientes: ' + validTypes.join(', '));
        }
        if (tipo.length === 0) {
            errors.push('El tipo de alumno es obligatorio');
        }
        return errors.join(', ');
    }

    const getNombreErrors = (nombre) => {
        const errors = [];
        if (nombre.length === 0) {
            errors.push('El nombre es obligatorio');
        }
        return errors.join(', ');
    }

    return (
        <div>
            <Loader show={isLoading}/>
            <div className="container">
                <div id="die-area">
                    <FormValidator errors={errors} setErrors={setErrors}>
                        <form onSubmit={handleSubmit}>
                            {
                                !asCame && !escuelaId &&
                                <div className="row">
                                    <div className="col-md-12">
                                        <div className={`form-group ${errors["solicitud[unidad]"] ? 'has-error' : ''}` }>
                                            <label htmlFor="input-tipo-unidad-ie">Escuela de Enfermería<span className="text-danger">*</span></label>
                                            <select
                                                className="form-control"
                                                id="input-tipo-unidad-ie"
                                                name={'solicitud[unidad]'}
                                                required
                                            >
                                                <option value="">Seleccionar...</option>
                                                {escuelas.map(escuela => (
                                                    <option key={escuela.id} value={escuela.id}>{escuela.nombreEnfermeria}</option>
                                                ))}
                                            </select>
                                            <p className="help-block">{errors["solicitud[unidad]"]}</p>
                                        </div>
                                    </div>
                                </div>
                            }
                            {
                                !asCame && !!escuelaId &&
                                <input type="hidden" name={'solicitud[unidad]'} value={escuelaId}/>
                            }
                            <fieldset className="fieldset">
                                {
                                    asCame &&
                                    <div className="row">
                                        <div className="col-md-12">
                                            <div
                                                className={`form-group ${errors["solicitud[unidad]"] ? 'has-error' : ''}`}>
                                                <label htmlFor="input-tipo-unidad">Escuela de Enfermería<span
                                                    className="text-danger">*</span></label>
                                                <select
                                                    className="form-control"
                                                    id="input-tipo-unidad"
                                                    name={'solicitud[unidad]'}
                                                    required
                                                >
                                                    <option value="">Seleccionar...</option>
                                                    {escuelas.map(escuela => (
                                                        <option key={escuela.id} value={escuela.id}>{escuela.nombreEnfermeria}</option>
                                                    ))}
                                                </select>
                                                <p className="help-block">{errors["solicitud[unidad]"]}</p>
                                            </div>
                                        </div>
                                    </div>
                                }

                                <div className="row">
                                    <div className="col-md-4">
                                        <div
                                            className={`form-group ${errors["solicitud[periodo]"] ? 'has-error' : ''}`}>
                                            <label htmlFor="input-tipo-periodo">Tipo periodo<span
                                                className="text-danger">*</span></label>
                                            <select
                                                className="form-control"
                                                id="input-tipo-periodo"
                                                name={'solicitud[periodo]'}
                                                required
                                                readOnly
                                            >
                                                <option value="">Seleccionar...</option>
                                                <option value="anual">Anual</option>
                                                <option value="semestral">Semestral</option>
                                                <option value="cuatri">Cuatrimestre</option>
                                            </select>
                                            <p className="help-block">{errors["solicitud[periodo]"]}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className={`form-group ${errors["fechaInicio"] ? 'has-error' : ''}`}>
                                            <label htmlFor="input-fecha-inicio">Fecha Inicio<span
                                                className="text-danger">*</span></label>
                                            <input type="date" name="fechaInicio" className="form-control" required
                                                   id={'input-fecha-inicio'}/>
                                            <p className="help-block">{errors["fechaInicio"]}</p>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className={`form-group ${errors["fechaFin"] ? 'has-error' : ''}`}>
                                            <label htmlFor="input-fecha-fin">Fecha Termino<span
                                                className="text-danger">*</span></label>
                                            <input type="date" name="fechaFin" className="form-control" required
                                                   id={'input-fecha-fin'}/>
                                            <p className="help-block">{errors["fechaFin"]}</p>
                                        </div>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-4">
                                        <div className={`form-group ${errors["file"] ? 'has-error' : ''}`}>
                                            <InputFile handleInputChange={onProcessXlsx}/>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className={`form-group`}>
                                            <label htmlFor="link-layout"> </label>
                                            <a id="link-layout" href={`${getSchemeAndHttpHost('/files/layout.xlsx')}`}
                                               target={'_blank'}
                                               className={'form-contol'}>Descargar layout de carga</a>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className={`form-group`}>
                                            <button
                                                className="btn btn-primary btn-block"
                                                disabled={!isValidFile(data)}
                                                type={'submit'}>Enviar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-6">
                                        <p><strong>Nota: </strong> Los tipos de alumno permitidos son los siguientes</p>
                                        <ul>
                                            <li>Externo</li>
                                            <li>Hijo de trabajador</li>
                                            <li>Extraordinario</li>
                                        </ul>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </FormValidator>
                </div>
                {showTable &&
                    <>
                        <h2>Alumnos a registrar: {data.length}</h2>
                        <div className="row">
                            <div className="panel panel-default col-md-12">
                                <div className="panel-body">
                                    <table className="table">
                                        <thead className="headers">
                                        <tr>
                                            <th className={'td-w-25'}>CURP</th>
                                            <th className={'td-w-25'}>Nombre</th>
                                            <th className={'td-w-25'}>Email</th>
                                            <th className={'td-w-25'}>Tipo de alumno</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {data.map((item, index) => {
                                            return (
                                                !!item[3] &&
                                                <tr key={index} className={!isValidRow(item) ? 'is-row-invalid' : ''}>
                                                    <td>
                                                        {item[3]}
                                                        {!isValidRow(item) &&
                                                            <p className="text-danger">
                                                                {getCurpErrors(item[3])}
                                                            </p>}
                                                    </td>
                                                    <td>{item[0]}
                                                        {!isValidRow(item) &&
                                                            <p className="text-danger">
                                                                {getNombreErrors(item[0])}
                                                            </p>}
                                                    </td>
                                                    <td>{item[1]}
                                                        {!isValidRow(item) &&
                                                            <p className="text-danger">
                                                                {getEmailErrors(item[1])}
                                                            </p>}
                                                    </td>
                                                    <td>{item[2]}
                                                        {!isValidRow(item) &&
                                                            <p className="text-danger">
                                                                {getTipoErrors(item[2])}
                                                            </p>}
                                                    </td>
                                                </tr>
                                            )
                                        })}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </>
                }
            </div>
            <Popup show={showPopup}
                   footer={<></>}
                   canClose={false}
                   title={'Espere un momento'}
                   body={
                       <>
                           <ProgressBar total={data.length} current={current}/>
                       </>
                   }/>
        </div>
    );
}

createRoot(document.getElementById('wrapper-page')).render(
    <EnfermeriaCreate asCame={window.asCame}
                      ooad={window.OOAD}
                      escuelaId={window.escuelaId}/>
);