import { useEffect, useState } from 'react';
import styles from './HeaderAdmin.module.scss';
import classNames from 'classnames/bind';
import apiAdmin from '../../../API/apiAdmin';

const cx = classNames.bind(styles);
const HeaderAdmin = () => {
    const [user, setUser] = useState({});
    const idUser = localStorage.getItem('adminId');
    useEffect(() => {
        if (idUser) {
            apiAdmin
                .get(`/user/${idUser}`)
                .then((res) => setUser(res.data))
                .catch((err) => console.log(err));
        }
    }, [idUser]);

    const getAvatarSrc = () => {
        if (!user || !user.avatar) {
            return 'http://localhost:8000/no-image.png';
        }

        if (typeof user.avatar === 'string' && user.avatar.startsWith('http')) {
            return user.avatar;
        }

        try {
            const avatar = typeof user.avatar === 'string' ? JSON.parse(user.avatar) : user.avatar;
            if (Array.isArray(avatar) && avatar.length > 0) {
                return `http://localhost:8000/${avatar[0]}`;
            }
            return `http://localhost:8000/${user.avatar}`;
        } catch (error) {
            return `http://localhost:8000/${user.avatar}`;
        }
    };

    return (
        <header className={cx('wrapper')}>
            <div className={cx('header-left')}>
                <img className={cx('logo')} src="http://localhost:3000/images/home/logo.png" alt="" />
            </div>
            <div className={cx('header-right')}>
                <img className={cx('avatar')} src={getAvatarSrc()} alt="" />
                <p className={cx('user-name')}>{user.name || 'Admin'}</p>
            </div>
        </header>
    );
};

export default HeaderAdmin;
