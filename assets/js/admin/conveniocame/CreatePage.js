import React from 'react';
import { createRoot } from "react-dom/client";
import ConvenioForm from "../convenioadmin/components/ConvenioForm";
import useFormConvenio from "../convenioadmin/hooks/useFormConvenio";
import Loader from "../../components/Loader/Loader";

function CreatePage({maxDateAllowed, usuario}) {

	const [isLoading, setIsLoading] = React.useState(true)
	const {
		handleSubmit, formErrors,
		setFormErrors, formRef, handleSearchByRfc,
		handleChangeTipoConvenio, showConveniosGenerales,
		handleChandeInstitucion, handleChangeNivelAcademico,
		motivosColaboracion, gradosAcademicos,
		hasConvenios, ooads, instituciones,
		disciplinas, carreras, handleChangeVigenciaText, cargos,
		canFormSubmit, setShowConveniosGenerales, conveniosGenerales, handleChangeCartaIntencion,
		showCOMAEM,setSelectedOoads, selectedOoads,
		selectedCiclosAcademicos, setSelectedCiclosAcademicos, setShowCOMAEM,
		handleChangeDisciplina, handleChangeCarrera, handleChangeTodasOOADs, showSelectOOADs,
		handleChangeTodosCiclosAcademicos, showSelectCiclosAcademicos, handleChangeMultiSelect,
		selectedTipo
	} = useFormConvenio({setIsLoading, isCame: true, usuario});

	return (
		<>
			<Loader show={isLoading}/>
			<ConvenioForm
				useMaxDateAllowed={true}
				isCame={true}
				usuario={usuario}
				maxDateAllowed={maxDateAllowed}
				formRef={formRef}
				formErrors={formErrors}
				setFormErrors={setFormErrors}
				handleSubmit={handleSubmit}
				hasConvenios={hasConvenios}
				instituciones={instituciones}
				ooads={ooads}
				motivosColaboracion={motivosColaboracion}
				gradosAcademicos={gradosAcademicos}
				handleChangeTipoConvenio={handleChangeTipoConvenio}
				handleChangeNivelAcademico={handleChangeNivelAcademico}
				handleChandeInstitucion={handleChandeInstitucion}
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
				handleChangeDisciplina={handleChangeDisciplina}
				handleChangeCarrera={handleChangeCarrera}
				showSelectOOADs={showSelectOOADs}
				handleChangeTodasOOADs={handleChangeTodasOOADs}
				handleChangeMultiSelect={handleChangeMultiSelect}
				handleChangeTodosCiclosAcademicos={handleChangeTodosCiclosAcademicos}
				showSelectCiclosAcademicos={showSelectCiclosAcademicos}
				handleSearchByRfc={handleSearchByRfc}
				tipo={selectedTipo}
			/>
		</>
	)
}

document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.querySelector('.react')).render(
		<CreatePage
			usuario={window.Usuario}
			maxDateAllowed={window.MAX_DATE_ALLOWED}/>,
	)});