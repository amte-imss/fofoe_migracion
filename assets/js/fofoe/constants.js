export const FOFOE_TIPO_PAGOS = {
  'CC': {
    'pendientes_val': 'a',
    'no_validos': 'd',
    'pendientes_facturacion': 'c',
    'validados_facturados': 'b',
    'sufijo_url': 'camposClinicos',
    'sufijo_api': ''
  },
  'POSGRADO': {
    'pendientes_val': 'pendiente_val',
    'no_validos': 'no_valido',
    'pendientes_facturacion': 'pendiente_factura',
    'validados_facturados': 'validado_facturado',
    'sufijo_url': 'posgrado/imss',
    'sufijo_api': '/posgrado/imss'
  },
  'POSGRADO_RP': {
    'pendientes_val': 'pendiente_val',
    'no_validos': 'no_valido',
    'pendientes_facturacion': 'pendiente_factura',
    'validados_facturados': 'validado_facturado',
    'sufijo_url': 'posgrado/noimss',
    'sufijo_api': '/posgrado/noimss'
  },
  'PERMANENTE_PRESENCIAL': {
    'pendientes_val': 'wait_validation',
    'no_validos': 'rejected',
    'pendientes_facturacion': 'invoice_pending',
    'validados_facturados': 'validated',
    'sufijo_url': 'edu-per/solicitud/modalidad-presencial',
    'sufijo_api': '/edu-per/solicitud/modalidad-presencial'
  },
  'PERMANENTE_DISTANCIA': {
    'pendientes_val': 'wait_validation',
    'no_validos': 'rejected',
    'pendientes_facturacion': 'invoice_pending',
    'validados_facturados': 'validated',
    'sufijo_url': 'edu-per/solicitud/modalidad-distancia',
    'sufijo_api': '/edu-per/solicitud/modalidad-distancia'
  },
  'PERMANENTE_SIMULACION': {
    'pendientes_val': 'wait_validation',
    'no_validos': 'rejected',
    'pendientes_facturacion': 'invoice_pending',
    'validados_facturados': 'validated',
    'sufijo_url': 'edu-per/solicitud/simulacion',
    'sufijo_api': '/edu-per/solicitud/simulacion'
  },
  'ESCUELA_ENFERMERIA': {
    'pendientes_val': 'wait_validation',
    'no_validos': 'rejected',
    'pendientes_facturacion': 'invoice_pending',
    'validados_facturados': 'validated',
    'sufijo_url': 'enfermeria/solicitud',
    'sufijo_api': '/enfermeria/solicitud'
  }
}

export const CATEGORIA_PAGO_ORDEN = {
  'CC': {
    'a': 'Fecha de pago: más reciente',
    'b': 'Fecha de pago: más antigua',
    'c': 'Número de solicitud: de mayor a menor',
    'd': 'Número de solicitud: de menor a mayor',
  },
  'POSGRADO': {
    'fecha_pago_desc': 'Fecha de pago: más reciente',
    'fecha_pago_asc': 'Fecha de pago: más antigua',
    'folio_desc': 'Folio: de mayor a menor',
    'folio_asc': 'Folio: de menor a mayor',
  },
  'POSGRADO_RP': {
    'fecha_pago_desc': 'Fecha de pago: más reciente',
    'fecha_pago_asc': 'Fecha de pago: más antigua',
    'folio_desc': 'Número de oficio: de mayor a menor',
    'folio_asc': 'Número de oficio: de menor a mayor',
  }
}

export const CATEGORIA_PAGO_HEADERS = {
  'CC': {
    'nombre': 'Institución Educativa',
    'id': 'No. de Solicitud',

  },
  'POSGRADO': {
    'nombre': 'Nombre',
    'id': 'Folio',
  },
  'POSGRADO_RP': {
    'nombre': 'Nombre',
    'id': 'No. de Oficio Aceptación',
  }
}