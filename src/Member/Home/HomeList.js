import { useContext, useEffect, useState } from 'react';
import './HomeList.css';
import apiMember from '../../API/apiMember';
import { Link, useParams, useLocation } from 'react-router-dom';
import MemberCartContext from '../../Context/MemberCartContext';
import { useDispatch, useSelector } from 'react-redux';
import { addQuantityCart } from '../../features/cart/Cart';
import { toast } from 'react-toastify';
import Loading from '../../component/Loading';

function formatPrice(price) {
    if (!price) return '';
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(price);
}

function HomeList() {
    const dispath = useDispatch();
    let totallocal = useContext(MemberCartContext);
    const [input, SetInput] = useState([]);
    const [visibleCount, setVisibleCount] = useState(12);
    const [sliderIndex, setSliderIndex] = useState(0);
    const [sliderProducts, setSliderProducts] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const { fetchCartCount } = useContext(MemberCartContext);

    const { categoryId } = useParams();
    const location = useLocation();
    const search = useSelector((state) => state.cart.search);

    function getAllProduct() {
        apiMember
            .get('/product')
            .then((res) => {
                SetInput(Array.isArray(res.data.data) ? res.data.data : []);
            })
            .catch((err) => {
                console.log(err);
                SetInput([]);
            });
    }

    useEffect(() => {
        setVisibleCount(12);

        const fetchFilteredProducts = (filters) => {
            let query = '/search/product?';
            if (filters.name) query += `name=${filters.name}&`;
            if (filters.minPrice) query += `minPrice=${filters.minPrice}&`;
            if (filters.maxPrice) query += `maxPrice=${filters.maxPrice}&`;
            if (filters.brand) query += `brand=${filters.brand}&`;
            if (filters.category) query += `category=${filters.category}&`;

            apiMember
                .get(query)
                .then((res) => {
                    const data = Array.isArray(res.data.data) ? res.data.data : [];
                    SetInput(data);
                    if (!filters.name && !filters.brand && !filters.category && !filters.minPrice) {
                        setSliderProducts(data);
                    }
                })
                .catch((err) => {
                    console.error(err);
                    SetInput([]);
                })
                .finally(() => {
                    setIsLoading(false);
                });
        };

        const handleFilterEvent = (e) => {
            fetchFilteredProducts(e.detail);
        };

        window.addEventListener('product-filter', handleFilterEvent);

        if (search) {
            fetchFilteredProducts({ name: search });
        } else if (categoryId) {
            setIsLoading(true);
            fetchFilteredProducts({ category: categoryId });
        } else {
            setIsLoading(true);
            apiMember
                .get('/product')
                .then((res) => {
                    const data = Array.isArray(res.data.data) ? res.data.data : [];
                    SetInput(data);
                    setSliderProducts(data);
                })
                .catch((err) => {
                    console.log(err);
                    SetInput([]);
                })
                .finally(() => {
                    setIsLoading(false);
                });
        }

        const interval = setInterval(() => {
            setSliderIndex((prev) => (prev + 1) % 6);
        }, 3000);

        return () => {
            window.removeEventListener('product-filter', handleFilterEvent);
            clearInterval(interval);
        };
    }, [categoryId, search, location.pathname]);

    const [isLoadingMore, setIsLoadingMore] = useState(false);

    const handleLoadMore = () => {
        setIsLoadingMore(true);
        setTimeout(() => {
            setVisibleCount((prev) => prev + 12);
            setIsLoadingMore(false);
        }, 800);
    };

    async function AddCart(product) {
        const token = localStorage.getItem('token');
        if (!token) {
            toast.warn('Vui lòng đăng nhập');
            return;
        }

        if (product.quantity <= 0) {
            toast.error('Sản phẩm này đã hết hàng');
            return;
        }

        try {
            await apiMember.post('/cart', {
                product_id: product.id,
                quantity: 1
            });

            dispath(addQuantityCart(product.id));
            fetchCartCount && fetchCartCount();

            toast.success('Thêm sản phẩm vào giỏ hàng thành công');
        } catch (err) {
            console.error(err.response?.data || err.message);
            toast.error('Thêm sản phẩm vào giỏ thất bại');
        }
    }

    function renderData() {
        if (!Array.isArray(input)) return null;

        if (input.length === 0) {
            return (
                <div className="no-products-message">
                    <i className="fa fa-search"></i>
                    <p>Rất tiếc, chúng tôi không tìm thấy sản phẩm nào phù hợp với yêu cầu của bạn.</p>
                </div>
            );
        }

        const currentItems = input.slice(0, visibleCount);

        return currentItems.map((value, index) => {
            const avatar = Array.isArray(value.image) && value.image.length > 0
                ? value.image
                : ['default.png'];

            const is_on_sale = value.sale > 0;
            const original_price = value.price;
            const sale_percent = value.sale;
            const new_price = original_price * (1 - sale_percent / 100);

            return (
                <div className="col-sm-4" key={index}>
                    <div className="product-card-new">
                        {is_on_sale && <div className="sale-badge">-{sale_percent}%</div>}

                        <Link to={`/member/home/product/detail/${value.id}`}>
                            <div className="product-image">
                                <img
                                    src={`http://localhost:8000/${avatar[0]}`}
                                    alt={value.name || 'Product'}
                                    onError={(e) => { e.target.src = '/images/default.png'; }}
                                />
                            </div>
                        </Link>

                        <div className="product-info">
                            <div>
                                <Link to={`/member/home/product/detail/${value.id}`} className="product-name">
                                    {value.name}
                                </Link>

                                <div className="price-container">
                                    {is_on_sale ? (
                                        <>
                                            <span className="price-new">{formatPrice(new_price)}</span>
                                            <span className="price-old">{formatPrice(original_price)}</span>
                                        </>
                                    ) : (
                                        <>
                                            <span className="price-new">{formatPrice(original_price)}</span>
                                        </>
                                    )}
                                </div>
                            </div>

                            <button onClick={() => AddCart(value)} className="buy-button">
                                <i className="fa fa-shopping-cart" />
                                Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                </div>
            );
        });
    }

    function renderHeroSlider() {
        if (!Array.isArray(sliderProducts)) return null;

        const saleProducts = sliderProducts
            .filter((p) => p.sale > 0)
            .sort((a, b) => b.sale - a.sale)
            .slice(0, 6);

        if (saleProducts.length === 0) return null;

        return (
            <div className="hero-slider-container">
                {saleProducts.map((value, index) => {

                    const avatar = Array.isArray(value.image) && value.image.length > 0
                        ? value.image
                        : ['default.png'];

                    const is_on_sale = value.sale > 0;
                    const original_price = value.price || 0;
                    const sale_percent = value.sale || 0;
                    const new_price = is_on_sale
                        ? original_price * (1 - sale_percent / 100)
                        : original_price;

                    let positionClass = 'card-hidden';
                    const diff = (index - sliderIndex + 6) % 6;

                    if (diff === 0) positionClass = 'card-center';
                    else if (diff === 1) positionClass = 'card-right';
                    else if (diff === 2) positionClass = 'card-far-right';
                    else if (diff === 5) positionClass = 'card-left';
                    else if (diff === 4) positionClass = 'card-far-left';

                    return (
                        <div className={`hero-product-card ${positionClass}`} key={index}>
                            {is_on_sale && <div className="sale-badge">-{sale_percent}%</div>}

                            <Link to={`/member/home/product/detail/${value.id}`}>
                                <div className="product-image">
                                    <img
                                        src={`http://localhost:8000/${avatar[0]}`}
                                        alt={value.name || 'Product'}
                                        onError={(e) => { e.target.src = '/images/default.png'; }}
                                    />
                                </div>
                            </Link>

                            <div className="product-info">
                                <Link to={`/member/home/product/detail/${value.id}`} className="product-name">
                                    {value.name || 'Unknown Product'}
                                </Link>
                                <div className="price-container">
                                    <span className="price-new">{formatPrice(new_price)}</span>
                                    <span className="price-old">{formatPrice(original_price)}</span>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        );
    }

    return (
        <div>
            {isLoading && <Loading />}
            <div className="hero-section">{renderHeroSlider()}</div>

            <div className="features_items" id="products-grid">
                <h2 className="title text-center">Sản Phẩm Nổi Bật</h2>

                {renderData()}
            </div>

            {visibleCount < input.length && (
                <div style={{ textAlign: 'center', width: '100%', marginTop: '20px', clear: 'both' }}>
                    <button
                        onClick={handleLoadMore}
                        className="load-more-btn"
                        disabled={isLoadingMore}
                    >
                        {isLoadingMore ? (
                            <>
                                <i className="fa fa-spinner fa-spin" style={{ marginRight: '10px' }}></i>
                                ĐANG TẢI...
                            </>
                        ) : (
                            'Hiển thị thêm sản phẩm'
                        )}
                    </button>
                </div>
            )}
        </div>
    );
}
export default HomeList;
