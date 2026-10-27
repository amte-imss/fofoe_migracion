import React from 'react';
import { createRoot } from "react-dom/client";
import ConvenioForm from "./components/ConvenioForm";
import useFormConvenio from "./hooks/useFormConvenio";
import Loader from "../../components/Loader/Loader";

function EditPage({convenio, maxDateAllowed, usuario, motivosColaboracion, nivelesAcademicos, poblaciones, recursosInitial}) {

	const [isLoading, setIsLoading] = React.useState(true)
	const {
		handleSubmit, formErrors,
		setFormErrors, formRef, handleSearchByRfc,
		handleChangeTipoConvenio, showConveniosGenerales,
		handleChandeInstitucion, handleChangeNivelAcademico,
		hasConvenios, ooads, instituciones,
		disciplinas, carreras, handleChangeVigenciaText, cargos,
		canFormSubmit, setShowConveniosGenerales, conveniosGenerales, handleChangeCartaIntencion,
		showCOMAEM, selectedOoads, setSelectedOoads,
		selectedCiclosAcademicos, setSelectedCiclosAcademicos, handleChangeTodasOOADs,
		showSelectOOADs, handleChangeTodosCiclosAcademicos, showSelectCiclosAcademicos,
		handleChangeMultiSelect, handleChangeCarrera, handleChangeDisciplina, selectedTipo,
		recursos, handleChangeMotivoColaboracion, handleChangeRecurso
	} = useFormConvenio({setIsLoading, convenio, recursosInitial});

	return (
		<>
			<Loader show={isLoading}/>
			<ConvenioForm
				tipo={selectedTipo}
				maxDateAllowed={maxDateAllowed}
				recursos={recursos}
				handleChangeRecurso={handleChangeRecurso}
				handleChangeMotivoColaboracion={handleChangeMotivoColaboracion}
				formRef={formRef}
				handleChangeTodosCiclosAcademicos={handleChangeTodosCiclosAcademicos}
				showSelectCiclosAcademicos={showSelectCiclosAcademicos}
				formErrors={formErrors}
				setFormErrors={setFormErrors}
				handleSubmit={handleSubmit}
				hasConvenios={hasConvenios}
				instituciones={instituciones}
				ooads={ooads}
				poblaciones={poblaciones}
				motivosColaboracion={motivosColaboracion}
				gradosAcademicos={nivelesAcademicos}
				handleChangeTipoConvenio={handleChangeTipoConvenio}
				handleChangeNivelAcademico={handleChangeNivelAcademico}
				handleChandeInstitucion={handleChandeInstitucion}
				handleChangeCarrera={handleChangeCarrera}
				handleChangeDisciplina={handleChangeDisciplina}
				showConveniosGenerales={showConveniosGenerales}
				handleChangeVigenciaText={handleChangeVigenciaText}
				setShowConveniosGenerales={setShowConveniosGenerales}
				handleChangeCartaIntencion={handleChangeCartaIntencion}
				conveniosGenerales={conveniosGenerales}
				disciplinas={disciplinas}
				carreras={carreras}
				cargos={cargos}
				canFormSubmit={canFormSubmit}
				showCOMAEM={showCOMAEM}
				selectedOoads={selectedOoads}
				setSelectedOoads={setSelectedOoads}
				setSelectedCiclosAcademicos={setSelectedCiclosAcademicos}
				selectedCiclosAcademicos={selectedCiclosAcademicos}
				handleChangeTodasOOADs={handleChangeTodasOOADs}
				showSelectOOADs={showSelectOOADs}
				handleChangeMultiSelect={handleChangeMultiSelect}
				handleSearchByRfc={handleSearchByRfc}/>
		</>
	)
}

document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.querySelector('.react')).render(
		<EditPage convenio={window.Convenio}
				  usuario={window.Usuario}
				  motivosColaboracion={window.MotivosColaboracion}
				  nivelesAcademicos={window.NivelesAcademicos}
				  poblaciones={window.Poblaciones}
				  recursosInitial={window.Recursos}
				  maxDateAllowed={window.MAX_DATE_ALLOWED}/>,
	)});