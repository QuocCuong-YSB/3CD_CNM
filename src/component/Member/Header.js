import { useContext, useState, useEffect } from 'react';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { Link, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { useDispatch } from 'react-redux';
import { setSearch } from '../../features/cart/Cart';
import './Header.css';
import apiMember from '../../API/apiMember';
import MemberCartContext from '../../Context/MemberCartContext';

function Header() {
    const { cart, fetchCartCount, resetCart } = useContext(MemberCartContext);
    const navigate = useNavigate();
    const dispath = useDispatch();
    const [categories, setCategories] = useState([]);

    useEffect(() => {
        apiMember
            .get('/category')
            .then((res) => {
                if (Array.isArray(res.data.data)) {
                    setCategories(res.data.data);
                }
            })
            .catch((err) => {
                console.error('Lỗi khi tải category cho header:', err);
            });
    }, []);

    const [keyword, setKeyword] = useState('');
    const [suggestions, setSuggestions] = useState([]);

    useEffect(() => {
        if (!keyword.trim()) {
            setSuggestions([]);
            return;
        }

        const delayDebounceFn = setTimeout(() => {
            apiMember
                .get('/search/product?name=' + keyword)
                .then((res) => {
                    if (Array.isArray(res.data.data)) {
                        setSuggestions(res.data.data);
                    }
                })
                .catch((err) => {
                    console.error(err);
                });
        }, 300);

        return () => clearTimeout(delayDebounceFn);
    }, [keyword]);

    function handleSearchChange(e) {
        let value = e.target.value;
        setKeyword(value);
    }

    function handleSearchSubmit(e) {
        e.preventDefault();
        dispath(setSearch(keyword));
        setSuggestions([]);
    }

    function handleSuggestionClick(product) {
        setKeyword('');
        setSuggestions([]);
        dispath(setSearch(''));
        navigate(`/member/home/product/detail/${product.id}`);
    }

    const [user, setUser] = useState(localStorage.getItem('user'));

    useEffect(() => {
        const handleUserUpdate = () => {
            setUser(JSON.parse(localStorage.getItem('user')));
        };

        window.addEventListener('user-updated', handleUserUpdate);

        window.addEventListener('storage', handleUserUpdate);

        return () => {
            window.removeEventListener('user-updated', handleUserUpdate);
            window.removeEventListener('storage', handleUserUpdate);
        };
    }, []);

    useEffect(() => {
        const handleUserUpdate = () => {
            const token = localStorage.getItem('token');
            if (token) {
                fetchCartCount && fetchCartCount();
            }
        };

        window.addEventListener('user-updated', handleUserUpdate);

        return () => {
            window.removeEventListener('user-updated', handleUserUpdate);
        };
    }, []);

    function HandleCart() {
        navigate('/member/home/cart');
    }
    function HandleHome() {
        navigate('/member/home');
    }
    function Logout() {
        apiMember
            .post('/logout')
            .then((res) => {
                toast.success(res.data.message);
            })
            .catch((err) => {
                console.error(err);
            })
            .finally(() => {
                localStorage.removeItem('token');
                localStorage.removeItem('tokenReferesh');
                localStorage.removeItem('user');
                localStorage.removeItem('IdUser');
                resetCart();
                setUser(null);
                navigate('/');
            });
    }
    function Login() {
        navigate('/');
    }
    function Account() {
        navigate('/member/account/update');
    }

    return (
        <header id="header">
            <div className="header-main">
                <div className="container">
                    <div className="row" style={{ alignItems: 'center' }}>
                        <div className="col-md-3">
                            <div className="logo-container">
                                <a href="#" onClick={HandleHome}>
                                    <img src="/images/home/logo.png" alt="Logo" />
                                </a>
                            </div>
                        </div>

                        <div className="col-md-9">
                            <div className="header-right-wrapper">
                                <div className="search-wrapper">
                                    <form className="search-container" onSubmit={handleSearchSubmit}>
                                        <input
                                            type="text"
                                            placeholder="Bạn cần tìm gì hôm nay..."
                                            value={keyword}
                                            onChange={handleSearchChange}
                                            onBlur={() => setTimeout(() => setSuggestions([]), 200)}
                                        />
                                        <button type="submit">
                                            <i className="fa fa-search"></i>
                                        </button>
                                    </form>

                                    {suggestions.length > 0 && (
                                        <div className="search-suggestions">
                                            <ul>
                                                {suggestions.map((product) => {
                                                    let avatar = '';
                                                    try {
                                                        avatar = product.image[0];
                                                    } catch (e) { }

                                                    return (
                                                        <li
                                                            key={product.id}
                                                            onMouseDown={() => handleSuggestionClick(product)}
                                                        >
                                                            <img
                                                                src={`http://localhost:8000/${avatar}`}
                                                                alt={product.name}
                                                            />
                                                            <span className="suggestion-name">{product.name}</span>
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                        </div>
                                    )}
                                </div>

                                <div className="header-info">
                                    <span>Tư vấn bán hàng:</span>
                                    <span className="hotline">0123.456.789</span>
                                </div>

                                <div className="header-info-icons">
                                    <a href="#" onClick={() => HandleCart()}>
                                        <i className="fa fa-shopping-cart"></i>
                                        <span>Giỏ hàng ({cart})</span>
                                    </a>

                                    {user ? (
                                        <>
                                            <a href="#" onClick={() => Account()}>
                                                {(() => {
                                                    let userAvatar = null;
                                                    let isExternal = false;
                                                    if (user.avatar) {
                                                        if (user.avatar.startsWith('http')) {
                                                            userAvatar = user.avatar;
                                                            isExternal = true;
                                                        } else {
                                                            try {
                                                                const avatars = JSON.parse(user.avatar);
                                                                if (Array.isArray(avatars) && avatars.length > 0) {
                                                                    userAvatar = avatars[0];
                                                                }
                                                            } catch (e) {
                                                                userAvatar = user.avatar;
                                                            }
                                                        }
                                                    }
                                                    return userAvatar ? (
                                                        <img
                                                            src={
                                                                isExternal
                                                                    ? userAvatar
                                                                    : `http://localhost:8000/${userAvatar}`
                                                            }
                                                            alt="Avatar"
                                                            className="user-avatar"
                                                        />
                                                    ) : (
                                                        <i className="fa fa-user"></i>
                                                    );
                                                })()}
                                                <span>{user.name}</span>
                                            </a>
                                            <a href="#" onClick={() => Logout()}>
                                                <i className="fa fa-lock"></i>
                                                <span>Đăng xuất</span>
                                            </a>
                                        </>
                                    ) : (
                                        <a href="#" onClick={() => Login()}>
                                            <i className="fa fa-lock"></i>
                                            <span>Đăng nhập</span>
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="header-nav">
                <div className="container">
                    <div className="row">
                        <div className="col-md-12">
                            <div className="navbar-header">
                                <button
                                    type="button"
                                    className="navbar-toggle"
                                    data-toggle="collapse"
                                    data-target=".navbar-collapse"
                                >
                                    <span className="sr-only">Toggle navigation</span>
                                    <span className="icon-bar" />
                                    <span className="icon-bar" />
                                    <span className="icon-bar" />
                                </button>
                            </div>
                            <div className="mainmenu collapse navbar-collapse">
                                <ul className="nav navbar-nav">
                                    <li>
                                        <Link to="/member/home">Trang chủ</Link>
                                    </li>
                                    {categories.slice(0, 5).map((category) => (
                                        <li key={category.id}>
                                            <Link to={`/member/category/${category.id}`}>{category.name}</Link>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    );
}

export default Header;
