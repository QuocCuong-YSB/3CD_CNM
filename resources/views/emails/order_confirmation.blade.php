<!DOCTYPE html>
<html>

<head>
    <title>Xác nhận đơn hàng</title>
</head>

<body>
    <h1>Cảm ơn bạn đã đặt hàng tại Shop Đồ Gia Dụng!</h1>
    <p>Chào {{ $order->name }},</p>
    <p>Đơn hàng <strong>#{{ $order->order_code }}</strong> của bạn đã được tiếp nhận và đang chờ xử lý.</p>

    <h3>Thông tin đơn hàng:</h3>
    <ul>
        <li>Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</li>
        <li>Phương thức thanh toán: {{ strtoupper($order->payment_method) }}</li>
        <li>Địa chỉ giao hàng: {{ $order->address }}</li>
        <li>Số điện thoại: {{ $order->phone }}</li>
    </ul>

    <h3>Chi tiết sản phẩm:</h3>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" align="right">Tạm tính:</td>
                <td>{{ number_format($order->total_amount + $order->discount - $order->eco_tax, 0, ',', '.') }}đ</td>
            </tr>
            @if($order->discount > 0)
                <tr>
                    <td colspan="3" align="right">Giảm giá ({{ $order->voucher_code }}):</td>
                    <td>-{{ number_format($order->discount, 0, ',', '.') }}đ</td>
                </tr>
            @endif
            <tr>
                <td colspan="3" align="right">Thuế Eco:</td>
                <td>{{ number_format($order->eco_tax, 0, ',', '.') }}đ</td>
            </tr>
            <tr>
                <td colspan="3" align="right"><strong>Tổng cộng:</strong></td>
                <td><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></td>
            </tr>
        </tfoot>
    </table>

    <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua email: ngovanduong.dev@gmail.com</p>
    <p>Trân trọng,<br>Shop Đồ Gia Dụng</p>
</body>

</html>