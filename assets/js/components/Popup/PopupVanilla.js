export default class PopupVanilla {
	constructor(popupSelector) {
		this._popup = document.querySelector(popupSelector);
		this._setEventListeners();
		this._handleEscEventAux = this._handleEscEvent.bind(this);
	}

	_setEventListeners() {
		this._popup.querySelector('.popup__close').addEventListener('click', () => {
			this.close();
		})
	}

	close() {
		this._popup.classList.remove('popup_show');
		document.removeEventListener('keydown', this._handleEscEventAux);
	}

	open() {
		this._popup.classList.add('popup_show');
		document.addEventListener('keydown', this._handleEscEventAux);
	}

	setText(text){
		this._popup.querySelector('.popup__text').textContent = text;
	}

	_handleEscEvent(evt){
		if(evt.key === 'Escape') {
			this.close();
		}
	}
}