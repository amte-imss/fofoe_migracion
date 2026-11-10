import React from "react";
import { createRoot } from "react-dom/client";
import Loader from "../../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../../utils";
import Popup from "../../../components/Popup/Popup";

function IndexPage({usuario}){
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
        if(usuario.status == ''){
            setUsuarioState({...usuario, statusFormatted: 'En espera de pago'});
        }
    }

    const showDownloadButton = ['', 'rejected', 'wait_payment'].includes(usuario.status)
    const showUploadButton = ['', 'rejected', 'wait_payment'].includes(usuario.status)

    return (
        <>
            <div>
                <Loader show={isLoading} />
                <div className="container">
                    <p><strong>Educación Permanente del Instituto Mexicano del Seguro Social</strong>
                    </p>
                    <p><strong>Tipo de Solicitud: </strong>{usuario.tipoSolicitud.name}</p>
                    <p><strong>Modalidad / Categoría: </strong>{usuario.duracion.modalidad.name}</p>
                    <p><strong>Duración / Unidad de Medida </strong>{usuario.duracion.name}</p>
                    <p><strong>Cantidad de horas / Participantes </strong>{usuario.quantity}</p>
                    <p><strong>Aplica descuento de 50% </strong>{usuario.hasDiscount ? 'SI' : 'NO'}</p>
                    <p><strong>Curso solicitado </strong>{usuario.curso.name}</p>
                    <p><strong>Participante: </strong>{usuario.participant}</p>
                    <p><strong>Email: </strong>{usuario.email}</p>
                    <p><strong>Estado del proceso: </strong>{usuarioState.statusFormatted}</p>
                    {usuarioState.status === 'rejected' && <p><strong>Motivo del rechazo: </strong>{usuarioState.lastPago.observaciones}</p>}
                    {usuarioState.lastPago && usuarioState.lastPago.factura  && <p><strong><a href={getSchemeAndHttpHost(`/edu-per/participant/factura/${usuarioState.lastPago.factura.id}`)} target={'_blank'}>Descargar Factura</a></strong></p>}

                    <div className="actions mt-30">
                        {showDownloadButton && <button
                            onClick={openModal}
                            className={'btn btn-block btn-primary'}>Descargar referencia de pago</button>}
                        {showUploadButton &&<a href="/edu-per/participant/carga-comprobante" className={'btn btn-block btn-default'}>Cargar comprobante de pago</a> }
                    </div>
                </div>
            </div>
            {showModal && <Popup show={showModal}
                                 closePopup={() => {setShowModal(false)}}
                                 size={'large'} body={<>
                <embed src={getSchemeAndHttpHost() + '/edu-per/participant/referencia'}
                       type="application/pdf" />
            </>}
            />}
        </>
    )
}


document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.getElementById('wrapper-page')).render(
        <IndexPage
            usuario={window.Solicitud}
        />)
})