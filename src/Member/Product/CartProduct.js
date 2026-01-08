import { useEffect, useState } from 'react';
import apiMember from '../../API/apiMember';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Breadcrumb from '../../component/Member/Breadcrumb';
import './CartProduct.css';

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(price || 0);
}

function CartProduct() {
    const [cartData, setCartData] = useState([]);
    const [totalAmount, setTotalAmount] = useState(0);
    const navigate = useNavigate();

    const fetchCart = async () => {
        try {
            const res = await apiMember.get('/cart');
            const carts = Array.isArray(res.data.data) ? res.data.data : [];
            setCartData(carts);

            let total = 0;
            carts.forEach((item) => {
                const product = item.product;
                const price =
                    product.sale > 0
                        ? product.price * (1 - product.sale / 100)
                        : product.price;
                total += price * item.quantity;
            });

            setTotalAmount(total);
        } catch (err) {
            toast.error('Không thể tải giỏ hàng');
        }
    };

    useEffect(() => {
        fetchCart();
    }, []);

    const increaseQty = async (productId) => {
        try {
            await apiMember.put(`/cart/product/${productId}`, { quantity: 1 });
            fetchCart();
        } catch (err) {
            toast.error(err.response?.data?.message);
        }
    };

    const decreaseQty = async (productId) => {
        try {
            await apiMember.put(`/cart/product/${productId}`, { quantity: -1 });
            fetchCart();
        } catch (err) {
            toast.error(err.response?.data?.message);
        }
    };

    const removeItem = async (productId) => {
        try {
            await apiMember.delete(`/cart/product/${productId}`);
            toast.success('Đã xóa sản phẩm khỏi giỏ hàng');
            fetchCart();
        } catch {
            toast.error('Xóa sản phẩm thất bại');
        }
    };

    const handleCheckout = () => {
        if (!cartData.length) {
            toast.warn('Giỏ hàng đang trống');
            return;
        }
        navigate('/member/product/checkout');
    };

    const renderCart = () => {
        if (!cartData.length) {
            return (
                <tr>
                    <td colSpan="5" style={{ textAlign: 'center', padding: 30 }}>
                        Giỏ hàng trống
                    </td>
                </tr>
            );
        }

        return cartData.map((item) => {
            const product = item.product;
            const image =
                Array.isArray(product.image) && product.image.length
                    ? product.image[0]
                    : 'default.png';

            const price =
                product.sale > 0
                    ? product.price * (1 - product.sale / 100)
                    : product.price;

            return (
                <tr key={product.id}>
                    <td>
                        <div className="product-info">
                            <img
                                src={`http://localhost:8000/${image}`}
                                alt={product.name}
                            />
                            <Link to={`/member/home/product/detail/${product.id}`}>
                                {product.name}
                            </Link>
                        </div>
                    </td>

                    <td>{formatPrice(price)}</td>

                    <td>
                        <div className="quantity-control">
                            <button onClick={() => decreaseQty(product.id)}>−</button>
                            <input value={item.quantity} readOnly />
                            <button onClick={() => increaseQty(product.id)}>+</button>
                        </div>
                    </td>

                    <td>{formatPrice(price * item.quantity)}</td>

                    <td>
                        <button
                            className="delete-btn"
                            onClick={() => removeItem(product.id)}
                        >
                            ✕
                        </button>
                    </td>
                </tr>
            );
        });
    };

    return (
        <section id="cart_items_new">
            <Breadcrumb items={[{ label: 'Giỏ hàng' }]} />
            <h2 className="cart_title">Giỏ Hàng</h2>

            <table className="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>{renderCart()}</tbody>
            </table>

            <div className="cart-total-box">
                <ul>
                    <li>
                        Tạm tính <span>{formatPrice(totalAmount)}</span>
                    </li>
                    <li>
                        Phí vận chuyển <span>Free</span>
                    </li>
                    <li className="total">
                        Tổng cộng <span>{formatPrice(totalAmount)}</span>
                    </li>
                </ul>

                <button className="checkout-btn" onClick={handleCheckout}>
                    Thanh toán
                </button>
            </div>
        </section>
    );
}

export default CartProduct;
