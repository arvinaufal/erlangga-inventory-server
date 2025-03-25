<!DOCTYPE html>
<html>
<head>
    <title>Products Report</title>
    <style>
        @page {
            size: landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
        }
        .date {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: auto;
        }
        th {
            background-color: #084ca4;
            color: white;
            text-align: left;
            padding: 6px;
            white-space: nowrap;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
                width: 100%;
            }
            .table-container {
                overflow-x: visible;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Products Report</div>
        <div class="date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="min-width: 120px;">Product Name</th>
                    <th style="min-width: 80px;">Code</th>
                    <th style="min-width: 80px;">Category</th>
                    <th class="text-right" style="min-width: 90px;">Sell Price</th>
                    <th class="text-right" style="min-width: 90px;">Buy Price</th>
                    <th class="text-right" style="min-width: 70px;">Opening Stock</th>
                    <th class="text-right" style="min-width: 70px;">Closing Stock</th>
                    <th class="text-right" style="min-width: 60px;">Sales Qty</th>
                    <th class="text-right" style="min-width: 90px;">Gross Profit</th>
                    <th class="text-right" style="min-width: 90px;">Nett Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $index => $product)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $product['product_name'] }}</td>
                    <td>{{ $product['product_code'] }}</td>
                    <td>{{ $product['product_category'] }}</td>
                    <td class="text-right">Rp {{ number_format($product['sell_price'], 2) }}</td>
                    <td class="text-right">Rp {{ number_format($product['buy_price'], 2) }}</td>
                    <td class="text-right">{{ $product['opening_stock'] }}</td>
                    <td class="text-right">{{ $product['closing_stock'] }}</td>
                    <td class="text-right">{{ $product['sales_quantity'] }}</td>
                    <td class="text-right">Rp {{ number_format($product['gross_profit'], 2) }}</td>
                    <td class="text-right">Rp {{ number_format($product['nett_profit'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>