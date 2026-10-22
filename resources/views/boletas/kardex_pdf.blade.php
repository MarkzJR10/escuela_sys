<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex Escolar - {{ $alumno->matricula }}</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 11px; 
            color: #333;
            line-height: 1.35;
            margin: 0;
            padding: 15px 25px;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #1a365d; 
            padding-bottom: 10px; 
            margin-bottom: 15px; 
        }
        .title { 
            font-size: 18px; 
            font-weight: bold; 
            color: #1a365d; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .subtitle { 
            font-size: 13px; 
            color: #4a5568; 
            font-weight: 600;
            text-transform: uppercase;
        }
        .info-box {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 20px;
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse;
        }
        .info-table td { 
            padding: 4px 6px; 
            vertical-align: top;
            font-size: 11px;
        }
        .grade-section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .grade-header {
            background-color: #2b6cb0;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 10px;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .grades-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px;
        }
        .grades-table th { 
            background-color: #edf2f7; 
            color: #2d3748;
            border: 1px solid #cbd5e0; 
            padding: 6px 4px; 
            text-align: center; 
            font-size: 10px;
            text-transform: uppercase;
        }
        .grades-table td { 
            border: 1px solid #cbd5e0; 
            padding: 6px 8px; 
            text-align: center; 
            font-size: 10.5px;
        }
        .grades-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: bold; }
        .bg-promedio {
            background-color: #f7fafc !important;
            font-weight: bold;
        }
        .footer-table {
            width: 100%; 
            margin-top: 35px; 
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-line { 
            width: 200px; 
            border-top: 1px solid #333; 
            margin: 35px auto 6px auto; 
        }
        .official-footer { 
            position: fixed; 
            bottom: 15px; 
            left: 0;
            width: 100%; 
            text-align: center; 
            font-size: 9.5px; 
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">COLEGIO {{ strtoupper(config('app.name', 'ESCUELA SYS')) }}</div>
        <div class="subtitle">Historial Académico Escolar (Kardex Oficial)</div>
    </div>

    <div class="info-box">
        <table class="info-table">
            <tr>
                <td style="width: 18%;"><strong>Nombre del Alumno:</strong></td>
                <td style="width: 45%;">{{ $alumno->nombre_completo }}</td>
                <td style="width: 18%;"><strong>Matrícula:</strong></td>
                <td style="width: 19%;"><code>{{ $alumno->matricula }}</code></td>
            </tr>
            <tr>
                <td><strong>Grado Actual:</strong></td>
                <td>{{ $alumno->gradoGrupo ? $alumno->gradoGrupo->grado . ' "' . $alumno->gradoGrupo->grupo . '"' : 'Sin asignar' }}</td>
                <td><strong>CURP:</strong></td>
                <td>{{ $alumno->curp ?: 'N/D' }}</td>
            </tr>
            <tr>
                <td><strong>Fecha de Emisión:</strong></td>
                <td>{{ date('d/m/Y') }}</td>
                <td><strong>Promedio Acumulado:</strong></td>
                <td>
                    <strong style="font-size: 13px; color: #1a365d;">
                        {{ !is_null($kardexData['promedio_general_acumulado']) ? number_format($kardexData['promedio_general_acumulado'], 1) : '-' }}
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Secciones agrupadas solamente por grado -->
    @forelse($kardexData['grados'] as $gradoInfo)
        <div class="grade-section">
            <div class="grade-header">
                {{ $gradoInfo['grado_label'] }}
                @if(!empty($gradoInfo['ciclo']))
                    <span style="font-weight: normal; margin-left: 10px;">(Ciclo Escolar: {{ $gradoInfo['ciclo'] }})</span>
                @endif
            </div>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="text-left" style="width: 44%; padding-left: 10px;">Asignatura</th>
                        <th style="width: 14%;">1° Trimestre</th>
                        <th style="width: 14%;">2° Trimestre</th>
                        <th style="width: 14%;">3° Trimestre</th>
                        <th style="width: 14%;">Promedio Final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gradoInfo['materias'] as $m)
                    <tr>
                        <td class="text-left font-bold" style="padding-left: 10px;">{{ $m['materia'] }}</td>
                        <td>{{ !is_null($m['p1']) ? number_format($m['p1'], 1) : '-' }}</td>
                        <td>{{ !is_null($m['p2']) ? number_format($m['p2'], 1) : '-' }}</td>
                        <td>{{ !is_null($m['p3']) ? number_format($m['p3'], 1) : '-' }}</td>
                        <td class="bg-promedio">
                            {{ !is_null($m['promedio']) ? number_format($m['promedio'], 1) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 10px; color: #718096;">No hay materias registradas para este grado.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background-color: #edf2f7; font-weight: bold;">
                        <td class="text-left" style="padding-left: 10px;">Promedio de {{ $gradoInfo['grado_label'] }}</td>
                        <td colspan="3" style="text-align: right; padding-right: 15px; color: #4a5568; font-size: 10px;">
                            Aprobadas: {{ $gradoInfo['materias_aprobadas'] }} | Reprobadas: {{ $gradoInfo['materias_reprobadas'] }}
                        </td>
                        <td class="bg-promedio" style="font-size: 11.5px; color: #1a365d;">
                            {{ !is_null($gradoInfo['promedio_grado']) ? number_format($gradoInfo['promedio_grado'], 1) : '-' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <div style="text-align: center; padding: 30px; color: #718096;">
            No se encontraron calificaciones registradas en el historial de este alumno.
        </div>
    @endforelse

    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <strong style="font-size: 11px;">Control Escolar</strong>
                <div style="font-size: 9.5px; color: #718096; margin-top: 2px;">Departamento de Servicios Escolares</div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <strong style="font-size: 11px;">Dirección Escolar</strong>
                <div style="font-size: 9.5px; color: #718096; margin-top: 2px;">Colegio {{ config('app.name', 'Escuela Sys') }}</div>
            </td>
        </tr>
    </table>

    <div class="official-footer">
        Kardex Histórico Escolar emitido el {{ date('d/m/Y \a \l\a\s H:i') }} hrs. Este documento certifica el avance académico oficial.
    </div>
</body>
</html>
