import classNames from 'classnames/bind';
import { FaSearch, FaSort, FaUserCog } from 'react-icons/fa';
import styles from './Member.module.scss';
import { useEffect, useState } from 'react';
import { toast } from 'react-toastify';
import Swal from 'sweetalert2';
import apiAdmin from '../../../API/apiAdmin';

const cx = classNames.bind(styles);

function Member() {
    const [users, setUsers] = useState([]);
    const [search, setSearch] = useState('');

    useEffect(() => {
        apiAdmin.get('/member').then((res) => {
            setUsers(res.data.data);
        });
    }, []);
    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            timeZone: 'Asia/Ho_Chi_Minh',
        });
    };

    const filteredUsers = users.filter(
        (user) =>
            user.name.toLowerCase().includes(search.toLowerCase()) ||
            user.email.toLowerCase().includes(search.toLowerCase()),
    );
    const handleToggleStatus = async (id, currentStatus) => {
        try {
            const isLocking = currentStatus === 1;

            const result = await Swal.fire({
                title: isLocking ? 'Khóa tài khoản' : 'Mở khóa tài khoản',
                text: isLocking
                    ? 'Bạn có chắc chắn muốn khóa tài khoản này?'
                    : 'Bạn có chắc chắn muốn mở khóa tài khoản này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: isLocking ? 'Khóa' : 'Mở khóa',
                cancelButtonText: 'Hủy',
            });

            if (result.isConfirmed) {
                const res = await apiAdmin.patch(`/member/${id}`);

                const newStatus = res.data.data.is_active ? 1 : 0;

                setUsers((prev) => prev.map((user) => (user.id === id ? { ...user, is_active: newStatus } : user)));

                toast.success(res.data.message);
            }
        } catch (error) {
            console.error(error);
        }
    };
    return (
        <div className={cx('member')}>
            <div className={cx('header')}>
                <h2>
                    <FaUserCog className={cx('icon')} />
                    Quản lý người dùng
                </h2>

                <div className={cx('search-box')}>
                    <FaSearch className={cx('search-icon')} />
                    <input
                        type="text"
                        placeholder="Tìm theo tên hoặc email..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                </div>
            </div>

            <div className="table-responsive">
                <table className="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredUsers.length > 0 ? (
                            filteredUsers.map((user, index) => (
                                <tr key={user.id}>
                                    <td>{index + 1}</td>
                                    <td>{user.name}</td>
                                    <td>{user.email}</td>
                                    <td>Member</td>
                                    <td>
                                        <span
                                            className={cx(user.is_active === 1 ? 'status-active' : 'status-inactive')}
                                        >
                                            {user.is_active === 1 ? 'Hoạt động' : 'Bị khóa'}
                                        </span>
                                    </td>
                                    <td>{formatDate(user.created_at)}</td>
                                    <td>
                                        <button
                                            className={cx(
                                                'btn-action',
                                                user.is_active === 1 ? 'btn-lock' : 'btn-unlock',
                                            )}
                                            onClick={() => handleToggleStatus(user.id, user.is_active)}
                                        >
                                            {user.is_active === 1 ? 'Khóa' : 'Mở khóa'}
                                        </button>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="7" style={{ textAlign: 'center' }}>
                                    Không tìm thấy người dùng
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default Member;
