export default class FormValidator {
	constructor(formElement, formConfig = DefaultFormConfig) {
		this._formElement = formElement;
		this._formConfig = formConfig;
	}

	_showInputError(formElement, inputElement, errorMessage) {
		const errorElement = formElement.querySelector(`.${inputElement.id}-error`);
		inputElement.classList.add(this._formConfig.inputErrorClass);
		errorElement.textContent = errorMessage;
		errorElement.classList.add(this._formConfig.errorClass);
	};

	_hideInputError(formElement, inputElement) {
		const errorElement = formElement.querySelector(`.${inputElement.id}-error`);
		inputElement.classList.remove(this._formConfig.inputErrorClass);
		errorElement.classList.remove(this._formConfig.errorClass);
		errorElement.textContent = "";
	};

	_checkInputValidity(formElement, inputElement) {
		if (!inputElement.validity.valid) {
			this._showInputError(formElement, inputElement, inputElement.validationMessage);
		} else {
			this._hideInputError(formElement, inputElement);
		}
	};

	_hasInvalidInput(inputList) {
		return inputList.some((inputElement) => {
			return !inputElement.validity.valid;
		});
	};

	_toggleButtonState(buttonElement) {
		const inputList = Array.from(this._formElement.querySelectorAll(this._formConfig.inputSelector));
		if (this._hasInvalidInput(inputList)) {
			buttonElement.classList.add(this._formConfig.inactiveButtonClass);
			buttonElement.disabled = true;
		} else {
			buttonElement.classList.remove(this._formConfig.inactiveButtonClass);
			buttonElement.disabled = false;
		}
	};

	_setEventListeners(initSubmitState) {
		const formElement = this._formElement;

		if(initSubmitState){
			const submitButtonElement = this._formElement.querySelector(this._formConfig.submitButtonSelector);
			const inputList = Array.from(this._formElement.querySelectorAll(this._formConfig.inputSelector));
			this._toggleButtonState(submitButtonElement);
		}

		formElement.addEventListener('input', (evt) => {
			if(evt.target.classList.contains(this._formConfig.inputClass)){
				const inputElement = evt.target;
				this._checkInputValidity(formElement, inputElement);
				const submitButtonElement = this._formElement.querySelector(this._formConfig.submitButtonSelector);
				this._toggleButtonState(submitButtonElement);
			}
		})
	};

	enableValidation(initSubmitState = true, preventDefault = true) {

		if(preventDefault){
			this._formElement.addEventListener("submit", function (evt) {
				evt.preventDefault();
			});
		}
		this._setEventListeners(initSubmitState);

	};
}

const DefaultFormConfig = {
	formSelector: ".form",
	inputSelector: ".form__input",
	inputClass: "form__input",
	submitButtonSelector: ".form__submit",
	inactiveButtonClass: "form__submit_inactive",
	inputErrorClass: "form__input_type_error",
	errorClass: "form__error_visible"
}