from django.core.management.base import BaseCommand
from django.contrib.auth.models import User
from taller.models import PerfilUsuario, Cliente, Vehiculo, Mecanico, Cita, Servicio, Repuesto, OrdenTrabajo, OrdenServicio, RepuestoUtilizado, Diagnostico, Pago
from datetime import date, datetime, timedelta
from decimal import Decimal

class Command(BaseCommand):
    help = 'Pobla la base de datos MariaDB con datos realistas para Motor Express'

    def handle(self, *args, **kwargs):
        self.stdout.write("Iniciando carga de datos en MariaDB...")

        if not User.objects.filter(username='admin').exists():
            u1 = User.objects.create_superuser('admin', 'admin@motorexpress.pe', 'admin123')
            u1.first_name = "Administrador"
            u1.last_name = "Principal"
            u1.save()
            PerfilUsuario.objects.update_or_create(user=u1, defaults={'rol': 'Administrador'})

        if not User.objects.filter(username='carlos').exists():
            u2 = User.objects.create_user('carlos', 'carlos@motorexpress.pe', 'carlos123')
            u2.first_name = "Carlos"
            u2.last_name = "Mendoza"
            u2.save()
            PerfilUsuario.objects.update_or_create(user=u2, defaults={'rol': 'Mecanico'})
        else:
            u2 = User.objects.get(username='carlos')
            u2.set_password('carlos123')
            u2.save()

        if not User.objects.filter(username='valeria').exists():
            u3 = User.objects.create_user('valeria', 'valeria@motorexpress.pe', 'valeria123')
            u3.first_name = "Valeria"
            u3.last_name = "Ríos"
            u3.save()
            PerfilUsuario.objects.update_or_create(user=u3, defaults={'rol': 'Recepcionista'})
        else:
            u3 = User.objects.get(username='valeria')
            u3.set_password('valeria123')
            u3.save()

        clientes_data = [
            {'dni_ruc': '47895214', 'nombres': 'Juan Carlos', 'apellidos': 'Pérez Quispe', 'telefono': '987112233', 'email': 'juan.perez@gmail.com', 'direccion': 'Av. Los Próceres 450, Santiago de Surco'},
            {'dni_ruc': '10458796', 'nombres': 'María Elena', 'apellidos': 'Gómez Salcedo', 'telefono': '974556677', 'email': 'maria.gomez@outlook.com', 'direccion': 'Calle Las Palmeras 124, San Borja'},
            {'dni_ruc': '71236548', 'nombres': 'Roberto', 'apellidos': 'López Morales', 'telefono': '998443322', 'email': 'roberto.lopez@yahoo.com', 'direccion': 'Jr. Junín 890, Miraflores'},
            {'dni_ruc': '45698712', 'nombres': 'Ana Lucía', 'apellidos': 'Torres Benítez', 'telefono': '955664411', 'email': 'ana.torres@gmail.com', 'direccion': 'Av. La Marina 2340, San Miguel'},
            {'dni_ruc': '20547896', 'nombres': 'Transportes Veloz SAC', 'apellidos': 'RUC 20547896321', 'telefono': '944112288', 'email': 'logistica@transveloz.pe', 'direccion': 'Av. Nicolás Arriola 1500, La Victoria'},
        ]
        clientes = []
        for c in clientes_data:
            obj, _ = Cliente.objects.get_or_create(dni_ruc=c['dni_ruc'], defaults=c)
            clientes.append(obj)

        vehiculos_data = [
            {'cliente': clientes[0], 'placa': 'ABC-123', 'marca': 'Toyota', 'modelo': 'Corolla', 'anio': 2021, 'color': 'Plata Metálico', 'kilometraje': 45000, 'observaciones': 'Sedán personal, mantenimientos al día.'},
            {'cliente': clientes[1], 'placa': 'BDX-845', 'marca': 'Hyundai', 'modelo': 'Tucson', 'anio': 2022, 'color': 'Gris Grafito', 'kilometraje': 32000, 'observaciones': 'SUV familiar, presenta ruidos en frenos.'},
            {'cliente': clientes[2], 'placa': 'F5T-912', 'marca': 'Nissan', 'modelo': 'Sentra', 'anio': 2019, 'color': 'Negro', 'kilometraje': 68000, 'observaciones': 'Pérdida de potencia en subidas.'},
            {'cliente': clientes[3], 'placa': 'C2P-741', 'marca': 'Kia', 'modelo': 'Rio', 'anio': 2020, 'color': 'Rojo Pasión', 'kilometraje': 51000, 'observaciones': 'Mantenimiento preventivo regular.'},
            {'cliente': clientes[4], 'placa': 'W4E-369', 'marca': 'Toyota', 'modelo': 'Hilux', 'anio': 2023, 'color': 'Blanco', 'kilometraje': 28000, 'observaciones': 'Camioneta de carga ligera, chequeo de tren delantero.'},
        ]
        vehiculos = []
        for v in vehiculos_data:
            obj, _ = Vehiculo.objects.get_or_create(placa=v['placa'], defaults=v)
            vehiculos.append(obj)

        mecanicos_data = [
            {'dni': '41258963', 'nombre_completo': 'Carlos Mendoza Vargas', 'especialidad': 'Diagnóstico y Electrónica Automotriz', 'telefono': '981234567', 'email': 'carlos.m@motorexpress.pe', 'estado': 'Activo'},
            {'dni': '43987412', 'nombre_completo': 'Miguel Ángel Castro', 'especialidad': 'Motores y Cajas de Cambio', 'telefono': '972345678', 'email': 'miguel.c@motorexpress.pe', 'estado': 'Activo'},
            {'dni': '46521478', 'nombre_completo': 'José Luis Flores', 'especialidad': 'Frenos, Dirección y Suspensión', 'telefono': '963456789', 'email': 'jose.f@motorexpress.pe', 'estado': 'Activo'},
            {'dni': '48965213', 'nombre_completo': 'Eduardo Ramos Silva', 'especialidad': 'Mantenimiento Preventivo y Afinamiento', 'telefono': '954567890', 'email': 'eduardo.r@motorexpress.pe', 'estado': 'Activo'},
        ]
        mecanicos = []
        for m in mecanicos_data:
            obj, _ = Mecanico.objects.get_or_create(dni=m['dni'], defaults=m)
            mecanicos.append(obj)

        servicios_data = [
            {'codigo': 'SRV-001', 'nombre': 'Afinamiento Electrónico Completo', 'categoria': 'Motor', 'descripcion': 'Limpieza y calibración de sensores, limpieza de cuerpo de aceleración, escaneo general OBD-II', 'precio': Decimal('180.00'), 'tiempo_estimado': '2 horas'},
            {'codigo': 'SRV-002', 'nombre': 'Cambio de Aceite Sintético y Filtro', 'categoria': 'Mantenimiento', 'descripcion': 'Drenado de aceite usado, colocación de aceite 100% sintético y cambio de filtro de aceite', 'precio': Decimal('120.00'), 'tiempo_estimado': '45 min'},
            {'codigo': 'SRV-003', 'nombre': 'Mantenimiento del Sistema de Frenos', 'categoria': 'Frenos', 'descripcion': 'Limpieza y purga del circuito hidráulico, rectificación o cambio de pastillas y discos', 'precio': Decimal('150.00'), 'tiempo_estimado': '1.5 horas'},
            {'codigo': 'SRV-004', 'nombre': 'Limpieza de Inyectores por Ultrasonido', 'categoria': 'Motor', 'descripcion': 'Desmontaje de rampa, prueba en banco, limpieza ultrasónica y reemplazo de microfiltros', 'precio': Decimal('160.00'), 'tiempo_estimado': '2 horas'},
            {'codigo': 'SRV-005', 'nombre': 'Alineamiento y Balanceo Computarizado', 'categoria': 'Suspensión', 'descripcion': 'Alineación láser 3D en 4 ruedas y balanceo dinámico de llantas', 'precio': Decimal('90.00'), 'tiempo_estimado': '1 hora'},
            {'codigo': 'SRV-006', 'nombre': 'Diagnóstico Computarizado Completo', 'categoria': 'Eléctrico', 'descripcion': 'Escaneo multimarca con osciloscopio, lectura de códigos DTC y diagnóstico en tiempo real', 'precio': Decimal('80.00'), 'tiempo_estimado': '45 min'},
            {'codigo': 'SRV-007', 'nombre': 'Recarga y Mantenimiento de Aire Acondicionado', 'categoria': 'Aire Acondicionado', 'descripcion': 'Vacío de circuito, detección de fugas, reposición de aceite y gas ecológico R134a', 'precio': Decimal('140.00'), 'tiempo_estimado': '1 hora'},
        ]
        servicios = []
        for s in servicios_data:
            obj, _ = Servicio.objects.get_or_create(codigo=s['codigo'], defaults=s)
            servicios.append(obj)

        repuestos_data = [
            {'codigo': 'REP-001', 'nombre': 'Filtro de Aceite Premium', 'categoria': 'Filtros', 'marca': 'Mann Filter', 'precio_unitario': Decimal('35.00'), 'stock': 24, 'stock_minimo': 6},
            {'codigo': 'REP-002', 'nombre': 'Pastillas de Freno Cerámicas Delanteras', 'categoria': 'Frenos', 'marca': 'Brembo', 'precio_unitario': Decimal('185.00'), 'stock': 12, 'stock_minimo': 4},
            {'codigo': 'REP-003', 'nombre': 'Aceite Sintético 5W-30 (Galón)', 'categoria': 'Lubricantes', 'marca': 'Mobil 1', 'precio_unitario': Decimal('160.00'), 'stock': 18, 'stock_minimo': 5},
            {'codigo': 'REP-004', 'nombre': 'Juego de Bujías de Iridio (x4)', 'categoria': 'Encendido', 'marca': 'NGK Laser Iridium', 'precio_unitario': Decimal('140.00'), 'stock': 15, 'stock_minimo': 4},
            {'codigo': 'REP-005', 'nombre': 'Filtro de Aire Motor', 'categoria': 'Filtros', 'marca': 'Bosch', 'precio_unitario': Decimal('45.00'), 'stock': 20, 'stock_minimo': 5},
            {'codigo': 'REP-006', 'nombre': 'Líquido de Frenos DOT 4 (1L)', 'categoria': 'Frenos', 'marca': 'Motul', 'precio_unitario': Decimal('40.00'), 'stock': 25, 'stock_minimo': 6},
            {'codigo': 'REP-007', 'nombre': 'Amortiguador Delantero Reforzado', 'categoria': 'Suspensión', 'marca': 'KYB Excel-G', 'precio_unitario': Decimal('260.00'), 'stock': 8, 'stock_minimo': 2},
            {'codigo': 'REP-008', 'nombre': 'Batería 13 Placas 65Ah', 'categoria': 'Eléctrico', 'marca': 'Etna Platinum', 'precio_unitario': Decimal('320.00'), 'stock': 7, 'stock_minimo': 3},
        ]
        repuestos = []
        for r in repuestos_data:
            obj, _ = Repuesto.objects.get_or_create(codigo=r['codigo'], defaults=r)
            repuestos.append(obj)

        today = date.today()
        citas_data = [
            {'codigo': 'CIT-001', 'cliente': clientes[0], 'vehiculo': vehiculos[0], 'fecha_cita': today + timedelta(days=1), 'hora_cita': '09:00:00', 'motivo': 'Mantenimiento de los 50,000 km y revisión de suspensión', 'estado': 'Confirmada'},
            {'codigo': 'CIT-002', 'cliente': clientes[1], 'vehiculo': vehiculos[1], 'fecha_cita': today + timedelta(days=2), 'hora_cita': '11:00:00', 'motivo': 'Revisión urgente de chirrido al frenar', 'estado': 'Confirmada'},
            {'codigo': 'CIT-003', 'cliente': clientes[2], 'vehiculo': vehiculos[2], 'fecha_cita': today + timedelta(days=3), 'hora_cita': '14:30:00', 'motivo': 'Escaneo de luz Check Engine encendida', 'estado': 'Pendiente'},
            {'codigo': 'CIT-004', 'cliente': clientes[3], 'vehiculo': vehiculos[3], 'fecha_cita': today, 'hora_cita': '10:00:00', 'motivo': 'Cambio preventivo de aceite y filtros de aire', 'estado': 'En Atención'},
            {'codigo': 'CIT-005', 'cliente': clientes[4], 'vehiculo': vehiculos[4], 'fecha_cita': today - timedelta(days=2), 'hora_cita': '15:00:00', 'motivo': 'Revisión integral de tren delantero', 'estado': 'Completada'},
        ]
        for c in citas_data:
            Cita.objects.get_or_create(codigo=c['codigo'], defaults=c)

        ahora = datetime.now()
        ordenes_data = [
            {'numero_orden': 'OT-2026-001', 'vehiculo': vehiculos[0], 'cliente': clientes[0], 'mecanico': mecanicos[0], 'fecha_ingreso': ahora - timedelta(days=5), 'fecha_estimada_entrega': ahora - timedelta(days=3), 'kilometraje_ingreso': 45100, 'nivel_combustible': '3/4', 'estado': 'Finalizada', 'problema_reportado': 'Mantenimiento programado y bujías.', 'notas_tecnicas': 'Vehículo en óptimas condiciones.'},
            {'numero_orden': 'OT-2026-002', 'vehiculo': vehiculos[1], 'cliente': clientes[1], 'mecanico': mecanicos[2], 'fecha_ingreso': ahora - timedelta(days=3), 'fecha_estimada_entrega': ahora - timedelta(days=1), 'kilometraje_ingreso': 32150, 'nivel_combustible': '1/2', 'estado': 'Finalizada', 'problema_reportado': 'Chirrido al frenar a más de 40 km/h.', 'notas_tecnicas': 'Pastillas gastadas al 90%.'},
            {'numero_orden': 'OT-2026-003', 'vehiculo': vehiculos[2], 'cliente': clientes[2], 'mecanico': mecanicos[0], 'fecha_ingreso': ahora - timedelta(days=1), 'fecha_estimada_entrega': ahora + timedelta(days=1), 'kilometraje_ingreso': 68200, 'nivel_combustible': '1/4', 'estado': 'En Proceso', 'problema_reportado': 'Pérdida de potencia al acelerar en pendientes.', 'notas_tecnicas': 'Limpieza de inyectores requerida.'},
            {'numero_orden': 'OT-2026-004', 'vehiculo': vehiculos[3], 'cliente': clientes[3], 'mecanico': mecanicos[3], 'fecha_ingreso': ahora, 'fecha_estimada_entrega': ahora + timedelta(hours=5), 'kilometraje_ingreso': 51200, 'nivel_combustible': 'Full', 'estado': 'En Proceso', 'problema_reportado': 'Mantenimiento preventivo periódico.', 'notas_tecnicas': 'Cambio de aceite en curso.'},
            {'numero_orden': 'OT-2026-005', 'vehiculo': vehiculos[4], 'cliente': clientes[4], 'mecanico': mecanicos[2], 'fecha_ingreso': ahora, 'fecha_estimada_entrega': ahora + timedelta(days=2), 'kilometraje_ingreso': 28400, 'nivel_combustible': '1/2', 'estado': 'En Diagnóstico', 'problema_reportado': 'Golpeteo en rueda delantera derecha.', 'notas_tecnicas': 'Inspección de suspensión.'},
        ]
        ordenes = []
        for o in ordenes_data:
            obj, _ = OrdenTrabajo.objects.get_or_create(numero_orden=o['numero_orden'], defaults=o)
            ordenes.append(obj)

        OrdenServicio.objects.get_or_create(orden=ordenes[0], servicio=servicios[0], defaults={'cantidad': 1, 'precio_unitario': Decimal('180.00'), 'subtotal': Decimal('180.00')})
        OrdenServicio.objects.get_or_create(orden=ordenes[0], servicio=servicios[1], defaults={'cantidad': 1, 'precio_unitario': Decimal('120.00'), 'subtotal': Decimal('120.00')})
        OrdenServicio.objects.get_or_create(orden=ordenes[1], servicio=servicios[2], defaults={'cantidad': 1, 'precio_unitario': Decimal('150.00'), 'subtotal': Decimal('150.00')})
        OrdenServicio.objects.get_or_create(orden=ordenes[2], servicio=servicios[3], defaults={'cantidad': 1, 'precio_unitario': Decimal('160.00'), 'subtotal': Decimal('160.00')})
        OrdenServicio.objects.get_or_create(orden=ordenes[2], servicio=servicios[5], defaults={'cantidad': 1, 'precio_unitario': Decimal('80.00'), 'subtotal': Decimal('80.00')})
        OrdenServicio.objects.get_or_create(orden=ordenes[3], servicio=servicios[1], defaults={'cantidad': 1, 'precio_unitario': Decimal('120.00'), 'subtotal': Decimal('120.00')})
        OrdenServicio.objects.get_or_create(orden=ordenes[4], servicio=servicios[5], defaults={'cantidad': 1, 'precio_unitario': Decimal('80.00'), 'subtotal': Decimal('80.00')})

        RepuestoUtilizado.objects.get_or_create(orden=ordenes[0], repuesto=repuestos[0], defaults={'cantidad': 1, 'precio_unitario': Decimal('35.00'), 'subtotal': Decimal('35.00')})
        RepuestoUtilizado.objects.get_or_create(orden=ordenes[0], repuesto=repuestos[3], defaults={'cantidad': 1, 'precio_unitario': Decimal('140.00'), 'subtotal': Decimal('140.00')})
        RepuestoUtilizado.objects.get_or_create(orden=ordenes[1], repuesto=repuestos[1], defaults={'cantidad': 1, 'precio_unitario': Decimal('185.00'), 'subtotal': Decimal('185.00')})
        RepuestoUtilizado.objects.get_or_create(orden=ordenes[1], repuesto=repuestos[5], defaults={'cantidad': 1, 'precio_unitario': Decimal('40.00'), 'subtotal': Decimal('40.00')})
        RepuestoUtilizado.objects.get_or_create(orden=ordenes[2], repuesto=repuestos[3], defaults={'cantidad': 1, 'precio_unitario': Decimal('140.00'), 'subtotal': Decimal('140.00')})
        RepuestoUtilizado.objects.get_or_create(orden=ordenes[3], repuesto=repuestos[0], defaults={'cantidad': 1, 'precio_unitario': Decimal('35.00'), 'subtotal': Decimal('35.00')})
        RepuestoUtilizado.objects.get_or_create(orden=ordenes[3], repuesto=repuestos[4], defaults={'cantidad': 1, 'precio_unitario': Decimal('45.00'), 'subtotal': Decimal('45.00')})

        for o in ordenes:
            o.recalcular_totales()

        diag_data = [
            {'orden': ordenes[0], 'vehiculo': vehiculos[0], 'mecanico': mecanicos[0], 'descripcion_falla': 'Desgaste de bujías por kilometraje', 'diagnostico_tecnico': 'Electrodos con carbón residual. Inyección en parámetros normales.', 'gravedad': 'Media', 'recomendaciones': 'Sustituir bujías y calibrar sensores.'},
            {'orden': ordenes[1], 'vehiculo': vehiculos[1], 'mecanico': mecanicos[2], 'descripcion_falla': 'Desgaste crítico en pastillas delanteras', 'diagnostico_tecnico': 'Espesor de fricción inferior a 2mm rozando placa acústica.', 'gravedad': 'Alta', 'recomendaciones': 'Cambio urgente de pastillas cerámicas.'},
            {'orden': ordenes[2], 'vehiculo': vehiculos[2], 'mecanico': mecanicos[0], 'descripcion_falla': 'DTC P0300 Falla de encendido', 'diagnostico_tecnico': 'Toberas de inyectores con barniz, causando mezcla pobre a 3000 RPM.', 'gravedad': 'Alta', 'recomendaciones': 'Limpieza por ultrasonido en banco.'},
            {'orden': ordenes[4], 'vehiculo': vehiculos[4], 'mecanico': mecanicos[2], 'descripcion_falla': 'Fuga hidráulica en amortiguador', 'diagnostico_tecnico': 'Retén de vástago roto por impacto severo en bache.', 'gravedad': 'Crítica', 'recomendaciones': 'Reemplazo inmediato de par delantero.'},
        ]
        for d in diag_data:
            Diagnostico.objects.get_or_create(orden=d['orden'], vehiculo=d['vehiculo'], defaults=d)

        pagos_data = [
            {'numero_comprobante': 'B001-000101', 'tipo_comprobante': 'Boleta', 'orden': ordenes[0], 'cliente': clientes[0], 'monto': Decimal('475.00'), 'metodo_pago': 'Yape', 'numero_operacion': 'OPE-98741258', 'notas': 'Pago completo realizado vía Yape.'},
            {'numero_comprobante': 'B001-000102', 'tipo_comprobante': 'Boleta', 'orden': ordenes[1], 'cliente': clientes[1], 'monto': Decimal('375.00'), 'metodo_pago': 'Tarjeta de Crédito', 'numero_operacion': 'VISA-AUTH-44125', 'notas': 'Pago POS Visa 1 cuota.'},
            {'numero_comprobante': 'B001-000103', 'tipo_comprobante': 'Boleta', 'orden': ordenes[2], 'cliente': clientes[2], 'monto': Decimal('150.00'), 'metodo_pago': 'Efectivo', 'numero_operacion': 'EFE-00034', 'notas': 'Adelanto en efectivo para repuestos.'},
            {'numero_comprobante': 'B001-000104', 'tipo_comprobante': 'Boleta', 'orden': ordenes[3], 'cliente': clientes[3], 'monto': Decimal('200.00'), 'metodo_pago': 'Tarjeta de Débito', 'numero_operacion': 'MC-DEBIT-5589', 'notas': 'Cancelación total con tarjeta débito.'},
        ]
        for p in pagos_data:
            Pago.objects.get_or_create(numero_comprobante=p['numero_comprobante'], defaults=p)

        self.stdout.write(self.style.SUCCESS("¡Datos iniciales cargados exitosamente en MariaDB!"))
