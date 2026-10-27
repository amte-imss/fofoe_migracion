import React from "react";
import { createRoot } from "react-dom/client";
import ButtonsCamexMenu from '../admin/convenioadmin/components/ButtonsCamexMenu';


document.addEventListener('DOMContentLoaded', function () {
	const node = document.getElementById('searcher-buttons');
	if (node) {
		createRoot(node).render(
			<ButtonsCamexMenu/>,
		);
	}
});
