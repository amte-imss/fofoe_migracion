import React from "react";
import { createRoot } from "react-dom/client";
import ButtonsCoordinaciones from '../admin/convenioadmin/components/ButtonsDisciplinas';


document.addEventListener('DOMContentLoaded', function() {
	createRoot(document.getElementById('searcher-buttons-disciplina')).render(
	  <ButtonsDisciplinas />,
	)
  });