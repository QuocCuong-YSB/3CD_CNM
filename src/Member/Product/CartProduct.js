import { useEffect, useState, useContext } from 'react';
import MemberCartContext from '../../Context/MemberCartContext';
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
    const [loadingId, setLoadingId] = useState(null);
    const { fetchCartCount } = useContext(MemberCartContext);

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
        } catch {
            toast.error('Không thể tải giỏ hàng');
        }
    };

    useEffect(() => {
        fetchCart();
    }, []);

    const increaseQty = async (productId) => {
        if (loadingId) return;
        setLoadingId(productId);
        try {
            await apiMember.put(`/cart/product/${productId}`, { quantity: 1 });
            await fetchCart();
            fetchCartCount && fetchCartCount();
        } catch (err) {
            toast.error(err.response?.data?.message);
        } finally {
            setLoadingId(null);
        }
    };

    const decreaseQty = async (productId) => {
        if (loadingId) return;
        setLoadingId(productId);
        try {
            await apiMember.put(`/cart/product/${productId}`, { quantity: -1 });
            await fetchCart();
            fetchCartCount && fetchCartCount();
        } catch (err) {
            toast.error(err.response?.data?.message);
        } finally {
            setLoadingId(null);
        }
    };

    const removeItem = async (productId) => {
        if (loadingId) return;
        setLoadingId(productId);
        try {
            await apiMember.delete(`/cart/product/${productId}`);
            toast.success('Đã xóa sản phẩm khỏi giỏ hàng');
            await fetchCart();
            fetchCartCount && fetchCartCount();
        } catch {
            toast.error('Xóa sản phẩm thất bại');
        } finally {
            setLoadingId(null);
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
                    <td colSpan="6" style={{ textAlign: 'center', padding: 30 }}>
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

                    {/* CỘT KHO */}
                    <td>{product.quantity}</td>

                    {/* CỘT SỐ LƯỢNG */}
                    <td>
                        <div className="quantity-control">
                            <button
                                className="quantity-btn"
                                disabled={item.quantity <= 1 || loadingId === product.id}
                                onClick={() => decreaseQty(product.id)}
                            >
                                −
                            </button>

                            <input
                                className="quantity-input"
                                value={item.quantity}
                                readOnly
                            />

                            <button
                                className="quantity-btn"
                                disabled={
                                    item.quantity >= Number(product.quantity) ||
                                    loadingId === product.id
                                }
                                onClick={() => increaseQty(product.id)}
                            >
                                +
                            </button>
                        </div>
                    </td>

                    <td>{formatPrice(price * item.quantity)}</td>

                    <td>
                        <button
                            className="delete-btn"
                            disabled={loadingId === product.id}
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
                        <th>Kho</th>
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
