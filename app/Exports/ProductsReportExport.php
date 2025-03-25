<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return collect($this->products);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Product Code',
            'Category',
            'Registered Date',
            'Sell Price',
            'Buy Price',
            'Opening Stock',
            'Closing Stock',
            'Sales Quantity',
            'Gross Profit',
            'Nett Profit'
        ];
    }

    public function map($product): array
    {
        return [
            $product['product_name'],
            $product['product_code'],
            $product['product_category'],
            $product['registered_date'],
            $product['sell_price'],
            $product['buy_price'],
            $product['opening_stock'],
            $product['closing_stock'],
            $product['sales_quantity'],
            $product['gross_profit'],
            $product['nett_profit']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set auto size for all columns
        foreach(range('A','K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Header background color
            'A1:K1' => [
                'fill' => [
                    'fillType' => 'solid', 
                    'startColor' => ['rgb' => '084ca4']
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF']
                ]
            ],
            
            // Format currency columns
            'E:K' => [
                'numberFormat' => [
                    'formatCode' => '"Rp"#,##0.00_-'
                ]
            ]
        ];
    }
}