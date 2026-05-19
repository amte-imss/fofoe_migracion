import React from "react";

export const FormValidator = (props) => {

	const handleInput = (event) => {
		if (event.target.validity.valid) {
			props.errors[event.target.name] = '';
		} else {
			props.errors[event.target.name] = event.target.validationMessage;
		}
		props.setErrors(Object.assign({}, props.errors))
	}

	return (
		<>
			{React.cloneElement(props.children, {onInput: handleInput})}
		</>
	)
}