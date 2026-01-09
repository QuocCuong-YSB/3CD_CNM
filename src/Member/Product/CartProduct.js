import { useEffect, useState } from 'react';
import apiMember from '../../API/apiMember';
import { Link, useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { setCartDetails } from '../../features/cart/Cart';
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
    const [isLoading, setIsLoading] = useState(true);
    const dispatch = useDispatch();
    const navigate = useNavigate();

    const fetchCart = async () => {
        try {
            const res = await apiMember.get('/cart');
            const carts = Array.isArray(res.data.data) ? res.data.data : [];
            setCartData(carts);

            const reduxCart = {};
            let total = 0;
            carts.forEach((item) => {
                reduxCart[item.product_id] = item.quantity;
                const product = item.product;
                const price =
                    product.sale > 0
                        ? product.price * (1 - product.sale / 100)
                        : product.price;
                total += price * item.quantity;
            });

            dispatch(setCartDetails(reduxCart));
            setTotalAmount(total);
        } catch (err) {
            toast.error('Không thể tải giỏ hàng');
        } finally {
            setIsLoading(false);
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

    const renderCartItems = () => {
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
                    <td data-label="Sản phẩm">
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

                    <td data-label="Giá">
                        <span className="price-text">{formatPrice(price)}</span>
                    </td>

                    <td data-label="Số lượng">
                        <div className="quantity-control">
                            <button onClick={() => decreaseQty(product.id)}>−</button>
                            <input value={item.quantity} readOnly />
                            <button onClick={() => increaseQty(product.id)}>+</button>
                        </div>
                    </td>

                    <td data-label="Tổng">
                        <span className="total-text">{formatPrice(price * item.quantity)}</span>
                    </td>

                    <td data-label="Hành động">
                        <button
                            className="delete-btn"
                            onClick={() => removeItem(product.id)}
                            title="Xóa khỏi giỏ hàng"
                        >
                            <i className="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            );
        });
    };

    if (isLoading) {
        return (
            <div className="cart-container" style={{ padding: '100px 0', textAlign: 'center' }}>
                <div className="spinner-border text-primary" role="status">
                    <span className="sr-only">Đang tải...</span>
                </div>
            </div>
        );
    }

    return (
        <section id="cart_items_new">
            <div className="cart-container">
                <Breadcrumb items={[{ label: 'Giỏ hàng' }]} />
                <h2 className="cart_title">Giỏ Hàng Của Bạn</h2>

                {!cartData.length ? (
                    <div className="empty-cart-container">
                        <div className="empty-cart-icon">
                            <i className="fa fa-shopping-basket"></i>
                        </div>
                        <h3>Giỏ hàng đang trống!</h3>
                        <p>Có vẻ như bạn chưa thêm sản phẩm nào vào giỏ hàng của mình.</p>
                        <Link to="/member/home" className="empty-cart-btn">
                            Khám Phá Sản Phẩm Ngay
                        </Link>
                    </div>
                ) : (
                    <div className="cart-content-wrapper">
                        <div className="cart-main-content">
                            <div className="cart-table-wrapper">
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
                                    <tbody>{renderCartItems()}</tbody>
                                </table>
                            </div>

                            <Link to="/member/home" className="continue-shopping">
                                <i className="fa fa-arrow-left"></i> Tiếp tục mua sắm
                            </Link>
                        </div>

                        <aside className="cart-sidebar">
                            <div className="cart-total-box">
                                <h3>
                                    <i className="fa fa-receipt"></i> Tóm tắt đơn hàng
                                </h3>
                                <ul>
                                    <li>
                                        <span>Tạm tính ({cartData.length} sản phẩm)</span>
                                        <span>{formatPrice(totalAmount)}</span>
                                    </li>
                                    <li>
                                        <span>Phí vận chuyển</span>
                                        <span style={{ color: '#38a169' }}>Miễn phí</span>
                                    </li>
                                    <li className="total">
                                        <span>Tổng cộng</span>
                                        <span>{formatPrice(totalAmount)}</span>
                                    </li>
                                </ul>

                                <button className="checkout-btn" onClick={handleCheckout}>
                                    Tiến hành thanh toán <i className="fa fa-chevron-right" style={{ fontSize: '12px' }}></i>
                                </button>
                            </div>
                        </aside>
                    </div>
                )}
            </div>
        </section>
    );
}

export default CartProduct;
