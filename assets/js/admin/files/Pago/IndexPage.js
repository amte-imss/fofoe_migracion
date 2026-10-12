import * as React from 'react'
import { createRoot } from "react-dom/client";
import Loader from "../../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../../utils";
import Popup from "../../../components/Popup/Popup";
import './IndexPage.css';
import {moneyFormat} from "../../../utils";

export default function IndexPage() {

    const [pago, setPago] = React.useState({});
    const [loading, setLoading] = React.useState(false);
    const [showComprobante, setShowComprobante] = React.useState(false);

    const handleSubmit = (event) => {
        event.preventDefault();
        setLoading(true);
        setPago({})
        fetch(getSchemeAndHttpHost(`/admin/files/pagos/${event.target.id.value}`))
            .then(response => response.json()).then(({data}) => {
            setPago(data);
        }).catch(error => {
            console.error('Error fetching pago:', error);
        }).finally(() => {
            setLoading(false);
        });
    }

    const handleShowComprobante = () => {
        setShowComprobante(true)
    }

    const handleUploadComprobante = (event) => {
        event.preventDefault();
        setLoading(true)
        fetch(getSchemeAndHttpHost(`/admin/files/pagos/${pago.id}/comprobante-upload`),{
            method: 'POST',
            body: new FormData(event.target)
        }).then(response => response.json()).then(result => {
            if(!result.status){
                alert('Error al subir el comprobante: ' + result.message);
            }else{
                alert('Comprobante reemplazado exitosamente');
            }
        }).finally(() => {
            setLoading(false);
        })
    }

    return (
        <>
            <Loader show={loading}/>
            <form className={'form'} onSubmit={handleSubmit}>
                <fieldset>
                    <label>
                        ID:
                        <input type="text" name="id"/>
                    </label>
                </fieldset>
                <fieldset>
                    <input type="submit" value={'Buscar'}/>
                </fieldset>
            </form>

            {!!pago.id &&
                <div>
                    <table>
                        <tbody>
                        <tr>
                            <td>Id</td>
                            <td>{pago.id}</td>
                        </tr>
                        <tr>
                            <td>Tipo</td>
                            <td>{pago.type}</td>
                        </tr>
                        <tr>
                            <td>Monto</td>
                            <td>{moneyFormat(pago.monto)}</td>
                        </tr>
                        <tr>
                            <td>Comprobante</td>
                            <td>
                                <a className={'btn'} target="_blank" onClick={handleShowComprobante}>Ver Comprobante</a>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <form
                                    onSubmit={handleUploadComprobante}
                                    encType="multipart/form-data">
                                    <input type="file" name="comprobante" accept="application/pdf"/>
                                    <button type="submit">Reemplazar comprobante</button>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            }

            <Popup title={'Comprobante de pago'}
                   body={<>
                       <iframe src={getSchemeAndHttpHost(`/admin/files/pagos/${pago.id}/comprobante`)} width="100%"
                               height="500px" title="Comprobante de pago"></iframe>
                   </>}
                   closePopup={() => setShowComprobante(false)}
                   show={showComprobante}
            />
        </>
    )
}


document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.querySelector('.react')).render(
        <IndexPage/>,
    )});