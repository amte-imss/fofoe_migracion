import 'regenerator-runtime/runtime';
import React from "react";
import { createRoot } from "react-dom/client";
import Loader from "../../components/Loader/Loader";
import {FormValidator} from "../../components/FormValidator/FormValidator";
import Popup from "../../components/Popup/Popup";
import {getSchemeAndHttpHost, transformSymfonyDate, objectToFormData, showErrors, sleep} from "../../utils";
import Swal from 'sweetalert2'
import 'sweetalert2/src/sweetalert2.scss';
import ApiEduPer from "../api";
import Select from "react-select";
import {
    CODE_RENTA_INSTALACIONES_SIMULADORES,
    DURACION_LABEL_DEFAULT,
    HOURS,
    LABEL_DURACION_SIMULACION, OTHER,
    PERSON,
    SIMULACION
} from "../constants";


const MODALIDAD_PRESENCIAL = 'modalidad-presencial';

function CreatePage() {
    const [isLoading, setIsLoading] = React.useState(false);
    const [errors, setErrors] = React.useState({});

    const [cursos, setCursos] = React.useState([]);
    const [modalidades, setModalidades] = React.useState([]);
    const [tipos, setTipos] = React.useState([]);
    const [duraciones, setDuraciones] = React.useState([]);

    const [formData, setFormData] = React.useState({
        tipo: '',
        duracion: '',
        modalidad: '',
        cantidad: 0,
        email: '',
        participante: '',
        curso: '',
        descuento: false
    });

    const tipoRef = React.useRef(null);
    const duracionRef = React.useRef(null);
    const cursoRef = React.useRef(null);
    const modalidadRef = React.useRef(null);

    React.useEffect(() => {
        getCatalogos();
    }, []);

    React.useEffect(() => {
        setIsLoading(true);
        const getData = async () => {
            duracionRef.current.value = '';
            modalidadRef.current.value = '';
            cursoRef.current.value = '';
            await Promise.all([
                ApiEduPer.getModalidades(formData.tipo).then(({data}) => {
                    setModalidades(data)
                })
            ])
            setIsLoading(false)
        }
        getData();

    }, [formData.tipo])

    React.useEffect(() => {
        setIsLoading(true);
        const getData = async () => {
            duracionRef.current.value = '';
            await Promise.all([
                ApiEduPer.getDuraciones(formData.modalidad).then(({data}) => {
                    setDuraciones(data)
                })
            ])
            setIsLoading(false)
        }
        getData();

    }, [formData.modalidad])

    const getCatalogos = async () => {
        await ApiEduPer.getTipoSolicitudes().then(({data}) => {
            setTipos(data);
        })

        await ApiEduPer.getCursos().then(({data}) => {
            setCursos(data);
        })
    }

    const showQuatityInput = () => {
        const foundQuatity = duraciones.find(item => item.code === formData.duracion);
        //console.log('showQuatityInput',formData.duracion, foundQuatity )
        return foundQuatity && [HOURS, PERSON].includes(foundQuatity.period)
    }

    const labelQuantityInput = () => {
        const foundObject = duraciones.find(item => item.code === formData.duracion);
        return foundObject && foundObject.period === PERSON ? 'Cantidad de Personas' : 'Cantidad de Horas';
    }

    const labelDuracionInput = () => {
        let result = DURACION_LABEL_DEFAULT;
        if (formData.tipo === SIMULACION) {
            result = LABEL_DURACION_SIMULACION;
        }
        return result;
    }

    const handleChange = (event) => {
        //console.log('handleChange', event.target.name)
        const {name, value, type, checked} = event.target;
        setFormData({
            ...formData,
            [name]: type === 'checkbox' ? checked : value
        });
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        setIsLoading(true);

        const data = new FormData();
        data.append('request[type]', formData.tipo);
        data.append('request[modality]', formData.modalidad);
        data.append('request[duration]', formData.duracion);
        data.append('request[quantity]', formData.cantidad);
        data.append('request[name]', formData.participante);
        data.append('request[email]', formData.email);
        data.append('request[course]', formData.curso);
        data.append('request[discount]', formData.descuento);

        ApiEduPer.storeRequest(data).then((result) => {
            if (result.status) {
                Swal.fire(
                    "Registro guardado con éxito",
                    '',
                    'success',
                ).then(() => {
                    let url = getSchemeAndHttpHost("/edu-per/");
                    window.location.href = url;
                })
            } else {
                //console.log(result.errors);
                showErrors(result.errors);
            }
        }).finally(() => {
            setIsLoading(false);
        });
    }

    const handleChangeCurso = (event) => {
        const {name, value, type, checked} = event.target;
        if(value === OTHER){
            Swal.fire({
                title: "Nombre del nuevo curso",
                input: "text",
                inputAttributes: {
                    autocapitalize: "on"
                },
                showCancelButton: true,
                confirmButtonText: "Guardar",
                showLoaderOnConfirm: true,
                cancelButtonText: 'Cancelar',
                preConfirm: async (content) => {
                    return ApiEduPer.storeCurso(content).catch(error => {
                        error.data.data.then(({errors}) => {
                            showErrors(errors);
                        });
                        cursoRef.current.value = '';
                        return Promise.resolve({status: false});
                    });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            }).then(async (result) => {
                if (result.isConfirmed && result.value.status) {
                    Swal.fire('Curso agregado con éxito');
                    setCursos([...cursos, result.value.data]);
                    await sleep(200);
                    setFormData({...formData, curso: result.value.data.code})
                    cursoRef.current.value = result.value.data.code;
                } else {
                    cursoRef.current.value = '';
                }
            });
        }else{
            setFormData({...formData, curso: event.target.value})
        }
    }

    return (
        <div>
            <Loader show={isLoading}/>
            <div className="container">
                <div id="die-area">
                    <form onSubmit={handleSubmit}>
                        <fieldset className="fieldset">
                            <legend>Datos del registros</legend>
                            <div className="row">
                                <div className="col-md-12">
                                    <div className="form-group">
                                        <label htmlFor="tipo">Tipo<span className="text-danger">*</span></label>
                                        <select className="form-control"
                                                id="tipo" required ref={tipoRef}
                                                name={'tipo'}
                                                onChange={handleChange}
                                        >
                                            <option value="">Seleccionar...</option>
                                            {tipos.map((tipo, index) => (
                                                <option key={index} value={tipo.code}>{tipo.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label htmlFor="modalidad">
                                            {formData.tipo === MODALIDAD_PRESENCIAL ? 'Categoría' : 'Modalidad'}
                                            <span className="text-danger">*</span>
                                        </label>
                                        <select className="form-control" id="modalidad"
                                                name={'modalidad'}
                                                onChange={handleChange}
                                                required ref={modalidadRef}>
                                            <option value="">Seleccionar...</option>
                                            {modalidades.map((modalidad, index) => (
                                                <option key={index} value={modalidad.code}>{modalidad.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label htmlFor="duracion">{labelDuracionInput()}<span
                                            className="text-danger">*</span></label>
                                        <select className="form-control" id="duracion"
                                                onChange={handleChange}
                                                name={'duracion'}
                                                required ref={duracionRef}>
                                            <option value="">Seleccionar...</option>
                                            {duraciones.map((item, index) => (
                                                <option key={index} value={item.code}>{item.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                                {
                                    showQuatityInput() &&
                                    <div className="col-md-4">
                                        <div className="form-group">
                                            <label htmlFor="cantidadUsuarios">{labelQuantityInput()}</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                id="cantidadUsuarios"
                                                min="1"
                                                onChange={handleChange}
                                                name={'cantidad'}
                                            />
                                        </div>
                                    </div>
                                }
                            </div>

                            <div className="row">
                                <div className="col-md-6">
                                    <div className="form-group">
                                        <label htmlFor="participante">
                                            Nombre del participante
                                        </label>
                                        <input type="text" className="form-control" name={'participante'}
                                               id={'participante'}
                                               onChange={handleChange}/>
                                    </div>
                                </div>

                                <div className="col-md-6">
                                    <div className="form-group">
                                        <label htmlFor="participante">
                                            Correo del participante
                                        </label>
                                        <input type="email" className="form-control" name={'email'} id={'email'}
                                               onChange={handleChange}/>
                                    </div>
                                </div>
                            </div>

                            <div className="row">

                                <div className="col-md-6">
                                    <div className="form-group">
                                        <label htmlFor="duracion">Curso<span className="text-danger">*</span></label>
                                        <select className="form-control" id="duracion"
                                                onChange={handleChangeCurso}
                                                name={'curso'}
                                                required ref={cursoRef}>
                                            <option value="">Seleccionar...</option>
                                            {cursos.map((item, index) => (
                                                <option key={index} value={item.code}>{item.name}</option>
                                            ))}
                                            <option value="other">Otro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div className="row">

                                <div className="col-md-4">
                                    <div className="form-group">
                                        <label>
                                            <input
                                                type="checkbox"
                                                onChange={handleChange}
                                                name={'descuento'}
                                            /> Aplicar 50% de Descuento
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset className="fieldset">
                            <div className="row">
                                <div className="col-md-6">
                                    <button type="submit" className="btn btn-block btn-success">Guardar Solicitud
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    );
}

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.getElementById('wrapper-page')).render(
        <CreatePage/>)
});