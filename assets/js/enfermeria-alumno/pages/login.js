import {getSchemeAndHttpHost} from "../../utils";
import FormValidator from "../../components/FormValidator/FormValidatorVanilla";
import "../../components/FormValidator/FormValidator.css";

const captchaBefore = document.querySelector('.captcha-before')

function reload_captcha() {
	document.querySelector('#captcha ~ a').removeEventListener('click', reload_captcha);
	document.querySelector('.captcha-container').remove();
	setCaptcha();
}

function setCaptcha() {
	fetch(getSchemeAndHttpHost() + '/generarCaptcha')
		.then(response => response.text())
		.then(content => {
			captchaBefore.insertAdjacentHTML('afterend', content)
			document.querySelector('#captcha ~ a').href = "#";
			document.querySelector('#captcha ~ a').addEventListener('click', reload_captcha)
			document.querySelector('#_captcha').name = 'alumno_login[captcha]';
		});
}

setCaptcha();

const formElement = document.querySelector('.form');

const formValidator = new FormValidator(formElement);
formValidator.enableValidation(true, false);

document.querySelector('#form-login-enf').action = getSchemeAndHttpHost(`/enfermeria-alumno/login/${window.SolicitudId}`);