import { useEffect, useState } from 'react';
import apiMember from '../../API/apiMember';
import './CartProduct.css';
import { useSelector, useDispatch } from 'react-redux';
import { removeFromCart, addQuantityCart, removeQuantityCart } from '../../features/cart/Cart';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Breadcrumb from '../../component/Member/Breadcrumb';

function formatPrice(price) {
    if (!price) return '';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

function CartProduct() {
    const cartItems = useSelector((state) => state.cart.items);
    const dispatch = useDispatch();
    const [cartData, setCartData] = useState([]);
    const [totalAmount, setTotalAmount] = useState(0);
    const navigate = useNavigate();
    const fetchCart = async () => {
        try {
            const res = await apiMember.get('/cart');
            const products = Array.isArray(res.data.data) ? res.data.data : [];
            setCartData(products);

            let total = 0;
            products.forEach((item) => {
                const product = item.product;
                const price = product.sale > 0 ? product.price * (1 - product.sale / 100) : product.price;
                total += price * item.quantity;
            });
            setTotalAmount(total);
        } catch (err) {
            console.error(err.response?.data || err.message);
            toast.error('Có lỗi xảy ra khi tải giỏ hàng!');
        }
    };

    useEffect(() => {
        fetchCart();
    }, []);

    // Thêm sản phẩm vào giỏ hàng
    const addToCartBackend = async (product_id, quantity) => {
        try {
            await apiMember.post('/cart', { product_id, quantity });
            fetchCart(); // fetch lại giỏ hàng để cập nhật giao diện
            toast.success('Thêm sản phẩm vào giỏ hàng thành công');
        } catch (err) {
            console.error(err.response?.data || err.message);
            toast.error(err.response?.data?.message || 'Có lỗi khi thêm sản phẩm');
        }
    };

    const removeQuantityCartProduct = (id, qty) => {
        if (qty > 1) dispatch(removeQuantityCart(id));
        else toast.info('Số lượng sản phẩm tối thiểu là 1');
    };

    const addQuantityCartProduct = (id, qty) => dispatch(addQuantityCart(id));

    const removeFromCartProduct = async (id) => {
        dispatch(removeFromCart(id));
        toast.success('Xóa sản phẩm khỏi giỏ hàng thành công');
        fetchCart();
    };

    const handleCheckout = (e) => {
        e.preventDefault();
        if (!cartData.length) toast.warn('Giỏ hàng của bạn đang trống!');
        else navigate('/member/product/checkout');
    };

    const renderData = () => {
        if (!cartData.length) {
            return (
                <tr>
                    <td colSpan="6" style={{ textAlign: 'center', padding: '30px' }}>
                        Giỏ hàng của bạn đang trống.
                    </td>
                </tr>
            );
        }

        return cartData.map((item) => {
            const product = item.product;
            const avatar = Array.isArray(product.image) && product.image.length > 0 ? product.image : ['default.png'];
            const is_on_sale = product.sale > 0;
            const original_price = product.price;
            const new_price = is_on_sale ? original_price * (1 - product.sale / 100) : original_price;

            return (
                <tr key={item.id}>
                    <td className="cart_product" data-label="Sản Phẩm">
                        <div className="product-info">
                            <img src={`http://localhost:8000/${avatar[0]}`} alt={product.name || 'Product'} />
                            <div>
                                <Link to={`/member/home/product/detail/${product.id}`}>{product.name}</Link>
                            </div>
                        </div>
                    </td>
                    <td className="cart_price" data-label="Giá Đơn Vị">
                        {is_on_sale ? (
                            <>
                                <p style={{ color: '#d9534f' }}>{formatPrice(new_price)}</p>
                                <p style={{ textDecoration: 'line-through', fontSize: '12px', color: '#999' }}>
                                    {formatPrice(original_price)}
                                </p>
                            </>
                        ) : (
                            <p>{formatPrice(original_price)}</p>
                        )}
                    </td>
                    <td className="cart_quantity" data-label="Số Lượng">
                        <div className="quantity-control">
                            <button className="quantity-btn" onClick={() => removeQuantityCartProduct(item.id, item.quantity)}>−</button>
                            <input type="text" className="quantity-input" value={item.quantity} readOnly />
                            <button className="quantity-btn" onClick={() => addQuantityCartProduct(item.id, item.quantity)}>+</button>
                        </div>
                    </td>
                    <td className="cart_total" data-label="Tổng Tiền">
                        <p className="cart_price">{formatPrice(new_price * item.quantity)}</p>
                    </td>
                    <td className="cart_delete" data-label="Xóa">
                        <a onClick={() => removeFromCartProduct(item.id)}><i className="fa fa-times" /></a>
                    </td>
                </tr>
            );
        });
    };

    const ecoTax = cartData.length ? 2 : 0;
    const finalTotal = cartData.length ? totalAmount + ecoTax : 0;

    return (
        <section id="cart_items_new">
            <Breadcrumb items={[{ label: 'Giỏ hàng' }]} />
            <h2 className="cart_title">Giỏ Hàng Của Bạn</h2>

            <div className="row">
                <div className="col-md-12">
                    <table className="cart-table">
                        <thead>
                            <tr>
                                <th>Sản Phẩm</th>
                                <th>Giá</th>
                                <th>Số Lượng</th>
                                <th>Tổng</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>{renderData()}</tbody>
                    </table>
                </div>
            </div>

            <div className="row">
                <div className="col-md-5 pull-right">
                    <div className="cart-total-box">
                        <h3>TỔNG CỘNG</h3>
                        <ul>
                            <li>Tạm tính <span>{formatPrice(totalAmount)}</span></li>
                            <li>Eco Tax <span>{formatPrice(ecoTax)}</span></li>
                            <li>Phí Vận Chuyển <span>Free</span></li>
                            <li className="total">Tổng <span>{formatPrice(finalTotal)}</span></li>
                        </ul>
                        <a href="#" className="checkout-btn" onClick={handleCheckout}>Tiến hành thanh toán</a>
                    </div>
                </div>
            </div>
        </section>
    );
}

export default CartProduct;
