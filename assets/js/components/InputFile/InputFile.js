import React from "react";
import './InputFile.css';
export default function InputFile({text = 'Seleccionar Archivo', handleInputChange = ()=> {}}){

	const [textContent, setTextContent] = React.useState(text);

	const handleChange = (event) => {
		const t = event.target.value;
		const labelText = 'Archivo : ' + t.toString().substring(12, t.length);
		setTextContent(labelText);
		if(handleInputChange){
			handleInputChange(event);
		}
	}

	return (
		<>
			 <span className="control-fileupload">
          <label htmlFor="file">{textContent}</label>
          <input type="file" id="file" onChange={handleChange}/>
        </span>
		</>
	)
}