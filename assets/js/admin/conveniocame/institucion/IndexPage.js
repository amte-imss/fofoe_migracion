import React from "react";
import { createRoot } from "react-dom/client";
import {getFormErrors, getSchemeAndHttpHost} from "../../../utils";
import Loader from "../../../components/Loader/Loader";
import ReactPaginate from "react-paginate";
import Swal from "sweetalert2";
import "../../../components/ReactPaginator/ReactPaginator.css";
import apiInstitucion from "./ApiInstitucion";
import Popup from "../../../components/Popup/Popup";
import "./IndexPage.css";

const getUsuario = (institucion, usuarioOOAD, campus ) => {
    if (!institucion.usuarios) {
        return null;
    }
    return institucion.usuarios.find(u => u.delegacionInstitucion && u.delegacionInstitucion.id == usuarioOOAD && (campus != null && u.campus && u.campus.id == campus));
}

export default function IndexPage({usuarioOOAD}) {

    const [isLoading, setIsLoading] = React.useState(false);
    const searchRef = React.useRef(null);
    const [total, setTotal] = React.useState(0);
    const [page, setPage] = React.useState(1);
    const [perPage, setPerPage] = React.useState(50);
    const [instituciones, setInstituciones] = React.useState([]);
    const [openPoupUser, setOpenPoupUser] = React.useState(false);
    const [selectedInstitucion, setSelectedInstitucion] = React.useState({});


    React.useEffect(() => {
        loadData();
    }, [page])

    const loadData = async () => {
        setIsLoading(true);
        apiInstitucion.paginateInstituciones({
            search: searchRef.current.elements.search.value || '',
            page,
            perPage
        }).then(content => {
            setInstituciones(content.data)
            setTotal(content.total);
        }).finally(() => {
            setIsLoading(false)
        })
    }

    const handleSearch = (event) => {
        event.preventDefault();
        setPage(1)
        loadData()
    }

    const handlePagination = value => {
        setPage(value.selected + 1);
    }

    const handleCleanSearch = () => {
        searchRef.current.reset();
        loadData();
    }

    // Método para generar el enlace completo
    const getDownloadLink = (institucion) => {
        const filePath = `${getSchemeAndHttpHost("/admin/institucion/")}${institucion.id}/cedula`;
        return filePath;
    }

    const handleManageUser = (value) => {
        setOpenPoupUser(true)
        setSelectedInstitucion(value)
    }

    return (
        <>
            <Loader show={isLoading}/>

            <div className="row mt-3">
                <div className="col-md-12">
                    <form className="form-inline text-right"
                          autoComplete={'off'}
                          method="post" ref={searchRef} onSubmit={handleSearch}>
                        <div className="form-group">
                            <input type="text" className="form-control" name="search"
                                   placeholder="Buscar..."/>
                        </div>
                        <button className="btn btn-outline-secondary" type="submit">
                            <i className="fa fa-search"></i>
                            <span className="hidden-xs hidden-sm">Buscar</span>
                        </button>
                        <button className="btn btn-outline-primary ml-1"
                                onClick={handleCleanSearch}
                                type="button">
                            <i className="fa fa-trash"></i>
                            <span className="hidden-xs hidden-sm">Limpiar filtros</span>
                        </button>
                    </form>
                </div>
            </div>
            <div className="row mt-3">
                <div className="col-md-4 mr-btn-headerConAdmin-admin ">
                    <a className="btn btn-primary action-new butonConve"
                       style={{width: "100%"}}
                       href={getSchemeAndHttpHost("/admin/institucion/export")}
                       target="_self">Exportar Instituciones</a>
                </div>
                <div id="clearFiltersAdmin" className="col-md-3 mr-btn-headerConAdmin-admin  d-none"><a
                    className="btn btn-primary action-new butonConve" href="/admin/instituto" target="_self">Limpiar
                    Filtros</a></div>
            </div>
            <div className="col-md-12">
                <div className="row">
                    <div className="table-responsive scrollListViewTable col-md-12">
                        <table className="table table-hover">
                            <thead>
                            <tr>
                                <th>Razón Social</th>
                                <th>RFC</th>
                                <th>Nombre Comercial</th>
                                <th>Email</th>
                                <th>Sitio Web</th>
                                <th>Dirección</th>
                                <th>Fax</th>
                                <th>Extensión</th>
                                <th>Teléfono</th>
                                <th>Cédula Fiscal</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            {
                                instituciones.map((instituto, index) => {
                                    return (
                                        <tr key={index}>
                                            <td>{instituto.razonSocial ? instituto.razonSocial.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.rfc ? instituto.rfc.toString().toUpperCase() : ''}</td>
                                            <td>
                                                <NombreConvenio instituto={instituto} usuarioOOAD={usuarioOOAD}/>
                                            </td>
                                            <td>{instituto.correo ? instituto.correo.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.sitioWeb ? instituto.sitioWeb.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.direccion ? instituto.direccion.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.fax ? instituto.fax.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.extension ? instituto.extension.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.telefono ? instituto.telefono.toString().toUpperCase() : ''}</td>
                                            <td>
                                                {
                                                    instituto.cedulaIdentificacion ?
                                                        <a href={getDownloadLink(instituto)}
                                                           target="_blank">
                                                            Descargar PDF
                                                        </a>
                                                        :
                                                        'No disponible'
                                                }
                                            </td>
                                            <td>
                                                <div className="">
                                                    <div className="p-1 col-md-4 btn-sm">
                                                        <button className="btn btn-primary"
                                                                style={{backgroundColor: "#137474", width: "10rem"}}
                                                                onClick={() => handleManageUser(instituto)}
                                                        >
                                                            {getUsuario(instituto, usuarioOOAD) ? 'Cambiar usuario' : 'Asignar Usuario'}
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    )
                                })
                            }
                            </tbody>
                        </table>
                    </div>
                    <ReactPaginate
                        previousLabel={'Anterior'}
                        nextLabel={'Siguiente'}
                        breakLabel={'...'}
                        breakClassName={'break-me'}
                        pageCount={total / perPage}
                        marginPagesDisplayed={2}
                        pageRangeDisplayed={parseInt(perPage)}
                        onPageChange={handlePagination}
                        containerClassName={'pagination'}
                        subContainerClassName={'pages pagination'}
                        activeClassName={'active'}
                    />
                </div>
            </div>
            <PopupUser
                setIsLoading={setIsLoading}
                usuarioOOAD={usuarioOOAD}
                institucion={selectedInstitucion}
                show={openPoupUser} onClose={() => setOpenPoupUser(false)}/>
        </>
    )

}

