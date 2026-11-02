from django import forms
from .models import Cliente, Vehiculo, Mecanico, Cita, Servicio, Repuesto, OrdenTrabajo, Diagnostico, Pago

class ClienteForm(forms.ModelForm):
    class Meta:
        model = Cliente
        fields = ['dni_ruc', 'nombres', 'apellidos', 'telefono', 'email', 'direccion']
        widgets = {
            'dni_ruc': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 47895214'}),
            'nombres': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Juan Carlos'}),
            'apellidos': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Pérez Quispe'}),
            'telefono': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 987654321'}),
            'email': forms.EmailInput(attrs={'class': 'form-control', 'placeholder': 'cliente@correo.com'}),
            'direccion': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Av. Los Próceres 450, Surco'}),
        }


class VehiculoForm(forms.ModelForm):
    class Meta:
        model = Vehiculo
        fields = ['cliente', 'placa', 'marca', 'modelo', 'anio', 'color', 'kilometraje', 'observaciones']
        widgets = {
            'cliente': forms.Select(attrs={'class': 'form-select'}),
            'placa': forms.TextInput(attrs={'class': 'form-control text-uppercase font-monospace fw-bold', 'placeholder': 'Ej. ABC-123'}),
            'marca': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Toyota'}),
            'modelo': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Corolla'}),
            'anio': forms.NumberInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 2022'}),
            'color': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Gris Plata'}),
            'kilometraje': forms.NumberInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 45000'}),
            'observaciones': forms.Textarea(attrs={'class': 'form-control', 'rows': 3, 'placeholder': 'Detalles de carrocería o notas del dueño...'}),
        }


class MecanicoForm(forms.ModelForm):
    class Meta:
        model = Mecanico
        fields = ['dni', 'nombre_completo', 'especialidad', 'telefono', 'email', 'estado']
        widgets = {
            'dni': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 41258963'}),
            'nombre_completo': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Carlos Mendoza Vargas'}),
            'especialidad': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Diagnóstico y Electrónica Automotriz'}),
            'telefono': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 981234567'}),
            'email': forms.EmailInput(attrs={'class': 'form-control', 'placeholder': 'mecanico@motorexpress.pe'}),
            'estado': forms.Select(attrs={'class': 'form-select'}),
        }


class CitaForm(forms.ModelForm):
    class Meta:
        model = Cita
        fields = ['codigo', 'cliente', 'vehiculo', 'fecha_cita', 'hora_cita', 'motivo', 'estado']
        widgets = {
            'codigo': forms.TextInput(attrs={'class': 'form-control font-monospace fw-bold'}),
            'cliente': forms.Select(attrs={'class': 'form-select'}),
            'vehiculo': forms.Select(attrs={'class': 'form-select'}),
            'fecha_cita': forms.DateInput(attrs={'class': 'form-control', 'type': 'date'}),
            'hora_cita': forms.TimeInput(attrs={'class': 'form-control', 'type': 'time'}),
            'motivo': forms.Textarea(attrs={'class': 'form-control', 'rows': 3, 'placeholder': 'Motivo del mantenimiento o síntoma...'}),
            'estado': forms.Select(attrs={'class': 'form-select'}),
        }


class ServicioForm(forms.ModelForm):
    class Meta:
        model = Servicio
        fields = ['codigo', 'nombre', 'categoria', 'descripcion', 'precio', 'tiempo_estimado']
        widgets = {
            'codigo': forms.TextInput(attrs={'class': 'form-control font-monospace fw-bold'}),
            'nombre': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Afinamiento Electrónico'}),
            'categoria': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Motor, Frenos, Suspensión'}),
            'descripcion': forms.Textarea(attrs={'class': 'form-control', 'rows': 3}),
            'precio': forms.NumberInput(attrs={'class': 'form-control fw-bold text-success', 'step': '0.01'}),
            'tiempo_estimado': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. 2 horas'}),
        }


