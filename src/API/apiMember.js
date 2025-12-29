import axios from 'axios';
import { toast } from 'react-toastify';

const apiMember = axios.create({
    baseURL: `http://localhost:8000/api/member`,
});
apiMember.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

apiMember.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err.response?.status === 401) {
            localStorage.clear();
            toast.error('Bạn chưa đăng nhập');
            setTimeout(() => {
                window.location.href = '/';
            }, 1500);
        }
        if (err.response?.status === 403) {
            localStorage.clear();
            toast.error('Bạn không có quyền truy cập');
            setTimeout(() => {
                window.location.href = '/';
            }, 1500);
        }

        return Promise.reject(err);
    },
);
export default apiMember;
