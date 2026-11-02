<?php

function seedInitialData(PDO $pdo) {
    $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrador Principal', 'admin@motorexpress.pe', $passwordHash, 'Administrador']);
    $stmt->execute(['Carlos Mendoza (Mecánico)', 'carlos@motorexpress.pe', $passwordHash, 'Mecanico']);
    $stmt->execute(['Valeria Ríos (Recepcionista)', 'valeria@motorexpress.pe', $passwordHash, 'Recepcionista']);

    $clientes = [
        ['47895214', 'Juan Carlos', 'Pérez Quispe', '987112233', 'juan.perez@gmail.com', 'Av. Los Próceres 450, Santiago de Surco'],
        ['10458796', 'María Elena', 'Gómez Salcedo', '974556677', 'maria.gomez@outlook.com', 'Calle Las Palmeras 124, San Borja'],
        ['71236548', 'Roberto', 'López Morales', '998443322', 'roberto.lopez@yahoo.com', 'Jr. Junín 890, Miraflores'],
        ['45698712', 'Ana Lucía', 'Torres Benítez', '955664411', 'ana.torres@gmail.com', 'Av. La Marina 2340, San Miguel'],
        ['20547896', 'Transportes Veloz SAC', 'RUC 20547896321', '944112288', 'logistica@transveloz.pe', 'Av. Nicolás Arriola 1500, La Victoria']
    ];
    $stmtCli = $pdo->prepare("INSERT INTO clientes (dni_ruc, nombres, apellidos, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($clientes as $cli) {
        $stmtCli->execute($cli);
    }

    $vehiculos = [
        [1, 'ABC-123', 'Toyota', 'Corolla', 2021, 'Plata Metálico', 45000, 'Sedán de uso personal, revisiones al día'],
        [2, 'BDX-845', 'Hyundai', 'Tucson', 2022, 'Gris Grafito', 32000, 'SUV familiar, presenta ruidos en frenos'],
        [3, 'F5T-912', 'Nissan', 'Sentra', 2019, 'Negro', 68000, 'Ligera pérdida de potencia y consumo de aceite'],
        [4, 'C2P-741', 'Kia', 'Rio', 2020, 'Rojo Pasión', 51000, 'Mantenimiento preventivo regular'],
        [5, 'W4E-369', 'Toyota', 'Hilux', 2023, 'Blanco', 28000, 'Camioneta de carga ligera, chequeo de suspensión']
    ];
    $stmtVeh = $pdo->prepare("INSERT INTO vehiculos (cliente_id, placa, marca, modelo, anio, color, kilometraje, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($vehiculos as $veh) {
        $stmtVeh->execute($veh);
    }

    $mecanicos = [
        ['41258963', 'Carlos Mendoza Vargas', 'Diagnóstico y Electrónica Automotriz', '981234567', 'carlos.m@motorexpress.pe', 'Activo'],
        ['43987412', 'Miguel Ángel Castro', 'Motores y Cajas de Cambio', '972345678', 'miguel.c@motorexpress.pe', 'Activo'],
        ['46521478', 'José Luis Flores', 'Frenos, Dirección y Suspensión', '963456789', 'jose.f@motorexpress.pe', 'Activo'],
        ['48965213', 'Eduardo Ramos Silva', 'Mantenimiento Preventivo y Afinamiento', '954567890', 'eduardo.r@motorexpress.pe', 'Activo']
    ];
    $stmtMec = $pdo->prepare("INSERT INTO mecanicos (dni, nombre_completo, especialidad, telefono, email, estado) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($mecanicos as $mec) {
        $stmtMec->execute($mec);
    }

    $servicios = [
        ['SRV-001', 'Afinamiento Electrónico Completo', 'Motor', 'Limpieza y calibración de sensores, limpieza de cuerpo de aceleración, escaneo general OBD-II', 180.00, '2 horas'],
        ['SRV-002', 'Cambio de Aceite Sintético y Filtro', 'Mantenimiento', 'Drenado de aceite usado, colocación de aceite 100% sintético y cambio de filtro de aceite', 120.00, '45 min'],
        ['SRV-003', 'Mantenimiento del Sistema de Frenos', 'Frenos', 'Limpieza y purga del circuito hidráulico, rectificación o cambio de pastillas y discos', 150.00, '1.5 horas'],
        ['SRV-004', 'Limpieza de Inyectores por Ultrasonido', 'Motor', 'Desmontaje de rampa, prueba en banco, limpieza ultrasónica y reemplazo de microfiltros', 160.00, '2 horas'],
        ['SRV-005', 'Alineamiento y Balanceo Computarizado', 'Suspensión', 'Alineación láser 3D en 4 ruedas y balanceo dinámico de llantas', 90.00, '1 hora'],
        ['SRV-006', 'Diagnóstico Computarizado Completo', 'Eléctrico', 'Escaneo multimarca con osciloscopio, lectura de códigos DTC y diagnóstico en tiempo real', 80.00, '45 min'],
        ['SRV-007', 'Recarga y Mantenimiento de Aire Acondicionado', 'Aire Acondicionado', 'Vacío de circuito, detección de fugas, reposición de aceite y gas ecológico R134a', 140.00, '1 hora']
    ];
    $stmtSrv = $pdo->prepare("INSERT INTO servicios (codigo, nombre, categoria, descripcion, precio, tiempo_estimado) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($servicios as $srv) {
        $stmtSrv->execute($srv);
    }

    $repuestos = [
        ['REP-001', 'Filtro de Aceite Premium', 'Filtros', 'Mann Filter', 35.00, 24, 6],
        ['REP-002', 'Pastillas de Freno Delanteras Cerámicas', 'Frenos', 'Brembo', 185.00, 12, 4],
        ['REP-003', 'Aceite Sintético 5W-30 (Galón)', 'Lubricantes', 'Mobil 1', 160.00, 18, 5],
        ['REP-004', 'Juego de Bujías de Iridio (x4)', 'Encendido', 'NGK Laser Iridium', 140.00, 15, 4],
        ['REP-005', 'Filtro de Aire Motor', 'Filtros', 'Bosch', 45.00, 20, 5],
        ['REP-006', 'Líquido de Frenos DOT 4 (1L)', 'Frenos', 'Motul', 40.00, 25, 6],
        ['REP-007', 'Amortiguador Delantero Reforzado', 'Suspensión', 'KYB Excel-G', 260.00, 8, 2],
        ['REP-008', 'Batería 13 Placas 65Ah', 'Eléctrico', 'Etna Platinum', 320.00, 7, 3]
    ];
    $stmtRep = $pdo->prepare("INSERT INTO repuestos (codigo, nombre, categoria, marca, precio_unitario, stock, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($repuestos as $rep) {
        $stmtRep->execute($rep);
    }

    $citas = [
        ['CIT-001', 1, 1, date('Y-m-d', strtotime('+1 day')), '09:00:00', 'Mantenimiento de los 50,000 km y revisión de suspensión', 'Confirmada'],
        ['CIT-002', 2, 2, date('Y-m-d', strtotime('+2 days')), '11:00:00', 'Revisión urgente de chirrido al frenar y pedal esponjoso', 'Confirmada'],
        ['CIT-003', 3, 3, date('Y-m-d', strtotime('+3 days')), '14:30:00', 'Escaneo de luz Check Engine encendida', 'Pendiente'],
        ['CIT-004', 4, 4, date('Y-m-d'), '10:00:00', 'Cambio preventivo de aceite y filtros de aire', 'En Atención'],
        ['CIT-005', 5, 5, date('Y-m-d', strtotime('-2 days')), '15:00:00', 'Revisión integral de tren delantero', 'Completada']
    ];
    $stmtCit = $pdo->prepare("INSERT INTO citas (codigo, cliente_id, vehiculo_id, fecha_cita, hora_cita, motivo, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($citas as $cit) {
        $stmtCit->execute($cit);
    }

    $ordenes = [
        ['OT-2026-001', 1, 1, 1, date('Y-m-d 08:30:00', strtotime('-5 days')), date('Y-m-d 17:00:00', strtotime('-3 days')), 45100, '3/4', 'Finalizada', 'Mantenimiento general programado y revisión de bujías.', 'Vehículo en excelente estado mecánico.', 300.00, 175.00, 475.00],
        ['OT-2026-002', 2, 2, 3, date('Y-m-d 09:15:00', strtotime('-3 days')), date('Y-m-d 16:00:00', strtotime('-1 day')), 32150, '1/2', 'Finalizada', 'Chirrido agudo al frenar a más de 40 km/h.', 'Pastillas desgastadas al 90%. Discos en buen estado.', 150.00, 225.00, 375.00],
        ['OT-2026-003', 3, 3, 1, date('Y-m-d 10:00:00', strtotime('-1 day')), date('Y-m-d 18:00:00', strtotime('+1 day')), 68200, '1/4', 'En Proceso', 'Pérdida de potencia al acelerar en pendientes pronunciadas.', 'Se requiere limpieza de inyectores y cambio de bujías.', 240.00, 140.00, 380.00],
        ['OT-2026-004', 4, 4, 4, date('Y-m-d 08:00:00'), date('Y-m-d 13:00:00'), 51200, 'Full', 'En Proceso', 'Mantenimiento preventivo periódico.', 'En proceso de cambio de aceite y filtros.', 120.00, 80.00, 200.00],
        ['OT-2026-005', 5, 5, 3, date('Y-m-d 11:30:00'), date('Y-m-d 17:00:00', strtotime('+2 days')), 28400, '1/2', 'En Diagnóstico', 'Golpeteo seco en la rueda delantera derecha al pasar baches.', 'Inspeccionando rótulas y amortiguadores.', 80.00, 0.00, 80.00]
    ];
    $stmtOrd = $pdo->prepare("INSERT INTO ordenes_trabajo (numero_orden, vehiculo_id, cliente_id, mecanico_id, fecha_ingreso, fecha_estimada_entrega, kilometraje_ingreso, nivel_combustible, estado, problema_reportado, notas_tecnicas, total_servicios, total_repuestos, total_general) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($ordenes as $ord) {
        $stmtOrd->execute($ord);
    }

    $ordenServicios = [
        [1, 1, 1, 180.00, 180.00],
        [1, 2, 1, 120.00, 120.00],
        [2, 3, 1, 150.00, 150.00],
        [3, 4, 1, 160.00, 160.00],
        [3, 6, 1, 80.00, 80.00],
        [4, 2, 1, 120.00, 120.00],
        [5, 6, 1, 80.00, 80.00]
    ];
    $stmtOS = $pdo->prepare("INSERT INTO orden_servicios (orden_id, servicio_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    foreach ($ordenServicios as $os) {
        $stmtOS->execute($os);
    }

    $repUtilizados = [
        [1, 1, 1, 35.00, 35.00],
        [1, 4, 1, 140.00, 140.00],
        [2, 2, 1, 185.00, 185.00],
        [2, 6, 1, 40.00, 40.00],
        [3, 4, 1, 140.00, 140.00],
        [4, 1, 1, 35.00, 35.00],
        [4, 5, 1, 45.00, 45.00]
    ];
    $stmtRU = $pdo->prepare("INSERT INTO repuestos_utilizados (orden_id, repuesto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    foreach ($repUtilizados as $ru) {
        $stmtRU->execute($ru);
    }

    $diagnosticos = [
        [1, 1, 1, 'Bujías con desgaste por kilometraje excesivo', 'Bujías originales gastadas con electrodos carbonizados. Sistema de inyección en parámetros normales.', 'Media', 'Cambiar juego de 4 bujías y realizar afinamiento de sensores.'],
        [2, 2, 3, 'Desgaste crítico en pastillas delanteras', 'Pastillas de freno con 2mm de espesor restante provocando contacto de placa avisadora metálica.', 'Alta', 'Sustituir pastillas por cerámicas y purgar fluido hidráulico.'],
        [3, 3, 1, 'Código DTC P0300 - Falla de encendido en cilindro 2 y 3', 'Suciedad en toberas de inyectores provocando mezcla pobre a altas RPM.', 'Alta', 'Limpieza de rampa de inyectores por ultrasonido y reemplazo de microfiltros.'],
        [5, 5, 3, 'Amortiguador delantero derecho con fuga de aceite hidráulico', 'Retén de amortiguador vencido por impacto en bache, pérdida de absorción de rebote.', 'Crítica', 'Reemplazo de amortiguador delantero y chequeo de terminales de dirección.']
    ];
    $stmtDiag = $pdo->prepare("INSERT INTO diagnosticos (orden_id, vehiculo_id, mecanico_id, descripcion_falla, diagnostico_tecnico, gravedad, recomendaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($diagnosticos as $diag) {
        $stmtDiag->execute($diag);
    }

    $pagos = [
        ['B001-000101', 'Boleta', 1, 1, 475.00, 'Yape', 'OPE-98741258', date('Y-m-d 16:30:00', strtotime('-3 days')), 'Pago realizado puntualmente mediante Yape.', 'Completado'],
        ['B001-000102', 'Boleta', 2, 2, 375.00, 'Tarjeta de Crédito', 'VISA-AUTH-44125', date('Y-m-d 15:20:00', strtotime('-1 day')), 'Pago con tarjeta Visa en 1 cuota POS Izipay.', 'Completado'],
        ['B001-000103', 'Boleta', 3, 3, 150.00, 'Efectivo', 'EFE-00034', date('Y-m-d 11:00:00'), 'Adelanto del 40% en efectivo para repuestos.', 'Completado'],
        ['B001-000104', 'Boleta', 4, 4, 200.00, 'Tarjeta de Débito', 'MASTERCARD-5589', date('Y-m-d 12:45:00'), 'Cancelación total con tarjeta débito BBVA.', 'Completado']
    ];
    $stmtPago = $pdo->prepare("INSERT INTO pagos (numero_comprobante, tipo_comprobante, orden_id, cliente_id, monto, metodo_pago, numero_operacion, fecha_pago, notas, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($pagos as $pago) {
        $stmtPago->execute($pago);
    }
}
