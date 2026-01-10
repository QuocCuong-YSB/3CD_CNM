import { useEffect, useState, useContext } from 'react';
import MemberCartContext from '../../Context/MemberCartContext';
import apiMember from '../../API/apiMember';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Breadcrumb from '../../component/Member/Breadcrumb';
import Loading from '../../component/Loading';
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
    const [isPageLoading, setIsPageLoading] = useState(true);

    const { fetchCartCount } = useContext(MemberCartContext);
    const navigate = useNavigate();

    const fetchCart = async (showLoading = false) => {
        if (showLoading) setIsPageLoading(true);
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
        } finally {
            if (showLoading) setIsPageLoading(false);
        }
    };

    useEffect(() => {
        fetchCart(true);
    }, []);

    const increaseQty = async (productId) => {
        if (loadingId) return;
        setLoadingId(productId);

        try {
            await apiMember.put(`/cart/product/${productId}`, { quantity: 1 });
            await fetchCart();
            fetchCartCount && fetchCartCount();
        } catch (err) {
            toast.error(err.response?.data?.message || 'Lỗi tăng số lượng');
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
            toast.error(err.response?.data?.message || 'Lỗi giảm số lượng');
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

    const handleCheckout = (e) => {
        e.preventDefault();
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

            const is_on_sale = product.sale > 0;
            const original_price = product.price;
            const new_price = is_on_sale
                ? original_price * (1 - product.sale / 100)
                : original_price;

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

                    <td>
                        {is_on_sale ? (
                            <>
                                <p style={{ color: '#d9534f' }}>{formatPrice(new_price)}</p>
                                <p
                                    style={{
                                        textDecoration: 'line-through',
                                        fontSize: '12px',
                                        color: '#999',
                                    }}
                                >
                                    {formatPrice(original_price)}
                                </p>
                            </>
                        ) : (
                            <p>{formatPrice(original_price)}</p>
                        )}
                    </td>

                    <td>{product.quantity}</td>

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

                    <td>{formatPrice(new_price * item.quantity)}</td>

                    <td>
                        <a
                            className="delete-btn"
                            disabled={loadingId === product.id}
                            onClick={() => removeItem(product.id)}
                        >
                            <i className="fa fa-times" />
                        </a>
                    </td>
                </tr>
            );
        });
    };

    const ecoTax = cartData.length ? 2 : 0;
    const finalTotal = cartData.length ? totalAmount + ecoTax : 0;

    return (
        <section className="cart-product-page">
            {isPageLoading && <Loading />}
            <div className="container">
                <Breadcrumb items={[{ label: 'Giỏ hàng' }]} />
                <h2 className="cart_title">Giỏ Hàng Của Bạn</h2>

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

                <div className="row">
                    <div className="col-md-5 pull-right">
                        <div className="cart-total-box">
                            <h3>TỔNG CỘNG</h3>
                            <ul>
                                <li>
                                    Tạm tính <span>{formatPrice(totalAmount)}</span>
                                </li>
                                <li>
                                    Eco Tax <span>{formatPrice(ecoTax)}</span>
                                </li>
                                <li>
                                    Phí vận chuyển <span>Free</span>
                                </li>
                                <li className="total">
                                    Tổng cộng <span>{formatPrice(finalTotal)}</span>
                                </li>
                            </ul>

                            <a href="#" className="checkout-btn" onClick={handleCheckout}>
                                Tiến hành thanh toán
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

export default CartProduct;

