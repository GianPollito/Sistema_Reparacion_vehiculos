from django.db import models
from django.contrib.auth.models import User
from django.db.models.signals import post_save
from django.dispatch import receiver
from decimal import Decimal

class PerfilUsuario(models.Model):
    ROLES = [
        ('Administrador', 'Administrador'),
        ('Recepcionista', 'Recepcionista'),
        ('Mecanico', 'Mecánico'),
    ]
    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='perfil')
    rol = models.CharField(max_length=50, choices=ROLES, default='Administrador')

    def __str__(self):
        return f"{self.user.username} ({self.rol})"

@receiver(post_save, sender=User)
def create_or_update_user_profile(sender, instance, created, **kwargs):
    if created:
        PerfilUsuario.objects.get_or_create(user=instance)
    else:
        if hasattr(instance, 'perfil'):
            instance.perfil.save()


class Cliente(models.Model):
    dni_ruc = models.CharField(max_length=20, verbose_name="DNI / RUC")
    nombres = models.CharField(max_length=100)
    apellidos = models.CharField(max_length=100)
    telefono = models.CharField(max_length=20, blank=True, null=True)
    email = models.EmailField(max_length=100, blank=True, null=True)
    direccion = models.CharField(max_length=200, blank=True, null=True)
    fecha_registro = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-id']

    def __str__(self):
        return f"{self.nombres} {self.apellidos} (DNI/RUC: {self.dni_ruc})"

    @property
    def nombre_completo(self):
        return f"{self.nombres} {self.apellidos}"


class Vehiculo(models.Model):
    cliente = models.ForeignKey(Cliente, on_delete=models.CASCADE, related_name='vehiculos')
    placa = models.CharField(max_length=20, unique=True)
    marca = models.CharField(max_length=50)
    modelo = models.CharField(max_length=50)
    anio = models.IntegerField(verbose_name="Año de Fabricación")
    color = models.CharField(max_length=30, blank=True, null=True)
    kilometraje = models.IntegerField(default=0)
    observaciones = models.TextField(blank=True, null=True)
    fecha_registro = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-id']

    def __str__(self):
        return f"[{self.placa}] {self.marca} {self.modelo} ({self.anio})"


class Mecanico(models.Model):
    ESTADOS = [('Activo', 'Activo'), ('Inactivo', 'Inactivo')]
    dni = models.CharField(max_length=20, unique=True)
    nombre_completo = models.CharField(max_length=120)
    especialidad = models.CharField(max_length=100)
    telefono = models.CharField(max_length=20, blank=True, null=True)
    email = models.EmailField(max_length=100, blank=True, null=True)
    estado = models.CharField(max_length=20, choices=ESTADOS, default='Activo')
    fecha_registro = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['nombre_completo']

    def __str__(self):
        return f"{self.nombre_completo} - {self.especialidad}"


class Cita(models.Model):
    ESTADOS = [
        ('Pendiente', 'Pendiente'),
        ('Confirmada', 'Confirmada'),
        ('En Atención', 'En Atención'),
        ('Completada', 'Completada'),
        ('Cancelada', 'Cancelada'),
    ]
    codigo = models.CharField(max_length=20, unique=True)
    cliente = models.ForeignKey(Cliente, on_delete=models.CASCADE, related_name='citas')
    vehiculo = models.ForeignKey(Vehiculo, on_delete=models.CASCADE, related_name='citas')
    fecha_cita = models.DateField()
    hora_cita = models.TimeField()
    motivo = models.TextField()
    estado = models.CharField(max_length=30, choices=ESTADOS, default='Pendiente')
    fecha_registro = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['fecha_cita', 'hora_cita']

    def __str__(self):
        return f"{self.codigo} - {self.vehiculo.placa} ({self.fecha_cita})"


