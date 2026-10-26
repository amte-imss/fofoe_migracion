import "../../components/Popup/Popup.css";
import PopupVanilla from "../../components/Popup/PopupVanilla";
import "../../components/Loader/styles.scss";

const popup = new PopupVanilla('.popup');
const popupNumeracion = new PopupVanilla('.popup_numeracion');
const popupSuccess = new PopupVanilla('.popup_success');
const cancelButton = document.querySelector('.popup .btn_cancel');
const cancelButton1 = document.querySelector('.popup_numeracion .btn_cancel');
const successButton = document.querySelector('.btn_success');
const successButton1 = document.querySelector('.btn_success1');
const successButtonNumeracion = document.querySelector('.popup_numeracion .btn_success');
const fuicaButton = document.querySelector('.btn_fuica');
const numeracionButton = document.querySelector('.btn_numeracion');
const loader = document.querySelector('.loading');

loader.style.display = 'none';

cancelButton.addEventListener('click', (e) => {
	popup.close();
})

cancelButton1.addEventListener('click', (e) => {
	popupNumeracion.close();
})

successButton1.addEventListener('click', (e) => {
	popupSuccess.close();
})

successButton.addEventListener('click', (e) => {
	//console.log('vamos por chimichnagas');
	//console.log(window.SCHEMA_AND_HTTP_HOST);
	loader.style.display = 'initial';

	fetch(`${window.SCHEMA_AND_HTTP_HOST}/api/admin/conveniosadmin/fix/fuica`, {
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

successButtonNumeracion.addEventListener('click', (e) => {
	//console.log('vamos por chimichnagas');
	//console.log(window.SCHEMA_AND_HTTP_HOST);
	loader.style.display = 'initial';

	fetch(`${window.SCHEMA_AND_HTTP_HOST}/api/admin/conveniosadmin/fix/numeracion`, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		}
	}).then(response => response.json()).then(data => {
		//console.log(data);
		popupNumeracion.close();
		popupSuccess.open();
		popupSuccess.setText('Tarea realizada con éxito')
	}).catch(error => {
		console.error(error);
	}).finally(() => {
		loader.style.display = 'none';
	})
})

fuicaButton.addEventListener('click', (e) => {
	popup.open();
})

numeracionButton.addEventListener('click', (e) => {
	popupNumeracion.open();
})
