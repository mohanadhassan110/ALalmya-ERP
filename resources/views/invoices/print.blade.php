<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $invoice->invoice_number }} - سنتر العالمية</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f3f4f6;
            color: #111827;
            padding: 20px;
        }
        .print-container {
            max-width: 800px; /* A4/A5 width suitable for screen preview */
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 5px;
            font-weight: 700;
        }
        .header p {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-details div {
            flex: 1;
        }
        .invoice-details h3 {
            font-size: 16px;
            color: #374151;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            display: inline-block;
        }
        .invoice-details p {
            font-size: 14px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            text-align: right;
            font-size: 14px;
        }
        th {
            background-color: #f9fafb;
            font-weight: 700;
            color: #1f2937;
        }
        .totals-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .totals {
            width: 50%;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .totals-row.grand-total {
            background-color: #f3f4f6;
            font-weight: 700;
            font-size: 16px;
            color: #111827;
            border-bottom: 2px solid #1f2937;
            border-top: 2px solid #1f2937;
        }
        .totals-row.paid {
            color: #059669;
            font-weight: 600;
        }
        .totals-row.remaining {
            color: #dc2626;
            font-weight: 600;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px dashed #d1d5db;
            padding-top: 15px;
        }
        
        .print-btn-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }
        .print-btn {
            background-color: #1f2937;
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .print-btn:hover {
            background-color: #374151;
        }

        /* Print Media Query */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
                margin: 0;
            }
            .print-btn-wrapper {
                display: none;
            }
            
            /* Add optimizations for 80mm thermal printers if detected by page size */
            @page {
                size: auto;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-wrapper no-print">
        <button class="print-btn" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            طباعة الفاتورة
        </button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <h1>سنتر العالمية للمفروشات</h1>
            <p>لجميع أنواع المفروشات - بيع جملة وتجزئة</p>
            <p>العنوان: [أدخل عنوان المحل هنا] | هاتف: [أدخل رقم الهاتف]</p>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div>
                <h3>تفاصيل الفاتورة</h3>
                <p><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>التاريخ:</strong> {{ $invoice->created_at->format('Y-m-d') }}</p>
                <p><strong>الوقت:</strong> {{ $invoice->created_at->format('h:i A') }}</p>
                <p><strong>نوع الفاتورة:</strong> {{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}</p>
            </div>
            <div>
                <h3>بيانات العميل</h3>
                @if($invoice->customer)
                    <p><strong>الاسم:</strong> {{ $invoice->customer->name }}</p>
                    <p><strong>الهاتف:</strong> {{ $invoice->customer->phone ?? '—' }}</p>
                    <p><strong>الرصيد الكلي:</strong> 
                        @if($invoice->customer->balance > 0)
                            <span style="color: #dc2626;" dir="rtl">عليه: {{ number_format($invoice->customer->balance, 2) }} ج.م</span>
                        @elseif($invoice->customer->balance < 0)
                            <span style="color: #059669;" dir="rtl">له: {{ number_format(abs($invoice->customer->balance), 2) }} ج.م</span>
                        @else
                            صفر
                        @endif
                    </p>
                @else
                    <p>عميل نقدي (تجزئة)</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">المنتج</th>
                    <th style="width: 15%;">الكمية</th>
                    <th style="width: 15%;">سعر الوحدة</th>
                    <th style="width: 20%;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $item->product_name }}</strong></td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->selling_price, 2) }}</td>
                    <td>{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-container">
            <div class="totals">
                <div class="totals-row">
                    <span>المجموع الفرعي:</span>
                    <span>{{ number_format($invoice->subtotal, 2) }} ج.م</span>
                </div>
                
                @if($invoice->discount > 0)
                <div class="totals-row" style="color: #dc2626;">
                    <span>الخصم:</span>
                    <span>-{{ number_format($invoice->discount, 2) }} ج.م</span>
                </div>
                @endif
                
                <div class="totals-row grand-total">
                    <span>الإجمالي المستحق:</span>
                    <span>{{ number_format($invoice->total, 2) }} ج.م</span>
                </div>
                
                <div class="totals-row paid">
                    <span>المبلغ المدفوع:</span>
                    <span>{{ number_format($invoice->paid, 2) }} ج.م</span>
                </div>
                
                @if($invoice->remaining > 0)
                <div class="totals-row remaining">
                    <span>المبلغ المتبقي:</span>
                    <span>{{ number_format($invoice->remaining, 2) }} ج.م</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div style="margin-top: 30px; font-size: 14px;">
            <p><strong>ملاحظات:</strong> {{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>شكراً لتعاملكم مع سنتر العالمية للمفروشات</p>
            <p>تم إصدار هذه الفاتورة من النظام</p>
        </div>
    </div>

    <!-- Auto-trigger print when the page loads -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500); // 500ms delay to ensure styles and fonts are loaded
        };
    </script>
</body>
</html>