class Servicio(models.Model):
    codigo = models.CharField(max_length=20, unique=True)
    nombre = models.CharField(max_length=150)
    categoria = models.CharField(max_length=50)
    descripcion = models.TextField(blank=True, null=True)
    precio = models.DecimalField(max_digits=10, decimal_places=2)
    tiempo_estimado = models.CharField(max_length=50, blank=True, null=True)

    class Meta:
        ordering = ['categoria', 'nombre']

    def __str__(self):
        return f"[{self.codigo}] {self.nombre} (S/. {self.precio})"


class Repuesto(models.Model):
    codigo = models.CharField(max_length=30, unique=True)
    nombre = models.CharField(max_length=150)
    categoria = models.CharField(max_length=50)
    marca = models.CharField(max_length=50, blank=True, null=True)
    precio_unitario = models.DecimalField(max_digits=10, decimal_places=2)
    stock = models.IntegerField(default=0)
    stock_minimo = models.IntegerField(default=5)

    class Meta:
        ordering = ['nombre']

    def __str__(self):
        return f"[{self.codigo}] {self.nombre} - S/. {self.precio_unitario} (Stock: {self.stock})"


class OrdenTrabajo(models.Model):
    ESTADOS = [
        ('Pendiente', 'Pendiente'),
        ('En Diagnóstico', 'En Diagnóstico'),
        ('En Proceso', 'En Proceso'),
        ('Esperando Repuestos', 'Esperando Repuestos'),
        ('Finalizada', 'Finalizada'),
        ('Entregada', 'Entregada'),
        ('Cancelada', 'Cancelada'),
    ]
    COMBUSTIBLE = [
        ('Reserva', 'Reserva'),
        ('1/4', '1/4'),
        ('1/2', '1/2'),
        ('3/4', '3/4'),
        ('Full', 'Full'),
    ]

    numero_orden = models.CharField(max_length=30, unique=True)
    vehiculo = models.ForeignKey(Vehiculo, on_delete=models.CASCADE, related_name='ordenes')
    cliente = models.ForeignKey(Cliente, on_delete=models.CASCADE, related_name='ordenes')
    mecanico = models.ForeignKey(Mecanico, on_delete=models.SET_NULL, null=True, blank=True, related_name='ordenes')
    fecha_ingreso = models.DateTimeField()
    fecha_estimada_entrega = models.DateTimeField(blank=True, null=True)
    kilometraje_ingreso = models.IntegerField(default=0)
    nivel_combustible = models.CharField(max_length=20, choices=COMBUSTIBLE, default='1/2')
    estado = models.CharField(max_length=30, choices=ESTADOS, default='Pendiente')
    problema_reportado = models.TextField()
    notas_tecnicas = models.TextField(blank=True, null=True)
    total_servicios = models.DecimalField(max_digits=10, decimal_places=2, default=Decimal('0.00'))
    total_repuestos = models.DecimalField(max_digits=10, decimal_places=2, default=Decimal('0.00'))
    total_general = models.DecimalField(max_digits=10, decimal_places=2, default=Decimal('0.00'))
    fecha_registro = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-id']

    def __str__(self):
        return f"{self.numero_orden} - {self.vehiculo.placa} ({self.estado})"

    def recalcular_totales(self):
        total_srv = sum(item.subtotal for item in self.servicios_detalle.all()) or Decimal('0.00')
        total_rep = sum(item.subtotal for item in self.repuestos_detalle.all()) or Decimal('0.00')
        self.total_servicios = total_srv
        self.total_repuestos = total_rep
        self.total_general = total_srv + total_rep
        self.save()

    @property
    def costo_total(self):
        return self.total_general

    @property
    def subtotal(self):
        return (self.total_general / Decimal('1.18')).quantize(Decimal('0.01'))

    @property
    def impuesto(self):
        return self.total_general - self.subtotal

    @property
    def total_pagado(self):
        pagos_comp = self.pagos.filter(estado='Completado')
        return sum(p.monto for p in pagos_comp) or Decimal('0.00')

    @property
    def saldo_pendiente(self):
        saldo = self.total_general - self.total_pagado
        return max(Decimal('0.00'), saldo)


