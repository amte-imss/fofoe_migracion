import "../../components/Popup/Popup.css";
import PopupVanilla from "../../components/Popup/PopupVanilla";
import "../../components/Loader/styles.scss";

const popup = new PopupVanilla('.popup');
const popupSuccess = new PopupVanilla('.popup_success');
const cancelButton = document.querySelector('.popup .btn_cancel');
const successButton = document.querySelector('.btn_success');
const successButton1 = document.querySelector('.btn_success1');
const actionButton = document.querySelector('.btn_action_1');
const loader = document.querySelector('.loading');

loader.style.display = 'none';

successButton1.addEventListener('click', (e) => {
    popupSuccess.close();
})


successButton.addEventListener('click', (e) => {
    //console.log('vamos por chimichnagas');
    //console.log(window.SCHEMA_AND_HTTP_HOST);
    loader.style.display = 'initial';

    fetch(`${window.SCHEMA_AND_HTTP_HOST}/admin/enfermeria/alumno/comprobante`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json()).then(data => {
        //console.log(data);
        popup.close();
        popupSuccess.open();
        popupSuccess.setText('Tarea realizada con éxito')
    }).catch(error => {
        console.error(error);
    }).finally(() => {
        loader.style.display = 'none';
    })
})

actionButton.addEventListener('click', (e) => {
    popup.open();
})

cancelButton.addEventListener('click', (e) => {
    popup.close();
})

successButton1.addEventListener('click', (e) => {
    popupSuccess.close();
})