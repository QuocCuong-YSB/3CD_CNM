import { useParams } from 'react-router-dom';
import './ProductDetail.css';
import { addToCart } from '../../features/cart/Cart';
import { useContext, useEffect, useState } from 'react';
import apiMember from '../../API/apiMember';
import { useDispatch } from 'react-redux';
import { toast } from 'react-toastify';
import Breadcrumb from '../../component/Member/Breadcrumb';
import MemberCartContext from '../../Context/MemberCartContext';

function formatPrice(price) {
    if (!price) return '';
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(price);
}

function ProductDetail() {
    const { id } = useParams();
    const [input, SetInput] = useState({});
    const [quantity, SetQuantity] = useState(1);
    const [selectedImg, SetselectedImg] = useState([]);
    const dispatch = useDispatch();

    const { fetchCartCount } = useContext(MemberCartContext);

    const [canReview, setCanReview] = useState(false);
    const [reviewMessage, setReviewMessage] = useState('');

    const [completedOrders, setCompletedOrders] = useState([]);
    const [selectedOrderCode, setSelectedOrderCode] = useState("");

    const [reviews, setReviews] = useState([]);
    const [filteredReviews, setFilteredReviews] = useState([]);
    const [filterType, setFilterType] = useState('all');

    const [commentText, setCommentText] = useState('');
    const [currentRating, setCurrentRating] = useState(0);
    const [hoverRating, setHoverRating] = useState(0);
    const [showReviewForm, setShowReviewForm] = useState(false);

    useEffect(() => {
        apiMember.get('/product/show/' + id).then((res) => {
            const productData = res.data;
            SetInput(productData);
            if (productData?.image) {
                let avatar = [];
                try {
                    avatar = typeof productData.image === 'string' ? JSON.parse(productData.image) : productData.image;
                } catch (e) {
                    avatar = Array.isArray(productData.image) ? productData.image : [productData.image];
                }
                SetselectedImg(avatar[0]);
            }
        });

        apiMember
            .get(`/product/${id}/reviews`)
            .then((res) => {
                const data = Array.isArray(res.data.data) ? res.data.data : [];
                console.log(data);

                setReviews(data);
                setFilteredReviews(data);
            })
            .catch((err) => console.log(err));
    }, [id]);

    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!token) {
            setCanReview(false);
            setReviewMessage('Vui lòng đăng nhập để đánh giá sản phẩm');
            return;
        }

        apiMember
            .get(`/product/${id}/can-review`, {
                headers: { Authorization: `Bearer ${token}` },
            })
            .then((res) => {
                if (res.data.can_review) {
                    setCanReview(true);
                    setReviewMessage('');
                } else {
                    setCanReview(false);
                    switch (res.data.reason) {
                        case 'not_purchased':
                            setReviewMessage('Bạn chưa mua sản phẩm này nên không thể đánh giá');
                            break;
                        case 'already_reviewed':
                            setReviewMessage('Bạn đã đánh giá sản phẩm này rồi');
                            break;
                        default:
                            setReviewMessage('Bạn không thể đánh giá sản phẩm này');
                    }
                }
            })
            .catch(() => {
                setCanReview(false);
                setReviewMessage('Không thể kiểm tra quyền đánh giá');
            });
    }, [id]);

    useEffect(() => {
        if (filterType === 'all') {
            setFilteredReviews(reviews);
        } else if (filterType === 'image') {
            setFilteredReviews(reviews);
        } else {
            const stars = parseInt(filterType);
            setFilteredReviews(reviews.filter((r) => r.rating === stars));
        }
    }, [filterType, reviews]);

    async function AddProductToCart() {
        const token = localStorage.getItem('token');
        if (!token) {
            toast.warn('Vui lòng đăng nhập');
            return;
        }

        const stock = input.quantity || input.quality || 0;
        if (stock <= 0) {
            toast.error('Sản phẩm này đã hết hàng');
            return;
        }

        if (quantity <= 0) {
            toast.warn('Vui lòng chọn số lượng lớn hơn 0');
            return;
        }

        try {
            await apiMember.post('/cart', {
                product_id: id,
                quantity: quantity
            });

            dispatch(addToCart({ id, qty: quantity }));
            toast.success(`Đã thêm ${quantity} sản phẩm vào giỏ hàng`);
            await fetchCartCount();
        } catch (err) {
            console.error(err.response?.data || err.message);
            toast.error('Thêm sản phẩm vào giỏ thất bại');
        }
    }

    function AddQuantity() {
        const maxStock = input.quantity || input.quantity || 0;
        if (quantity < maxStock) {
            SetQuantity((quantity) => quantity + 1);
        }
    }
    function DeleteQuantity() {
        SetQuantity(quantity > 1 ? (quantity) => quantity - 1 : 1);
    }
    function SetselectedImgTop(src) {
        SetselectedImg(src);
    }

    const handleReviewSubmit = () => {
        const token = localStorage.getItem('token');
        if (!token) {
            toast.warn('Vui lòng đăng nhập để viết đánh giá');
            return;
        }
        if (currentRating === 0) {
            toast.warn('Vui lòng chọn số sao đánh giá');
            return;
        }
        if (!commentText.trim()) {
            toast.warn('Vui lòng nhập nội dung đánh giá');
            return;
        }

        const orderCode = selectedOrderCode;

        if (!orderCode) {
            toast.warn('Không xác định được đơn hàng để đánh giá');
            return;
        }

        const config = {
            headers: { Authorization: `Bearer ${token}` },
        };

        apiMember.post(`/product/${id}/reviews`, {
            product_id: id,
            rating: currentRating,
            comment: commentText,
            order_code: selectedOrderCode,
        }, config)
            .then((res) => {
                toast.success('Đánh giá thành công!');
                setCommentText('');
                setCurrentRating(0);
                setShowReviewForm(false);

                // reload reviews
                apiMember
                    .get(`/product/${id}/reviews`)
                    .then((r) => {
                        const data = r.data.data || [];
                        setReviews(data);
                        setFilteredReviews(data);
                    });
            })
            .catch((err) => {
                console.error(err);
                if (err.response?.status === 403) {
                    toast.error('Bạn cần mua và nhận hàng trước khi đánh giá');
                } else if (err.response?.status === 409) {
                    toast.error('Bạn đã đánh giá đơn hàng này rồi');
                } else {
                    toast.error(err.response?.data?.error || 'Lỗi khi gửi đánh giá');
                }
            });
    };

    const renderStars = (rating) => {
        return [...Array(5)].map((_, index) => {
            const fullStar = index + 1 <= rating;
            const halfStar = index + 0.5 === rating;

            let iconClass = 'fa fa-star-o';
            let color = '#ccc';

            if (rating >= index + 1) {
                iconClass = 'fa fa-star';
                color = '#FE980F';
            } else if (rating >= index + 0.5) {
                iconClass = 'fa fa-star-half-o';
                color = '#FE980F';
            }

            return <i key={index} className={iconClass} style={{ color: color, marginRight: '2px' }}></i>;
        });
    };

    const totalReviews = reviews.length;
    const avgRating = totalReviews > 0 ? (reviews.reduce((sum, r) => sum + r.rating, 0) / totalReviews).toFixed(1) : 0;

    const starCounts = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    reviews.forEach((r) => {
        if (starCounts[r.rating] !== undefined) starCounts[r.rating]++;
    });
    function renderData() {
        let avatar = [];
        if (input?.image) {
            try {
                avatar = typeof input.image === 'string' ? JSON.parse(input.image) : input.image;
            } catch (e) {
                avatar = Array.isArray(input.image) ? input.image : [input.image];
            }
        }

        const is_on_sale = input.sale > 0;
        const original_price = input.price;
        const sale_percent = input.sale;
        const new_price = original_price * (1 - sale_percent / 100);

        return (
            <div className="product-details-new">
                <div className="col-sm-5 product-image-gallery">
                    <div className="main-image">
                        <img src={`http://localhost:8000/${selectedImg}`} alt="Main product" />
                    </div>
                    <div className="thumbnail-list">
                        {avatar.map((value, index) => {
                            return (
                                <img
                                    key={index}
                                    src={`http://localhost:8000/${value}`}
                                    alt={`Thumbnail ${index + 1}`}
                                    onClick={() => SetselectedImgTop(value)}
                                    className={selectedImg === value ? 'active' : ''}
                                />
                            );
                        })}
                    </div>
                </div>

                <div className="col-sm-7 product-info-right">
                    <h2 className="product-title">{input.name}</h2>

                    <p className="product-summary">
                        Thương hiệu: <strong>{input.id_brand?.name || input.company || 'Đang cập nhật'}</strong>
                        <br />
                    </p>

                    <div className="price-box">
                        {is_on_sale ? (
                            <>
                                <span className="price-new">{formatPrice(new_price)}</span>
                                <span className="price-old">{formatPrice(original_price)}</span>
                            </>
                        ) : (
                            <span className="price-new">{formatPrice(original_price)}</span>
                        )}
                    </div>

                    <div className="quantity-container">
                        <div className="quantity-label">Số Lượng:</div>
                        <div className="quantity-control">
                            <button
                                className="quantity-btn"
                                onClick={() => DeleteQuantity()}
                                disabled={quantity <= 1}
                                style={{
                                    opacity: quantity <= 1 ? 0.5 : 1,
                                    cursor: quantity <= 1 ? 'not-allowed' : 'pointer',
                                }}
                            >
                                −
                            </button>
                            <input type="text" className="quantity-input" value={quantity} readOnly />
                            <button
                                className="quantity-btn"
                                onClick={() => AddQuantity()}
                                disabled={quantity >= (input.quantity || input.quality)}
                                style={{
                                    opacity: quantity >= (input.quantity || input.quality) ? 0.5 : 1,
                                    cursor: quantity >= (input.quantity || input.quality) ? 'not-allowed' : 'pointer',
                                }}
                            >
                                +
                            </button>
                        </div>
                        <div
                            className="quantity-available"
                            style={{ marginLeft: '15px', display: 'flex', alignItems: 'center', color: '#757575' }}
                        >
                            {(input.quantity || input.quality) > 0 ? (
                                `${input.quantity || input.quality} sản phẩm có sẵn`
                            ) : (
                                <span style={{ color: '#d70018', fontWeight: 'bold' }}>Hết hàng</span>
                            )}
                        </div>
                    </div>

                    <div>
                        <button
                            className="add-to-cart-btn"
                            onClick={() => AddProductToCart()}
                            disabled={input.quantity <= 0}
                            style={{
                                opacity: input.quantity <= 0 ? 0.6 : 1,
                                cursor: input.quantity <= 0 ? 'not-allowed' : 'pointer',
                                background: input.quantity <= 0 ? '#ccc' : 'var(--primary-color)',
                            }}
                        >
                            <i className="fa fa-shopping-cart"></i>
                            {input.quantity <= 0 ? 'Hết hàng' : 'Thêm vào giỏ'}
                        </button>
                        <button className="buy-by-phone-btn">
                            <i className="fa fa-phone"></i>
                            Gọi đặt mua
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div>
            {input.name && (
                <Breadcrumb
                    items={[
                        { label: 'Sản Phẩm', path: '/member/home' },
                        ...(input.id_category
                            ? [{ label: input.id_category.name, path: `/member/category/${input.id_category.id}` }]
                            : []),
                        { label: input.name },
                    ]}
                />
            )}
            {renderData()}

            <div className="category-tab shop-details-tab">
                <div className="col-sm-12">
                    <ul className="nav nav-tabs">
                        <li className="active">
                            <a href="#details" data-toggle="tab">
                                Thông số kỹ thuật
                            </a>
                        </li>
                        <li>
                            <a href="#companyprofile" data-toggle="tab">
                                Mô tả sản phẩm
                            </a>
                        </li>
                        <li>
                            <a href="#reviews" data-toggle="tab">
                                Đánh giá
                            </a>
                        </li>
                    </ul>
                </div>
                <div className="tab-content">
                    <div className="tab-pane fade active in" id="details">
                        <h4>Thông số sản phẩm</h4>
                        <table className="table">
                            <tbody>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <td>{input.name}</td>
                                </tr>
                                <tr>
                                    <th>Thương hiệu</th>
                                    <td>
                                        {input.id_brand?.name ||
                                            input.brand?.name ||
                                            input.company?.name ||
                                            'Đang cập nhật'}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Chi tiết</th>
                                    <td style={{ whiteSpace: 'pre-wrap' }}>{input.detail}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div className="tab-pane fade" id="companyprofile">
                        <h4>Mô tả chi tiết sản phẩm</h4>
                        <p style={{ whiteSpace: 'pre-wrap' }}>{input.detail}</p>
                    </div>

                    <div className="tab-pane fade" id="reviews">
                        {/* Rating Dashboard */}
                        <div
                            className="rating-dashboard"
                            style={{
                                marginBottom: '30px',
                                border: '1px solid #eee',
                                padding: '20px',
                                borderRadius: '8px',
                                display: 'flex',
                                flexWrap: 'wrap',
                            }}
                        >
                            <div
                                className="rating-summary col-sm-5"
                                style={{ borderRight: '1px solid #eee', textAlign: 'center', paddingRight: '20px' }}
                            >
                                <div
                                    className="avg-score"
                                    style={{ fontSize: '48px', fontWeight: 'bold', color: '#FE980F', lineHeight: '1' }}
                                >
                                    {avgRating}
                                    <span style={{ fontSize: '24px', color: '#999' }}>/5</span>
                                </div>
                                <div className="avg-stars" style={{ fontSize: '18px', margin: '10px 0' }}>
                                    {renderStars(parseFloat(avgRating))}
                                </div>
                                <div className="total-rating-count" style={{ color: '#666', marginBottom: '15px' }}>
                                    {totalReviews} lượt đánh giá
                                </div>
                                {completedOrders.length > 0 && (
                                    <div style={{ marginBottom: '15px' }}>
                                        <label style={{ fontWeight: 'bold' }}>Chọn đơn hàng để đánh giá:</label>
                                        <select
                                            value={selectedOrderCode}
                                            onChange={(e) => setSelectedOrderCode(e.target.value)}
                                            style={{ marginLeft: '10px', padding: '5px', borderRadius: '4px', border: '1px solid #ddd' }}
                                        >
                                            {completedOrders.map((order) => (
                                                <option key={order.order_code} value={order.order_code}>
                                                    {order.order_code} - {new Date(order.created_at).toLocaleDateString()}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                )}
                                <button
                                    className="btn btn-primary"
                                    style={{
                                        background: canReview ? '#d70018' : '#ccc',
                                        border: 'none',
                                        padding: '10px 30px',
                                        fontWeight: 'bold',
                                        cursor: canReview ? 'pointer' : 'not-allowed',
                                    }}
                                    disabled={!canReview}
                                    onClick={() => {
                                        if (!canReview) return;
                                        const token = localStorage.getItem('token');
                                        apiMember
                                            .get(`/product/${id}/completed-orders`, {
                                                headers: { Authorization: `Bearer ${token}` },
                                            })
                                            .then((res) => {
                                                setCompletedOrders(res.data.data || []);
                                                if (res.data.data?.length > 0) {
                                                    setSelectedOrderCode(res.data.data[0].order_code);
                                                }
                                                setShowReviewForm(!showReviewForm);
                                            })
                                            .catch(() => toast.error('Không thể lấy đơn hàng để đánh giá'));
                                    }}
                                >
                                    Viết đánh giá
                                </button>
                            </div>

                            <div className="rating-bars col-sm-7" style={{ paddingLeft: '30px' }}>
                                {[5, 4, 3, 2, 1].map((star) => {
                                    const count = starCounts[star];
                                    const percent = totalReviews > 0 ? (count / totalReviews) * 100 : 0;
                                    return (
                                        <div
                                            key={star}
                                            className="rating-bar-row"
                                            style={{ display: 'flex', alignItems: 'center', marginBottom: '8px' }}
                                        >
                                            <span style={{ width: '20px', fontWeight: 'bold' }}>
                                                {star}{' '}
                                                <i
                                                    className="fa fa-star"
                                                    style={{ fontSize: '10px', color: '#ccc' }}
                                                ></i>
                                            </span>
                                            <div
                                                className="progress"
                                                style={{
                                                    flex: 1,
                                                    height: '8px',
                                                    margin: '0 10px',
                                                    background: '#eee',
                                                    borderRadius: '4px',
                                                }}
                                            >
                                                <div
                                                    className="progress-bar"
                                                    style={{
                                                        width: `${percent}%`,
                                                        background: '#d70018',
                                                        height: '100%',
                                                        borderRadius: '4px',
                                                    }}
                                                ></div>
                                            </div>
                                            <span
                                                style={{
                                                    width: '70px',
                                                    fontSize: '12px',
                                                    color: '#666',
                                                    textAlign: 'right',
                                                }}
                                            >
                                                {count} đánh giá
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        {!canReview && reviewMessage && (
                            <div
                                style={{
                                    marginTop: '20px',
                                    padding: '20px',
                                    border: '1px dashed #d70018',
                                    borderRadius: '8px',
                                    background: '#fff5f5',
                                    color: '#d70018',
                                    fontWeight: 'bold',
                                    textAlign: 'center',
                                }}
                            >
                                {reviewMessage}
                            </div>
                        )}

                        {showReviewForm && (
                            <div
                                className="replay-box"
                                style={{
                                    marginTop: '20px',
                                    border: '1px solid #eee',
                                    padding: '20px',
                                    borderRadius: '8px',
                                    background: '#f9f9f9',
                                }}
                            >
                                <div className="row">
                                    <div className="col-sm-12">
                                        <h2 style={{ marginTop: 0 }}>Viết đánh giá của bạn</h2>
                                        <div className="text-area">
                                            <div className="blank-arrow">
                                                <label>Đánh giá:</label>
                                            </div>

                                            <div style={{ fontSize: '24px', marginBottom: '15px', cursor: 'pointer' }}>
                                                {[...Array(5)].map((_, index) => {
                                                    const starValue = index + 1;
                                                    return (
                                                        <i
                                                            key={index}
                                                            className="fa fa-star"
                                                            style={{
                                                                color:
                                                                    starValue <= (hoverRating || currentRating)
                                                                        ? '#FE980F'
                                                                        : '#ccc',
                                                                marginRight: '5px',
                                                            }}
                                                            onClick={() => setCurrentRating(starValue)}
                                                            onMouseEnter={() => setHoverRating(starValue)}
                                                            onMouseLeave={() => setHoverRating(0)}
                                                        ></i>
                                                    );
                                                })}
                                                <span style={{ fontSize: '14px', color: '#666', marginLeft: '10px' }}>
                                                    {currentRating ? '' : '(Chọn số sao)'}
                                                </span>
                                            </div>

                                            <textarea
                                                name="message"
                                                rows="4"
                                                placeholder="Mời bạn chia sẻ cảm nhận về sản phẩm..."
                                                value={commentText}
                                                onChange={(e) => setCommentText(e.target.value)}
                                                style={{
                                                    marginBottom: '15px',
                                                    width: '100%',
                                                    padding: '10px',
                                                    borderRadius: '4px',
                                                    border: '1px solid #ddd',
                                                }}
                                            ></textarea>

                                            <button
                                                type="button"
                                                className="btn btn-primary"
                                                style={{ background: '#d70018', border: 'none', padding: '10px 30px' }}
                                                onClick={handleReviewSubmit}
                                            >
                                                Gửi đánh giá
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        <div
                            className="review-filters"
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '10px',
                                margin: '20px 0',
                                flexWrap: 'wrap',
                            }}
                        >
                            <span style={{ fontWeight: 'bold', fontSize: '16px' }}>Lọc đánh giá theo:</span>
                            {['all', '5', '4', '3', '2', '1'].map((type) => {
                                let label = '';
                                if (type === 'all') label = 'Tất cả';
                                else label = `${type} sao`;

                                const isActive = filterType === type;
                                return (
                                    <button
                                        key={type}
                                        onClick={() => setFilterType(type)}
                                        style={{
                                            border: isActive ? '1px solid #d70018' : '1px solid #ddd',
                                            background: isActive ? '#fff3f3' : '#fff',
                                            color: isActive ? '#d70018' : '#333',
                                            padding: '5px 15px',
                                            borderRadius: '20px',
                                            cursor: 'pointer',
                                            outline: 'none',
                                        }}
                                    >
                                        {label}
                                    </button>
                                );
                            })}
                        </div>

                        <div className="response-area">
                            <ul className="media-list">
                                {filteredReviews.length > 0 ? (
                                    filteredReviews.map((review, index) => {
                                        const date = new Date(review.createdAt).toLocaleDateString();
                                        return (
                                            <li
                                                className="media"
                                                key={index}
                                                style={{
                                                    marginBottom: '20px',
                                                    borderBottom: '1px solid #eee',
                                                    paddingBottom: '20px',
                                                }}
                                            >
                                                <a className="pull-left" href="#">
                                                    <div
                                                        style={{
                                                            width: '50px',
                                                            height: '50px',
                                                            borderRadius: '50%',
                                                            background: '#ccc',
                                                            display: 'flex',
                                                            alignItems: 'center',
                                                            justifyContent: 'center',
                                                            color: '#fff',
                                                            fontWeight: 'bold',
                                                            fontSize: '20px',
                                                            overflow: 'hidden',
                                                        }}
                                                    >
                                                        {review.avatar_user ? (
                                                            <img
                                                                src={`http://localhost:3001/${review.avatar_user}`}
                                                                alt=""
                                                                style={{
                                                                    width: '100%',
                                                                    height: '100%',
                                                                    objectFit: 'cover',
                                                                }}
                                                            />
                                                        ) : review.name_user ? (
                                                            review.name_user.charAt(0).toUpperCase()
                                                        ) : (
                                                            'U'
                                                        )}
                                                    </div>
                                                </a>
                                                <div className="media-body">
                                                    <h4
                                                        className="media-heading"
                                                        style={{ fontSize: '16px', fontWeight: 'bold' }}
                                                    >
                                                        {review.name_user || 'Khách hàng'}
                                                    </h4>
                                                    <div style={{ margin: '5px 0' }}>
                                                        {renderStars(review.rating)}
                                                        <span
                                                            style={{
                                                                fontSize: '12px',
                                                                color: '#999',
                                                                marginLeft: '10px',
                                                            }}
                                                        >
                                                            <i className="fa fa-clock-o"></i> {date}
                                                        </span>
                                                    </div>
                                                    <p style={{ marginTop: '10px' }}>{review.comment}</p>
                                                </div>
                                            </li>
                                        );
                                    })
                                ) : (
                                    <p>Chưa có đánh giá nào phù hợp bộ lọc.</p>
                                )}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
export default ProductDetail;
