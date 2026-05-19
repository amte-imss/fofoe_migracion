import React from "react";

export default function InputAutoComplete({children,
																						data,
																						onSetValue,
																						classlist,
																						id, name, attributes= {},
																						config = {name: 'name'}}) {

	const [selected, setSelected] = React.useState(null)
	const inputRef = React.useRef();

	const handleFocusOut = () => {
		if(!selected){
			inputRef.current.value = '';
		}
	}

	const handleInput = (event) =>  {
		const value = event.target.value;
		const found = data.find(item => {
			return item[config.value].toString() === value.toString()
		})
		if(found){
			inputRef.current.value = found[config.name];
			setSelected(found);
			if(onSetValue){
				onSetValue(found)
			}
		}else{
			setSelected(null);
		}
	}

	return (
		<>
			<input type="text"
						 id={id}
						 ref={inputRef}
						 onBlur={handleFocusOut}
						 onInput={handleInput}
						 name={name}
						 placeholder={attributes.placeholder ?? null}
						 required={attributes.required ?? false}
						 pattern={attributes.pattern ?? null}
						 readOnly={attributes.readOnly ?? false}
						 className={classlist} list={`classlist_${id}`}/>
			{children}
			<datalist id={`classlist_${id}`}>
				{data.map((item, index) => {
					return <option
						key={`datalist_item_${index}`}
						value={item[config.value]}>
						{item[config.name]}
					</option>
				})}
			</datalist>
		</>
	)
}