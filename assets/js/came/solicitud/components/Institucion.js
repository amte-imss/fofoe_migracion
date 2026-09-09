import * as React from 'react'
import SelectSearch from 'react-select-search/dist/cjs';
import './Institucion.scss';
import {getSchemeAndHttpHost} from "../../../utils";

const InstitucionS = ({instituciones, disableSelect, callbackIsLoading, institucion, parentCallback}) => {
    React.useEffect(() => {
        console.log(instituciones, disableSelect, callbackIsLoading, institucion, parentCallback);
    }, [])
    return (<><h1>jejejej</h1></>)
}

const Institucion = ({instituciones, disableSelect, callbackIsLoading, institucion, parentCallback}) => {

    const [selectedInstitution, setSelectedInstitution] = React.useState(institucion ? institucion : {});

    const [rfc, setRfc] = React.useState(institucion && institucion.rfc ? institucion.rfc : '');
    const [domicilio, setDomicilio] = React.useState(institucion && institucion.direccion ? institucion.direccion : '');
    const [phone, setPhone] = React.useState(institucion && institucion.telefono ? institucion.telefono : '');
    const [extension, setExtension] = React.useState(institucion && institucion.extension ? institucion.extension : '')
    const [web, setWeb] = React.useState(institucion && institucion.sitioWeb ? institucion.sitioWeb : '');
    const [email, setEmail] = React.useState(institucion && institucion.correo ? institucion.correo : '');
    const [fax, setFax] = React.useState(institucion && institucion.fax ? institucion.fax : '');
    const [representante, setRepresentante] = React.useState(institucion && institucion.representante ? institucion.representante : '')

    const [errores, setErrores] = React.useState({});
    const [alert, setAlert] = React.useState({});

    const [disableSelectState, setDisableSelectState] = React.useState(false)

    const handleSelectedInstitution = (value) => {
        const results = instituciones.filter(item => {
            return value.toString() === item.id.toString()
        });
        const institucion = results.length > 0 ? results[0] : {};
        setSelectedInstitution(institucion);
        setRfc(institucion.rfc ? institucion.rfc : '');
        setDomicilio(institucion.direccion ? institucion.direccion : '');
        setPhone(institucion.telefono ? institucion.telefono : '');
        setExtension(institucion.extension ? institucion.extension: '');
        setWeb(institucion.sitioWeb ? institucion.sitioWeb : '');
        setEmail(institucion.correo ? institucion.correo : '');
        setFax(institucion.fax ? institucion.fax : '');
        setRepresentante(institucion.representante ? institucion.representante : '');
        if(institucion.id){
            callbackIsLoading(true);
            fetch(`${getSchemeAndHttpHost()}/came/api/convenio/${institucion.id}`)
                .then(response => {
                    return response.json()}, error => {
                    console.error(error)})
                .then(json => {
                    conveniosCallback(json.data);
                })
                .finally(() => {callbackIsLoading(false);});
            parentCallback(institucion);
        } else {
            parentCallback({});
        }
        return institucion ? institucion : {};
    }

    const handleUpdateInstitucion = (event) => {
        event.preventDefault();
        setAlert({});
        callbackIsLoading(true);
        if(validateForm()){
            let data = new FormData();
            data.append('institucion[rfc]', rfc);
            data.append('institucion[direccion]', domicilio);
            data.append('institucion[correo]', email);
            data.append('institucion[fax]', fax);
            data.append('institucion[sitioWeb]', web);
            data.append('institucion[telefono]', phone);
            data.append('institucion[extension]', extension);
            data.append('institucion[representante]', representante);
            fetch(`${getSchemeAndHttpHost()}/came/api/institucion/` + selectedInstitution.id, {
                method: 'post',
                body: data
            }).then(response => response.json(), error => {
                console.error(error);
            }).then(json => {
                if (json.errors) {
                    setErrores(json.errors);
                }
                setAlert(Object.assign(alert, {
                    show: true,
                    message: json.message,
                    type: (json.status ? 'success' : 'danger')
                }))
                if(json.data){
                    parentCallback(json.data);
                }
                if(json.status){
                    setDisableSelectState(true);
                }
            }).finally(() => {
                callbackIsLoading(false)
            });
        }else {
            callbackIsLoading(false)
        }
    }

    const getInstituciones = () =>{
        const result = [];
       // const result = [{value:'-', name: 'Seleccionar ...'}];
        instituciones.map(item => {
            result.push({value: item.id.toString(), name:item.nombre});
        })
        return result;
    }

    const validateForm = () => {
        let result = true;
        let obj_errors = {};
        if(rfc.toString().length > 13 || rfc.toString().length < 12){
            result = false;
            obj_errors = Object.assign(obj_errors, {'rfc': ['Este valor debería tener 12 caracteres como mínimo o 13 como máximo.']});
        }
        if(phone.toString().length < 8 || phone.toString().length > 15 ){
            result = false;
            obj_errors = Object.assign(obj_errors, {'telefono': ['Este valor debería tener 10 caracteres.']});
        }
        if(!(/^\d+$/.test(phone))) {
            result = false;
            obj_errors = Object.assign(obj_errors, {'telefono': ['Solo se pueden ingresar números']});
        }

        if(extension.trim() !== '' && !(/^\d+$/.test(extension))) {
            result = false;
            obj_errors = Object.assign(obj_errors, {'extension': ['Solo se pueden ingresar números']});
        }

        if(!(/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email))){
            result = false;
            obj_errors = Object.assign(obj_errors, {'correo': ['Este valor no es una dirección de email válida']});
        }

        if(representante.indexOf(' ') < 1) {
            result = false;
            obj_errors = Object.assign(obj_errors, {'representante': ['Este valor no es un nombre válido']});
        }

        if(representante.length > 250) {
            result = false;
            obj_errors = Object.assign(obj_errors, {'representante': ['La longitud de representante debe ser menor a 250 letras']});
        }

        if(!result){
            setErrores(obj_errors);
        }else{
            setErrores({});
        }
        return result;
    }


    return (
        <>
            <div className="row">
                <div className="col-md-12">
                    <div className={`form-group ${errores.institucion ? 'has-error has-feedback' : ''}`}>
                        <div className={`alert alert-${alert.type} `}
                             style={{display: (alert.show ? 'block' : 'none')}}>
                            <a className="close" onClick={e => setAlert({})}>&times;</a>
                            {alert.message}
                        </div>
                        <label htmlFor="institucion_name">Nombre de la institución:</label>
                        <SelectSearch
                            id={'institucion_name'}
                            options={getInstituciones()}
                            search
                            onChange={value => handleSelectedInstitution(value)}
                            value={selectedInstitution.id ? selectedInstitution.id.toString() : ''}
                            placeholder="Seleccionar ..."
                            required={true}
                            disabled={disableSelect || disableSelectState}
                        />
                        <span className="help-block">{errores.institucion ? errores.institucion[0] : ''}</span>
                        {/*<p><strong>Seleccionada: </strong> {selectedInstitution.id}</p>*/}
                    </div>
                </div>
            </div>
            <form onSubmit={handleUpdateInstitucion}>


                <div style={{display: (selectedInstitution.id ? 'block' : 'none')}}>
                    <div className="row">
                        {false &&
                        <div className="col-md-4">
                            <div className={`form-group ${errores.representante ? 'has-error has-feedback' : ''}`}>
                                <label className="control-label" htmlFor="representante">Representante:</label>
                                <input id="representante"
                                       className={'form-control'}
                                       required={true}
                                       type="text"
                                       value={representante}
                                       onChange={e => setRepresentante(e.target.value)}
                                />
                                <span className="help-block">{errores.representante ? errores.representante[0] : ''}</span>
                            </div>
                        </div>
                        }
                        <div className="col-md-4">
                            <div className={`form-group ${errores.rfc ? 'has-error has-feedback' : ''}`}>
                                <label className="control-label" htmlFor="rfc">RFC:</label>
                                <input id="rfc"
                                       className={'form-control'}
                                       required={true}
                                       type="text"
                                       value={rfc}
                                       onChange={e => setRfc(e.target.value)}
                                />
                                <span className="help-block">{errores.rfc ? errores.rfc[0] : ''}</span>
                            </div>
                        </div>
                        {false &&
                        <div className="col-md-4">
                            <div className={`form-group ${errores.direccion ? 'has-error has-feedback' : ''}`}>
                                <label htmlFor="domicilio">Domicilio:</label>
                                <input id={'domicilio'} className={'form-control'}
                                       required={true}
                                       type="text" value={domicilio} onChange={e => setDomicilio(e.target.value)}/>
                                <span className="help-block">{errores.direccion ? errores.direccion[0] : ''}</span>
                            </div>
                        </div>
                        }
                    </div>
                    {false &&
                    <div className="row">
                        <div className="col-md-3">
                            <div className={`form-group ${errores.telefono ? 'has-error has-feedback' : ''}`}>
                                <label htmlFor="phone">Número teléfonico:</label>
                                <input id={'phone'} className={'form-control'}
                                       required={true}
                                       type="text" value={phone} onChange={e => setPhone(e.target.value)}/>
                                <span className="help-block">{errores.telefono ? errores.telefono[0] : ''}</span>
                            </div>
                        </div>
                        <div className="col-md-2">
                            <div className={`form-group ${errores.extension ? 'has-error has-feedback' : ''}`}>
                                <label htmlFor="extension">Extensión:</label>
                                <input id={'v'} className={'form-control'}
                                       type="text" value={extension} onChange={e => setExtension(e.target.value)}/>
                                <span className="help-block">{errores.v ? errores.extension[0] : ''}</span>
                            </div>
                        </div>
                        <div className="col-md-7">
                            <div className={`form-group ${errores.sitioWeb ? 'has-error has-feedback' : ''}`}>
                                <label htmlFor="web_page">Página web (Opcional):</label>
                                <input id="web_page" className={'form-control'}
                                       type="text" value={web} onChange={e => setWeb(e.target.value)}/>
                                <span className="help-block">{errores.sitioWeb ? errores.sitioWeb[0] : ''}</span>
                            </div>
                        </div>
                    </div>
                    }

                    {false &&
                    <div className="row">
                        <div className="col-md-3">
                            <div className={`form-group ${errores.correo ? 'has-error has-feedback' : ''}`}>
                                <label htmlFor="email">Correo electrónico:</label>
                                <input id="email" className={'form-control'}
                                       required={true}
                                       type="email" value={email} onChange={e => setEmail(e.target.value)}/>
                                <span className="help-block">{errores.correo ? errores.correo[0] : ''}</span>
                            </div>
                        </div>
                        <div className="col-md-3">
                            <div className={`form-group ${errores.fax ? 'has-error has-feedback' : ''}`}>
                                <label htmlFor="fax">Número de fax (Opcional):</label>
                                <input id="fax" className={'form-control'}
                                       type="text" value={fax} onChange={e => setFax(e.target.value)}/>
                                <span className="help-block">{errores.fax ? errores.fax[0] : ''}</span>
                            </div>
                        </div>
                        {false &&
                        <div className="col-md-4">
                            <label htmlFor="btn_institucion">&#160;</label>
                            <button id="btn_institucion" className={'form-control btn btn-primary'}>Guardar datos de la Institución Educativa</button>
                        </div>}
                    </div>
                    }
                </div>
            </form>
        </>
    )
}

export default Institucion;
