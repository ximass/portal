<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peças - Pedido {{ $orderNumber }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .page {
            padding: 15px;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            page-break-inside: avoid;
            max-height: 190mm;
        }
        
        .page:not(:first-child) {
            page-break-before: always;
        }
        
        .header {
            border: 2px solid #000;
            margin-bottom: 5px;
            padding: 4px 8px;
            text-align: center;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .company-details {
            font-size: 8px;
        }
        
        .order-info {
            background-color: #f5f5f5;
            border: 1px solid #000;
            padding: 3px 6px;
            margin-bottom: 5px;
            font-size: 8px;
        }
        
        .order-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-info-table td {
            padding: 2px 6px;
            border: 1px solid #ccc;
        }
        
        .order-info-table .label {
            font-weight: bold;
            width: 15%;
            background-color: #e8e8e8;
        }
        
        .order-info-table .value {
            width: 35%;
        }
        
        .part-header {
            background-color: #e6f2ff;
            border: 1px solid #000;
            padding: 3px 6px;
            margin-bottom: 5px;
            text-align: center;
        }
        
        .part-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1px;
        }
        
        .part-number {
            font-size: 8px;
            color: #666;
        }
        
        .content-wrapper {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        
        .image-section {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background-color: #fafafa;
            width: 80%;
            display: table-cell;
            vertical-align: middle;
            overflow: hidden;
            position: relative;
        }
        
        .image-section img {
            max-width: 100%;
            max-height: 130mm;
            object-fit: contain;
        }
        
        .image-section img.rotated {
            transform: rotate(90deg);
            transform-origin: center center;
            max-width: 130mm;
            max-height: 130mm;
        }
        
        .no-image {
            color: #999;
            font-style: italic;
        }
        
        .info-section {
            width: 20%;
            display: table-cell;
            vertical-align: top;
            padding-left: 10px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        
        .info-table th,
        .info-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 8px;
            text-align: left;
        }
        
        .info-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 40%;
        }
        
        .info-table-two-col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        
        .info-table-two-col th,
        .info-table-two-col td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 8px;
            text-align: left;
        }
        
        .info-table-two-col th {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 20%;
        }
        
        .info-group-title {
            background-color: #e0e0e0;
            font-weight: bold;
            padding: 2px 4px;
            margin-top: 3px;
            margin-bottom: 2px;
            border: 1px solid #000;
            font-size: 9px;
        }
        
        .processes-section {
            margin-top: 4px;
            border: 1px solid #000;
            padding: 4px 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        
        .processes-title {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 9px;
        }
        
        .processes-list {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
        
        .processes-list li {
            padding: 2px 0;
            border-bottom: 1px solid #eee;
            font-size: 8px;
            line-height: 1.2;
        }
        
        .processes-list li:last-child {
            border-bottom: none;
        }
        
        @page {
            margin: 10mm;
            size: A4 landscape;
        }
    </style>
</head>
<body>
    @foreach($parts as $index => $part)
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company-name">ENGFRIG MÁQUINAS E EQUIPAMENTOS LTDA</div>
            <div class="company-details">E-mail: engfrig@engfrig.ind.br | site: www.engfrig.ind.br | CNPJ: 30.783.030/0001-82</div>
        </div>

        <!-- Order Info -->
        <div class="order-info">
            <table class="order-info-table">
                <tr>
                    <td class="label">Data:</td>
                    <td class="value">{{ $createdDate }}</td>
                    <td class="label">Pedido nº:</td>
                    <td class="value">{{ $orderNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Cliente:</td>
                    <td class="value" colspan="3">{{ $customerName }}</td>
                </tr>
                <tr>
                    <td class="label">Prazo de entrega:</td>
                    <td class="value">{{ $deliveryDate }}</td>
                    <td class="label">Peça:</td>
                    <td class="value">{{ $index + 1 }} de {{ count($parts) }}</td>
                </tr>
            </table>
        </div>

        <!-- Part Header -->
        <div class="part-header">
            <div class="part-title">{{ $part->obs ?? $part->title ?? 'Peça sem título' }}</div>
            <div class="part-number">Peça #{{ $index + 1 }}</div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Image Section (Larger) -->
            <div class="image-section">
                @if($part->content)
                    <img src="{{ public_path($part->content) }}" alt="Imagem da peça" class="{{ $part->is_vertical ?? false ? 'rotated' : '' }}">
                @else
                    <div class="no-image">Sem imagem disponível</div>
                @endif
            </div>

            <!-- Info Section (Smaller) -->
            <div class="info-section">
                <!-- Basic Info -->
                <table class="info-table">
                    <tr>
                        <th>Tipo</th>
                        <td>{{ $partTypes[$part->type] ?? $part->type ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Quantidade</th>
                        <td>{{ $part->quantity ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Unidade</th>
                        <td>{{ $part->unit === 'piece' ? 'Peça' : ($part->unit === 'kg' ? 'KG' : 'Peça') }}</td>
                    </tr>
                </table>

                <!-- Type Specific Info -->
                @if($part->type === 'material' || $part->type === 'sheet')
                    <div class="info-group-title">Especificações de {{ $part->type === 'sheet' ? 'Chapa' : 'Material' }}</div>
                    <table class="info-table">
                        <tr>
                            <th>Material</th>
                            <td>{{ $part->material->name ?? '-' }}</td>
                        </tr>
                        @if($part->type === 'sheet')
                        <tr>
                            <th>Chapa</th>
                            <td>{{ $part->sheet->name ?? '-' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Largura</th>
                            <td>{{ $part->width ?? '-' }} mm</td>
                        </tr>
                        <tr>
                            <th>Comprimento</th>
                            <td>{{ $part->length ?? '-' }} mm</td>
                        </tr>
                    </table>
                @elseif($part->type === 'bar')
                    <div class="info-group-title">Especificações de Barra</div>
                    <table class="info-table">
                        <tr>
                            <th>Barra</th>
                            <td>{{ $part->bar->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Comprimento</th>
                            <td>{{ $part->length ?? '-' }} mm</td>
                        </tr>
                    </table>
                @elseif($part->type === 'component')
                    <div class="info-group-title">Especificações de Componente</div>
                    <table class="info-table">
                        <tr>
                            <th>Componente</th>
                            <td>{{ $part->component->name ?? '-' }}</td>
                        </tr>
                    </table>
                @endif

                <!-- Weight Info -->
                <div class="info-group-title">Pesos</div>
                <table class="info-table-two-col">
                    <tr>
                        <th>Líq. unit.</th>
                        <td>{{ number_format($part->unit_net_weight ?? 0, 2, ',', '.') }} KG</td>
                        <th>Bruto unit.</th>
                        <td>{{ number_format($part->unit_gross_weight ?? 0, 2, ',', '.') }} KG</td>
                    </tr>
                    <tr>
                        <th>Líq. total</th>
                        <td>{{ number_format($part->net_weight ?? 0, 2, ',', '.') }} KG</td>
                        <th>Bruto total</th>
                        <td>{{ number_format($part->gross_weight ?? 0, 2, ',', '.') }} KG</td>
                    </tr>
                </table>

                <!-- Processes Section -->
                @if($part->processes && count($part->processes) > 0)
                    <div class="processes-section">
                        <div class="processes-title">Processos:</div>
                        <ul class="processes-list">
                            @foreach($part->processes as $process)
                            <li>
                                <strong>{{ $process->title }}</strong>
                                @if($process->pivot && $process->pivot->time)
                                    - Tempo: {{ $process->pivot->time }} min
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
