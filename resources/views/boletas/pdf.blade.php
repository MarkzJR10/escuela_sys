<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones - {{ $alumno->matricula }}</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 12px; 
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 15px 25px;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #2c3e50; 
            padding-bottom: 12px; 
            margin-bottom: 20px; 
        }
        .title { 
            font-size: 20px; 
            font-weight: bold; 
            color: #2c3e50; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .subtitle { 
            font-size: 14px; 
            color: #7f8c8d; 
            font-weight: 600;
            text-transform: uppercase;
        }
        .info-table { 
            width: 100%; 
            margin-bottom: 20px; 
            border-collapse: collapse;
        }
        .info-table td { 
            padding: 5px 8px; 
            vertical-align: top;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 20px;
        }
        .grades-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px; 
        }
        .grades-table th { 
            background-color: #2c3e50; 
            color: #ffffff;
            border: 1px solid #2c3e50; 
            padding: 8px 6px; 
            text-align: center; 
            font-size: 11px;
            text-transform: uppercase;
        }
        .grades-table td { 
            border: 1px solid #bdc3c7; 
            padding: 7px 8px; 
            text-align: center; 
            font-size: 11.5px;
        }
        .grades-table tr:nth-child(even) td {
            background-color: #fdfdfd;
        }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .bg-promedio {
            background-color: #f1f2f6 !important;
            font-weight: bold;
        }
        .footer-table {
            width: 100%; 
            margin-top: 50px; 
            border-collapse: collapse;
        }
        .signature-line { 
            width: 220px; 
            border-top: 1px solid #333; 
            margin: 40px auto 8px auto; 
        }
        .official-footer { 
            position: fixed; 
            bottom: 20px; 
            left: 0;
            width: 100%; 
            text-align: center; 
            font-size: 10px; 
            color: #95a5a6;
            border-top: 1px solid #eee;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">COLEGIO {{ strtoupper(config('app.name', 'ESCUELA SYS')) }}</div>
        <div class="subtitle">Boleta Oficial de Calificaciones</div>
        <div style="font-size: 11px; color: #555; margin-top: 3px;">
            Ciclo Escolar: <strong>{{ $boletaData['ciclo'] }}</strong>
        </div>
    </div>

    <div class="info-box">
        <table class="info-table" style="margin-bottom: 0;">
            <tr>
                <td style="width: 20%;"><strong>Alumno:</strong></td>
                <td style="width: 45%;">{{ $alumno->nombre_completo }}</td>
                <td style="width: 15%;"><strong>Matrícula:</strong></td>
                <td style="width: 20%;"><code>{{ $alumno->matricula }}</code></td>
            </tr>
            <tr>
                <td><strong>Grado y Grupo:</strong></td>
                <td>{{ $boletaData['grado'] }} "{{ $boletaData['grupo'] }}"</td>
                <td><strong>CURP:</strong></td>
                <td>{{ $alumno->curp ?: 'N/D' }}</td>
            </tr>
            <tr>
                <td><strong>Profesor(a) Titular:</strong></td>
                <td>{{ $boletaData['maestro'] ?: 'Sin asignar' }}</td>
                <td><strong>Fecha de Emisión:</strong></td>
                <td>{{ date('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="grades-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 40%; padding-left: 12px;">Asignatura</th>
                <th style="width: 15%;">1° Trimestre</th>
                <th style="width: 15%;">2° Trimestre</th>
                <th style="width: 15%;">3° Trimestre</th>
                <th style="width: 15%;">Promedio Final</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boletaData['materias'] as $m)
            <tr>
                <td class="text-left font-bold" style="padding-left: 12px;">{{ $m['materia'] }}</td>
                <td>{{ !is_null($m['p1']) ? number_format($m['p1'], 1) : '-' }}</td>
                <td>{{ !is_null($m['p2']) ? number_format($m['p2'], 1) : '-' }}</td>
                <td>{{ !is_null($m['p3']) ? number_format($m['p3'], 1) : '-' }}</td>
                <td class="bg-promedio">
                    {{ !is_null($m['p_final']) ? number_format($m['p_final'], 1) : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 15px; color: #7f8c8d;">No hay calificaciones registradas para este ciclo escolar.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #eaeded; font-weight: bold;">
                <td class="text-left" style="padding-left: 12px; font-size: 12px; text-transform: uppercase;">Promedio General</td>
                <td colspan="3" style="text-align: right; padding-right: 15px; font-size: 11px; color: #555;">
                    Materias Cursadas: {{ $boletaData['total_materias'] }} | Aprobadas: {{ $boletaData['materias_aprobadas'] }}
                </td>
                <td class="bg-promedio" style="font-size: 13px;">
                    {{ !is_null($boletaData['promedio_general']) ? number_format($boletaData['promedio_general'], 1) : '-' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <strong style="font-size: 12px;">
                    {{ $boletaData['maestro'] ?: 'Profesor(a) Titular' }}
                </strong>
                <div style="font-size: 10.5px; color: #7f8c8d; margin-top: 3px;">Profesor(a) de Planta</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <strong style="font-size: 12px;">Dirección Escolar</strong>
                <div style="font-size: 10.5px; color: #7f8c8d; margin-top: 3px;">Colegio {{ config('app.name', 'Escuela Sys') }}</div>
            </td>
        </tr>
    </table>

    <div class="official-footer">
        Documento oficial emitido por el sistema escolar el {{ date('d/m/Y \a \l\a\s H:i') }} hrs. Válido con firma y sello escolar.
    </div>
</body>
</html>
