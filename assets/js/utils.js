import Swal from "sweetalert2";

export const getSchemeAndHttpHost = (uri = '') => {
    return window.SCHEMA_AND_HTTP_HOST + uri;
}

export const moneyFormat = (monto) => {
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    });
    return formatter.format(monto);
}

export const dateFormat = (date) => {
    const options = {year: 'numeric', month: '2-digit', day: '2-digit'};
    return new Date(date).toLocaleDateString('es-MX', options);
}

export const transformSymfonyDate = (dateString, nameField) => {
    const date = new Date(dateString + ' 00:00:00');
    const result = {};
    result[`${nameField}[day]`]   = date.getDate();
    result[`${nameField}[month]`] = date.getMonth() + 1;
    result[`${nameField}[year]`]  = date.getFullYear();
    return result;
}

export function objectToFormData(obj) {
    const formData = new FormData();
    Object.entries(obj).forEach(([key, value]) => {
        formData.append(key, value);
    });
    return formData;
}

export class FetchError extends Error {
    constructor(message, data) {
        super(message);
        this.data = data;
    }
}

export const transformErrors = (errors, prefix = '') => {
    const newErrors = {};
    for (const [key, value] of Object.entries(errors)) {
        let newValue = null;
        if (Array.isArray(value)) {
            newValue = value[0];
        } else if (typeof value === 'string') {
            newValue = value;
        }
        if (prefix) {
            newErrors[`${prefix}[${key}]`] = newValue;
        } else {
            newErrors[`${key}`] = newValue;
        }
    }
    return newErrors;
}

export function showErrors(errors) {
    const errorsArray = [];
    for (const i in errors) {
        errors[i].forEach(item => {
            if (!errorsArray.includes(item)) {
                errorsArray.push(`${i}: ${item}`);
            }
        });
    }
    const content = errorsArray.join("<br>");
    Swal.fire(
        'Se presento un error al tratar de realizar la petición',
        content,
        'warning',
    );
}

export const sleep = (milliseconds) => {
    return new Promise((resolve) => {
        setTimeout(() => { resolve(); }, milliseconds);
    });
}

export const getFormErrors = (form) => {
    const result = { status: true, errors: {} };
    Array.from(form.elements).forEach(element => {
        if (element.name && !element.validity.valid) {
            result.status = false;
            result.errors[element.name] = element.validationMessage;
        }
    });
    return result;
}

export const checkInputFile = (input, validations = {}, callback) => {
    const messages = [];
    let file = input instanceof File ? input : input.files[0];
    if (validations.size && file.size > validations.size) {
        const mb = validations.size / 1024 / 1024;
        messages.push(`El archivo no debe ser mayor a ${mb} MB`);
    }
    if (messages.length > 0) {
        Swal.fire({ icon: 'warning', title: 'Archivo no válido', html: messages.join('<br>') });
    } else if (callback) {
        callback();
    }
}
