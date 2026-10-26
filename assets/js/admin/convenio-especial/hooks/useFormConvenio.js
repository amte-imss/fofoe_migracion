import React from "react";
import apiDelegacion from "../../../api/ApiDelegaciones";
import apiConvenio from "../api/ApiConvenio";
import apiCatalogos from "../../../api/ApiCatalogos";
import Swal from "sweetalert2";
import {getInputValues, getSchemeAndHttpHost, objectToFormData, showErrors} from "../../../utils";

export default function useFormConvenio({setIsLoading, convenio, isCame, usuario, recursosInitial = []}) {


    const [formErrors, setFormErrors] = React.useState({});
    const [ooads, setOoads] = React.useState([]);
    const [hasConvenios, setHasConvenios] = React.useState(false);
    const [instituciones, setInstituciones] = React.useState([]);
    const [conveniosGenerales, setConveniosGenerales] = React.useState([]);
    const [showConveniosGenerales, setShowConveniosGenerales] = React.useState(false);
    const [motivosColaboracion, setMotivosColaboracion] = React.useState([]);
    const [gradosAcademicos, setGradosAcademicos] = React.useState([]);
    const [disciplinas, setDisciplinas] = React.useState([]);
    const [carreras, setCarreras] = React.useState([]);
    const [cargos, setCargos] = React.useState([]);
    const [showCOMAEM, setShowCOMAEM] = React.useState(false);
    const [selectedOoads, setSelectedOoads] = React.useState([]);
    const [selectedCiclosAcademicos, setSelectedCiclosAcademicos] = React.useState([]);
    const [showSelectOOADs, setShowSelectOOADs] = React.useState(false);
    const [showSelectCiclosAcademicos, setShowSelectCiclosAcademicos] = React.useState(true);
    const [selectedTipo, setSelectedTipo] = React.useState('');
    const [recursos, setRecursos] = React.useState([]);

    const formRef = React.useRef();

    React.useEffect(() => {
        getCatalogs();
        setConvenioInfo();
        setRecursos(recursosInitial)
    }, [])

    const setConvenioInfo = async () => {
        if (convenio) {
            setHasConvenios(true);
            await sleep(300);

            await apiConvenio.getInstituciones(convenio.rfc).then(data => {
                setInstituciones(data);
                formRef.current['appbundle_convenio_admin[institucion]'].value = convenio.institucion.id;
            })
            formRef.current['appbundle_convenio_admin[rfc]'].value = convenio.rfc;
            formRef.current['appbundle_convenio_admin[razon_social]'].value = convenio.razon_social;
            formRef.current['appbundle_convenio_admin[nombre]'].value = convenio.nombre;
            formRef.current['appbundle_convenio_admin[tipo]'].value = convenio.tipo;
            formRef.current['appbundle_convenio_admin[sector]'].value = convenio.sector;
            formRef.current['appbundle_convenio_admin[objetivo_colaboracion]'].value = convenio.objetivo_colaboracion;
            formRef.current['appbundle_convenio_admin[fecha_firma]'].value = convenio.fecha_firma;
            formRef.current['appbundle_convenio_admin[vigencia_text]'].value = convenio.vigencia_text;
            formRef.current['appbundle_convenio_admin[fecha_vence_convenio]'].value = convenio.fecha_vence_convenio;
            formRef.current['appbundle_convenio_admin[cargo_emite_signfirst]'].value = convenio.cargo_emite_signfirst;
            formRef.current['appbundle_convenio_admin[nombre_emite_signfirst]'].value = convenio.nombre_emite_signfirst;
            formRef.current['appbundle_convenio_admin[cargo_emite_signsecond]'].value = convenio.cargo_emite_signsecond;
            formRef.current['appbundle_convenio_admin[nombre_emite_signsecond]'].value = convenio.nombre_emite_signsecond;
            formRef.current['appbundle_convenio_admin[recibe_signfirst]'].value = convenio.recibe_signfirst;
            formRef.current['appbundle_convenio_admin[recibe_signsecond]'].value = convenio.recibe_signsecond;

            setSelectedTipo(convenio.tipo);

            await sleep(700)
            await sleep(700)
            formRef.current['appbundle_convenio_admin[cargoFirmaIdFirst]'].value = convenio.cargoFirmaIdFirst ? convenio.cargoFirmaIdFirst.id : '';
            formRef.current['appbundle_convenio_admin[cargoFirmaIdSecond]'].value = convenio.cargoFirmaIdSecond ? convenio.cargoFirmaIdSecond.id : '';
            formRef.current['appbundle_convenio_admin[url_convenio]'].value = convenio.url_convenio;
            if (!isCame) {
                if (convenio.todasDelegaciones) {
                    setShowSelectOOADs(false);
                } else {
                    setShowSelectOOADs(true);
                    document.querySelector('#todasOOADsNo').checked = true;
                }
            }

            formRef.current['appbundle_convenio_admin[nivelAcademico]'].value = convenio.nivel_academico ? convenio.nivel_academico.id : '';
            formRef.current['appbundle_convenio_admin[motivo_colaboracion]'].value = convenio.motivo_colaboracion ? convenio.motivo_colaboracion.id : '';
            formRef.current['appbundle_convenio_admin[poblacion]'].value = convenio.poblacion ? convenio.poblacion.id : '';
            formRef.current['appbundle_convenio_admin[recurso]'].value = convenio.recurso ? convenio.recurso.id : '';

            const delegacionesSeleccionadas = [];
            convenio.delegaciones.forEach(item => {
                delegacionesSeleccionadas.push({
                    value: item.id, label: item.nombre
                })
            })
            setSelectedOoads(delegacionesSeleccionadas);
            setIsLoading(false);
        }

    }

    const getCatalogs = async () => {
        return new Promise(async (resolve, reject) => {
            await apiDelegacion.getDelegaciones().then(({data}) => {
                setOoads(data)
            });

            await apiCatalogos.getCoordinacionesFirmantes().then(({data}) => {
                setCargos(data);
            })

            if (!convenio) {
                setIsLoading(false);
            }
            resolve();
        })
    }

    const handleSubmit = (event) => {
        event.preventDefault();

        const origin = document.activeElement;
        if (origin.id === 'convenio_rfc') {
            handleSearchByRfc(event);
            return;
        }


        const form = formRef.current;
        if (!form.checkValidity()) {
            form.reportValidity();
            const errors = {}
            for (const i of formRef.current.elements) {
                if (i.name && !i.validity.valid) {
                    errors[i.name] = i.validationMessage;
                }
            }
            setFormErrors(errors);
            return;
        }
        if (!isCame) {
            if (isValidOoadSelects()) {
                formErrors['appbundle_convenio_admin[delegacion]'] = 'Se debe seleccionar una OOAD';
                setFormErrors({...formErrors});
            }
        }

        if (isValidCicloAcademicos()) {
            formErrors['appbundle_convenio_admin[cicloAcademico]'] = 'Se debe indicar un ciclo académico';
            setFormErrors({...formErrors});
        }
        if (convenio) {
            updateConvenio()
        } else {
            storeConvenio()
        }
    }

    const isValidOoadSelects = () => {
        if (showSelectOOADs) {
            return selectedOoads.length > 0;
        }
        return true;
    }

    const isValidCicloAcademicos = () => {
        //	if(showSelectCiclosAcademicos){
        return selectedCiclosAcademicos.length > 0;
        //	}
        //	return true;
    }

    const updateConvenio = () => {
        const inputValues = getInputValues(formRef.current)
        const formData = objectToFormData(inputValues);
        if (!isCame) {
            formData.append('appbundle_convenio_admin[todasDelegaciones]', showSelectOOADs ? 0 : 1);
        } else {
            formData.append('appbundle_convenio_admin[todasDelegaciones]', 0);
        }
        selectedOoads.forEach(item => {
            formData.append('appbundle_convenio_admin[delegaciones][]', item.value);
        })
        // se elimina la delegacion unica
        formData.delete('appbundle_convenio_admin[delegacion]');
        formData.delete('appbundle_convenio_admin[cicloAcademico]');

        setIsLoading(true);

        apiConvenio.updateConvenio(isCame, convenio.id, formData).then(result => {
            if (result.status) {
                Swal.fire(
                    "Convenio actualizado con éxito",
                    '',
                    'success',
                ).then(() => {
                    let url = getSchemeAndHttpHost('/admin/convenios-especiales');
                    if (isCame) {
                        url = getSchemeAndHttpHost('/admin/convenioscame/convenioscame');
                    }
                    window.location.href = url;
                })
            } else {
                showErrors(result.errors);
            }
        }).catch(errors => {
            errors.data.data.then(result => {
                showErrors(result.errors);
            });
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const storeConvenio = () => {
        const inputValues = getInputValues(formRef.current)
        const formData = objectToFormData(inputValues);
        formData.delete('appbundle_convenio_admin[delegacion]');
        if (!isCame) {
            formData.append('appbundle_convenio_admin[todasDelegaciones]', showSelectOOADs ? 0 : 1);
        } else {
            formData.append('appbundle_convenio_admin[todasDelegaciones]', 0);
        }


        if (!isCame) {
            selectedOoads.forEach(item => {
                formData.append('appbundle_convenio_admin[delegaciones][]', item.value);
            })
        } else {
            console.log('formRef.current.elements.delegacion.value', formRef.current.elements.delegacion.value);
            formData.append('appbundle_convenio_admin[delegaciones][]', formRef.current.elements.delegacion.value);
        }

        setIsLoading(true);

        apiConvenio.storeConvenio(isCame, formData).then(result => {
            if (result.status) {
                Swal.fire(
                    "Convenio guardado con éxito",
                    '',
                    'success',
                ).then(() => {
                    let url = getSchemeAndHttpHost('/admin/convenios-especiales');
                    if (isCame) {
                        url = getSchemeAndHttpHost('/admin/convenioscame/convenioscame');
                    }
                    window.location.href = url;
                })
            } else {
                //console.log(result.errors);
                showErrors(result.errors);
            }
        }).catch(errors => {
            errors.data.data.then(result => {
                showErrors(result.errors);
            });
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const handleChangeRecurso = (event) => {
        //console.log('handleChangeRecurso', event.target.value, recursos.find(item => item.id == event.target.value));
        const recurso = recursos.find(item => item.id == event.target.value);
        const motive = formRef.current['appbundle_convenio_admin[motivo_colaboracion]'].value;
        if(recurso.nombre.toString().toLowerCase() === 'otros'){
            Swal.fire({
                title: "Nombre del nuevo recurso",
                input: "text",
                inputAttributes: {
                    autocapitalize: "off"
                },
                showCancelButton: true,
                confirmButtonText: "Guardar",
                showLoaderOnConfirm: true,
                cancelButtonText: 'Cancelar',
                preConfirm: async (content) => {
                    return apiConvenio.postRecurso(content, motive).catch(error => {
                        error.data.data.then(({errors}) => {
                            showErrors(errors);
                        })
                        formRef.current['appbundle_convenio_admin[recurso]'].value = '';
                        return Promise.resolve({status: false});
                    });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            }).then(async (result) => {
                if (result.isConfirmed && result.value.status) {
                    Swal.fire('Recurso guardado con éxito');
                    setRecursos([...recursos, result.value.data]);
                    await sleep(200);
                    formRef.current['appbundle_convenio_admin[recurso]'].value = result.value.data.id;
                } else {
                    formRef.current['appbundle_convenio_admin[recurso]'].value = '';
                }
            });
        }
    }

    const handleChangeMultiSelect = (name, callback, values) => {
        if (values.length === 0) {
            formErrors[name] = 'Debe seleccionar una opción';
            setFormErrors({...formErrors});
        } else {
            formErrors[name] = '';
            setFormErrors({...formErrors});
        }
        callback(values);
    }

    const handleSearchByRfc = (event) => {
        setIsLoading(true);
        setInstituciones([]);

        apiConvenio.getInstituciones(formRef.current['appbundle_convenio_admin[rfc]'].value).then(data => {
            if (data.length) {
                setHasConvenios(true);
                setInstituciones(data);
                formRef.current['appbundle_convenio_admin[razon_social]'].value = data[0].razonSocial;
                formRef.current['appbundle_convenio_admin[nombre]'].value = data[0].nombre;
            } else {
                Swal.fire(
                    "El R.F.C. capturado, no obtuvo registros de instituciones",
                    '',
                    'warning',
                )
                setHasConvenios(false);
            }
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const handleChangeTipoConvenio = (event) => {
        if (formRef.current['appbundle_convenio_admin[convenio_general]']) {
            formRef.current['appbundle_convenio_admin[convenio_general]'].value = '';
        }
        setConveniosGenerales([]);
        setShowConveniosGenerales(false);
        const institucion = formRef.current['appbundle_convenio_admin[institucion]'].value;
        if (!institucion) {
            Swal.fire(
                "Datos faltantes",
                'Se requiere seleccionar previamente una institución',
                'warning',
            )
            formRef.current['appbundle_convenio_admin[tipo]'].value = '';
            return;
        }
        const tipo = formRef.current['appbundle_convenio_admin[tipo]'].value;
        if (!tipo) {
            return;
        }
        setSelectedTipo(event.target.value);
        setIsLoading(true);
        apiConvenio.getConveniosGeneralesByInstitucion(formRef.current['appbundle_convenio_admin[institucion]'].value).then(async ({data}) => {
            setConveniosGenerales(data);
            const tipo = formRef.current['appbundle_convenio_admin[tipo]'].value;
            if (tipo === 'Específico') {
                setShowConveniosGenerales(true);
            } else if (tipo === 'General' && data.length) {
                let links = '';
                data.forEach(item => {
                    links = `<a href="${getSchemeAndHttpHost(`/admin/conveniosadmin/conveniosadmin/show?id=${item.id}`)}" target="_blank">${item.fuica}, vigencia: ${item.fechaVenceConvenioFormatted}</a><br>`
                })
                Swal.fire(
                    "La institución seleccionada ya cuenta con convenios generales vigentes",
                    links,
                    'info',
                )
            }
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const handleChandeInstitucion = (event) => {
        formRef.current['appbundle_convenio_admin[tipo]'].value = '';
        if (formRef.current['appbundle_convenio_admin[convenio_general]']) {
            formRef.current['appbundle_convenio_admin[convenio_general]'].value = '';
        }
        setShowConveniosGenerales(false);
        setConveniosGenerales([]);
    }

    const handleChangeNivelAcademico = (event) => {
        formRef.current['appbundle_convenio_admin[carrera]'].value = '';
        const nivelAcademico = formRef.current['appbundle_convenio_admin[nivelAcademico]'].value;
        if (nivelAcademico === 'other') {
            storeNivelAcademico();
        }
    }

    const storeNivelAcademico = () => {
        Swal.fire({
            title: "Nombre del nuevo nivel o grado académico",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Guardar",
            showLoaderOnConfirm: true,
            cancelButtonText: 'Cancelar',
            preConfirm: async (content) => {
                return apiCatalogos.postNivelAcademico(content).catch(error => {
                    error.data.data.then(({errors}) => {
                        showErrors(errors);
                    })
                    formRef.current['appbundle_convenio_admin[nivelAcademico]'].value = '';
                    return Promise.resolve({status: false});
                });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then(async (result) => {
            if (result.isConfirmed && result.value.status) {
                Swal.fire('Nivel Académico guardado con éxito');
                setGradosAcademicos([...gradosAcademicos, result.value.data]);
                await sleep(200);
                formRef.current['appbundle_convenio_admin[nivelAcademico]'].value = result.value.data.id;
            } else {
                formRef.current['appbundle_convenio_admin[nivelAcademico]'].value = '';
            }
        });
    }

    const handleChangeDisciplina = (event) => {
        const disciplina = formRef.current['appbundle_convenio_admin[disciplina]'].value;
        if (disciplina === 'other') {
            storeDisciplina();
        }
    }

    const storeDisciplina = () => {
        Swal.fire({
            title: "Nombre de la nueva disciplina",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Guardar",
            showLoaderOnConfirm: true,
            cancelButtonText: 'Cancelar',
            preConfirm: async (content) => {
                return apiCatalogos.postDisciplina(content).catch(error => {
                    error.data.data.then(({errors}) => {
                        showErrors(errors);
                    })
                    formRef.current['appbundle_convenio_admin[disciplina]'].value = '';
                    return Promise.resolve({status: false});
                });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then(async (result) => {
            if (result.isConfirmed && result.value.status) {
                Swal.fire('Disciplina guardada con éxito');
                setDisciplinas([...disciplinas, result.value.data]);
                await sleep(200);
                formRef.current['appbundle_convenio_admin[disciplina]'].value = result.value.data.id;
            } else {
                formRef.current['appbundle_convenio_admin[disciplina]'].value = '';
            }
        });
    }

    const handleChangeCarrera = () => {
        const carrera = formRef.current['appbundle_convenio_admin[carrera]'].value;
        console.log('otra')
        if (carrera === 'other') {
            storeCarrera();
        }
    }

    const handleChangeMotivoColaboracion = () => {
        const motivo = formRef.current['appbundle_convenio_admin[motivo_colaboracion]'].value;
        setIsLoading(true);
        apiConvenio.getRecursos(motivo).then((recursos) => {
            setRecursos(recursos);
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const storeCarrera = () => {
        Swal.fire({
            title: "Nombre de la nueva carrera",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Guardar",
            showLoaderOnConfirm: true,
            cancelButtonText: 'Cancelar',
            preConfirm: async (content) => {
                return apiCatalogos.postCarrera(content).catch(error => {
                    error.data.data.then(({errors}) => {
                        showErrors(errors);
                    })
                    formRef.current['appbundle_convenio_admin[carrera]'].value = '';
                    return Promise.resolve({status: false});
                });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then(async (result) => {
            if (result.isConfirmed && result.value.status) {
                Swal.fire('Carrera guardada con éxito');
                setCarreras([...carreras, result.value.data]);
                await sleep(200);
                formRef.current['appbundle_convenio_admin[carrera]'].value = result.value.data.id;
            } else {
                formRef.current['appbundle_convenio_admin[carrera]'].value = '';
            }
        });
    }

    const sleep = (milliseconds) => {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve();
            }, milliseconds)
        })
    }

    const handleChangeVigenciaText = (event) => {
        const increment = event.target.value;
        const date = formRef.current['appbundle_convenio_admin[fecha_firma]'].value;
        if (!date) {
            Swal.fire(
                "Para calcular la fecha de vigencia se necesita indicar Fecha de Firma y la Vigencia en años",
                '',
                'warning',
            )
            return;
        }
        setIsLoading(true);
        apiConvenio.calcYearsDate(date, increment).then(({data}) => {
            formRef.current['appbundle_convenio_admin[fecha_vence_convenio]'].value = data;
        }).finally(() => {
            setIsLoading(false)
        })
    }

    const handleChangeCartaIntencion = (event) => {
        if (event.target.value == 1) {
            setShowCOMAEM(false)
        } else {
            if (formRef.current['appbundle_convenio_admin[fecha_emite_comaem]']) {
                formRef.current['appbundle_convenio_admin[fecha_emite_comaem]'].value = '';
            }
            if (formRef.current['appbundle_convenio_admin[fecha_vence_comaem]']) {
                formRef.current['appbundle_convenio_admin[fecha_vence_comaem]'].value = '';
            }
            setShowCOMAEM(true);
        }
    }

    const canFormSubmit = () => {
        let result = true;
        if (!formRef.current) {
            return false;
        }
        for (const i in formRef.current.elements) {
            const input = formRef.current.elements[i];
            if (input.name && input.validity && !input.validity.valid) {
                result = false;
            }
        }
        return result;
    }


    const handleChangeTodasOOADs = (event) => {
        if (event.target.value == 1) {
            setShowSelectOOADs(false);
        } else {
            setShowSelectOOADs(true);
        }
    }

    const handleChangeTodosCiclosAcademicos = (event) => {
        if (event.target.value == 1) {
            setShowSelectCiclosAcademicos(false);
        } else {
            setShowSelectCiclosAcademicos(true);
        }
    }

    return {
        handleSubmit, formErrors, setFormErrors,
        handleSearchByRfc, formRef, ooads, hasConvenios, instituciones,
        conveniosGenerales, showConveniosGenerales, motivosColaboracion, gradosAcademicos,
        disciplinas, cargos, carreras, handleChangeTipoConvenio, handleChandeInstitucion,
        handleChangeNivelAcademico, handleChangeVigenciaText, canFormSubmit, handleChangeCartaIntencion,
        showCOMAEM, selectedOoads, setSelectedOoads, selectedCiclosAcademicos, setSelectedCiclosAcademicos,
        setShowCOMAEM, handleChangeDisciplina, handleChangeCarrera, handleChangeTodasOOADs,
        showSelectOOADs, showSelectCiclosAcademicos, handleChangeTodosCiclosAcademicos,
        handleChangeMultiSelect, selectedTipo, recursos, handleChangeMotivoColaboracion,
        handleChangeRecurso
    };
}