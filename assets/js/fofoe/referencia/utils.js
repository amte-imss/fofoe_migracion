export const isPagoCampoClinico = (pago) => {
  return 'institucion_id' in pago
}

export const isPagoPosgrado = (pago) => {
  return 'residencia_id' in pago || 'residencia' in pago
}