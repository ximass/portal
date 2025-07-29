<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento {{ $orderNumber }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            padding-bottom: 120px;
            color: #333;
        }
        
        .header {
            border: 2px solid #000;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            padding: 10px;
        }
        
        .logo {
            width: 80px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }
        
        .logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .company-info {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }
        
        .company-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .company-details {
            font-size: 9px;
            line-height: 1.1;
        }
        
        .document-info {
            border-left: 1px solid #000;
            padding-left: 10px;
            width: 120px;
        }
        
        .document-info div {
            margin-bottom: 3px;
            font-size: 9px;
        }
        
        .customer-section {
            border: 1px solid #000;
            margin-bottom: 20px;
            padding: 10px;
        }
        
        .customer-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .customer-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .customer-row {
            display: table-row;
        }
        
        .customer-cell {
            display: table-cell;
            border: 1px solid #000;
            padding: 5px;
            font-size: 10px;
        }
        
        .customer-label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 80px;
        }
        
        .greeting {
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 10px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #e6f2ff;
            font-weight: bold;
            text-align: center;
        }
        
        .item-header {
            background-color: #cce6ff;
            font-weight: bold;
        }
        
        .item-image {
            width: 80px;
            text-align: center;
            vertical-align: middle;
        }
        
        .item-image img {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }
        
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .totals-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 11px;
        }
        
        .totals-label {
            font-weight: bold;
            text-align: right;
            width: 60%;
        }
        
        .totals-value {
            text-align: right;
            width: 40%;
        }
        
        .conditions {
            border-top: 2px dashed #000;
            padding-top: 20px;
            font-size: 11px;
        }
        
        .conditions-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .conditions-grid {
            display: table;
            width: 100%;
        }
        
        .conditions-row {
            display: table-row;
        }
        
        .conditions-cell {
            display: table-cell;
            padding: 3px 10px 3px 0;
        }
        
        .conditions-label {
            font-weight: bold;
            width: 150px;
        }
        
        .footer {
            border-top: 2px dashed #000;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            padding: 20px;
            text-align: center;
            font-size: 10px;
            background-color: white;
            box-sizing: border-box;
        }
        
        .footer-company {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="{{ public_path('images/logo_engfrig.png') }}" alt="EngFrig Logo">
            </div>
            <div class="company-info">
                <div class="company-name">ENGFRIG MÁQUINAS E EQUIPAMENTOS LTDA</div>
                <div class="company-details">
                    E-mail: engfrig@engfrig.ind.br | site: www.engfrig.ind.br | CNPJ: 30.785.030/0001-82
                </div>
            </div>
            <div class="document-info">
                <div><strong>Criado em:</strong> {{ $createdDate }}</div>
                <div><strong>Orçamento nº:</strong> {{ $orderNumber }}</div>
            </div>
        </div>
    </div>

    <!-- Customer Data -->
    <div class="customer-section">
        <div class="customer-title">Dados do destinatário</div>
        <div class="customer-grid">
            <div class="customer-row">
                <div class="customer-cell customer-label">Cliente:</div>
                <div class="customer-cell">{{ $order->customer->name ?? 'N/A' }}</div>
                <div class="customer-cell customer-label">CNPJ:</div>
                <div class="customer-cell">{{ $order->customer->cnpj ?? 'N/A' }}</div>
            </div>
            <div class="customer-row">
                <div class="customer-cell customer-label">Telefone:</div>
                <div class="customer-cell">{{ $order->customer->phone ?? 'N/A' }}</div>
                <div class="customer-cell customer-label">E-mail:</div>
                <div class="customer-cell">{{ $order->customer->email ?? 'N/A' }}</div>
            </div>
            <div class="customer-row">
                <div class="customer-cell customer-label">Endereço:</div>
                <div class="customer-cell">{{ $order->customer->address ?? 'N/A' }}</div>
                <div class="customer-cell customer-label">Estado:</div>
                <div class="customer-cell">{{ $order->customer?->state?->abbreviation ?? '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    @foreach($order->sets as $setIndex => $set)
        @foreach($set->setParts as $partIndex => $part)
        <table class="items-table">
            <thead>
                <tr>
                    <th class="item-header" colspan="7">Conjunto {{ str_pad($setIndex + 1, 2, '0', STR_PAD_LEFT) }}: {{ $part->title }}</th>
                </tr>
                <tr>
                    <th colspan="2">NCM: {{ $part->ncm->code ?? '-' }}</th>
                    <th colspan="3">Referência EngFrig: {{ $part->reference ?? '-' }}</th>
                    <th colspan="2">Referência Cliente: {{ $part->obs ?? '-' }}</th>
                </tr>
                <tr>
                    <th>Imagem</th>
                    <th>Quantidade</th>
                    <th>Unidade de medida</th>
                    <th>Valor unitário</th>
                    <th>Valor total</th>
                    <th>IPI</th>
                    <th>ICMS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="item-image">
                        @if($part->secondary_content)
                            <img src="{{ public_path($part->secondary_content) }}" alt="Imagem do item">
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format($part->quantity ?? 0, 0) }}</td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: right;">R$ {{ number_format($part->unit_value ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($part->final_value ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ $part->ncm ? number_format($part->ncm->ipi, 2, ',', '.') : '0,00' }}%</td>
                    <td style="text-align: right;">{{ $order->customer?->state?->icms ? number_format($order->customer->state->icms, 2, ',', '.') : '0,00' }}%</td>
                </tr>
            </tbody>
        </table>
        @endforeach
    @endforeach

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td class="totals-label">Total geral (R$):</td>
            <td class="totals-value">R$ {{ number_format($totalGeral ?? 0, 2, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Commercial Conditions -->
    <div class="conditions">
        <div class="conditions-title">Condições comerciais:</div>
        <div class="conditions-grid">
            <div class="conditions-row">
                <div class="conditions-cell conditions-label">Frete:</div>
                <div class="conditions-cell">{{ $order->delivery_type ?? '-' }}</div>
            </div>
            <div class="conditions-row">
                <div class="conditions-cell conditions-label">Prazo de entrega:</div>
                <div class="conditions-cell">{{ $order->estimated_delivery_date ? $order->estimated_delivery_date : '-' }}</div>
            </div>
            <div class="conditions-row">
                <div class="conditions-cell conditions-label">Pagamento:</div>
                <div class="conditions-cell">{{ $order->payment_obs ? $order->payment_obs : '-' }}</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-company">ENGFRIG MÁQUINAS E EQUIPAMENTOS LTDA</div>
        <div>Rua Bom Jesus da Serra, N°76-E, CEP 89.810-220 - Chapecó - Santa Catarina</div>
        <div>Fone: (49) 3199-3189 - (49) 98880-6943 - CNPJ ENGFRIG:30.783.030/0001-82</div>
    </div>
</body>
</html>
