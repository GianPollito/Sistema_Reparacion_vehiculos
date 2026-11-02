from django.contrib import admin
from .models import PerfilUsuario, Cliente, Vehiculo, Mecanico, Cita, Servicio, Repuesto, OrdenTrabajo, OrdenServicio, RepuestoUtilizado, Diagnostico, Pago

admin.site.site_header = "Motor Express - Panel Administrativo"
admin.site.site_title = "Motor Express"
admin.site.index_title = "Administración del Taller Automotriz"

admin.site.register(PerfilUsuario)
admin.site.register(Cliente)
admin.site.register(Vehiculo)
admin.site.register(Mecanico)
admin.site.register(Cita)
admin.site.register(Servicio)
admin.site.register(Repuesto)

class OrdenServicioInline(admin.TabularInline):
    model = OrdenServicio
    extra = 1

class RepuestoUtilizadoInline(admin.TabularInline):
    model = RepuestoUtilizado
    extra = 1

@admin.register(OrdenTrabajo)
class OrdenTrabajoAdmin(admin.ModelAdmin):
    list_display = ('numero_orden', 'vehiculo', 'cliente', 'mecanico', 'fecha_ingreso', 'estado', 'total_general')
    list_filter = ('estado', 'mecanico')
    search_fields = ('numero_orden', 'vehiculo__placa', 'cliente__nombres')
    inlines = [OrdenServicioInline, RepuestoUtilizadoInline]

admin.site.register(Diagnostico)
admin.site.register(Pago)
