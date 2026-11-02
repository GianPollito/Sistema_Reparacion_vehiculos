from django.db.models import F

def global_info(request):
    data = {
        'APP_NAME': 'Motor Express',
        'APP_TAGLINE': 'Enterprise Fleet & Workshop Suite',
        'APP_COMPANY': 'Motor Express Automotive Corp.',
        'APP_PHONE': '+51 (01) 458-9200',
        'APP_EMAIL': 'operaciones@motorexpress.pe',
        'APP_ADDRESS': 'Av. Industrial 1240, Complejo Automotriz',
        'APP_HOURS': 'Lun - Sáb: 08:00 - 18:30',
        'CURRENCY_SYMBOL': 'S/',
        'SIDEBAR_ORDENES_ACTIVAS': 0,
        'SIDEBAR_CITAS_PENDIENTES': 0,
        'SIDEBAR_REPUESTOS_BAJOS': 0,
    }
    try:
        from .models import OrdenTrabajo, Cita, Repuesto
        data['SIDEBAR_ORDENES_ACTIVAS'] = OrdenTrabajo.objects.exclude(estado__in=['Entregado', 'Cancelado']).count()
        data['SIDEBAR_CITAS_PENDIENTES'] = Cita.objects.filter(estado__in=['Pendiente', 'Confirmada']).count()
        data['SIDEBAR_REPUESTOS_BAJOS'] = Repuesto.objects.filter(stock__lte=F('stock_minimo')).count()
    except Exception:
        pass
    return data
