import {getLastPago} from "../utils";

export const getMontoTotalResidencia = (residencia) => {
  const lastPago = getLastPago(residencia.pagos)

  if (lastPago.validado === true) {
    return [getMontoTotalPagos(residencia.pagos), 'MXN']
  }

  return [residencia.monto, residencia.tipoMoneda]
}

const getMontoTotalPagos = (pagos) => {
  let total = 0
  pagos.forEach(pago => total += parseFloat(pago.monto) )
  return total
}

export const getGradosAcademicos = (duracion) => {
  const grados = ['Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto', 'Sexto', 'Septimo', 'Octavo', 'Noveno', 'Decimo']
  return grados.slice(0, duracion);
}