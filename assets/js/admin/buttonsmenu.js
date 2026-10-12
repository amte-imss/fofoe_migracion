import React from "react";
import { createRoot } from "react-dom/client";
import ButtonsMenu from '../admin/convenioadmin/components/ButtonsMenu';


document.addEventListener('DOMContentLoaded', function() {
	createRoot(document.getElementById('searcher-buttons-conve')).render(
	  <ButtonsMenu />,
	)
  });