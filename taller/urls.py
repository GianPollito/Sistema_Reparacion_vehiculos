from django.urls import path
from . import views

urlpatterns = [
    path('', views.dashboard_view, name='dashboard'),
    path('dashboard/', views.dashboard_view, name='dashboard_alias'),

    path('login/', views.login_view, name='login'),
    path('register/', views.register_view, name='register'),
    path('logout/', views.logout_view, name='logout'),

    path('clientes/', views.cliente_list, name='cliente_list'),
    path('clientes/crear/', views.cliente_create, name='cliente_create'),
    path('clientes/<int:pk>/', views.cliente_detail, name='cliente_detail'),
    path('clientes/<int:pk>/editar/', views.cliente_update, name='cliente_update'),
    path('clientes/<int:pk>/eliminar/', views.cliente_delete, name='cliente_delete'),

    path('vehiculos/', views.vehiculo_list, name='vehiculo_list'),
    path('vehiculos/crear/', views.vehiculo_create, name='vehiculo_create'),
    path('vehiculos/<int:pk>/', views.vehiculo_detail, name='vehiculo_detail'),
    path('vehiculos/<int:pk>/editar/', views.vehiculo_update, name='vehiculo_update'),
    path('vehiculos/<int:pk>/eliminar/', views.vehiculo_delete, name='vehiculo_delete'),

    path('mecanicos/', views.mecanico_list, name='mecanico_list'),
    path('mecanicos/crear/', views.mecanico_create, name='mecanico_create'),
    path('mecanicos/<int:pk>/editar/', views.mecanico_update, name='mecanico_update'),
    path('mecanicos/<int:pk>/eliminar/', views.mecanico_delete, name='mecanico_delete'),

    path('citas/', views.cita_list, name='cita_list'),
    path('citas/crear/', views.cita_create, name='cita_create'),
    path('citas/<int:pk>/editar/', views.cita_update, name='cita_update'),
    path('citas/<int:pk>/estado/<str:estado>/', views.cita_cambiar_estado, name='cita_cambiar_estado'),
    path('citas/<int:pk>/eliminar/', views.cita_delete, name='cita_delete'),

    path('ordenes/', views.orden_list, name='orden_list'),
    path('ordenes/crear/', views.orden_create, name='orden_create'),
    path('ordenes/<int:pk>/', views.orden_detail, name='orden_detail'),
    path('ordenes/<int:pk>/editar/', views.orden_update, name='orden_update'),
    path('ordenes/<int:pk>/imprimir/', views.orden_imprimir, name='orden_imprimir'),
    path('ordenes/<int:pk>/eliminar/', views.orden_delete, name='orden_delete'),

    path('diagnosticos/', views.diagnostico_list, name='diagnostico_list'),
    path('diagnosticos/crear/', views.diagnostico_create, name='diagnostico_create'),
    path('diagnosticos/<int:pk>/editar/', views.diagnostico_update, name='diagnostico_update'),
    path('diagnosticos/<int:pk>/eliminar/', views.diagnostico_delete, name='diagnostico_delete'),

    path('servicios/', views.servicio_list, name='servicio_list'),
    path('servicios/crear/', views.servicio_create, name='servicio_create'),
    path('servicios/<int:pk>/editar/', views.servicio_update, name='servicio_update'),
    path('servicios/<int:pk>/eliminar/', views.servicio_delete, name='servicio_delete'),

    path('repuestos/', views.repuesto_list, name='repuesto_list'),
    path('repuestos/crear/', views.repuesto_create, name='repuesto_create'),
    path('repuestos/<int:pk>/editar/', views.repuesto_update, name='repuesto_update'),
    path('repuestos/<int:pk>/eliminar/', views.repuesto_delete, name='repuesto_delete'),

    path('pagos/', views.pago_list, name='pago_list'),
    path('pagos/crear/', views.pago_create, name='pago_create'),
    path('pagos/<int:pk>/', views.pago_detail, name='pago_detail'),
    path('pagos/<int:pk>/imprimir/', views.pago_imprimir, name='pago_imprimir'),
    path('pagos/<int:pk>/eliminar/', views.pago_delete, name='pago_delete'),

    path('reportes/', views.reportes_index, name='reportes_index'),
    path('reportes/servicios/', views.reporte_servicios, name='reporte_servicios'),
    path('reportes/vehiculos/', views.reporte_vehiculos, name='reporte_vehiculos'),
    path('reportes/reparaciones/', views.reporte_reparaciones, name='reporte_reparaciones'),
    path('reportes/ingresos/', views.reporte_ingresos, name='reporte_ingresos'),
    path('reportes/exportar-csv/', views.reportes_export_csv, name='reportes_export_csv'),
]
