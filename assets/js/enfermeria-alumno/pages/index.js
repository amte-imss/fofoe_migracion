import React from "react";
import ReactDOM from "react-dom";
import Loader from "../../components/Loader/Loader";
import '../styles/enfermeria-alumno.css';
import {getSchemeAndHttpHost} from "../../utils";
import Popup from "../../components/Popup/Popup";

function EnfermeriaAlumnoIndex({usuario}) {
    const [isLoading, setIsLoading] = React.useState(true);
    const [showModal, setShowModal] = React.useState(false);
    const [usuarioState, setUsuarioState] = React.useState(usuario);

    React.useEffect(() => {
        setTimeout(() => {
            setIsLoading(false);
        }, 500)
    }, [])

    const openModal = () => {
        setShowModal(true);
        if (usuario.status == '') {
            setUsuarioState({...usuario, statusFormatted: 'En espera de pago'});
        }
    }

    const showDownloadButton = ['', 'rejected', 'rejected_doc', 'wait_payment'].includes(usuario.status);
    const showUploadButton = ['rejected_doc', 'wait_payment'].includes(usuario.status);
    const showMessageReference = ['rejected', ''].includes(usuario.status);

    return (
        <>
            <div>
                <Loader show={isLoading}/>
                <div className="container">
                    <p><strong>Licenciatura en Escuelas de Enfermería del Instituto Mexicano del Seguro
                        Social: </strong>
                        <br/>
                        {usuario.solicitud.unidad.nombreEnfermeria}
                    </p>
                    <p><strong>Alumno: </strong>{usuario.nombre}</p>
                    <p><strong>CURP: </strong>{usuario.curp}</p>
                    <p><strong>Email: </strong>{usuario.email}</p>
                    <p><strong>Periodo: </strong>{usuario.solicitud.periodo}</p>
                    <p><strong>Fecha inicio: </strong>{usuario.solicitud.fechaInicioFormatted}</p>
                    <p><strong>Fecha fin: </strong>{usuario.solicitud.fechaFinFormatted}</p>
                    <p><strong>Estado del proceso: </strong>{usuarioState.statusFormatted}</p>
                    {['rejected', 'rejected_doc'].includes(usuarioState.status) &&
                        <p><strong>Motivo del rechazo: </strong>{usuarioState.lastPago.observaciones}</p>}
                    {usuarioState.lastPago && usuarioState.lastPago.factura && <p><strong><a
                        href={getSchemeAndHttpHost(`/enfermeria-alumno/${usuarioState.lastPago.factura.id}/factura`)}
                        target={'_blank'}>Descargar Factura</a></strong></p>}
                    {showMessageReference && <p className="text-danger">Para continuar con el proceso, descarga la referencia de pago</p>}


                    <div className="actions mt-30">
                        {showDownloadButton && <button
                            onClick={openModal}
                            className={'btn btn-block btn-primary'}>Descargar referencia de pago</button>}
                        {showUploadButton &&
                            <a href={getSchemeAndHttpHost("/enfermeria-alumno/carga-comprobante")} className={'btn btn-block btn-default'}>Cargar
                                comprobante de pago</a>}
                    </div>
                </div>
            </div>
            {showModal && <Popup show={showModal}
                                 closePopup={() => {
                                     setShowModal(false);
									 if(usuarioState.status === '') {
										 window.location.reload(); // Reload the page to update the status
									 }
                                 }}
                                 size={'large'} body={<>
                <embed src={getSchemeAndHttpHost() + '/enfermeria-alumno/referencia'}
                       type="application/pdf"/>
            </>}
            />}
        </>
    )
}


document.addEventListener('DOMContentLoaded', () => {
    ReactDOM.render(
        <EnfermeriaAlumnoIndex
            usuario={window.Usuario}
        />,
        document.getElementById('wrapper-page')
    )
})