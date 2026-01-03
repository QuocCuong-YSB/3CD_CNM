import axios from 'axios';
import { toast } from 'react-toastify';
const apiAdmin = axios.create({
    baseURL: `http://localhost:8000/api/admin`,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

apiAdmin.interceptors.request.use((config) => {
    const token = localStorage.getItem('adminToken');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

apiAdmin.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err.response?.status === 401) {
            localStorage.clear();
            toast.error('Bạn chưa đăng nhập');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 1500);
        }
        if (err.response?.status === 403) {
            localStorage.clear();
            toast.error('Bạn không có quyền truy cập');
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 1500);
        }

        return Promise.reject(err);
    },
);
export default apiAdmin;
