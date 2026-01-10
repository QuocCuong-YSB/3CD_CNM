<!DOCTYPE html>
<html>

<head>
    <title>Xác nhận đơn hàng</title>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 10px;
        }

        .header {
            background: #3498db;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }

        .details {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Cảm ơn bạn đã đặt hàng!</h2>
        </div>
        <div class="details">
            <p>Chào <strong>{{ $orderData['user']['name'] }}</strong>,</p>
            <p>Đơn hàng của bạn đã được tiếp nhận thành công.</p>
            <p><strong>Mã đơn hàng:</strong> {{ $orderData['orderCode'] }}</p>
            <p><strong>Phương thức thanh toán:</strong> {{ $orderData['paymentMethod'] }}</p>

            <h3>Chi tiết sản phẩm:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderData['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>{{ number_format($item['price'], 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="text-align: right; font-weight: bold; font-size: 1.2em;">Tổng cộng:
                {{ number_format($orderData['total'], 0, ',', '.') }} đ</p>
        </div>
        <div class="footer">
            <p>Đây là email thông báo tự động. Vui lòng không trả lời email này.</p>
        </div>
    </div>
</body>

</html>