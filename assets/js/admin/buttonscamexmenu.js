import React from "react";
import ReactDOM from "react-dom";
import ButtonsCamexMenu from '../admin/convenioadmin/components/ButtonsCamexMenu';


document.addEventListener('DOMContentLoaded', function () {
	const node = document.getElementById('searcher-buttons');
	if (node) {
		ReactDOM.render(
			<ButtonsCamexMenu/>,
			node
		);
	}
});