class RepuestoForm(forms.ModelForm):
    class Meta:
        model = Repuesto
        fields = ['codigo', 'nombre', 'categoria', 'marca', 'precio_unitario', 'stock', 'stock_minimo']
        widgets = {
            'codigo': forms.TextInput(attrs={'class': 'form-control font-monospace fw-bold'}),
            'nombre': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Pastillas de Freno Cerámicas'}),
            'categoria': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Frenos, Filtros, Lubricantes'}),
            'marca': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Brembo, Bosch, Mann Filter'}),
            'precio_unitario': forms.NumberInput(attrs={'class': 'form-control fw-bold text-primary', 'step': '0.01'}),
            'stock': forms.NumberInput(attrs={'class': 'form-control'}),
            'stock_minimo': forms.NumberInput(attrs={'class': 'form-control'}),
        }


class OrdenTrabajoForm(forms.ModelForm):
    class Meta:
        model = OrdenTrabajo
        fields = ['numero_orden', 'vehiculo', 'cliente', 'mecanico', 'fecha_ingreso', 'fecha_estimada_entrega', 'kilometraje_ingreso', 'nivel_combustible', 'estado', 'problema_reportado', 'notas_tecnicas']
        widgets = {
            'numero_orden': forms.TextInput(attrs={'class': 'form-control font-monospace fw-bold'}),
            'vehiculo': forms.Select(attrs={'class': 'form-select'}),
            'cliente': forms.Select(attrs={'class': 'form-select'}),
            'mecanico': forms.Select(attrs={'class': 'form-select'}),
            'fecha_ingreso': forms.DateTimeInput(attrs={'class': 'form-control', 'type': 'datetime-local'}),
            'fecha_estimada_entrega': forms.DateTimeInput(attrs={'class': 'form-control', 'type': 'datetime-local'}),
            'kilometraje_ingreso': forms.NumberInput(attrs={'class': 'form-control'}),
            'nivel_combustible': forms.Select(attrs={'class': 'form-select'}),
            'estado': forms.Select(attrs={'class': 'form-select'}),
            'problema_reportado': forms.Textarea(attrs={'class': 'form-control', 'rows': 3}),
            'notas_tecnicas': forms.Textarea(attrs={'class': 'form-control', 'rows': 2}),
        }


class DiagnosticoForm(forms.ModelForm):
    class Meta:
        model = Diagnostico
        fields = ['orden', 'vehiculo', 'mecanico', 'descripcion_falla', 'diagnostico_tecnico', 'gravedad', 'recomendaciones']
        widgets = {
            'orden': forms.Select(attrs={'class': 'form-select'}),
            'vehiculo': forms.Select(attrs={'class': 'form-select'}),
            'mecanico': forms.Select(attrs={'class': 'form-select'}),
            'descripcion_falla': forms.TextInput(attrs={'class': 'form-control'}),
            'diagnostico_tecnico': forms.Textarea(attrs={'class': 'form-control', 'rows': 3}),
            'gravedad': forms.Select(attrs={'class': 'form-select'}),
            'recomendaciones': forms.Textarea(attrs={'class': 'form-control', 'rows': 2}),
        }


class PagoForm(forms.ModelForm):
    class Meta:
        model = Pago
        fields = ['numero_comprobante', 'tipo_comprobante', 'orden', 'cliente', 'monto', 'metodo_pago', 'numero_operacion', 'notas']
        widgets = {
            'numero_comprobante': forms.TextInput(attrs={'class': 'form-control font-monospace fw-bold text-primary'}),
            'tipo_comprobante': forms.Select(attrs={'class': 'form-select'}),
            'orden': forms.Select(attrs={'class': 'form-select'}),
            'cliente': forms.Select(attrs={'class': 'form-select'}),
            'monto': forms.NumberInput(attrs={'class': 'form-control fw-bold text-success fs-5', 'step': '0.01'}),
            'metodo_pago': forms.Select(attrs={'class': 'form-select'}),
            'numero_operacion': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Ej. Operación POS, Celular Yape, etc.'}),
            'notas': forms.Textarea(attrs={'class': 'form-control', 'rows': 2}),
        }
