<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            text-align: right;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
        }

        .invoice-info {
            margin-bottom: 20px;
        }

        .invoice-info th,
        .invoice-info td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }

        .total {
            font-weight: bold;
            text-align: left;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>الفاتورة رقم: {{ $invoice->invoice_number }}</h1>
        </div>

        <div class="invoice-info">
            <table width="100%">
                <tr>
                    <th>العميل</th>
                    <td>{{ $invoice->customer->name }}</td>
                </tr>
                <tr>
                    <th>العنوان</th>
                    <td>{{ $invoice->customer->address }}</td>
                </tr>
                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <th>المبلغ الإجمالي</th>
                    <td>{{ number_format($invoice->total, 2) }} $</td>
                </tr>
                <tr>
                    <th>حالة الدفع</th>
                    <td>{{ $invoice->status }}</td>
                </tr>
                <tr>
                    <th>الضريبة</th>
                    <td>{{ number_format($invoice->total * 0.15, 2) }} $ (15%)</td>
                </tr>
            </table>
        </div>

        <div class="total">
            <p>إجمالي المبلغ: {{ number_format($invoice->total, 2) }} $</p>
        </div>

        <div class="footer">
            <p>شكراً لطلبك!</p>
        </div>
    </div>

</body>

</html>