const PopupUser = ({institucion, show, onClose, setIsLoading, usuarioOOAD}) => {

    const [showSelectCampus, setShowSelectCampus] = React.useState(false);
    const [campus, setCampus] = React.useState([]);
    const [selectedCampus, setSelectedCampus] = React.useState('');
    const [formErrors, setFormErrors] = React.useState({});

    const usuario = getUsuario(institucion, usuarioOOAD, selectedCampus);

    React.useEffect(() => {
        if(show){
            setIsLoading(true);
            apiInstitucion.getCampus(institucion.id).then(data => {
                setCampus(data);
            }).finally(()=>{
                setIsLoading(false);
            })
            if(institucion.usersByCampus){
                document.querySelector('#usersByCampus').checked=true;
                setShowSelectCampus(true);
            }
        }else{
            setShowSelectCampus(false);
        }
    }, [show])


    const handleSubmitUsuario = (event) => {
        event.preventDefault();
        setFormErrors({})
        const formErrorsCurrent = getFormErrors(event.target);
        console.log(formErrorsCurrent)
        if(!formErrorsCurrent.status){
            setFormErrors(formErrorsCurrent.errors);
            return;
        }
        setIsLoading(true);
        const form = event.target;
        const formData = new FormData(form);
        apiInstitucion.storeUsuario(institucion.id, formData).then(response => {
            if (response.status) {
                onClose();
                Swal.fire({
                    title: 'Usuario actualizado',
                    text: response.message,
                    icon: 'success'
                })
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message,
                    icon: 'error'
                })
            }
        }).finally(() => {
            setIsLoading(false);
        })
    }

    const onInputUsersByCampus = (event) => {
        setShowSelectCampus(event.target.checked);
    }

    const onInputCampus = event => {
        if (event.target.value === 'other') {
            Swal.fire({
                title: 'Indique el nombre del nuevo campus',
                input: 'text',
                inputPlaceholder: 'Nombre del campus',
                showCancelButton: true,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    setIsLoading(true);
                    const nombreCampus = result.value?.trim();
                    if (!nombreCampus) {
                        Swal.fire('Error', 'Debe ingresar un nombre', 'error');
                        return;
                    }
                    const formData = new FormData();
                    formData.append('request[name]', nombreCampus);
                    apiInstitucion.storeCampus(institucion.id, formData).then(data => {
                        Swal.fire('Campus guardado con éxito', '', 'success');
                        setCampus([...campus, data.campus]);
                        setSelectedCampus(data.campus.id);
                        setTimeout(()=>{
                            document.querySelector('#campus').value = data.campus.id;
                        }, 500)
                    }).catch(error => {
                        Swal.fire('Error', 'No se pudo guardar el campus', 'error');
                    }).finally(() => {
                        setIsLoading(false);
                    });
                } else if (result.isDismissed) {
                    document.querySelector('#campus').value = '';
                }
            });
        }else{
            setSelectedCampus(event.target.value);
        }
    }

    return <Popup
        show={show}
        body={
            <>
                <form id={'formUser'} onSubmit={handleSubmitUsuario} autoComplete={'off'} noValidate>
                    <fieldset>
                        <div className="form-group">
                            <label htmlFor="usersByCampus">
                                <input type="checkbox"
                                       id={'usersByCampus'}
                                       readOnly={institucion.userByCampus}
                                       name={'request[usersByCampus]'} onInput={onInputUsersByCampus}/>
                                <span className={'ml-3'}>Usuarios por campus</span>
                            </label>
                        </div>

                        {showSelectCampus &&
                            <div className="form-group">
                                <label htmlFor="campus">Campus</label>
                                <select name="request[campus]" id="campus" required={true} className={'form-control'}
                                        onChange={onInputCampus}>
                                    <option value="">Seleccionar...</option>
                                    {campus.map((item, i) => {
                                        return <option key={i} value={item.id}>{item.nombre}</option>
                                    })}
                                    <option value="other">Agregar campus</option>
                                </select>
                            </div>}

                        <div className="form-group">
                            <label htmlFor="nombre">Nombre</label>
                            <input type="text"
                                   required
                                   defaultValue={usuario?.nombre || ''}
                                   className="form-control" name="request[name]" id="nombre"/>
                            <small className="text-danger">{formErrors['request[name]']}</small>
                        </div>
                        <div className="form-group">
                            <label htmlFor="last_name">Paterno</label>
                            <input type="text"
                                   required
                                   defaultValue={usuario?.nombre || ''}
                                   className="form-control" name="request[last_name]" id="last_name"/>
                            <small className="text-danger">{formErrors['request[last_name]']}</small>
                        </div>
                        <div className="form-group">
                            <label htmlFor="other_name">Materno</label>
                            <input type="text"
                                   defaultValue={usuario?.nombre || ''}
                                   className="form-control" name="request[other_name]" id="other_name"/>
                            <small className="text-danger">{formErrors['request[other_name]']}</small>
                        </div>
                        <div className="form-group">
                            <label htmlFor="usuario">Correo</label>
                            <input type="email"
                                   required
                                   defaultValue={usuario?.correo || ''}
                                   className="form-control" name="request[correo]" id="usuario"/>
                            <small className="text-danger">{formErrors['request[correo]']}</small>
                        </div>
                        <div className="form-group">
                            <label htmlFor="password">Contraseña</label>
                            <input type="password"
                                   minLength={8}
                                   required className="form-control" name="request[password]"
                                   id="password"/>
                            <small className="text-danger">{formErrors['request[password]']}</small>
                        </div>
                    </fieldset>
                </form>
            </>
        }
        footer={
            <>
                <div className="popup__footer-actions popup__footer-actions_flex">
                    <input type="submit" form={'formUser'} className={'btn btn-primary btn-block'}
                           value={'Actualizar'}/>
                    <input type="button" className={'btn btn-danger btn-block'} onClick={onClose} value={'Cancelar'}/>
                </div>
            </>
        }
        title={'Gestión de usuario para la institución: ' + institucion.razonSocial}
        closePopup={onClose}></Popup>
}

function NombreConvenio({usuarioOOAD, instituto}) {
    if (!instituto) return <span></span>;
    const convenio = instituto.convenios.find(c => {
        return c.delegacionConvenios.some(dc => dc.delegacion.id == usuarioOOAD)
    });
    if (convenio) {
        return <span>{convenio.nombre.toString().toUpperCase()}</span>
    }
    return (<span className="">{instituto.nombre ? instituto.nombre.toString().toUpperCase() : ''}</span>)
}

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.querySelector('.react')).render(
        <IndexPage usuarioOOAD={window.USUARIO_OOAD}/>,
    )});