class OrdenServicio(models.Model):
    orden = models.ForeignKey(OrdenTrabajo, on_delete=models.CASCADE, related_name='servicios_detalle')
    servicio = models.ForeignKey(Servicio, on_delete=models.CASCADE)
    cantidad = models.IntegerField(default=1)
    precio_unitario = models.DecimalField(max_digits=10, decimal_places=2)
    subtotal = models.DecimalField(max_digits=10, decimal_places=2)

    def save(self, *args, **kwargs):
        self.subtotal = self.cantidad * self.precio_unitario
        super().save(*args, **kwargs)
        self.orden.recalcular_totales()

    def delete(self, *args, **kwargs):
        orden = self.orden
        super().delete(*args, **kwargs)
        orden.recalcular_totales()


class RepuestoUtilizado(models.Model):
    orden = models.ForeignKey(OrdenTrabajo, on_delete=models.CASCADE, related_name='repuestos_detalle')
    repuesto = models.ForeignKey(Repuesto, on_delete=models.CASCADE)
    cantidad = models.IntegerField(default=1)
    precio_unitario = models.DecimalField(max_digits=10, decimal_places=2)
    subtotal = models.DecimalField(max_digits=10, decimal_places=2)
    fecha_asignacion = models.DateTimeField(auto_now_add=True)

    def save(self, *args, **kwargs):
        self.subtotal = self.cantidad * self.precio_unitario
        super().save(*args, **kwargs)
        self.orden.recalcular_totales()

    def delete(self, *args, **kwargs):
        orden = self.orden
        super().delete(*args, **kwargs)
        orden.recalcular_totales()


class Diagnostico(models.Model):
    GRAVEDAD = [
        ('Baja', 'Baja'),
        ('Media', 'Media'),
        ('Alta', 'Alta'),
        ('Crítica', 'Crítica'),
    ]
    orden = models.ForeignKey(OrdenTrabajo, on_delete=models.SET_NULL, null=True, blank=True, related_name='diagnosticos')
    vehiculo = models.ForeignKey(Vehiculo, on_delete=models.CASCADE, related_name='diagnosticos')
    mecanico = models.ForeignKey(Mecanico, on_delete=models.CASCADE, related_name='diagnosticos')
    fecha_diagnostico = models.DateTimeField(auto_now_add=True)
    descripcion_falla = models.TextField()
    diagnostico_tecnico = models.TextField()
    gravedad = models.CharField(max_length=20, choices=GRAVEDAD, default='Media')
    recomendaciones = models.TextField(blank=True, null=True)

    class Meta:
        ordering = ['-fecha_diagnostico']

    def __str__(self):
        return f"Diag {self.vehiculo.placa} ({self.gravedad})"


class Pago(models.Model):
    METODOS = [
        ('Efectivo', 'Efectivo'),
        ('Tarjeta de Crédito', 'Tarjeta de Crédito'),
        ('Tarjeta de Débito', 'Tarjeta de Débito'),
        ('Yape', 'Yape'),
        ('Plin', 'Plin'),
    ]
    TIPOS = [
        ('Boleta', 'Boleta'),
        ('Factura', 'Factura'),
        ('Recibo de Caja', 'Recibo de Caja'),
    ]
    numero_comprobante = models.CharField(max_length=30, unique=True)
    tipo_comprobante = models.CharField(max_length=20, choices=TIPOS, default='Boleta')
    orden = models.ForeignKey(OrdenTrabajo, on_delete=models.CASCADE, related_name='pagos')
    cliente = models.ForeignKey(Cliente, on_delete=models.CASCADE, related_name='pagos')
    monto = models.DecimalField(max_digits=10, decimal_places=2)
    metodo_pago = models.CharField(max_length=50, choices=METODOS)
    numero_operacion = models.CharField(max_length=50, blank=True, null=True)
    fecha_pago = models.DateTimeField(auto_now_add=True)
    notas = models.TextField(blank=True, null=True)
    estado = models.CharField(max_length=20, default='Completado')

    class Meta:
        ordering = ['-id']

    def __str__(self):
        return f"{self.numero_comprobante} - S/. {self.monto} ({self.metodo_pago})"
