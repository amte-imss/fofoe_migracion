import { createRoot } from "react-dom/client";
import * as React from 'react'
import DatosSolicitudCC from "./components/DatosSolicitudCC";
import HistorialPagos from "./components/HistorialPagos";
import ExpedienteSolicitudCC from "./components/ExpedienteSolicitudCC";
import HistorialFacturas from "./components/HistorialFacturas";
import DatosResidenciaPosgrado from "./components/DatosResidenciaPosgrado";
import Popup from "../../components/Popup/Popup";
import Swal from "sweetalert2";
import Loader from "../../components/Loader/Loader";
import {FetchError, getSchemeAndHttpHost} from "../../utils";

const DetalleReferenciaCC = ({pagos, solicitud, campos}) => {

    function getFacturas() {
        let facturas = pagos.map((pago) => pago.factura);
        return facturas.filter((factura, idx) =>
            factura &&
            facturas.findIndex(item =>
                item && item.folio === factura.folio) === idx
        );
    }

    function getLastPago() {
        return pagos.length > 0 ?
            pagos.reduce(
                (acc, pago) =>
                    pago.id > acc.id ? pago : acc, pagos[0]) : null;
    }

    return (
        <div className='row mt-20'>
            <DatosSolicitudCC
                solicitud={solicitud}
                campos={campos}
                pago={getLastPago()}
            />
            <HistorialPagos
                pagos={pagos}
            />

            <HistorialFacturas
                facturas={getFacturas()}
            />

            <ExpedienteSolicitudCC
                solicitud={solicitud}
                campos={campos}
                pago={getLastPago()}
            />
        </div>
    )

}

const FormFacturaPosgrado = ({onClose, onSubmit}) => {

    const [fileName, setFileName] = React.useState('');

    const handleChangeFile = (event) => {
        setFileName(event.target.files[0].name);
    }

    return (
        <>
            <form onSubmit={onSubmit}>
                <div className="form-group">
                    <label htmlFor="folioFactura">Folio de la factura</label>
                    <input type="text"
                           required
                           defaultValue={fileName}
                           readOnly={true}
                           name={'factura[folio]'}
                           className="form-control"
                           id="folioFactura" placeholder="Folio de la factura"/>
                </div>
                <div className="form-group">
                    <label htmlFor="fechaFactura">Fecha de la factura</label>
                    <input type="date"
                           className="form-control" id="fechaFactura"
                           required
                           max={new Date().toISOString().split('T')[0]}
                           name={'factura[fechaFacturacion]'}
                           placeholder="Fecha de la factura"/>
                </div>
                <div className="form-group">
                    <label htmlFor="archivoFactura">Archivo de la factura</label>
                    <input type="file"
                           accept=".zip"
                           required
                           onChange={handleChangeFile}
                           name={'factura[zipFile]'}
                           className="form-control-file" id="archivoFactura"/>
                    <span className='text-danger text-sm'>Solo se permiten archivos .zip</span>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <button type="submit" className="btn btn-primary btn-block">Enviar</button>
                    </div>
                    <div className="col-md-6">
                        <button type="button" className="btn btn-danger btn-block" onClick={onClose}>Cancelar</button>
                    </div>
                </div>

            </form>
        </>
    )
}


const DetalleReferenciaPosgrado = ({pagos, residencia}) => {

    const [isLoading, setIsLoading] = React.useState(false);
    const [showPopup, setShowPopup] = React.useState(false);

    const handleUploadFactura = (event) => {
        event.preventDefault();
        setIsLoading(true);
        const formData = new FormData(event.target);

        const pago = getLastPago();
        formData.append('factura[monto]', pago.monto);

        return fetch(`${getSchemeAndHttpHost()}/fofoe/pagos/posgrado/solicitud/${pago.id}/factura`, {
            method: 'post',
            body: formData
        }).then(response =>{
            if(response.ok){
                return response.json();
            }
            throw new FetchError(response.statusText, {
                status: false,
                message: response.statusText,
                data: response.json()
            });
        }).then(() => {
            Swal.fire(
                {
                    title: 'Factura guardada con éxito',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                }
            ).then(() => {
                window.location.href = '/';
            })
        }).finally(() => {
            setIsLoading(false);
        });
    }

    function getFacturas() {
        let facturas = pagos.map((pago) => pago.factura);
        return facturas.filter((factura, idx) =>
            factura &&
            facturas.findIndex(item =>
                item && item.folio === factura.folio) === idx
        );
    }

    function getLastPago() {
        return pagos.length > 0 ?
            pagos.reduce(
                (acc, pago) =>
                    pago.id > acc.id ? pago : acc, pagos[0]) : null;
    }

    return (
        <>
            <Loader show={isLoading}/>
            <Popup show={showPopup}
                   title={'Subir Factura'}
                   body={<FormFacturaPosgrado
                       onSubmit={handleUploadFactura}
                       onClose={() => setShowPopup(false)}
                   />}
                   footer={<></>}
                   closePopup={() => {
                       setShowPopup(false)
                   }}
            />
            <div className='row mt-20'>
                <DatosResidenciaPosgrado
                    onRegistrarFactura={() => {setShowPopup(true); console.log('Registrar Factura')}}
                    residencia={residencia}
                    pago={getLastPago()}
                />
                <HistorialPagos
                    pagos={pagos}
                />

                <HistorialFacturas
                    facturas={getFacturas()}
                />

            </div>
        </>
    )

}

document.addEventListener('DOMContentLoaded', () => {
    let component = null
    switch (window.CATEGORIA_PAGO) {
        case 'CC':
            component =
                <DetalleReferenciaCC
                    pagos={window.PAGOS}
                    solicitud={window.SOLICITUD}
                    campos={window.CAMPOS}
                />
            break;
        case 'POSGRADO':
            component =
                <DetalleReferenciaPosgrado
                    pagos={window.PAGOS}
                    residencia={window.RESIDENCIA}
                />
            break;
    }
    const rootElement = document.getElementById('detalle-referencia-component');
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(component);
    }
});
